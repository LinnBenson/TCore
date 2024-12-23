<?php

use application\model\users_login;
use application\server\userServer;
use support\method\tool;
use support\middleware\request;

    class userController {
        // 添加虚拟用户
        public function add_virtual() {
            $res = request::get([
                'username' => 'must:true,type:username,min:4,max:12',
                'email' => 'must:true,type:email',
                'phone' => 'must:false,type:phone',
                'password' => 'must:true',
                'agent' => 'type:number'
            ]);
            $res['status'] = 'virtual';
            $check = userServer::create( $res );
            if ( $check === true ) {
                return task::echo( 0, ['true',['type'=>'create']] );
            }
            return $check;
        }
        // 修改配置
        public function edit_config() {
            $res = request::get([
                'allow_login' => 'type:boolean',
                'allow_register' => 'type:boolean',
                'verify_email' => 'type:boolean',
                'verify_phone' => 'type:boolean',
                'invite' => 'type:boolean',
                'expired' => 'must:true,type:number'
            ]);
            // 修改配置
            $config = config( 'user' );
            $config['allow']['login'] = $res['allow_login'];
            $config['allow']['register'] = $res['allow_register'];
            $config['verify']['email'] = $res['verify_email'];
            $config['verify']['phone'] = $res['verify_phone'];
            $config['invite'] = $res['invite'];
            $config['expired'] = $res['expired'];
            // 保存
            $check = tool::coverConfig( 'config/user.config.php', $config );
            return task::echo( $check ? 0 : 2, [$check ? 'true' : 'false',['type'=>'edit']]);
        }
        // 下线所有设备
        public function down_all() {
            users_login::query()->update([
                'enable' => 0
            ]);
            return task::echo( 0, ['true',['type'=>'operate']]);
        }
        // 下线指定设备
        public function down() {
            $res = request::get([
                'type' => 'must:true',
                'uid' => 'type:number',
                'login_ip' => '',
                'login_id' => ''
            ]);
            $type = $res['type'];
            $value = $res[$type];
            if ( empty( $value ) ) { return task::echo( 2, ['error.input'] ); }
            users_login::where( $type, $value )->update([
                'enable' => 0
            ]);
            return task::echo( 0, ['true',['type'=>'operate']]);
        }
    }