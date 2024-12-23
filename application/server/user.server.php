<?php
namespace application\server;

use application\model\media;
use application\model\push_record;
use application\model\users;
use application\model\users_login;
use core;
use Illuminate\Support\Carbon;
use support\method\tool;
use support\middleware\storage;
use support\middleware\view;
use task;

    class userServer {
        /**
         * 登录接口
         */
        public static function login( $res ) {
            $user = task::$user;
            // 检查登录类型
            if ( !empty( $res['user'] ) ) {
                $login = $res['user'];
                $loginType = 'username';
                if ( filter_var( $login, FILTER_VALIDATE_EMAIL ) ) {
                    $loginType = 'email';
                }
            }else {
                $login = $res['phone'];
                $loginType = 'phone';
            }
            if ( empty( $login ) ) { return task::echo( 2, ['account.loginError'] ); }
            // 查询登录用户
            $check = users::where( $loginType, $login )->select( 'id', 'password', 'status' )->first();
            if ( !$check ) { return task::echo( 2, ['account.loginError'] ); }
            // 验证登录用户
            $password = decrypt( $check->password );
            if ( hash( 'sha256', $res['password'].env( 'APP_KEY', 'TCore' ) ) !== $password ) {
                return task::echo( 2, ['account.loginError'] );
            }
            // 检查是否允许登录
            if ( !config( 'user.allow.login' ) && $check->status !== 'admin' ) { return task::echo( 2, ['error.close'] ); }
            // 验证通过
            $check = users::find( $check->id );
            if ( !$check ) { return task::echo( 2, ['false',['type'=>'account.login']] ); }
            if ( empty( $check->enable ) ) { return task::echo( 2, ['account.enable'] ); }
            $user->setUser( $check->toArray() );
            // 写入登录信息
            $expired_time = config( 'user.expired' );
            if ( !empty( $res['remember'] ) ) { $expired_time = config( 'user.expired' ) * 10; }
            $check = users_login::where( 'login_id', $user->id )->first();
            if ( $check ) {
                $check = $check->fill([
                    'uid' => $user->uid,
                    'type' => 'view',
                    'token' => $user->token,
                    'auth' => 1,
                    'enable' => 1,
                    'expired' => getTime( time() + $expired_time ),
                    'expired_time' => $expired_time,
                    'login_ip' => $user->ip,
                    'login_device' => $user->ua,
                ])->save();
            }else {
                $check = users_login::create([
                    'uid' => $user->uid,
                    'type' => 'view',
                    'token' => $user->token,
                    'auth' => 1,
                    'enable' => 1,
                    'expired' => getTime( time() + $expired_time ),
                    'expired_time' => $expired_time,
                    'login_id' => $user->id,
                    'login_ip' => $user->ip,
                    'login_device' => $user->ua,
                ]);
            }
            if ( !$check ) { return task::echo( 2, ['false',['type'=>'account.login']] ); }
            // 返回 Token
            return task::echo( 0, [
                'token' => $user->token,
                'userinfo' => $user->userinfo()
            ]);
        }
        /**
         * 创建用户
         */
        public static function create( $res ) {
            $create = [];
            // 验证用户名
            if ( empty( $res['username'] ) ) { return task::echo( 2, ['account.usernameNo'] ); }
            $exists = users::where( 'username', $res['username'] )->exists();
            if ( $exists ) { return task::echo( 2, ['account.usernameNo'] ); }
            $create['username'] = $res['username'];
            // 验证邮箱
            if ( empty( $res['email'] ) ) { return task::echo( 2, ['account.emailNo'] ); }
            $exists = users::where( 'email', $res['email'] )->exists();
            if ( $exists ) { return task::echo( 2, ['account.emailNo'] ); }
            $create['email'] = $res['email'];
            // 验证密码
            if ( empty( $res['password'] ) ) { return task::echo( 2, ['account.passwordNo'] ); }
            $create['password'] = task::$user->setPassword( $res['password'] );
            // 验证手机号
            if ( !empty( $res['phone'] ) ) {
                $exists = users::where( 'phone', $res['phone'] )->exists();
                if ( $exists ) { return task::echo( 2, ['account.phoneNo'] ); }
                $create['phone'] = $res['phone'];
            }
            // 组建上级代理
            $agent = false;
            if ( isset( $res['agent'] ) ) {
                $agent = users::find( $res['agent'] );
            }else {
                // 查询邀请码
                $invite = config( 'user.invite' );
                if ( $invite && empty( $res['invite'] ) ) { return task::echo( 2, ['account.inviteNo'] ); }
                if ( !empty( $res['invite'] ) ) {
                    $agent = users::where( 'invite', $res['invite'] )->first();
                }
                if ( $invite && !$agent ) { return task::echo( 2, ['account.inviteNo'] ); }
            }
            if ( $agent ) {
                $create['agent'] = $agent->id;
                $create['agent_node'] = "{$agent->agent_node}{$agent->id}|";
            }else {
                $create['agent_node'] = "|";
            }
            // 生成邀请码
            $create['invite'] = tool::rand( 8 );
            for ( $i=0; $i < 99; $i++ ) {
                $exists = users::where( 'invite', $create['invite'] )->exists();
                if ( !$exists ) { break; }
                $create['invite'] = tool::rand( 8 );
            }
            // 组建用户昵称
            $create['nickname'] = !empty( $res['nickname'] ) ? $res['nickname'] : $res['username'];
            // 组建用户身份
            $create['status'] = !empty( $res['status'] ) ? $res['status'] : 'user';
            // 组建注册信息
            $create['register_ip'] = task::$user->ip;
            $create['register_device'] = task::$user->ua;
            // 开始添加用户
            $create['enable'] = 1;
            $check = users::create( $create );
            if ( !$check ) { return task::echo( 2, ['false',['type'=>'create']] ); }
            return true;
        }
        /**
         * 删除用户
         */
        public static function delete( $uid ) {
            core::$db::beginTransaction(); // 开始事务
            try {
                // 从数据库删除用户
                $check = users::where( 'id', $uid )->delete();
                if ( !$check ) { throw new Exception( "Failed to delete user with ID: {$uid}" ); }
                // 清空用户上传的内容
                $media = media::where( 'uid', $uid )->get();
                if ( $media ) {
                    $media = $media->toArray();
                    foreach( $media as $item ) {
                        $storage = $item['storage']; $storage = config( "storage.{$storage}.dir" );
                        $file = "storage/media{$storage}/{$item['file']}";
                        $abbreviation = "storage/media/abbreviation/{$item['file']}";
                        if ( file_exists( $file ) ) {
                            $check = unlink( $file );
                            if ( !$check ) { throw new Exception( "Failed to delete file: {$file}" ); }
                        }
                        if ( file_exists( $abbreviation ) ) {
                            $check = unlink( $abbreviation );
                            if ( !$check ) { throw new Exception( "Failed to delete file: {$abbreviation}" ); }
                        }
                    }
                }
                // 清除头像
                $storage = new storage( 'avatar' );
                $storage->delete( "{$uid}.png" );
                $abbreviation = "storage/media/abbreviation/{$uid}.png";
                if ( file_exists( $abbreviation ) ) {
                    $check = unlink( $abbreviation );
                    if ( !$check ) { throw new Exception( "Failed to delete file: {$abbreviation}" ); }
                }
                // 清空其它关联表
                $about = [ 'users_login', 'media' ];
                foreach( $about as $class ) {
                    $class = "\application\model\\{$class}";
                    $class::where( 'uid', $uid )->delete();
                    $exists = $class::where( 'uid', $uid )->exists();
                    if ( $exists ) { throw new Exception( "Failed to delete related records in table: {$class}" ); }
                };
                core::$db::commit(); // 事务提交
                return true;
            } catch ( \Exception $e ) {
                core::$db::rollBack(); // 事务回滚
                core::log( $e, 'core' );
                return false;
            }
        }
        /**
         * 发送验证码
         */
        public static function verify( $res ) {
            // 查询是否验证频繁
            $id = task::$user->id; $ip = task::$user->ip;
            $toadyCount = push_record::where( 'source', 'verify' )
            ->whereDate( 'created_at', Carbon::today() )
            ->where(function( $query )use( $id, $ip ) {
                $query->where( 'send_id', $id )->orWhere( 'send_ip', $ip );
            })->count();
            if ( $toadyCount > 10 ) { return false; } // 当天发送超过 10 条
            $check = push_record::where( 'source', 'verify' )
                ->where( 'created_at', '>=', Carbon::now()->subSeconds( 50 ) )
                ->where(function( $query )use( $id, $ip ) {
                    $query->where( 'send_id', $id )->orWhere( 'send_ip', $ip );
                })->exists();
            if ( $check ) { return false; } // 50 秒内存在发送记录
            // 准备数据
            $send = [
                'title' => t( 'account.verify' ),
                'source' => 'verify',
                'send_id' => $id,
                'send_ip' => $ip,
            ];
            // 生成邀请码
            $send['text'] = $code = tool::rand( 6, 'number' );
            // 开始发送
            if ( !empty( $res['email'] ) ) {
                $send['to'] = $res['email'];
                $send['content'] = view::show( 'system/verify', [ 'code' => $code ] );
                return core::async( 'async', 'push_email', $send, 2 );
            }
            return false;
        }
        /**
         * 修改用户资料
         */
        public static function edit( $res ) {
            $update = [];
            // 验证用户名
            if ( $res['username'] !== task::$user->info['username'] ) {
                if ( empty( $res['username'] ) ) { return task::echo( 2, ['account.usernameNo'] ); }
                $exists = users::where( 'username', $res['username'] )->exists();
                if ( $exists ) { return task::echo( 2, ['account.usernameNo'] ); }
                $update['username'] = $res['username'];
            }
            // 验证昵称
            if ( $res['nickname'] !== task::$user->info['nickname'] ) {
                $update['nickname'] = $res['nickname'];
            }
            // 更新签名
            if ( $res['slogan'] !== task::$user->info['slogan'] ) {
                $update['slogan'] = $res['slogan'];
            }
            // 开始更新用户
            $run = true;
            if ( !empty( $update ) ) {
                $run = users::where( 'id', task::$user->uid )->update( $update );
            }
            // 修改头像
            if ( $run && !empty( $res['avatar'] ) ) {
                // 检查是否上传头像
                $storage = new storage( 'avatar' );
                $run = $storage->cacheSave( $res['avatar'], [
                    'name' => task::$user->uid.".png"
                ]);
            }
            return $run;
        }
        /**
         * 修改绑定关系
         */
        public static function bind( $res ) {
            $update = [];
            // 手机号
            if ( $res['phone'] !== task::$user->info['phone'] ) {
                $exists = users::where( 'phone', $res['phone'] )->exists();
                if ( $exists ) { return task::echo( 2, ['account.phoneNo'] ); }
                $update['phone'] = $res['phone'];
            }
            // 邮箱
            if ( $res['email'] !== task::$user->info['email'] ) {
                if ( empty( $res['email'] ) ) { return task::echo( 2, ['account.emailNo'] ); }
                $exists = users::where( 'email', $res['email'] )->exists();
                if ( $exists ) { return task::echo( 2, ['account.emailNo'] ); }
                $update['email'] = $res['email'];
            }
            // 开始更新
            $run = true;
            if ( !empty( $update ) ) {
                $run = users::where( 'id', task::$user->uid )->update( $update );
            }
            return $run;
        }
        /**
         * 安全项修改
         */
        public static function safety( $res ) {
            // 比对原密码
            $user = users::find( task::$user->uid );
            if ( empty( $user ) || empty( $res['password_new'] ) ) { return false; }
            $password = decrypt( $user->password );
            if ( hash( 'sha256', $res['password'].env( 'APP_KEY', 'TCore' ) ) !== $password ) {
                return task::echo( 2, ['account.loginError'] );
            }
            // 开始修改密码
            $user->password = task::$user->setPassword( $res['password_new'] );
            $run = $user->save();
            if ( $run ) {
                // 下线所有用户
                users_login::where( 'uid', task::$user->uid )->update([ 'enable' => 0 ]);
                return true;
            }
            return false;
        }
    }