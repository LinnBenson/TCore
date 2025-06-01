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
        public $entrance = null;
        /**
         * 插件初始化
         */
        public function init() {
            require_once "{$this->path}support/AccountController.controller.php";
            $this->entrance = config( 'api.account', 'account' );
            Plug( 'TCoreView' )->library([
                "{$this->entrance}.header" => "{$this->path}resource/view/header.view.html",
                "{$this->entrance}.footer" => "{$this->path}resource/view/footer.view.html",
                "{$this->entrance}.login" => "{$this->path}resource/view/login.view.html",
                "{$this->entrance}.register" => "{$this->path}resource/view/register.view.html",
                "{$this->entrance}.user" => "{$this->path}resource/view/user.view.html",
                "{$this->entrance}.edit" => "{$this->path}resource/view/edit.view.html",
                "{$this->entrance}.safety" => "{$this->path}resource/view/safety.view.html",
                "{$this->entrance}.authError" => "{$this->path}resource/view/authError.view.html"
            ]);
        }
        /**
         * 路由注册
         */
        public function addRouter( $type ) {
            if ( $type === 'view' ) {
                // 无需登录
                Router::add( "/{$this->entrance}" )->name( 'ViewAny' )->group(function(){
                    // 访问资源
                    Router::add( '/assets' )->assets( "{$this->path}resource/view/assets/" )->save();
                    // 身份验证失败
                    Router::add( '/auth/error' )->auth(function( $request ){
                        if ( !$request->user->state ) {
                            $link = '';
                            if ( !empty( $request->get['back'] ) ) { $link = "?back=".$request->get['back']; }
                            if ( !empty( $request->get['pass'] ) ) { $link = !empty( $link ) ? "{$link}&pass=".$request->get['pass'] : "?pass=".$request->get['pass']; }
                            if ( !empty( $request->get['invite'] ) ) { $link = !empty( $link ) ? "{$link}&invite=".$request->get['invite'] : "?invite=".$request->get['invite']; }
                            return ToUrl( "/{$this->entrance}/login{$link}" );
                        }
                        return true;
                    })->view( "{$this->entrance}.authError" )->save();
                })->url( "{$this->entrance}/login" )->save();
                // 需要未登录
                Router::add( "/{$this->entrance}" )->name( 'ViewMustNoLogin' )->auth(function( $request ){ return $request->user->state ? ToUrl( "/{$this->entrance}/user" ) : true; })->group(function(){
                    // 登录到您的账户
                    Router::add( '/login' )->view( "{$this->entrance}.login" )->save();
                    // 注册新账户
                    Router::add( '/register' )->view( "{$this->entrance}.register" )->save();
                })->save();
                // 需要登录
                Router::add( "/{$this->entrance}" )->name( 'ViewMustLogin' )->auth(function( $request ){ return !$request->user->state ? ToUrl( "/{$this->entrance}/login" ) : true; })->group(function(){
                    // 管理您的账户
                    Router::add( '/user' )->view( "{$this->entrance}.user" )->save();
                    // 修改用户资料
                    Router::add( '/edit' )->view( "{$this->entrance}.edit" )->save();
                    // 安全项管理
                    Router::add( '/safety' )->view( "{$this->entrance}.safety" )->save();
                })->save();
            }else if ( $type === 'api' ) {
                // 无需登录
                Router::add( "/{$this->entrance}" )->name( 'ApiAny' )->group(function() {
                    // 登录
                    Router::add( '/login' )->controller( [ \Plug\TCoreAccount\AccountController::class, 'login' ] )->save();
                    // 发送验证码
                    Router::add( '/verify/{{type}}' )->controller( [ \Plug\TCoreAccount\AccountController::class, 'verify' ] )->save();
                    // 访问头像
                    Router::add( '/avatar/{{uid}}' )->controller( [ \Plug\TCoreAccount\AccountController::class, 'avatar' ] )->save();
                });
                // 需要登录
                Router::add( "/{$this->entrance}" )->name( 'ApiMustLogin' )->auth(function( $request ){ return $request->user->state; })->group(function() {
                    // 修改安全项
                    Router::add( '/safety' )->controller( [ \Plug\TCoreAccount\AccountController::class, 'safety' ] )->save();
                    // 修改资料
                    Router::add( '/edit' )->controller( [ \Plug\TCoreAccount\AccountController::class, 'edit' ] )->save();
                    // 上传头像
                    Router::add( '/upload/avatar' )->controller( [ \Plug\TCoreAccount\AccountController::class, 'uploadAvatar' ] )->save();
                    // 下线所有账户
                    Router::add( '/offlineall' )->controller( [ \Plug\TCoreAccount\AccountController::class, 'offlineAll' ] )->save();
                    // 修改密码
                    Router::add( '/edit/password' )->controller( [ \Plug\TCoreAccount\AccountController::class, 'editPassword' ] )->save();
                });
            }
        }
        /**
         * 语言包注册
         */
        public function addLang( $data ) {
            if ( $data['target'] !== $this->entrance ) { return false; }
            $file = "{$this->path}resource/lang/{$data['lang']}.lang.php";
            if ( !file_exists( $file ) ) { $file = "{$this->path}resource/lang/".config( 'app.lang' ).".lang.php"; }
            return file_exists( $file ) ? require_once $file : false;
        }
    };