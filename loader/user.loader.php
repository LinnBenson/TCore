<?php
namespace loader;

use application\model\users;
use application\model\users_login;
use core;

    class user {
        public $code = false;
        public $type = '';
        public $allHeader = [];
        // 访问信息
        public $id = 'unknown';
        public $ip = 'unknown';
        public $ua = 'unknown';
        public $lang = 'unknown';
        public $token = '';
        public $time = 0;
        // 用户权限级别
        public $statusList = [
            'admin' => 1000,
            'agent' => 800,
            'manage' => 600,
            'vip' => 400,
            'approve' => 300,
            'user' => 200,
            'virtual' => 100,
            'visitor' => 1
        ];
        public $timezoneList = [
            'zh-CN' => 8,
        ];
        // 用户信息
        public $state = false;
        public $uid = false;
        public $status = false;
        public $level = 0;
        public $info = [];
        // 初始化用户
        public function __construct( $type ) {
            $this->type = $type;
            if ( $type === 'web' ) { return $this->loadUser_web(); }
            if ( $type === 'cmd' || $type === 'service' ) { return $this->loadUser_localhost(); }
            if ( $type === 'service_link' ) { return $this->loadUser_temporary(); }
            if ( is_array( $type ) ) { return $this->reset( $type ); }
        }
        /**
         * 初始化用户 - web
         */
        private function loadUser_web() {
            $data = [];
            // 加载用户 ID
            $id = $this->header( 'identifier' );
            if ( empty( $id ) ) { $id = $_COOKIE['identifier']; }
            if ( empty( $id ) || !is_uuid( $id ) || $id === '00000000-0000-0000-0000-000000000000' ) {
                $id = UUID();
                setcookie( 'identifier', $id, time() + ( 86400 * 365 ), '/' );
            }
            $data['id'] = $id;
            // 查询访问 IP
            if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
                $data['ip'] = $_SERVER['HTTP_CLIENT_IP'];
            }else if ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                $data['ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }else {
                $data['ip'] = $_SERVER['REMOTE_ADDR'];
            }
            if ( $data['ip'] === '0.0.0.0' ) { $data['ip'] = 'unknown'; }
            // 查询访问 UA
            $data['ua'] = $_SERVER['HTTP_USER_AGENT'];
            // 加载用户语言
            $lang = $this->header( 'lang' );
            if ( empty( $lang ) ) { $lang = $_COOKIE['lang']; }
            core::loadLang( $lang );
            $data['lang'] = core::$textLang;
            $timezone = $this->header( 'timezone' );
            if ( empty( $timezone ) && $timezone !== '0' && $timezone !== 0 ) { $timezone = $_COOKIE['timezone']; }
            $data['time'] = is_numeric( $timezone ) ? intval( $timezone ) : $this->timezoneList[$data['lang']];
            // 加载用户 Toen
            $token = $this->header( 'token' );
            if ( empty( $token ) ) { $token = $_COOKIE['token']; }
            if ( !empty( $token ) ) { $data['token'] = $token; }
            // 设置
            $this->reset( $data );
        }
        /**
         * 临时加载用户
         */
        private function loadUser_temporary() {
            $this->reset([
                'lang' => core::$textLang,
                'time' => $this->timezoneList[core::$textLang]
            ]);
        }
        /**
         * 初始化用户 - localhost
         */
        public function loadUser_localhost() {
            $this->reset([
                'id' => '00000000-0000-0000-0000-000000000000',
                'ip' => '0.0.0.0',
                'ua' => 'localhost',
                'lang' => core::$textLang,
                'time' => $this->timezoneList[core::$textLang]
            ]);
        }
        /**
         * 初始化用户 - service_link
         */
        public function loadUser_service_link( $res = [] ) {
            $set = [];
            if ( empty( $res ) ) { return false; }
            // 处理可能存在的意外结果
            if ( !is_array( $res['header'] ) ) { $res['header'] = []; }
            if ( !is_array( $res['cookie'] ) ) { $res['cookie'] = []; }
            // 加载用户 ID
            $set['id'] = $res['header']['identifier'] ?? $res['cookie']['identifier'];
            if ( !is_uuid( $set['id'] ) && $set['id'] === '00000000-0000-0000-0000-000000000000' ) { $set['id'] = 'unknown'; }
            // 加载用户 IP
            if ( !empty( $res['ip'] ) ) { $set['ip'] = $res['ip']; }
            if ( $set['ip'] === '0.0.0.0' ) { $data['ip'] = 'unknown'; }
            // 加载用户 UA
            if ( !empty( $res['ua'] ) ) { $set['ua'] = $res['ua']; }
            // 加载用户语言
            $set['lang'] = !empty( $res['header']['lang'] ) ? $res['header']['lang'] : $res['cookie']['lang'];
            // 检查用户语言是否有效
            $file = "storage/lang/{$set['lang']}.lang.php";
            if ( empty( $set['lang'] ) || !file_exists( $file ) ) {
                $set['lang'] = core::$textLang;
            }
            // 加载用户时区
            $timezone = $res['header']['timezone'];
            if ( !empty( $timezone ) || $timezone !== '0' || $timezone !== 0 ) { $timezone = $res['header']['timezone']; }
            $set['time'] = is_numeric( $timezone ) ? intval( $timezone ) : $this->timezoneList[$set['lang']];
            // 加载用户 token
            $set['token'] = !empty( $res['header']['token'] ) ? $res['header']['token'] : $res['cookie']['token'];
            return $this->reset( $set );
        }
        /**
         * 修改访问信息
         * - $data array 修改内容
         * ---
         * return null
         */
        public function reset( $data ) {
            $this->id = !empty( $data['id'] ) ? $data['id'] : $this->id;
            $this->ip = !empty( $data['ip'] ) ? $data['ip'] : $this->ip;
            $this->ua = !empty( $data['ua'] ) ? $data['ua'] : $this->ua;
            $this->lang = !empty( $data['lang'] ) ? $data['lang'] : $this->lang;
            $this->time = is_numeric( $data['time'] ) ? $data['time'] : 0;
            $this->token = !empty( $data['token'] ) ? $data['token'] : $this->token;
            if ( !empty( $this->token ) ) { $this->authToken(); }
        }
        /**
         * 修改用户信息
         * - $data array 用户数据
         * ---
         * return boolean 修改结果
         */
        public function setUser( $data ) {
            if (
                empty( $data ) ||
                !is_array( $data ) ||
                empty( $data['id'] ) ||
                empty( $data['status'] ) ||
                empty( $data['enable'] )
            ) { return false; }
            // 生成 Token
            if ( empty( $this->token ) ) {
                $token = $this->setPassword( $data['password'] );
                $this->token = base64_encode( json_encode([ 'uid' => $data['id'] , 'token' => $token ]));
            }
            // 挂载用户信息
            $this->state = true;
            $this->uid = $data['id'];
            $this->status = $data['status'];
            $this->level = $this->getLevel( $data['status'] );
            unset( $data['password'] );
            $this->info = $data;
            return true;
        }
        /**
         * 验证用户 Token
         * ---
         * return boolean 验证结果
         */
        public function authToken() {
            // 验证 Token 是否有效
            $token = $this->token;
            if ( empty( $token ) ) { $this->logout(); return false; }
            $tokenInfo = base64_decode( $token );
            if ( !is_json( $tokenInfo ) ) { $this->logout(); return false; }
            $tokenInfo = json_decode( $tokenInfo, true );
            if ( empty( $tokenInfo['uid'] ) || empty( $tokenInfo['token'] ) ) { $this->logout(); return false; }
            // 查询登录信息
            $checkLogin = users_login::where( 'token', $token )
                            ->where( 'auth', 1 )->where( 'enable', 1 )
                            ->where( 'expired', '>', getTime() )
                            ->where( 'login_id', $this->id )->first();
            if ( !$checkLogin ) { $this->logout(); return false; }
            if ( intval( $tokenInfo['uid'] ) !==  intval( $checkLogin->uid ) ) { $this->logout(); return false; }
            // 检查是否允许登录
            $user = users::find( $checkLogin->uid );
            if ( !config( 'user.allow.login' ) && $user->status !== 'admin'  ) { $this->logout(); return false; }
            // 验证通过
            $checkLogin->expired = getTime( time() + intval( $checkLogin->expired_time ) );
            $checkLogin->login_ip = $this->ip;
            $checkLogin->login_device = $this->ua;
            $checkLogin->save();
            if ( !$user || empty( $user->enable ) ) { $this->logout(); return false; }
            $this->setUser( $user->toArray() );
            return true;
        }
        /**
         * 要求用户注销登录
         * ---
         * return boolean 注销结果
         */
        public function logout( $setCode = true ) {
            if ( $setCode ) { $this->code = 403; }
            $this->token = '';
            $this->state = false;
            $this->uid = false;
            $this->status = false;
            $this->level = 0;
            $this->info = [];
            return true;
        }
        /**
         * 获取用户 Header
         * - $key string 键名
         * ---
         * return string 查询结果
         */
        public function header( $key ) {
            if ( function_exists( 'getallheaders' ) ) {
                $headers = getallheaders();
            }else {
                $headers = [];
                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 5) == 'HTTP_') {
                        $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                        $headers[$headerName] = $value;
                    }
                }
            }
            return $headers[ucfirst( $key )];
        }
        /**
         * 生成用户密码
         * - $text string 用户原密码
         * ---
         * return string 加密后的密码
         */
        public function setPassword( $text ) {
            return encrypt( hash( 'sha256', $text.env( 'APP_KEY', 'TCore' ) ) );
        }
        /**
         * 生成手机号
         * - $qv number 区号
         * - $phone number 手机号
         * ---
         * return string 组合后的号码
         */
        public function setPhone( $qv, $phone ) {
            if ( empty( $qv ) || empty( $phone ) || !is_numeric( $qv ) || !is_numeric( $phone ) ) { return ''; }
            return "+{$qv} {$phone}";
        }
        /**
         * 获取用户级别信息
         * - $status false|number|string 级别
         * ---
         * return number 级别参数
         */
        public function getLevel( $status = false ) {
            if ( is_numeric( $status ) ) { $status = users::get( 'status', $status ); }
            if ( $status === false ) { $status = $this->status; }
            $level = $this->statusList[$status];
            return !empty( $level ) ? $level : 0;
        }
        /**
         * 查询用户信息
         * - $id number 用户 ID
         * ---
         * return array 用户开放信息
         */
        public function userinfo( $id = false ) {
            if ( !empty( $id ) ) {
                $user = users::find( $id );
                if ( !$user ) { return []; }
                $user = $user->toArray();
            }else {
                $user = $this->info;
            }
            if ( empty( $user ) || empty( $user['enable'] ) ) { return []; }
            return [
                'uid' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'nickname' => $user['nickname'],
                'status' => $user['status'],
                'invite' => $user['invite']
            ];
        }
        /**
         * 验证用户级别
         * - $allow array 允许的范围
         * ---
         * return boolean 验证结果
         */
        public function authLevel( $allow ) {
            if ( !is_array( $allow ) || !is_numeric( $allow[0] ) || !is_numeric( $allow[1] ) ) { return false; }
            if ( $this->level < $allow[0] ) { return false; }
            if ( $this->level > $allow[1] ) { return false; }
            return true;
        }
    };