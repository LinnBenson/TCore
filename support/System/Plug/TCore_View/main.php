<?php

use Plug\TCore_View\BuildView;
use Support\Handler\File;
use Support\Handler\Request;
use Support\Handler\Router;

    return new class {
        // 基础配置
        public $config = [
            'info' => [
                'name' => 'TCore_View',
                'title' => '视图渲染器',
                'version' => '1.0.0',
                'author' => 'TCore',
                'description' => 'TCore system view rendering plugin.'
            ],
            'auto' => [
                'RouteRegistration' => 'addRouter'
            ],
            'view' => null,
        ];
        /**
         * 入口函数
         */
        public function show( $view, $share, $cache = true ) {
            $plugPath = Plug( $this->config['info']['name'], 'folder' );
            require_once "{$plugPath}/support/BuildView.php";
            if ( empty( $this->config['view'] ) ) {
                $this->config['view'] = require_once "{$plugPath}/support/Config.php";
                $userConfig = "config/view.config.php";
                if ( file_exists( $userConfig ) ) { $this->config['view'] = array_merge( $this->config['view'], require_once $userConfig ); }
            }
            return (new BuildView([
                'view' => $view,
                'share' => is_array( $share ) ? $share : [],
                'cache' => !empty( $cache ) ? true : false,
                'plugPath' => Plug( $this->config['info']['name'], 'folder' ),
                'config' => $this->config['view'],
            ]))->show();
        }
        /**
         * 添加路由
         */
        public function addRouter( $router ) {
            if ( $router !== 'view' ) { return null; }
            Router::add( '/tcore_view/assets', 'GET' )->to(function( Request $request ){
                $target = $request->get['file'] ?? null;
                if ( empty( $target ) ) { return null; }
                $path = Plug( $this->config['info']['name'], 'folder' )."library/assets/{$target}";
                return File::echo( $path );
            })->save();
        }
    };