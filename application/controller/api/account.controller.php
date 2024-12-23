<?php

use application\model\push_record;
use application\model\users;
use application\model\users_login;
use application\server\userServer;
use Illuminate\Support\Carbon;
use support\middleware\request;

    class accountController {
        /**
         * 登录接口
         */
        public function login() {
            $res = request::get([
                'user' => '',
                'phone' => 'type:phone',
                'password' => 'must:true',
                'remember' => 'type:boolean',
            ]);
            return userServer::login( $res );
        }
        /**
         * 发送验证码
         */
        public function verify() {
            $res = request::get([
                'email' => 'must:true,type:email'
            ]);
            $run = userServer::verify( $res );
            return task::result( 'send', $run );
        }
        /**
         * 找回密码验证
         */
        public function retrieve_verify() {
            $res = request::get([
                'user' => 'must:true'
            ]);
            $email = $res['user'];
            if ( !filter_var( $res['user'], FILTER_VALIDATE_EMAIL ) ) {
                $email = users::where( 'username', $res['user'] )->value( 'email' );
            }
            $run = true;
            if ( $email ) {
                $run = userServer::verify([ 'email' => $email ]);
            }
            return task::result( 'send', $run );
        }
        /**
         * 注册接口
         */
        public function register() {
            if ( !config( 'user.allow.register' ) ) { return task::echo( 2, ['error.close'] ); }
            $res = request::get([
                'username' => 'must:true,type:username,min:4,max:12',
                'email' => 'must:true,type:email',
                'verify' => config( 'user.verify.email' ) ? 'must:true,type:username' : 'type:username',
                'password' => 'must:true',
                'invite' => config( 'user.invite' ) ? 'must:true' : '',
            ]);
            // 查询验证码是否有效
            if ( config( 'user.verify.email' ) ) {
                $check = $this->checkCode( $res );
            }
            if ( is_json( $check ) ) { return $check; }
            // 开始添加账户
            $run = userServer::create( $res );
            if ( is_json( $run ) ) { return $run; }
            return task::result( 'account.register', $run );
        }
        /**
         * 修改资料
         */
        public function edit() {
            $res = request::get([
                'avatar' => '',
                'username' => 'must:true,type:username,min:4,max:12',
                'nickname' => 'must:true,min:1,max:28',
                'slogan' => 'max:120'
            ]);
            // 开始修改账户
            $run = userServer::edit( $res );
            if ( is_json( $run ) ) { return $run; }
            return task::result( 'edit', $run );
        }
        /**
         * 修改绑定关系
         */
        public function bind() {
            if ( !config( 'user.allow.register' ) ) { return task::echo( 2, ['error.close'] ); }
            $res = request::get([
                'phone' => 'type:phone',
                'email' => 'must:true,type:email',
                'verify' => config( 'user.verify.email' ) ? 'must:true,type:username' : 'type:username'
            ]);
            // 查询验证码是否有效
            if ( config( 'user.verify.email' ) ) {
                $check = $this->checkCode( $res );
            }
            if ( is_json( $check ) ) { return $check; }
            // 开始修改账户绑定信息
            $run = userServer::bind( $res );
            if ( is_json( $run ) ) { return $run; }
            return task::result( 'edit', $run );
        }
        /**
         * 安全项修改
         */
        public function safety() {
            $res = request::get([
                'password' => 'must:true',
                'password_new1' => 'must:true',
                'password_new2' => 'must:true',
            ]);
            if ( $res['password_new1'] !== $res['password_new2'] ) {
                return task::echo( 2, ['account.passwordFail'] );
            }
            $res['password_new'] = $res['password_new1'];
            // 开始修改
            $run = userServer::safety( $res );
            if ( is_json( $run ) ) { return $run; }
            return task::result( 'edit', $run );
        }
        /**
         * 找回密码
         */
        public function retrieve() {
            $res = request::get([
                'user' => 'must:true',
                'verify' => 'must:true,type:username',
                'password' => 'must:true',
            ]);
            $res['email'] = $res['user'];
            if ( !filter_var( $res['user'], FILTER_VALIDATE_EMAIL ) ) {
                $res['email'] = users::where( 'username', $res['user'] )->value( 'email' );
            }
            if ( empty( $res['email'] ) ) { return task::echo( 2, ['account.verify_error'] ); }
            $check = $this->checkCode( $res );
            if ( is_json( $check ) ) { return $check; }
            // 开始重置密码
            $run = users::where( 'email', $res['email'] )->update([
                'password' => task::$user->setPassword( $res['password'] )
            ]);
            if ( $run ) {
                // 下线所有用户
                $uid = users::where( 'email', $res['email'] )->value( 'id' );
                users_login::where( 'uid', $uid )->update([ 'enable' => 0 ]);
            }
            return task::result( 'edit', $run );
        }
        /**
         * 查询验证码
         */
        private function checkCode( $res ) {
            $code = push_record::where( 'source', 'verify' )
                ->where( 'type', 'email' )
                ->where( 'to', $res['email'] )
                ->whereNull( 'remark' )
                ->where( 'created_at', '>=', Carbon::now()->subMinutes( 10 ) )
                ->latest( 'created_at' )
                ->first();
            if ( empty( $res['verify'] ) || empty( $code ) || $code->content !== $res['verify'] ) {
                return task::echo( 2, ['account.verify_error'] );
            }
            if ( $code ) { $code->remark = 'used'; $code->save(); }
            return true;
        }
    }