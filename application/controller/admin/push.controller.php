<?php

use application\model\push_record;
use support\method\push;
use support\method\tool;
use support\middleware\request;

    class pushController {
        // 建立推送
        public function add_push() {
            $res = request::get([
                'async' => 'type:boolean',
                'type' => 'must:true,type:username',
                'to' => 'must:true',
                'title' => 'must:true',
                'content' => 'must:true,safe:false',
            ]);
            $config = config( 'push' );
            if ( empty( $config[$res['type']] ) ) {
                return task::echo( 2, ['error.input'] );
            }
            // 准备发送的数据
            $send = [
                'uid' => task::$user->uid,
                'type' => $res['type'],
                'title' => $res['title'],
                'to' => $res['to'],
                'content' => $res['content'],
                'source' => 'Administrator',
                'send_id' => task::$user->id,
                'send_ip' => task::$user->ip,
            ];
            // 开始发送
            $run = false;
            switch ( $res['type'] ) {
                case 'email':
                    $run = $res['async'] ? core::async( 'async', 'push_email', $send, 3 ) : push::email( $send );
                    break;
                case 'bark':
                    $run = $res['async'] ? core::async( 'async', 'push_bark', $send, 3 ) : push::bark( $send );
                    break;
                case 'telegram':
                    $run = $res['async'] ? core::async( 'async', 'push_telegram', $send, 3 ) : push::telegram( $send );
                    break;

                default: break;
            }
            // 记录下推送
            if ( $run && !$res['async'] ) { push_record::create( $send ); }
            return task::result( 'send', $run );
        }
        // 修改配置信息
        public function edit_config() {
            $res = request::get([
                'email_default' => '',
                'email_host' => '',
                'email_port' => '',
                'email_username' => '',
                'email_password' => '',
                'email_from' => '',
                'email_encrypt' => '',
                'bark_default' => '',
                'bark_host' => '',
                'telegram_default' => '',
                'telegram_host' => ''
            ]);
            $config = config( 'push' );
            $cache = config( 'cache' );
            foreach( $res as $key => $value ) {
                $keys = explode( '_', $key );
                if ( $config[$keys[0]][$keys[1]] !== $value ) {
                    $cache['push'][$keys[0]][$keys[1]] = $value;
                }
            }
            // 开始注入配置缓存
            $run = tool::coverConfig( "config/cache.config.php", $cache );
            return task::result( 'save', $run );
        }
    }