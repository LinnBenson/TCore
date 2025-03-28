<?php

namespace Support\Slots;

use Support\Handler\Request;
use Support\Handler\Router;
use Support\Helper\Tool;

    class RouterBuild {
        private $target = null; // 目标
        private $config = []; // 请求方法
        public $group = null; // 分组
        /**
         * 路由构造
         * - Router::add( $target:string(目标), $method:string(请求方法) );
         * - return object(RouterBuild)
         */
        public function __construct( $target, $method ) {
            $this->group = Router::$groupCache;
            if ( empty( $target ) ) { $target = is_object( $this->group ) ? $this->group->target : '/'; }
            if ( empty( $method ) ) { $method = is_object( $this->group ) ? $this->group->config['method'] : 'ANY'; }
            $method = strtoupper( $method );
            if ( is_object( $this->group ) ) {
                $this->group = $this->group;
                $target = $target === '/' ? '' : $target;
                $target = $this->group->target.$target;
            }
            $target = str_replace( ".", "/", $target );
            if ( strpos( $target, '/' ) !== 0 ) { $target = "/{$target}"; }
            $this->target = $target;
            $this->config = [
                'enable' => false,
                'method' => $method
            ];
        }
        /**
         * 以 controller 响应结果
         * - ->controller( $class:array|string(请求对象) );
         * - return object(RouterBuild)
         */
        public function controller( $class ) {
            $this->addResult(function( ...$parameter )use( $class ) {
                return Tool::runMethod( 'Controller', $class, ...$parameter );
            });
            return $this;
        }
        /**
         * 以 task 响应结果
         * - ->task( $class:array|string(请求对象) );
         * - return object(RouterBuild)
         */
        public function task( $class ) {
            $this->addResult(function( ...$parameter )use( $class ) {
                return Tool::runMethod( 'Task', $class, ...$parameter );
            });
            return $this;
        }
        /**
         * 以函数响应结果
         * - ->to( $function:function(请求函数) );
         * - return object(RouterBuild)
         */
        public function to( $function ) {
            if ( !is_callable( $function ) ) { return $this; }
            $this->addResult(function( ...$parameter )use( $function ) {
                return $function( ...$parameter );
            });
            return $this;
        }
        /**
         * 验证器
         * - ->auth( $function:function(请求函数) );
         * - return object(RouterBuild)
         */
        public function auth( $function ) {
            if ( !is_callable( $function ) ) { return $this; }
            if ( empty( $this->config['auth'] ) ) { $this->config['auth'] = []; }
            $this->config['auth'][] = function( ...$parameter )use( $function ) {
                return $function( ...$parameter );
            };
            return $this;
        }
        /**
         * 以公开文件响应结果
         * - ->public( $file:string(请求文件) );
         * - return object(RouterBuild)
         */
        public function public( $file ) {
            if ( !is_string( $file ) ) { return $this; }
            $this->config['result'] = function()use( $file ) {
                $file = __file( "public/{$file}" );
                if ( !file_exists( $file ) ) { return null; }
                return file_get_contents( $file );
            };
            $this->config['enable'] = true;
            return $this;
        }
        /**
         * location 重定向
         * - ->url( $url:string(重定向地址) );
         * - return object(RouterBuild)
         */
        public function url( $url ) {
            if ( !is_string( $url ) ) { return $this; }
            $this->config['result'] = function() use( $url ) {
                return "<script type=\"text/javascript\">window.location.href='{$url}';</script>";
            };
            $this->config['enable'] = true;
            return $this;
        }
        /**
         * 视图内容
         * - ->view( $file:string(视图文件), $share|[]:array(共享参数) );
         * - return object(RouterBuild)
         */
        public function view( $file, $share = [] ) {
            if ( !is_string( $file ) ) { return $this; }
            $this->config['result'] = function( Request $request ) use( $file, $share ) {
                return view( $file, $share, $request );
            };
            $this->config['enable'] = true;
            return $this;
        }
        /**
         * 注册响应结果
         * - ->addResult( $method:function(结果处理) );
         * - return boolean(注册结果)
         */
        private function addResult( $method ) {
            $this->config['result'] = function( ...$parameter )use( $method ) {
                return $method( ...$parameter );
            };
            return $this->config['enable'] = true;
        }
        /**
         * 群组路由
         * - ->group( $method:function(路由) );
         * - return null
         */
        public function group( $method ) {
            if ( !is_callable( $method ) ) { return $this; }
            Router::$groupCache = $this;
            $method();
            Router::$groupCache = null;
            return null;
        }
        /**
         * 保存路由
         * - ->save();
         * - return boolean(保存结果)
         */
        public function save() {
            if ( !empty( $this->config ) && $this->config['enable'] === true ) {
                if (
                    is_object( $this->group ) &&
                    is_array( $this->group->config['auth'] )
                ) {
                    $this->config['auth'] = array_merge( $this->group->config['auth'], $this->config['auth'] );
                }
                Router::$cache[Router::$nameCache]["{$this->config['method']}|{$this->target}"] = $this->config;
            }
            return true;
        }
    }