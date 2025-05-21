<?php

use Plug\TCoreView\BuildView;
use Support\Handler\Request;
use Support\Handler\Router;
use Support\Helper\Tool;

    return new class {
        // 基础配置
        public $plug = [
            'name' => 'TCoreView',
            'title' => '视图渲染器',
            'version' => '1.0.0',
            'author' => 'TCore',
            'description' => 'TCore system view rendering plugin.',
            'auto' => [
                'RouteRegistration' => 'addRouter'
            ],
        ];
        public $path = true;
        public $config, $library;
        public function init() {
            Tool::plugLoadConfig( $this, 'config', 'support/main.config.php' );
            $this->library = [
                'ViewHeader' => "{$this->path}library/header.view.html",
                'ViewFooter' => "{$this->path}library/footer.view.html",
                'ViewForm' => "{$this->path}library/form.view.html",
                'module.switch' => "{$this->path}library/module/switch.view.html",
                'module.card' => "{$this->path}library/module/card.view.html",
            ];
        }
        /**
         * 入口函数
         */
        public function show( $view, $share, $cache = true ) {
            require_once "{$this->path}support/BuildView.class.php";
            return (new BuildView([
                'view' => $view,
                'share' => is_array( $share ) ? $share : [],
                'cache' => !empty( $cache ) ? true : false,
                'plugPath' => $this->path,
                'config' => $this->config,
                'library' => $this->library
            ]))->show();
        }
        /**
         * 添加路由
         */
        public function addRouter( $router ) {
            if ( $router !== 'view' ) { return null; }
            Router::add( '/tcore_view/assets', 'GET' )->assets( "{$this->path}library/assets/" )->save();
        }
        /**
         * 绑定资源地址
         */
        public function library( $data ) {
            $this->library = array_merge( $this->library, $data );
            return true;
        }
    };