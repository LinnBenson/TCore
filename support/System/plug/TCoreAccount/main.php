<?php
    /**
     * 账户管理插件
     */

use Support\Handler\Router;

    return new class {
        public $plug = [
            'name' => 'TCoreAccount',
            'title' => '账户管理',
            'version' => '1.0.0',
            'author' => 'TCore',
            'description' => 'TCore System Account Management',
            'auto' => [
                'RouteRegistration' => 'addRouter',
                'QueryLanguagePackage' => 'addLang'
            ],
        ];
        public $path = true;
        /**
         * 插件初始化
         */
        public function init() {
            require_once "{$this->path}support/AccountController.controller.php";
            Plug( 'TCoreView' )->library([
                'account.header' => "{$this->path}resource/view/header.view.html",
                'account.footer' => "{$this->path}resource/view/footer.view.html",
                'account.login' => "{$this->path}resource/view/login.view.html",
                'account.user' => "{$this->path}resource/view/user.view.html",
                'account.edit' => "{$this->path}resource/view/edit.view.html",
                'account.safety' => "{$this->path}resource/view/safety.view.html",
                'account.authError' => "{$this->path}resource/view/authError.view.html"
            ]);
        }
        /**
         * 路由注册
         */
        public function addRouter( $type ) {
            if ( $type === 'view' ) {
                Router::add( '/account' )->group(function(){
                    // 访问资源
                    Router::add( '/assets' )->assets( "{$this->path}resource/view/assets/" )->save();
                    // 登录到您的账户
                    Router::add( '/login' )->auth(function( $request ){
                        return $request->user->state ? ToUrl( '/account/user' ) : true;
                    })->view( 'account.login' )->save();
                    // 管理您的账户
                    Router::add( '/user' )->auth(function( $request ){
                        return !$request->user->state ? ToUrl( '/account/login' ) : true;
                    })->view( 'account.user' )->save();
                    // 修改用户资料
                    Router::add( '/edit' )->auth(function( $request ){
                        return !$request->user->state ? ToUrl( '/account/login' ) : true;
                    })->view( 'account.edit' )->save();
                    // 安全项管理
                    Router::add( '/safety' )->auth(function( $request ){
                        return !$request->user->state ? ToUrl( '/account/login' ) : true;
                    })->view( 'account.safety' )->save();
                    // 身份验证失败
                    Router::add( '/auth/error' )->auth(function( $request ){
                        if ( !$request->user->state ) {
                            $link = '';
                            if ( !empty( $request->get['back'] ) ) { $link = "?back=".$request->get['back']; }
                            if ( !empty( $request->get['pass'] ) ) { $link = !empty( $link ) ? "{$link}&pass=".$request->get['pass'] : "?pass=".$request->get['pass']; }
                            if ( !empty( $request->get['invite'] ) ) { $link = !empty( $link ) ? "{$link}&invite=".$request->get['invite'] : "?invite=".$request->get['invite']; }
                            return ToUrl( "/account/login{$link}" );
                        }
                        return true;
                    })->view( 'account.authError' )->save();
                })->url( 'account/login' )->save();
            }else if ( $type === 'api' ) {
                Router::add( '/account' )->group(function() {
                    // 登录
                    Router::add( '/login' )->controller( [ \Plug\TCoreAccount\AccountController::class, 'login' ] )->save();
                    // 修改资料
                    Router::add( '/edit' )->auth(function( $request ){ return $request->user->state; })->controller( [ \Plug\TCoreAccount\AccountController::class, 'edit' ] )->save();
                    // 上传头像
                    Router::add( '/upload/avatar' )->auth(function( $request ){ return $request->user->state; })->controller( [ \Plug\TCoreAccount\AccountController::class, 'uploadAvatar' ] )->save();
                    // 下线所有账户
                    Router::add( '/offlineall' )->auth(function( $request ){ return $request->user->state; })->controller( [ \Plug\TCoreAccount\AccountController::class, 'offlineAll' ] )->save();
                    // 修改密码
                    Router::add( '/edit/password' )->auth(function( $request ){ return $request->user->state; })->controller( [ \Plug\TCoreAccount\AccountController::class, 'editPassword' ] )->save();
                    // 发送验证码
                    Router::add( '/verify/{{type}}' )->controller( [ \Plug\TCoreAccount\AccountController::class, 'verify' ] )->save();
                    // 修改安全项
                    Router::add( '/safety' )->auth(function( $request ){ return $request->user->state; })->controller( [ \Plug\TCoreAccount\AccountController::class, 'safety' ] )->save();
                    // 访问头像
                    Router::add( '/avatar/{{uid}}' )->controller( [ \Plug\TCoreAccount\AccountController::class, 'avatar' ] )->save();
                });
            }
        }
        /**
         * 语言包注册
         */
        public function addLang( $data ) {
            if ( $data['target'] !== 'account' ) { return false; }
            $file = "{$this->path}resource/lang/{$data['lang']}.lang.php";
            if ( !file_exists( $file ) ) { $file = "{$this->path}resource/lang/".config( 'app.lang' ).".lang.php"; }
            return file_exists( $file ) ? require_once $file : false;
        }
    };