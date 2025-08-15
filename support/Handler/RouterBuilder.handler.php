<?php
    /**
     * 路由注册器
     */
    namespace Support\Handler;

    class RouterBuilder {
        public $state = true; // 可构造状态
        public $child = false; // 是否子路由
        public $target = null; // 路由目标
        public $filter = null; // 路由目标
        public $method = null; // 路由方法
        public $auth = null; // 路由权限
        public $result = null; // 路由结果
        public $name = null;
        /**
         * 构造函数
         * - 用于构造路由
         * - @param string $target 路由目标
         * - @param string $method 路由方法，默认为 'ANY'
         * - @return void
         */
        public function __construct( $target, $method = null ) {
            // 路由构建器缓存
            if ( is_object( Router::$buildCache ) ) { $this->child = true; }
            // 创建参数
            if ( $this->child ) {
                $this->target = Router::$buildCache->target.( $target === '/' ? '' : $target ); // 路由目标
                $this->method = strtoupper( !empty( $method ) ? $method : Router::$buildCache->method ); // 路由方法
                $this->auth = Router::$buildCache->auth; // 路由权限
                $this->result = Router::$buildCache->result; // 路由结果
            }else {
                $this->target = $target; // 路由目标
                $this->method = strtoupper( !empty( $method ) ? $method : 'ANY' ); // 路由方法
                $this->auth = []; // 路由权限
                $this->result = null; // 路由结果
            }
            $filter = explode( '/', $this->target );
            $this->filter = isset( $filter[1] ) ? "/{$filter[1]}" : "/";
            // 状态检查
            if ( is_string( Router::$loadFilter ) && strpos( $this->filter, '{{' ) !== false && Router::$loadFilter !== $this->filter ) {
                $this->state = false;
            }
        }
        /**
         * 添加路由权限
         * - 用于添加路由权限
         * - @param callable $callable 权限函数
         * - @return RouterBuild 路由构建器对象
         */
        public function auth( $callable ) {
            // 参数检查
            if ( !$this->state || !is_callable( $callable ) ) { return $this; }
            $this->auth[] = $callable;
            return $this;
        }
        // 访问函数
        public function to( $callable ) {
            // 参数检查
            if ( !$this->state || !is_callable( $callable ) ) { return $this; }
            $this->result = $callable;
            return $this;
        }
        // 访问 URL 地址
        public function url( $url ) {
            // 参数检查
            if ( !$this->state || !is_string( $url ) ) { return $this; }
            $this->result = function() use( $url ) {
                return Router::ToUrl( $url );
            };
            return $this;
        }
        // 访问接口控制器
        public function controller( $class ) {
            // 参数检查
            if ( !$this->state ) { return $this; }
            $this->result = function( ...$parameter )use( $class ) {
                return Router::ToController( $class, ...$parameter );
            };
            return $this;
        }
        // 访问视图文件
        public function view( $view, $share = [] ) {
            // 参数检查
            if ( !$this->state || !is_string( $view ) ) { return $this; }
            $this->result = function( $request )use( $view, $share ) {
                return Router::ToView( $view, array_merge( $share, [ 'request' => $request ] ) );
            };
            return $this;
        }
        // 访问 public 文件
        public function file( $file, $expires = 2592000 ) {
            // 参数检查
            if ( !$this->state || !is_string( $file ) ) { return $this; }
            $this->result = function()use( $file, $expires ) {
                return Router::ToFile( $file, $expires );
            };
            return $this;
        }
        // 命名
        public function name( $name ) {
            // 参数检查
            if ( !$this->state || !is_string( $name ) ) { return $this; }
            $this->name = $name;
            return $this;
        }
        /**
         * 组路由
         * - 用于创建一级组子级路由
         * - @param callable $routers 路由函数
         * - @return RouterBuild 路由构建器对象
         */
        public function group( $routers ) {
            // 参数检查
            if ( !$this->state || !is_callable( $routers ) ) { return $this; }
            Router::$buildCache = $this;
            $routers( $this );
            Router::$buildCache = null;
            return $this;
        }
        /**
         * 保存路由
         * - 用于保存路由
         * - @return bool 保存结果
         */
        public function save() {
            // 参数检查
            if ( !$this->state || empty( $this->target ) || empty( $this->result ) ) { return false; }
            // 添加到路由
            if ( !is_array( Router::$cache[Router::$loadName] ) ) { Router::$cache[Router::$loadName] = []; }
            if ( strpos( $this->filter, '{{' ) === false ) {
                Router::$cache[Router::$loadName]["Root|{$this->filter}"]["{$this->method}|{$this->target}"] = [
                    'name' => $this->name,
                    'auth' => $this->auth,
                    'result' => $this->result
                ];
            }else {
                Router::$cache[Router::$loadName]["Root"]["{$this->method}|{$this->target}"] = [
                    'name' => $this->name,
                    'auth' => $this->auth,
                    'result' => $this->result
                ];
            }
            // 返回状态
            return true;
        }
    }