<?php
use loader\user;
use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Response;
use Workerman\Timer;
use Channel\Server;
use Channel\Client;

    class task {
        // 当前用户
        public static $user = null;
        // 服务项信息
        public static $ref = null;
        public static $name = '';
        public static $config = [];
        public static $thread = null;
        /**
         * 启动
         */
        public static function start( $name ) {
            router::$type = "service_{$name}";
            self::$user = new user( 'service' );
            // 加载服务项信息
            self::$name = $name;
            self::$config = config( "service.{$name}" );
            if ( empty( self::$config ) || empty( self::$name ) ) { return "The service configuration does not exist.\n"; }
            // 唤醒 Channel Core
            if ( $name === 'async' ) {
                self::channelStart();
            }
            // 开始运行服务
            self::workerman();
        }
        /**
         * 运行服务
         */
        private static function workerman() {
            Worker::$logFile = config( 'log.service_'.self::$name.'.file' );
            Worker::$stdoutFile = config( 'log.service_'.self::$name.'.file' );
            self::$ref = new Worker( self::$config['protocol']."://0.0.0.0:".self::$config['port'] ); // 协议
            self::$ref->name = self::$config['name']; // 名称
            self::$ref->user = self::$config['run']; // 运行身份
            self::$ref->count = self::$config['thread']; // 运行线程
            // 服务启动行为
            self::$ref->onWorkerStart = function( Worker $ref ) { router::start( 'onWorkerStart', [ $ref ] ); };
            // 用户连接成功建立
            self::$ref->onConnect = function( TcpConnection $conn ) { router::start( 'onConnect', [ $conn ] ); };
            // 核心流程 - 收到消息
            self::$ref->onMessage = function( TcpConnection $conn, $res ) {
                // 提前响应
                if ( self::$config['close'] === true ) { $conn->send( task::echoTo( 0, ['success'] ) ); }
                // 路由介入
                router::start( 'onMessage', [ $conn, $res ] );
            };
            // 用户关闭连接
            self::$ref->onClose = function( TcpConnection $conn ) { router::start( 'onClose', [ $conn ] ); };
            // 开始连接
            Worker::runAll();
        }
        /**
         * 唤醒 Channel Core
         * ---
         * return boolean 唤醒结果
         */
        public static function channelStart() {
            $port = config( 'app.channel' );
            $check = shell_exec( "netstat -tuln | grep :{$port}" );
            if ( $check && strpos( $check, 'php') !== false ) {
                return true;
            }
            new Server( '0.0.0.0', $port );
            return true;
        }
        /**
         * 连接到 Channel Core
         * ---
         * return boolean 连接结果
         */
        public static function channelLink() {
            try {
                Client::connect( '0.0.0.0', config( 'app.channel' ) );
                return true;
            }catch ( \Exception $e ) {
                task::log( getTime()." Channel connection failed: {$e->getMessage()}\n" );
                return false;
            }
            return false;
        }
        /**
         * 传递消息到 Channel Core
         * - $service string 分组
         * - $action string 执行动作
         * - $res array 传递数据
         * - $thread number 执行线程
         * ---
         * return null
         */
        public static function channel( $service, $action, $res, $thread = false ) {
            // 整理消息
            $data = [
                'action' => "system_{$action}",
                'res' => $res
            ];
            if ( is_numeric( $thread ) ) { $data['thread'] = $thread; }
            // 发送消息
            Client::publish( $service, $data );
        }
        public static $timer = []; // 计时器保存位置
        /**
         * 设置计时器
         * - $name string 设置的计时器
         * - $time number 间隔时间 （秒）
         * - $method function 执行操作
         * ---
         * return any
         */
        public static function addTimer( $name, $time, $method ) {
            if ( !is_numeric( $time ) || !is_callable( $method ) ) { return false; }
            self::$timer[$name] = Timer::add( $time, function()use ( $method ) {
                $method();
            });
            return self::$timer[$name];
        }
        /**
         * 删除计时器
         * - $name string 设置的计时器
         * ---
         * return boolean
         */
        public static function delTimer( $name ) {
            unset( self::$timer[$name] );
            return Timer::del( self::$timer[$name] );
        }
        /**
         * 写入 Log
         * - $content 错误内容
         * ---
         * return boolean 写入结果
         */
        public static function log( $content ) { return core::log( $content, 'service_'.self::$name ); }
        /* 群组数据 */
        public static $group = [];
        /**
         * 加入群组
         * - $name string 群组名称
         * - $conn object 用户连接
         * ---
         * return boolean 加入结果
         */
        public static function join( $name, $conn ) {
            if ( empty( $name ) || !is_object( $conn ) ) { return false; }
            $group = &self::getGroup( $name );
            if ( !is_array( $group ) ) { return false; }
            // 检查是否重复加入
            if ( !isset( $conn->group ) ) { $conn->group = []; }
            if ( in_array( $name, $conn->group ) ) { return true; }
            // 注入群组
            $conn->group[] = $name;
            $group[] = $conn;
            return true;
        }
        /**
         * 退出群组
         * - $name string 群组名称
         * - $conn object 用户连接
         * ---
         * return boolean 退出结果
         */
        public static function quit( $name, $conn ) {
            if ( empty( $name ) || !is_object( $conn ) ) { return false; }
            $group = &self::getGroup( $name );
            if ( !is_array( $group ) ) { return false; }
            // 检查并移除成员
            if ( ( $key = array_search( $conn, $group, true ) ) !== false ) {
                unset( $group[$key] );
                // 重置数组索引
                $group = array_values( $group );
                // 移除用户群组配置
                if ( ( $key = array_search( $name, $conn->group, true ) ) !== false ) {
                    unset( $conn->group[$key] );
                }
                return true;
            }
            return false;
        }
        /**
         * 发送到群组
         * - $name string 群组名称
         * - $res array 发送的数据
         * ---
         * return boolean 发送结果
         */
        public static function send( $name, $res ) {
            if ( empty( $name ) || empty( $res ) ) { return false; }
            $group = &self::getGroup( $name );
            if ( !is_array( $group ) ) { return false; }
            // 遍历并推送消息
            if ( empty( $group ) ) { return true; }
            foreach( $group as $conn ) {
                if ( is_object( $conn ) && !empty( $conn->method ) && is_callable( $conn->method['echo'] ) ) {
                    $conn->method['echo']( $res );
                }
            }
            return true;
        }
        /**
         * 发送到所有线程群组
         * - $name string 群组名称
         * - $res array 发送的数据
         * ---
         * return boolean 发送结果
         */
        public static function sendAll( $name, $res ) {
            self::channel( task::$name, "send", [ 'name' => $name, 'res' => $res ], true );
        }
        /**
         * 定位分组
         */
        private static function &getGroup( $name ) {
            $names = explode( '.', $name );
            $groupPointer = &self::$group;
            foreach ( $names as $groupName ) {
                if ( empty( $groupName ) ) { return $groupPointer; }
                if ( !isset( $groupPointer[$groupName] ) ) {
                    $groupPointer[$groupName] = [];
                }
                $groupPointer = &$groupPointer[$groupName];
            }
            return $groupPointer;
        }
        /**
         * 输出结果
         * - $type string 输出类型
         * - $state any 修改结果
         * ---
         * return string 结果
         */
        public static function result( $type, $state ) {
            return task::echo(
                !empty( $state ) || $state === '0' || $state === 0 ? 0 : 2,
                [!empty( $state ) ? 'true' : 'false',['type' => $type]]
            );
        }
        /**
         * 输出函数
         * - state number 回调状态
         * - $content string 回调内容
         * - $code number 响应代码
         * ---
         * return json
         */
        public static function echo( $state, $content, $code = 200 ) {
            return json_encode( [ $state, $content, $code ], JSON_UNESCAPED_UNICODE );
        }
        // 服务项重置回调函数
        public static function echoTo( $state, $content, $code = 200, $user = null ) {
            // 查询用户语言包
            $text = false;
            if ( !empty( $user ) && is_object( $user ) && !empty( $user->lang ) && $user->lang !== core::$textLang ) {
                if ( file_exists( "storage/lang/{$user->lang}.lang.php" ) ) { $text = import( "storage/lang/{$user->lang}.lang.php" ); }
            }
            $check = false;
            if ( is_array( $content ) && count( $content ) <= 2 ) { $check = t( $content[0], $content[1], $text ); }
            if ( !empty( $check ) ) { $content = $check; }
            $result = array();
            if ( is_array( $state ) && count( $state ) === 2 ) {
                $result['m'] = $state[1];
                $state = $state[0];
            }
            switch ( $state ) {
                case 0: $result['s'] = 'success'; break;
                case 1: $result['s'] = 'fail'; break;
                case 2: $result['s'] = 'error'; break;
                case 3: $result['s'] = 'warn'; break;
                default: $result['s'] = 'unknown'; break;
            }
            $code = !empty( $code ) && is_numeric( $code ) ? $code : 200;
            if ( is_numeric( $user->code ) ) { $code = $user->code; $user->code = false; }
            $result['c'] = $code;
            $result['t'] = time();
            $result['d'] = $content;
            if ( self::$config['protocol'] === 'http' ) {
                $response = new Response( $code, [ 'Content-Type' => 'application/json' ] );
                $response->withBody( json_encode( $result, JSON_UNESCAPED_UNICODE ));
                return $response;
            }
            return json_encode( $result, JSON_UNESCAPED_UNICODE );
        }
    }