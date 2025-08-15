<?php
    /**
     * 核心驱动器
     */
    namespace Support;
    use Dotenv\Dotenv;
    use Illuminate\Database\Capsule\Manager as Capsule;

    class Bootstrap {
        public static $type = null; // 构建类型[http|cli|webSocket]
        public static $init = false; // 是否已初始化[array]
        public static $cache = []; // 缓存对象[array]
        public static $db = null; // 数据库连接[Capsule|null]
        /**
         * 启动核心构建请求
         * - $type: 构建类型[http|cli|webSocket]
         * - $callback: 回调函数[function]|null]
         * - return mixed 最终执行结果
         */
        public static function build( $type, $callback = null ) {
            // 初始化应用
            self::init();
            // 设置驱动器
            self::$type = $type;
            self::$init = true;
            self::permissions( 'SYSTEM_STARTUP' );
            // 执行回调方法
            try {
                return is_callable( $callback ) ? self::permissions( 'RETURN_RESULTS', $callback() ) : null;
            }catch ( \Throwable $e ) {
                // 回调方式执行错误
                self::log( 'Bootstrap', "Callback error: ".$e->getMessage() );
                return "Bootstrap callback error: ".$e->getMessage();
            }
        }
        /**
         * 初始化应用
         * - return void
         */
        private static function init() {
            // 基础依赖文件
            require_once 'support/Helper/Global.helper.php';
            require_once 'support/Helper/System.helper.php';
            // 加载 Composer
            import( 'vendor/autoload.php' );
            // 加载 .env 文件
            try {
                $dotenv = Dotenv::createImmutable( getcwd() );
                $dotenv->load();
            }catch( Throwable $e ) {
                exit( ".env import error: ".$e->getMessage() );
            }
            if ( !env( 'APP_ENABLE', false ) ) { exit( 'This application is currently closed.' ); }
            // 调整 PHP 配置
            if ( config( 'app.debug' ) ) {
                error_reporting( E_ERROR | E_WARNING | E_PARSE );
                ini_set( 'display_errors', 1 );
            }else {
                error_reporting( 0 ); ini_set( 'display_errors', 0 );
            }
            date_default_timezone_set( config( 'app.timezone' ) );
            // 自动加载应用
            self::$cache['autoload'] = config( 'autoload.base' );
            spl_autoload_register( function( $class ) {
                if ( isset( Bootstrap::$cache['autoload'][$class] ) ) {
                    import( Bootstrap::$cache['autoload'][$class] );
                    return true;
                }else if ( startsWith( $class, 'App\\' ) ) {
                    $class = explode( '\\', $class );
                    $class[0] = strtolower( $class[0] ); // App
                    import( implode( '/', $class ).".".strtolower( $class[1] ).".php" );
                    return true;
                }
                Bootstrap::log( 'Autoload', "Class {$class} not found." );
                return false;
            });
            // 挂载数据库
            try {
                self::$db = new Capsule;
                self::$db->addConnection([
                    'driver' => config( 'database.mysql.driver' ),
                    'charset' => config( 'database.mysql.charset' ),
                    'collation' => config( 'database.mysql.collation' ),
                    'prefix' => config( 'database.mysql.prefix' ),
                    'host' => config( 'database.mysql.host' ),
                    'port' => config( 'database.mysql.port' ),
                    'database' => config( 'database.mysql.database' ),
                    'username' => config( 'database.mysql.username' ),
                    'password' => config( 'database.mysql.password' ),
                ]);
                self::$db->setAsGlobal();
                self::$db->bootEloquent();
            }catch ( \Throwable $e ) {
                self::log( 'Database', 'Connection Error: '.$e->getMessage() );
            }
        }
        /**
         * 构建查询缓存
         * - $type: 缓存类型[thread|file]
         * - $name: 缓存名称[string]
         * - $data: 缓存数据[function]|null
         * - return mixed 数据
         */
        public static function cache( $type, $name, $data = null ) {
            switch ( $type ) {
                case 'thread':
                    // 从线程中搜索
                    if ( isset( self::$cache[$name] ) ) { return self::$cache[$name]; }
                    // 如果没有数据，则执行回调
                    if ( is_callable( $data ) ) {
                        self::$cache[$name] = $data();
                        return self::$cache[$name];
                    }
                    break;
                case 'file':
                    // 调试模式禁用时不加载缓存
                    if ( env( 'APP_DEBUG', false ) === true ) { return $data(); }
                    // 从文件中搜索
                    $fileType = endsWith( $name, 'php' ) ? 'php' : 'txt';
                    $name = h( $name );
                    $file = inFolder( "storage/cache/Bootstrap/{$name}.{$fileType}" );
                    if ( file_exists( $file ) ) { return $type === 'php' ? require $file : file_get_contents( $file ); }
                    // 如果没有数据，则执行回调
                    if ( is_callable( $data ) ) {
                        $result = $data();
                        if ( $type === 'php' ) {
                            if ( is_array( $result ) ) {
                                file_put_contents( $file, "<?php\nreturn ".var_export( $result, true ).";\n" );
                            }else {
                                file_put_contents( $file, $result ); $result = require $file;
                            }
                            return $result;
                        }
                        file_put_contents( $file, $result );
                        return $result;
                    }
                    break;

                default: break;
            }
            return null;
        }
        /**
         * 扩展权限介入
         * - $name: 权限名[string]
         * - $data: 权限数据[function]|null
         * - return void 权限返回结果
         */
        public static function permissions( $name, $data = null ) {
            $plugs = import( 'config/permissions.config.php' );
            if ( isset( $plugs[$name] ) ) { $plugs = $plugs[$name]; }
            $result = $data;
            foreach( $plugs as $plug ) {
                $plug = plug( $plug );
                if ( isset( $plug->permissions[$name] ) && is_callable( $plug->permissions[$name] ) ) {
                    $result = $plug->permissions[$name]( $data );
                }
            }
            return $result;
        }
        /**
         * 自动加载类文件
         * - $config: 类信息[array]
         * - return bool 是否成功加载
         */
        public static function autoload( $config ) {
            if ( !is_array( $config ) ) { return false; }
            self::$cache['autoload'] = array_merge( self::$cache['autoload'], $config );
            return true;
        }
        /**
         * 抛出一个错误
         * - $data: 错误信息[string]
         * - return void
         */
        public static function error( $data ) { throw new \Exception( $data ); }
        /**
         * 写入日志
         * - $title: 日志标题[string]
         * - $content: 日志内容[string]|object
         * - return bool 是否成功写入日志
         */
        public static function log( $title, $content ) {
            if ( Bootstrap::$init ) {
                Bootstrap::permissions( 'MAIN_SYSTEM_LOG', [ 'title' => $title, 'content' => $content ] );
            }
            $file = inFolder( 'storage/log/Bootstrap_'.date( 'Ymd' ).'.log' );
            // 传入为对象时认为是异常对象
            if ( is_object( $content ) && method_exists( $content, 'getMessage' ) ) {
                $content = "{$content->getFile()}[{$content->getLine()}]: {$content->getMessage()}";
            }
            // 再次检查是否为字符串
            if ( !is_string( $content ) ) { $content = print_r( $content, true ); }
            // 准备写入内容
            $content = date( 'H:i:s' )." {$title} | {$content}\n";
            return file_put_contents( $file, $content, FILE_APPEND );
        }
    }