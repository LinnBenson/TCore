<?php

namespace Plug\TCoreAccount;

use App\Model\PushRecord;
use App\Model\User;
use App\Model\UserToken;
use App\Server\AccountServer;
use Support\Handler\Account;
use Support\Handler\Redis;
use Support\Handler\Request;
use Support\Helper\Push;
use Support\Helper\Tool;

    class AccountController {
        private $source = 'Account verify';
        /**
         * 用户登录
         */
        public function login( Request $request ) {
            $res = $request->vaildata([
                'user' => 'type:string|must',
                'password' => 'type:string|must',
                'remember' => 'type:boolean'
            ]);
            if ( !config( 'account.login.enable' ) ) { return $request->echo( 2, ['base.error.close'] ); }
            // 查询试图登录的类型
            $login = 'username'; // 登录类型
            if ( filter_var( $res['user'], FILTER_VALIDATE_EMAIL ) ) { $login = 'email'; }
            if ( preg_match( '/^\+\d{1,3}(\s\d+)+$/', $res['user'] ) === 1 ) { $login = 'phone'; }
            // 登录信息比对
            $cache = User::where( $login, $res['user'] )->first();
            if ( !empty( $cache ) && is_object( $cache ) && !empty( $cache->id ) ) {
                $res['password'] = $request->user->password( $res['password'], $cache->device );
                if ( $res['password'] === $cache->password ) {
                    // 登录成功
                    $user = new Account( $cache->id );
                    return $request->echo( 0, [
                        'user' => $user->share(),
                        'token' => $user->token( $request, $res['remember'] )
                    ]);
                }
            }
            // 登录失败
            return $request->echo( 1, ['account.api.loginError'] );
        }
        /**
         * 发送验证码
         */
        public function verify( Request $request, $type ) {
            if ( empty( $type ) || !ctype_alnum( $type ) ) { return $request->echo( 1, ['base.send:base.false'] ); }
            $res = $request->vaildata([
                'receive' => 'must|type:string|max:50'
            ]);
            // 频繁发送检查
            $frequently = PushRecord::where( 'type', $type )
                ->where( 'receive', $res['receive'] )
                ->where( 'source', $this->source )
                ->where( 'created_at', '>=', toDate( time() - 60 ) )
                ->exists();
            if ( !empty( $frequently ) ) { return $request->echo( 2, [ 'account.api.verifyFrequently' ] ); }
            // 准备发送数据
            $code = Tool::rand( 6, 'number' );
            $send = [
                    'uid' => $request->user->state ? $request->user->uid : null,
                    'title' => $request->t( 'account.api.verifyTitle' ),
                    'receive' => $res['receive'],
                    'source' => $this->source,
                    'remark' => $code
            ];
            // 发送到邮箱
            if ( $type === 'email' && config( 'account.verify.email' ) && filter_var( $res['receive'], FILTER_VALIDATE_EMAIL ) && config( 'account.verify.email' ) ) {
                $send['content'] = $request->t( 'account.api.verifyContent', [ 'code' => $code ] );
                $send = Push::email( $send, true );
                return $request->echo( $send, ['base.send'] );
            }
            // 发送到手机号
            if ( $type === 'phone' && config( 'account.verify.phone' ) && preg_match( '/^\+\d{1,3}(\s\d+)+$/', $res['receive'] ) && config( 'account.verify.phone' ) ) {

            }
            return $request->echo( 1, ['base.send:base.false'] );
        }
        /**
         * 修改用户资料
         */
        public function edit( Request $request ) {
            $res = $request->vaildata([
                'nickname' => 'must|type:string|max:20',
                'username' => 'must|type:alnum|max:12',
                'slogan' => 'must|type:string|max:50',
                'edituserinfo' => 'type:verify|must'
            ]);
            Redis::delCache( "VerifyImg_edituserinfo_{$request->id}" );
            $exists = User::where( 'username', $res['username'] )->where( 'id', '!=', $request->user->uid )->exists();
            if ( $exists ) {
                return $request->echo( 2, ['account.api.repeatUsername'] );
            }
            $request->user->info->nickname = $res['nickname'];
            $request->user->info->username = $res['username'];
            $request->user->info->slogan = $res['slogan'];
            return $request->echo( $request->user->info->save(), ['base.edit'] );
        }
        /**
         * 修改密码
         */
        public function editPassword( Request $request ) {
            $res = $request->vaildata([
                'oldPassword' => 'type:string|must',
                'newPassword1' => 'type:string|must',
                'newPassword2' => 'type:string|must'
            ]);
            if ( $res['newPassword1'] !== $res['newPassword2'] ) {
                return $request->echo( 2, ['account.api.password'] );
            }
            // 检查密码是否正确
            $res['oldPassword'] = $request->user->password( $res['oldPassword'], $request->user->info->device );
            if ( $res['oldPassword'] !== $request->user->info->password ) {
                return $request->echo( 2, ['account.api.passwordError'] );
            }
            // 验证通过
            $request->user->info->password = $request->user->password( $res['newPassword1'], $request->user->info->device );
            $state = $request->user->info->save();
            if ( $state ) {
                $this->offlineAll( $request );
            }
            return $request->echo( $state, ['base.edit'] );
        }
        /**
         * 修改安全项
         */
        public function safety( Request $request ) {
            $res = $request->vaildata([
                'type' => 'type:alnum|must',
                'password' => 'type:string|must',
                'code' => 'type:alnum'
            ]);
            // 修改邮箱
            if ( $res['type'] === 'email' ) {
                $edit = $request->vaildata([
                    'email' => 'type:email',
                    'editemail' => 'type:verify|must'
                ]);
                Redis::delCache( "VerifyImg_editemail_{$request->id}" );
                // 检查密码是否正确
                $res['password'] = $request->user->password( $res['password'], $request->user->info->device );
                if ( $res['password'] !== $request->user->info->password ) {
                    return $request->echo( 2, ['account.api.passwordError'] );
                }
                // 检查是否需要填写验证
                if ( config( 'account.verify.email' ) ) {
                    $state = AccountServer::authReceive( 'email', $res['email'], $res['code'], $this->source );
                    if ( !$state ) {
                        return $request->echo( 2, ['account.api.codeError', ['name' => 'Email']] );
                    }
                }
                // 重复性检查
                $repeat = AccountServer::repeat( 'email', $edit['email'] );
                if ( $repeat ) { return $request->echo( 2, ['account.api.repeatEmail'] ); }
                // 验证通过
                $request->user->info->email = $edit['email'];
                return $request->echo( $request->user->info->save(), ['base.edit'] );
            }
            // 修改手机号
            if ( $res['type'] === 'phone' ) {
                $edit = $request->vaildata([
                    'phone' => 'type:phone',
                    'editphone' => 'type:verify|must'
                ]);
                Redis::delCache( "VerifyImg_editphone_{$request->id}" );
                // 检查密码是否正确
                $res['password'] = $request->user->password( $res['password'], $request->user->info->device );
                if ( $res['password'] !== $request->user->info->password ) {
                    return $request->echo( 2, ['account.api.passwordError'] );
                }
                // 检查是否需要填写验证
                if ( config( 'account.verify.phone' ) ) {
                    $state = AccountServer::authReceive( 'phone', $res['phone'], $res['code'], $this->source );
                    if ( !$state ) {
                        return $request->echo( 2, ['account.api.codeError', ['name' => 'Phone']] );
                    }
                }
                // 重复性检查
                $repeat = AccountServer::repeat( 'phone', $edit['phone'] );
                if ( $repeat ) { return $request->echo( 2, ['account.api.repeatPhone'] ); }
                // 验证通过
                $request->user->info->phone = $edit['phone'];
                return $request->echo( $request->user->info->save(), ['base.edit'] );
            }
            return $request->echo( false, ['base.edit'] );
        }
        /**
         * 下线所有用户
         */
        public function offlineAll( Request $request ) {
            $set = UserToken::where( 'uid', $request->user->uid )->update([
                'enable' => false
            ]);
            return $request->echo( !empty( $set ) ? true : false, ['base.operate'] );
        }
        /**
         * 获取用户头像
         */
        public function avatar( Request $request, $uid ) {
            if ( empty( $uid ) || !is_numeric( $uid ) ) { return null; }
            $avatar = "storage/media/avatar/{$uid}.png";
            if ( !file_exists( $avatar ) ) {
                $avatar = "public/library/avatar.png";
            }
            $avatar = ToFile( $avatar );
            $expired = $request->user->state && intval( $request->user->uid ) === intval( $uid ) ? 0 : 259200;
            return $avatar ? $avatar->echo( $expired ) : null;
        }
        /**
         * 上传头像
         */
        public function uploadAvatar( Request $request ) {
            // 暂时保存至缓存
            $upload = Controller( 'BaseController@upload', $request );
            // 检查上传状态
            if ( !is_json( $upload ) ) { return $request->echo( 1, ['base.upload:base.false'] ); }
            $upload = json_decode( $upload, true );
            if ( !is_array( $upload['data'] ) || !is_string( $upload['data']['upload'] ) ) {
                return $request->echo( 1, ['base.upload:base.false'] );
            }
            // 检查上传文件
            $file = ToFile( $upload['data']['upload'] );
            if ( empty( $file->id ) ) { return $request->echo( 1, ['base.upload:base.false'] ); }
            $allow = [ 'jpg', 'png', 'gif', 'jpeg' ];
            if ( !in_array( $file->ext, $allow ) ) {
                $file->delete();
                return $request->echo( 1, ['base.upload:base.false'] );;
            }
            // 保存用户头像
            $dir = inFolder( "storage/media/avatar/{$request->user->uid}.png" );
            return $request->echo( !empty( $file->copy( $dir, true ) ) ? true : false, ['base.upload'] );
        }
    }