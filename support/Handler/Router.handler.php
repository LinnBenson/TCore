<?php

namespace Support\Handler;

use Bootstrap;
use Support\Slots\RouterBuild;

    /**
     * 路由驱动器
     */
    class Router {
        public static $name = null; // 路由名称
        public static $targetRoot = null; // 路由目标
        public static $cache = []; // 路由缓存
        /**
         * 路由初始化
         * - 用于初始化加载路由
         * - @param Request $request 请求对象
         * - @return string 路由结果
         */
        public static function init( Request $request, $targetRoot = null ) {
            // 加载路由文件
            self::load( $request->router, $targetRoot );
            // 输出路由结果
            return self::search( $request, $request->router, $request->target, $request->method );
        }
        /**
         * 路由加载
         * - 用于加载路由文件
         * - @param string $router 路由名称
         * - @return bool 加载结果
         */
        public static function load( $router, $targetRoot = null ) {
            if ( empty( $router ) || !is_string( $router ) ) { return false; }
            if ( isset( self::$cache[$router] ) ) { return true; }
            $routerFile1 = "support/System/router/{$router}.router.php";
            $routerFile2 = "router/{$router}.router.php";
            try {
                self::$name = $router; // 路由名称
                self::$targetRoot = $targetRoot; // 路由目标
                if ( file_exists( $routerFile1 ) ) { require $routerFile1; }
                if ( file_exists( $routerFile2 ) ) { require $routerFile2; }
                // 系统干预流程
                Bootstrap::processRun( 'RouteRegistration', $router );
                self::$name = null; // 路由名称
                self::$targetRoot = null; // 路由目标
            }catch ( \Throwable $th ) {
                Bootstrap::log( "Route Registration: {$router}", $th );
            }
            return true;
        }
        /**
         * 路由搜索
         * - 用于搜索路由
         * - @param Request $request 请求对象
         * - @param string $router 路由名称
         * - @param string $target 路由目标
         * - @param string $method 路由方法，默认为 ANY
         * - @param array $parameter 传递参数
         * - @return mixed 路由结果
         */
        public static function search( Request $request, $router, $target, $method = 'ANY', $parameter = [] ) {
            // 参数检查
            if ( empty( $target ) || !is_string( $target ) ) { return self::error( $request, 500, [ 'base.error.input' ] ); }
            if ( empty( $method ) || !is_string( $method ) ) { $method = 'ANY'; }
            if ( empty( Router::$cache[$router] ) ) { return self::error( $request, 404, [ 'base.error.404' ] ); }
            if ( !is_array( Router::$cache[$router] ) ) { return self::error( $request, 500, [ 'base.error.500' ] ); }
            // 搜索路由
            $result = null;
            $targetRoot = explode( '/', $target )[1];
            $routers = array_merge( Router::$cache[$router]["Root|{$targetRoot}"] ?? [], Router::$cache[$router]["Root"] ?? [] );
            $targetName = "{$method}|{$target}"; $targetNameAny = "ANY|{$target}";
            // 精准搜索
            if ( isset( $routers[$targetName] ) ) { return self::runRouter( $request, $routers[$targetName] ); }
            if ( isset( $routers[$targetNameAny] ) ) { return self::runRouter( $request, $routers[$targetNameAny] ); }
            // 模糊搜索
            foreach( $routers as $routerTarget => $router ) {
                if ( strpos( $routerTarget, '{{' ) === false ) { continue; }
                $routerTargets = explode( '|', $routerTarget );
                if ( $routerTargets[0] !== $request->method && $routerTargets[0] !== 'ANY' ) { continue; }
                $pattern = preg_replace( '#{{.*?\?}}#', '(.*?)', $routerTargets[1] );
                $pattern = preg_replace( '#{{[^}]+}}#', '([^/]+)', $pattern );
                if ( preg_match( "#^{$pattern}$#", $target, $matches ) === 1 ) {
                    array_shift( $matches );
                    $matches = array_merge( $parameter, $matches );
                    return self::runRouter( $request, $router, ...$matches );
                }
            }
            // 返回结果
            return !empty( $result ) ? $result : self::error( $request, 404, [ 'base.error.404' ] );
        }
        /**
         * 运行路由
         * - 用于运行指定路由配置
         * - @param Request $request 请求对象
         * - @param array $config 路由配置
         * - @param mixed $parameter 传递参数
         * - @return mixed 路由结果
         */
        public static function runRouter( Request $request, $config, ...$parameter ) {
            try {
                // 路由权限验证
                if ( !empty( $config['auth'] ) && is_array( $config['auth'] ) ) {
                    foreach( $config['auth'] as $auth ) {
                        $auth = $auth( $request, ...$parameter );
                        if ( $auth !== true ) {
                            if ( is_string( $auth ) ) { return $auth; }
                            return self::error( $request, 402, [ 'base.error.prohibit' ] );
                        }
                    }
                }
                // 路由执行
                $result = $config['result']( $request, ...$parameter );
                if ( $result === false ) { return self::error( $request, 402, [ 'base.error.prohibit' ] ); }
                if ( $result === null ) { return self::error( $request, 404, [ 'base.error.404' ] ); }
                return $result;
            } catch ( \Throwable $th ) {
                // 可能存在 JSON 格式错误
                if ( is_json( $th->getMessage() ) ) { return $th->getMessage(); }
                // 未知错误
                Bootstrap::log( "Route Execution", $th );
            }
            return self::error( $request, 500, [ 'base.error.500' ] );
        }
        /**
         * 路由构建器
         * - 用于构建路由
         * - @param string $target 路由目标
         * - @param string $method 路由方法，默认为 null
         * - @return RouterBuild 路由构建器对象
         */
        public static $buildCache = null; // 路由构建器缓存
        public static function add( $target, $method = null ) {
            return new RouterBuild( $target, $method );
        }
        /**
         * 路由错误处理
         * - 用于输出由于路由导致的错误
         * - @param Request $request 请求对象
         * - @param int $code 错误代码
         * - @param mixed $msg 错误内容
         * - @return string 错误结果
         */
        public static function error( Request $request, $code, $msg ) {
            if ( $request->router === 'view' ) {
                // 语言处理
                if ( is_array( $msg ) && count( $msg ) <= 2 && is_string( $msg[0] ) ) {
                    $check = $request->t( $msg[0], $msg[1] ?? [] );
                    if ( $check !== $msg[0] ) { $msg = $check; }
                }
                return View( 'error', [ 'request' => $request, 'code' => $code, 'msg' => $msg ] );
            }
            return $request->echo( 2, $msg, $code );
        }
    }