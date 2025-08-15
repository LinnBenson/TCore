<?php
    /**
     * 账户构造器
     */
    namespace Support\Handler;

    use App\Model\User;
    use App\Model\UserToken;

    class Account {
        public $state = false; // 账户挂载状态
        public $uid = null; // 用户ID
        public $level = null; // 用户级别
        public $status = 'VISITOR'; // 用户级别名称
        public $info = null; // 用户信息
        /**
         * 构造用户
         * - $request: 请求对象[Request]
         * - $token: 登录信息[mixed|null]
         */
        public function __construct( $token = null ) {
            $uid = null;
            if ( config( 'account.login.enable' ) && $token instanceof Request ) {
                $request = $token;
                $token = $request->header['token'] ?? $request->cookie['token'] ?? $request->session['token'];
                if ( !empty( $token ) ) {
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
            }else if ( is_numeric( $token ) ) {
                $uid = $token;
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
         * - return array 公开信息
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
         * - $request: 请求对象[Request]
         * - $remember: 是否记住登录状态[bool]
         * - return string Token
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
         * 密码混淆
         * - $text: 密码文本[string]
         * - $garble: 混淆字符[string]|''
         * - return string 新密码
         */
        public static function password( $text, $garble = '' ) {
            return strtoupper( h( "{$text}_{$garble}" ) );
        }
        /**
         * 级别转身份
         * - return string 用户级别名称
         */
        public function levelToStatus() {
            if ( $this->level === null ) { return 'VISITOR'; }
            // 配置
            $config = config( 'account.status' );
            foreach( $config as $status => $number ) {
                if ( $this->level <= $number ) { return $status; }
            }
            return 'VISITOR';
        }
        /**
         * 身份级别验证
         * - $max: 最大允许级别[number]
         * - return boolean 验证结果
         */
        public function auth( $max ) {
            if ( empty( $max ) || !is_numeric( $max ) || !$this->state || !is_numeric( $this->level ) ) { return false; }
            return floatval( $max ) >= $this->level ? true : false;
        }
    }