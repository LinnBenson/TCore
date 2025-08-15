<?php
    /**
     * 请求构造器
     */
    namespace Support\Handler;

    use Support\Bootstrap;

    class Request {
        // 请求数据
        public $type, $id, $lang, $method, $target, $source, $header, $get, $post, $cookie, $file, $session, $ip, $router, $share;
        // 响应代码
        public $code = 200;
        // 请求用户
        private $login = null;
        // 允许覆盖的请求数据
        private $covers = [ 'type', 'id', 'lang', 'method', 'target', 'source', 'header', 'get', 'post', 'cookie', 'file', 'session', 'ip', 'router', 'share' ];
        // 允许的请求类型
        private $types = [ 'Http', 'Cli', 'Websocket' ];
        /**
         * 构造请求
         * - $type: 请求数据[array]
         * - return void
         */
        public function __construct( $data ) {
            //生成数据
            $this->cover( $data );
            // 检查请求参数
            $this->checkParameters();
            // 插件介入
            Bootstrap::permissions( 'REQUEST_INITIALIZATION_COMPLETED', $this );
        }
        /**
         * 获取用户信息
         * - $login: 强制尝试登录[boolean]
         * - return Account|false
         */
        public function user( $login = false ) {
            if ( $login || $this->login === null ) {
                $account = new Account( $this );
                $this->login = $account->state === true ? $account : false;
            }
            return is_object( $this->login ) && $this->login->state === true ? $this->login : false;
        }
        /**
         * 检查数据
         * - $rule: 检查规则[array]
         * - $data: 检查数据[array|null]
         * - return array 返回检查结果
         */
        public function vaildata( $rule, $data = null ) {
            // 检查数据
            if ( $data === null ) { $data = $this->post; }
            $data = is_array( $data ) ? $data : [];
            //构造检查器
            $vaildata = new Vaildata( $rule, $data );
            return $vaildata->check( $this );
        }
        /**
         * 访问语言包
         * - $key: 语言包键[string]
         * - $replace: 替换内容[array]
         * - return string 返回语言包内容
         */
        public function t( $key, $replace = [] ) { return __( $key, $replace, $this->lang ); }
        /**
         * 输出响应数据
         * - $state: 响应状态[bool|int|array]
         * - $data: 响应数据[array]
         * - $code: 响应代码[int|null]
         * - $header: 响应头[array]
         * - return string 返回响应数据[json]
         */
        public function echo( $state, $data = [], $code = null, $header = [ 'Content-Type' => 'application/json' ] ) {
            // 检查包含的返回方法
            $method = null;
            if ( is_array( $state ) && count( $state ) === 2 ) { $method = $state[1]; $state = $state[0]; }
            // 返回状态
            $stateMap = [ 0 => 'success', 1 => 'fail', 2 => 'error', 3 => 'warning', ];
            if ( is_bool( $state ) ) {
                if ( is_array( $data ) && count( $data ) === 1 ) {
                    $addState = $state ? 'base.true' : 'base.false';
                    $data[0] = "{$data[0]}:{$addState}";
                }
                $state = $state ? 0 : 1;
            }
            $stateName = $stateMap[$state] ?? 'UNKNOWN';
            // 语言处理
            if ( is_array( $data ) && count( $data ) <= 2 && is_string( $data[0] ) ) {
                $msg = $this->t( $data[0], $data[1] ?? [] );
                if ( $msg !== $data[0] ) { $data = $msg; }
            }
            // 处理返回
            $res = [
                'state' => $stateName,
                'code' => is_numeric( $code ) ? $code : $this->code,
                'time' => time(),
                'data' => $data,
            ];
            if ( !empty( $method ) ) { $res['method'] = $method; }
            // 系统流程干预
            $res = Bootstrap::permissions( 'RETURN_INTERFACE_DATA', $res );
            // 返回数据
            if ( is_callable( $this->share['FormattingReturnedData'] ) ) { $res = $this->share['FormattingReturnedData']( $this, $res, $header ); }
            return is_array( $res ) ? json_encode( $res, JSON_UNESCAPED_UNICODE ) : $res;
        }
        /**
         * 覆盖请求数据
         * - $data: 请求数据[array]
         * - return bool 是否成功覆盖
         */
        public function cover( $data ) {
            if ( !is_array( $data ) ) { return false; }
            // 覆盖请求数据
            foreach ( $this->covers as $key ) {
                if ( isset( $data[$key] ) ) { $this->$key = $data[$key]; }
            }
            // 自动设置请求数据
            if ( !isset( $data['id'] ) ) {
                $this->id = $this->header['id'] ?? $this->session['id'] ?? $this->cookie['id'] ?? uuid();
            }
            if ( !isset( $data['lang'] ) ) {
                $this->lang = $this->header['lang'] ?? $this->session['lang'] ?? $this->cookie['lang'] ?? config( 'app.lang' );
            }
            // 覆盖完成
            $this->checkParameters();
            return true;
        }
        /**
         * 检查请求参数
         * - return void
         */
        private function checkParameters() {
            // 检查请求类型
            $this->type = in_array( $this->type, $this->types ) ? $this->type : $this->types[0];
            // 设置请求 ID
            $this->id = is_uuid( $this->id ) ? $this->id : uuid();
            // 设置语言
            $this->lang = is_string( $this->lang ) && !empty( $this->lang ) ? $this->lang : config( 'app.lang' );
            // 设置请求方法
            $this->method = strtoupper( $this->method ?? 'GET' );
            // 设置请求目标
            $this->target = $this->target !== '' && $this->target !== null ? $this->target : '/';
            // 设置请求源
            $this->source = $this->source !== '' && $this->source !== null ? $this->source : 'UNKNOWN';
            // 设置请求头
            $this->header = is_array( $this->header ) ? $this->header : [];
            // 设置请求 GET 参数
            $this->get = is_array( $this->get ) ? $this->get : [];
            // 设置请求 POST 参数
            $this->post = is_array( $this->post ) ? $this->post : [];
            // 设置请求 Cookie
            $this->cookie = is_array( $this->cookie ) ? $this->cookie : [];
            // 设置请求文件
            $this->file = is_array( $this->file ) ? $this->file : [];
            // 设置请求会话
            $this->session = is_array( $this->session ) ? $this->session : [];
            // 设置请求 IP
            $this->ip = filter_var( $this->ip, FILTER_VALIDATE_IP ) ? $this->ip : 'UNKNOWN';
            // 设置请求路由
            $this->router = isset( $this->router ) && is_string( $this->router ) ? $this->router : 'UNKNOWN';
            // 设置共享数据
            $this->share = is_array( $this->share ) ? $this->share : [];
            // 尝试自动保存请求 ID
            if ( isset( $this->share['SaveRequestId'] ) && is_callable( $this->share['SaveRequestId'] ) ) {
                $this->share['SaveRequestId']( $this->id );
            }
            // http 来源二次处理
            if ( $this->type === 'Http' && $this->router === 'UNKNOWN' ) {
                $target = explode( '/', $this->target );
                switch ( $target[1] ) {
                    case 'api': $this->router = 'api'; break;
                    case 'storage': $this->router = 'storage'; break;
                    case 'app': $this->router = 'app'; break;

                    default: $this->router = 'view'; break;
                }
                $this->target = preg_replace( '/^\/(api|app|storage)/', '', $this->target );
            }
        }
    }