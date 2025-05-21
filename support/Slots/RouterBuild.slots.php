<?php

namespace Support\Slots;

use Support\Handler\Router;

    /**
     * 路由构造器 - 辅助工具
     */
    class RouterBuild {
        public $state = true; // 可构造状态
        public $child = false; // 是否子路由
        public $target = null; // 路由目标
        public $method = null; // 路由方法
        public $auth = null; // 路由权限
        public $result = null; // 路由结果
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
                $this->target = Router::$buildCache->target.$target; // 路由目标
                $this->method = strtoupper( !empty( $method ) ? $method : Router::$buildCache->method ); // 路由方法
                $this->auth = Router::$buildCache->auth; // 路由权限
                $this->result = Router::$buildCache->result; // 路由结果
            }else {
                $this->target = $target; // 路由目标
                $this->method = strtoupper( !empty( $method ) ? $method : 'ANY' ); // 路由方法
                $this->auth = []; // 路由权限
                $this->result = null; // 路由结果
            }
        }
        /**
         * 添加路由权限
         * - 用于添加路由权限
         * - @param callable $callable 权限函数
         * - @return RouterBuild 路由构建器对象
         */
        public function auth( $callable ) {
            if ( !is_callable( $callable ) ) { return $this; }
            $this->auth[] = $callable;
            return $this;
        }
        // 访问函数
        public function to( $callable ) {
            if ( !is_callable( $callable ) ) { return $this; }
            $this->result = $callable;
            return $this;
        }
        // 访问 URL 地址
        public function url( $url ) {
            if ( !is_string( $url ) ) { return $this; }
            $this->result = function() use( $url ) {
                return ToUrl( $url );
            };
            return $this;
        }
        // 访问接口控制器
        public function controller( $class ) {
            $this->result = function( ...$parameter )use( $class ) {
                return Controller( $class, ...$parameter );
            };
            return $this;
        }
        // 访问任务控制器
        public function task( $class ) {
            $this->result = function( ...$parameter )use( $class ) {
                return Task( $class, ...$parameter );
            };
            return $this;
        }
        // 访问 public 文件
        public function file( $file ) {
            if ( !is_string( $file ) ) { return $this; }
            $this->result = function()use( $file ) {
                $file = ToFile( "public/{$file}" );
                return $file ? $file->echo() : null;
            };
            return $this;
        }
        // 访问视图文件
        public function view( $view, $share = [] ) {
            if ( !is_string( $view ) ) { return $this; }
            $this->result = function( $request )use( $view, $share ) {
                return View( $view, array_merge( $share, [ 'request' => $request ] ) );
            };
            return $this;
        }
        // 动态调用资产
        public function assets( $path ) {
            if ( !is_string( $path ) ) { return $this; }
            $this->result = function( $request )use( $path ) {
                $target = $request->get['file'] ?? null;
                if ( empty( $target ) ) { return null; }
                $path = "{$path}{$target}";
                $file = ToFile( $path );
                return $file ? $file->echo() : null;
            };
            return $this;
        }
        /**
         * 组路由
         * - 用于创建一级组子级路由
         * - @param callable $routers 路由函数
         * - @return RouterBuild 路由构建器对象
         */
        public function group( $routers ) {
            if ( !is_callable( $routers ) ) { return $this; }
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
            if ( !is_array( Router::$cache[Router::$name] ) ) { Router::$cache[Router::$name] = []; }
            Router::$cache[Router::$name]["{$this->method}|{$this->target}"] = [
                'auth' => $this->auth,
                'result' => $this->result
            ];
            // 返回状态
            return true;
        }
    }