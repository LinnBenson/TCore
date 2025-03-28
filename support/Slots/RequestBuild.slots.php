<?php

namespace Support\Slots;

    class RequestBuild {
        public $type = null;
        public $router = null;
        public $id = null;
        public $ua = null;
        public $ip = null;
        public $code = 200;
        public $method = null;
        public $target = null;
        public $source = null;
        public $header = [];
        public $get = [];
        public $post = [];
        public $cookie = [];
        public $session = [];
        public $uid = null;
        public $user = null;
        public $lang = null;
        public $share = [ 'saveId' => null, 'echo' => null ];
        /**
         * HTTP 请求
         */
        protected function http() {
            $this->method = $_SERVER['REQUEST_METHOD'];
            $this->target = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
            $this->source = $_SERVER['HTTP_REFERER'] ?? '/';
            $this->header = array_change_key_case( getallheaders(), CASE_LOWER );
            $this->get = $_GET;
            $this->post = $_POST;
            $this->cookie = $_COOKIE;
            if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
                $this->ip = $_SERVER['HTTP_CLIENT_IP'];
            }else if ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }else {
                $this->ip = $_SERVER['REMOTE_ADDR'];
            }
            $this->ua = $_SERVER['HTTP_USER_AGENT'];
            // 加载类型
            $this->httpLoadCheck();
            // 设置共享方法
            $this->share = [
                // 保存 ID
                'saveId' => function( $id ) { setcookie( 'id', $id, time() + 30 * 24 * 60 * 60, '/' ); },
                'echo' => function( $res, $header ) {
                    if ( is_array( $res ) && is_numeric( $res['code'] ) ) { http_response_code( $res['code'] ?? $this->code ); }
                    foreach ( $header as $key => $value ) { header( "{$key}: {$value}" ); }
                    return $res;
                }
            ];
        }
        /**
         * http 来源二次处理
         */
        private function httpLoadCheck() {
            if ( empty( $this->target ) ) { $this->target = '/'; }
            $target = explode( '/', $this->target );
            switch ( $target[1] ) {
                case 'api': $this->router = 'api'; break;
                case 'storage': $this->router = 'storage'; break;
                case 'app': $this->router = 'app'; break;

                default: $this->router = 'view'; break;
            }
            $this->target = preg_replace( '/^\/(api|app|storage)/', '', $this->target );
            if ( empty( $this->target ) ) { $this->target = '/'; }
        }
    }