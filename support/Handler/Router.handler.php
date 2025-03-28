<?php

namespace Support\Handler;

use App\Bootstrap\MainProcess;
use Support\Slots\RouterBuild;

    class Router {
        public static $cache = []; // 缓存
        private static $error404 = 'Not Found.'; // 404 错误提示
        /**
         * 路由开始
         * - Router::start( $request:object(Request) );
         * - return any(路由搜索结果)
         */
        public static function start( Request $request ) {
            // 加载路由
            if ( empty( $request->router ) ) { return self::error( $request, [404, self::$error404] ); }
            if ( !is_array( self::$cache[$request->router] ) ) {
                self::load( $request->router );
            }
            // 路由搜索
            return self::search( $request, $request->target );
        }
        /**
         * 路由搜索
         * - Router::search( $request:object(Request), $target:string(目标), $parameter:array(传递参数) );
         * - return any(路由搜索结果)
         */
        public static function search( Request $request, $target, $parameter = [] ) {
            $error404 = function()use( $request ) { return self::error( $request, [404, self::$error404] ); };
            if ( !is_array( self::$cache[$request->router] ) ) { return $error404(); }
            // 参数整理
            $routers = self::$cache[$request->router];
            $method = $request->method;
            if ( $target !== '/' ) { $target = rtrim( $target, "/" ); }
            // 精准搜索
            if ( isset( $routers["{$method}|{$target}"] ) ) {
                return self::runRouter( $request, $routers["{$method}|{$target}"] );
            }
            if ( isset( $routers["ANY|{$target}"] ) ) {
                return self::runRouter( $request, $routers["ANY|{$target}"] );
            }
            // 模糊搜索
            if ( !empty( $routers ) && is_array( $routers ) ) {
                foreach( $routers as $routerTarget => $router ) {
                    if ( strpos( $routerTarget, '{{' ) === false ) { continue; }
                    $routerTargets = explode( '|', $routerTarget );
                    if ( $routerTargets[0] !== $request->method && $routerTargets[0] !== 'ANY' ) { continue; }
                    $pattern = preg_replace( '#{{[^?]+}}#', '([^/]+)', $routerTargets[1] );
                    $pattern = preg_replace( '#{{.*?\?}}#', '(.*?)', $pattern );
                    if ( preg_match( "#^{$pattern}$#", $target, $matches ) === 1 ) {
                        array_shift( $matches );
                        $matches = array_merge( $parameter, $matches );
                        return self::runRouter( $request, $router, ...$matches );
                    }
                }
            }
            // 404错误
            return $error404();
        }
        /**
         * 路由执行
         * - Router::runRouter( $request:object(Request), $router:array(路由配置), ...$parameter:any(传递参数) );
         * - return any(路由执行结果)
         */
        private static function runRouter( Request $request, $router, ...$parameter ) {
            try {
                // 路由权限验证
                if ( !empty( $router['auth'] ) && is_array( $router['auth'] ) ) {
                    foreach( $router['auth'] as $auth ) {
                        $auth = $auth( $request, ...$parameter );
                        if ( $auth !== null ) { return $auth; }
                    }
                }
                // 路由执行
                $result = $router['result']( $request, ...$parameter );
                if ( $result === null ) { return self::error( $request, [404, self::$error404] ); }
                return MainProcess::ResponseResult( $request, $result );
            } catch ( \Throwable $e ) {
                // 可能存在 JSON 格式错误
                if ( is_json( $e->getMessage() ) ) { return $e->getMessage(); }
                // 未知错误
                Log::to( 'Router' )->error([ "Run Router", $e ]);
            }
            return self::error( $request, [500, 'Response Error.'] );
        }
        /**
         * 路由加载
         * - Router::load( $routerName:string(路由类型) );
         * - return boolean(加载结果)
         */
        public static $nameCache = null;
        public static function load( $routerName ) {
            if ( empty( $routerName ) || is_array( self::$cache[$routerName] ) ) { return false; }
            self::$cache[$routerName] = [];
            // 获取路由映射配置
            $config = config( "router.{$routerName}" );
            if ( empty( $config ) ) { return false; }
            // 加载路由映射
            try {
                self::$nameCache = $routerName;
                import( $config );
            } catch ( \Exception $e ) {
                Log::to( 'Router' )->error([ "Load Router: {$routerName}", $e ]);
                return false;
            }
            return true;
        }
        /**
         * 路由错误
         * - Router::error( $request:object(Request), $error:array(错误信息) );
         * - return string(错误信息)
         */
        public static function error( Request $request, $error ) {
            $code = $error[0]; $text = $error[1];
            if ( $request->router === 'view' ) {
                return view( 'error', [ 'code' => $code, 'message' => $text ] );;
            }
            return $request->echo( 2, $text, $code );
        }
        /**
         * 构建路由
         * - Router::add( $target:string(目标), $method:string(请求方法) );
         * - return object(RouterBuild)
         */
        public static $groupCache = null;
        public static function add( $target, $method = null ) {
            return new RouterBuild( $target, $method );
        }
    }