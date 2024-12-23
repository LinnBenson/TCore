<?php
use support\method\tool;
use support\middleware\view;

    class routerRegister {
        // 当前实体
        private $enable = false;
        private $ref = null;
        // 当前配置
        public $config = [];
        public $argvDel = 0;
        // 设置实体
        public function setRef( $enable, $name, $ref ) {
            $this->enable = $enable; // 路由是否渲染
            $this->config['name'] = $name; // 路由名称
            $this->ref = $ref; // 当前实体
            // 返回实体
            return $this->ref;
        }
        // 调用控制器
        public function controller( $file, $method = false ) {
            // 路由是否渲染
            if ( !$this->enable ) { return $this->ref; }
            // 添加路由方法
            $this->config['method'] = function( $argv = [], $sendData = [] )use ( $file, $method ) {
                $allArgv = implode( '/', $argv );
                // 调用控制器
                $files = explode( '.', $file );
                $controller = "application/controller/{$files[0]}.controller.php";
                if ( !file_exists( $controller ) ) { return router::error( 500, t( 'error.nullController' ) ); }
                $class = explode( '/', $files[0] ); $class = $class[count( $class ) - 1];
                $class = "{$class}Controller";
                if ( !class_exists( $class ) ) { require $controller; }
                // 判断当前使用方法
                if ( empty( $method ) ) {
                    $name = str_replace( '/*', '', $this->config['name'] );
                    $name = str_replace( $name, '', $allArgv );
                    $method = explode( '/', $name );
                    $method = $method[1];
                    if ( empty( $name ) ) { $method = 'index'; }
                }
                if ( empty( $method ) ) { $method = 'index'; }
                $api = new $class;
                if ( method_exists( $api, $method ) ) {
                    if ( !isPrivate( $class, $method ) ) {
                        $argv = array_slice( $argv, $this->argvDel );
                        if ( !empty( $sendData ) && is_array( $sendData ) ) { $argv = array_merge( $sendData, $argv ); }
                        return $api->$method( $argv[0], $argv[1], $argv[2], $argv[3], $argv[4], $argv[5], $argv[6] );
                    }else {
                        return router::error( 500, t( 'error.privateMethod' ) );
                    }
                }
                return router::error( 404 );
            };
            // 返回实体
            return $this->ref;
        }
        // 调用页面
        public function public( $file ) {
            // 路由是否渲染
            if ( !$this->enable ) { return $this->ref; }
            // 添加路由方法
            $this->config['method'] = function()use ( $file ) {
                $link = "public/{$file}";
                if ( strpos( $link, '.' ) === false ) {
                    $link = "{$link}.html";
                }
                if ( !file_exists( $link ) ) { return router::error( 500, t( 'error.nullPage' ) ); }
                if ( tool::endsWith( $link, '.php' ) ) {
                    ob_start();
                        require $link;
                    $html = ob_get_clean();
                }else {
                    $html = file_get_contents( $link );
                }
                return $html;
            };
            // 返回实体
            return $this->ref;
        }
        // 调用视图
        public function view( $file ) {
            // 路由是否渲染
            if ( !$this->enable ) { return $this->ref; }
            // 添加路由方法
            $this->config['method'] = function()use ( $file ) {
                return view::show( $file );
            };
            // 返回实体
            return $this->ref;
        }
        // 调用跳转代码
        public function link( $url ) {
            // 路由是否渲染
            if ( !$this->enable ) { return $this->ref; }
            // 添加路由方法
            $this->config['method'] = function()use ( $url ) {
                return "<script type=\"text/javascript\">window.location.href='{$url}';</script>";
            };
            // 返回实体
            return $this->ref;
        }
        // 传递参数开始
        public function start( $num ) {
            $this->argvDel = intval( $num );
            // 返回实体
            return $this->ref;
        }
        // 权限检查
        public function auth( $to = false ) {
            // 路由是否渲染
            if ( !$this->enable ) { return $this->ref; }
            // 执行用户逻辑
            $check = $to;
            if ( is_callable( $to ) ) { $check = $to(); }
            if ( $check === true ) { return $this->ref; }
            if ( $check === false  ) { $this->enable = false; return $this->ref; }
            if ( is_callable( $check ) ) { $this->config['method'] = $check; return $this->ref; }
            if ( is_string( $check ) ) { $this->config['method'] = function()use ( $check ) { return $check; }; return $this->ref; }
            return $this->ref;
        }
        // 渲染路由
        public function save() {
            // 路由是否渲染
            if ( !$this->enable ) { return $this->ref; }
            if (
                !empty( $this->config['name'] ) &&
                !empty( $this->config['method'] ) &&
                is_callable( $this->config['method'] )
            ) {
                // 添加路由配置
                router::$config[$this->config['name']] = $this->config['method'];
                return true;
            }
            return false;
        }
        // 渲染子级路由
        public function children( $method ) {
            // 路由是否渲染
            if ( !$this->enable ) { return $this->ref; }
            // 检查配置
            if ( is_callable( $method ) && strpos( router::$argv, $this->config['name'] ) === 0 ) {
                // 设置好自身路由
                $this->save();
                $method( $this->config['name'] );
                return true;
            }
            return false;
        }
    }