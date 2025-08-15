<?php
    /**
     * 路由处理器
     */
    namespace Support\Handler;

    use Support\Bootstrap;

    class Router {
        public static $cache = []; // 缓存数据[array]
        /**
         * 加载路由
         */
        public static $loadName = null; // 路由名称
        public static $loadFilter = null; // 路由过滤
        /**
         * 加载路由
         * - $name: 路由名称[string]
         * - $filter: 路由过滤[string|null]
         * - return bool 是否成功加载
         */
        public static function load( $name, $filter = null ) {
            if ( empty( $name ) || !is_string( $name ) ) { return false; }
            if ( isset( self::$cache[$name] ) ) { return true; }
            try {
                self::$loadName = $name; // 路由名称
                self::$loadFilter = $filter; // 路由过滤
                $routerFile1 = "support/System/router/{$name}.router.php";
                $routerFile2 = "router/{$name}.router.php";
                if ( file_exists( $routerFile1 ) ) { require $routerFile1; }
                if ( file_exists( $routerFile2 ) ) { require $routerFile2; }
                // 系统干预流程
                Bootstrap::permissions( 'REGISTERING_ROUTES', [ 'name' => $name, 'filter' => $filter ] );
                self::$loadName = null; // 路由名称
                self::$loadFilter = null; // 路由过滤
            }catch ( \Throwable $th ) {
                Bootstrap::log( "Router register: {$router}", $th );
                return false;
            }
            return true;
        }
        /**
         * 路由搜索
         * - $request: 请求对象[Request]
         * - $router: 路由类型[string]
         * - $target: 路由目标[string]
         * - $method: 路由方法[string]
         * - $parameter: 请求参数[array]
         * - return mixed 路由结果
         */
        public static function search( Request $request, $router, $target, $method = 'ANY', $parameter = [] ) {
            // 参数检查
            if ( empty( $target ) || !is_string( $target ) ) { return self::error( $request, 500, [ 'base.error.input' ] ); }
            if ( empty( $method ) || !is_string( $method ) ) { $method = 'ANY'; }
            if ( empty( Router::$cache[$router] ) ) { return self::error( $request, 404, [ 'base.error.404' ] ); }
            if ( !is_array( Router::$cache[$router] ) ) { return self::error( $request, 500, [ 'base.error.500' ] ); }
            // 搜索路由
            $target = $target === '/' ? '/' : rtrim( $target, '/' );
            $targetRoot = explode( '/', $target );
            $targetRoot = isset( $targetRoot[1] ) ? "/{$targetRoot[1]}" : "/";
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
            return self::error( $request, 404, [ 'base.error.404' ] );
        }
        /**
         * 运行路由
         * - $request: 请求对象[Request]
         * - $config: 路由配置[array]
         * - $parameter: 请求参数
         * - return mixed 运行结果
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
                Bootstrap::log( "Router Execution", $th );
            }
            return self::error( $request, 500, [ 'base.error.500' ] );
        }
        /**
         * 添加路由
         * - $target: 路由目标[string]
         * - $method: 路由方法[string|null]
         * - return RouterBuilder 返回路由构建器对象
         */
        public static $buildCache = null; // 路由构建器缓存
        public static function add( $target, $method = null ) {
            return new RouterBuilder( $target, $method );
        }
        /**
         * 报告错误
         * - $request: 请求对象[Request]
         * - $code: 错误代码[int]
         * - $msg: 错误信息[string|array]
         * - return string 返回错误响应数据
         */
        public static function error( Request $request, $code, $msg ) {
            // 语言处理
            if ( is_array( $msg ) && count( $msg ) <= 2 && is_string( $msg[0] ) ) {
                $check = $request->t( $msg[0], $msg[1] ?? [] );
                if ( $check !== $msg[0] ) { $msg = $check; }
            }
            $permissions = Bootstrap::permissions( 'ROUTING_RESPONSE_ERROR', [ 'request' => $request, 'code' => $code, 'msg' => $msg ] );
            if ( is_string( $permissions ) ) { return $permissions; }
            if ( $request->router === 'view' ) {
                return view( 'error', [ 'request' => $request, 'code' => $code, 'msg' => $msg ] );
            }
            return $request->echo( 2, $msg, $code );
        }
        /**
         * 访问接口控制器
         * - $class: 控制器类名[string]
         * - $parameter: 控制器参数
         * - return mixed 返回控制器执行结果
         */
        public static function ToController( $class, ...$parameter ) {
            if ( !is_string( $class ) && !is_array( $class ) ) { return null; }
            $execute = is_array( $class ) ? [ $class[0], $class[1] ] : explode( '@', $class );
            $controllerName = $execute[0];
            if ( strpos( $controllerName, '\\' ) === false ) {
                $controllerName = str_replace( '.', '\\', $controllerName );
                $controllerName = "App\\Controller\\{$controllerName}";
            }
            $methodName = $execute[1];
            if ( !empty( $methodName ) ) {
                $controller = new $controllerName; // 构造控制器
                if ( !method_exists( $controller, $methodName ) || (new \ReflectionMethod( $controller, $methodName ))->isPrivate() ) {
                    return null;
                }
                if ( method_exists( $controller, 'init' ) && !(new \ReflectionMethod( $controller, 'init'))->isPrivate() ) {
                    $check =  $controller->init( ...$parameter );
                    if ( $check !== null ) { return $check; }
                }
                return $controller->{$methodName}( ...$parameter );
            }
            return null;
        }
        /**
         * 路由跳转
         * - $url: 跳转地址[string]
         * - return string 返回跳转脚本
         */
        public static function ToUrl( $url ) {
            return "<script type=\"text/javascript\">window.location.href='{$url}';</script>";
        }
        /**
         * 访问视图文件
         * - $view: 视图文件路径[string]
         * - $share: 共享参数[array]
         * - return string 返回视图内容
         */
        public static function ToView( $view, $share = [] ) {
            return view( $view, $share );
        }
        /**
         * 访问文件
         * - $file: 文件路径[string]
         * - $expires: 过期时间[number]|2592000
         * - return string 返回文件内容
         */
        public static function ToFile( $file, $expires = 2592000 ) {
            return ( new File( $file ) )->echo( $expires );
        }
    }