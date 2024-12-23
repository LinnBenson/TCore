<?php

use application\server\serviceServer;
use support\middleware\view;

    class baseController {
        /**
         * 系统基础信息
         */
        public function index() {
            core::async( 'chat', 'print/group', [], true);
            $result = [
                'info' => [
                    'name' => config( 'app.name' ),
                    'host' => config( 'app.host' ),
                    'debug' => config( 'app.debug' ),
                    'timezone' => config( 'app.timezone' ),
                    'lang' => config( 'app.lang' ),
                    'version' => config( 'app.version' )
                ],
                'theme' => config( 'theme' ),
                'userinfo' => [],
                'service' => []
            ];
            // 用户信息
            if ( task::$user->state ) {
                $result['userinfo'] = task::$user->userinfo();
            }
            // 服务项目
            $config = serviceServer::getAllService();
            foreach( $config as $name => $item ) {
                if ( !empty( $item['public'] ) ) {
                    $result['service'][] = [
                        'name' => $name,
                        'link' => $item['public'],
                        'protocol' => $item['protocol'],
                        'state' => $item['state'] ? true : false
                    ];
                }
            };
            return task::echo( 0, $result );
        }
        /**
         * 获取系统语言
         */
        public function lang( $project ) {
            view::$project = $project;
            $text = array_merge( core::$text, view::addArgv() );
            header( 'Content-Type: application/json' );
            $expired = 30 * 24 * 60 * 60;
            header( "Cache-Control: max-age={$expired}, public" );
            header( "Expires: ".gmdate( 'D, d M Y H:i:s', time() + $expired )." GMT" );
            return json_encode( $text, JSON_UNESCAPED_UNICODE );
        }
    }