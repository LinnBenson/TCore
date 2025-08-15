<?php
    namespace App\Controller;

    use Support\Handler\Account;
    use Support\Handler\Request;

    /**
     * AccountController 控制器
     */
    class AccountController {
        /**
         * 登录接口
         */
        public function login( Request $request ) {
            if ( !config( 'account.login.enable' ) ) { return $request->echo( 1, ['base.error.close'] ); }
            $res = $request->vaildata([
                'username' => 'required|type:string|min:3|max:20',
                'password' => 'required|type:password|min:6|max:20',
                'remember' => 'type:boolean|default:false'
            ]);
            $type = 'username';
            if ( filter_var( $res['username'], FILTER_VALIDATE_EMAIL ) ) { $type = 'email'; }
            if ( preg_match( '/^\+\d{1,3}(\s\d+)+$/', $res['username'] ) === 1 ) { $type = 'phone'; }
            $user = \App\Model\User::where( $type, $res['username'] )->first();
            if ( !$user ) { return $request->echo( 1, ['account.error.login'] ); }
            if ( empty( $user->enable ) ) { return $request->echo( 1, ['account.error.blacklist'] ); }
            $res['password'] = Account::password( $res['password'], config( 'app.key' ) );
            if ( $res['password'] !== $user->password ) { return $request->echo( 1, ['account.error.login'] ); }
            $user = new Account( $user->id );
            return $request->echo( 0, [
                'token' => $user->token( $request, $res['remember'] ),
                'user' => $user->share()
            ]);
        }
    }