<?php

namespace App\Server;

use App\Model\PushRecord;
use App\Model\User;
use Support\Helper\Tool;

    class AccountServer{
        /**
         * 账户验证
         */
        public static function authReceive( $type, $receive, $code, $source ) {
            if ( empty( $receive ) || empty( $code ) ) { return false; }
            $data = PushRecord::where( 'type', $type )
                ->where( 'receive', $receive )
                ->where( 'source', $source )
                ->where( 'created_at', '>=', toDate( time() - 600 ) )
                ->orderBy( 'id', 'desc' )
                ->first();
            if ( empty( $data ) || $data->remark !== $code ) {
                return false;
            }
            $data = PushRecord::where( 'type', $type )
                ->where( 'receive',$receive )
                ->where( 'source', $source )->update([ 'remark' => null ]);
            return true;
        }
        /**
         * 重复检查
         */
        public static function repeat( $word, $value ) {
            $exists = User::where( $word, $value )->exists();
            return $exists ? true : false;
        }
        /**
         * 创建账户
         */
        public static function create( $data ) {
            // 创建用户
            $user = new User();
            $user->username = $data['username'];
            $user->email = $data['email'] ?? null;
            $user->phone = $data['phone'] ?? null;
            $user->password = $data['password'];
            $user->nickname = $data['nickname'] ?? $data['username'];
            $user->level = $data['level'] ?? 500000;
            $user->enable = $data['enable'] ?? true;
            $user->invite = Tool::rand( 8 );
            $user->agent = $data['agent'] ?? null;
            $user->agent_node = $data['agent_node'] ?? '|';
            $user->network = $data['network'] ?? null;
            $user->device = $data['device'] ?? null;
            return !empty( $user->save() );
        }
        /**
         * 删除账户
         */
        public static function delete( $uid ) {
            // 删除头像
            $avatar = "storage/media/avatar/{$uid}.png";
            $avatar = ToFile( $avatar );
            if ( $avatar && !empty( $avatar->id  )) {
                $state = $avatar->delete();
                if ( empty( $state ) ) { return false; }
            }
            // 删除 Token 记录
            $state = PushRecord::where( 'uid', $uid )->delete();
            if ( empty( $state ) ) { return false; }
            // 删除用户
            $state = User::where( 'id', $uid )->delete();
            return !empty( $state );
        }
    };