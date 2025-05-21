<?php

namespace Support\Handler;

use App\Model\User;
use App\Model\UserToken;

    /**
     * 账户构造器
     */
    class Account {
        public $state = false; // 用户构造状态
        public $uid = null; // 用户 UID
        public $level = null; // 用户级别
        public $status = null; // 用户级别名称
        public $info = null; // 用户信息
        /**
         * 构造用户
         * - 用于构造一个用户
         * - @param mixed $request 登录方式
         * - @return void
         */
        public function __construct( $request = null ) {
            $uid = null;
            // 明确以指定用户登录
            if ( is_numeric( $request ) ) { $uid = $request; }
            // 访问自动判断
            if ( config( 'account.login.enable' ) && is_object( $request ) ) {
                $token = $request->header['token'] ?? $request->cookie['token'] ?? $request->session['token'];
                if ( empty( $token ) ) { return null; }
                $token = UserToken::where( 'device', $request->id )->where( 'token', $token )->first();
                if (
                    !config( 'account.login.enable' ) ||
                    empty( $token ) ||
                    empty( $token->enable )
                ) {
                    $request->code = 401;
                    return null;
                }
                $remember = !empty( $token->remember ) ? config( 'account.login.maintain' )[1] : config( 'account.login.maintain' )[0];
                if ( time() > ( strtotime( $token->updated_at ) + $remember ) ) { $request->code = 401; return null; }
                $uid = $token->uid;
            }
            if ( !is_numeric( $uid ) ) { return null; }
            // 从数据库获取用户
            $user = User::where( 'id', $uid )->first();
            if ( empty( $user ) || empty( $user->enable ) ) { return null; }
            $this->state = true;
            $this->uid = $user->id;
            $this->level = $user->level;
            $this->status = $this->levelToStatus( $user->level );
            $this->info = $user;
            return true;
        }
        /**
         * 共享信息
         * - 用于输出用户可公开的信息
         * - @return array 公开信息
         */
        public function share() {
            if ( !$this->state ) { return []; }
            return [
                'uid' => $this->uid,
                'username' => $this->info->username,
                'email' => $this->info->email,
                'phone' => $this->info->phone,
                'nickname' => $this->info->nickname,
                'slogan' => $this->info->slogan,
                'status' => $this->status,
                'invite' => $this->info->invite
            ];
        }
        /**
         * 生成密钥
         * - 用于生成用户的可登录 Token
         * - @param Request $request Request
         * - @param bool $remember 是否记住登录状态
         * - @return string Token
         */
        public function token( Request $request, $remember = false ) {
            if ( !$this->state ) { return ''; }
            // 生成 Token
            $token = strtoupper( h( "{$this->uid}_{$this->password}_".time() ) );
            // 记录本次密钥
            return UserToken::updateOrCreate(
                [ 'uid' => $this->uid, 'device' => $request->id ],
                [
                    'token'   => $token,
                    'enable'  => true,
                    'remember' => $remember
                ]
            ) ? $token : null;
        }
        /**
         * 密码生成
         * - 用于规范密码的加密方式
         * - @param string $text 原始密码
         * - @param string $garble 混淆字符
         * - @return string 新密码
         */
        public function password( $text, $garble = '' ) {
            return strtoupper( h( "{$text}_{$garble}" ) );
        }
        /**
         * 级别转身份
         * - 用于输入用户可视化级别
         * - @param int $level 用户级别
         * - @return string|null 用户级别名称
         */
        public function levelToStatus( $level ) {
            if ( empty( $level ) || !is_numeric( $level ) ) { return null; }
            // 配置
            $config = config( 'account.status' );
            foreach( $config as $status => $number ) {
                if ( $level <= $number ) { return $status; }
            }
            return 'VISITOR';
        }
        /**
         * 身份级别验证
         * - 用于验证用户身份是否合法
         * - @param int $max 最大允许级别
         * - @return boolean 验证结果
         */
        public function auth( $max ) {
            if ( empty( $max ) || !is_numeric( $max ) || !$this->state || !is_numeric( $this->level ) ) { return false; }
            return floatval( $max ) >= $this->level ? true : false;
        }
    }