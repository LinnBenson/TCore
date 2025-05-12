<?php

namespace Support\Slots;

use Support\Handler\Session;

    /**
     * 请求构建器 - 辅助工具
     */
    class RequestBuild {
        public $type, $router, $code, $share,
        $id, $ua, $ip, $method, $target, $source, $header, $get, $post, $file, $cookie, $session, $user, $lang;
        /**
         * 自动构建
         * - 根据请求类型自动构建请求
         * - @return bool 构建结果
         */
        public function autoBuild() {
            if ( is_array( $this->type ) ) {
                // 手动构建请求
                $this->edit( $this->type );
            }else {
                // 自动构建请求
                switch ( $this->type ) {
                    case 'http':
                        $this->autoBuildHttp(); break;

                    default: return false; break;
                }
            }
            // 如果是 HTTP 请求，则进行二次处理
            if ( $this->type === 'http' ) { $this->httpLoadCheck(); }
            // 构建完成
            return true;
        }
        /**
         * 自动构建 - HTTP
         */
        private function autoBuildHttp() {
            // 获取基本参数
            $this->method = $_SERVER['REQUEST_METHOD'];
            $this->target = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
            $this->source = $_SERVER['HTTP_REFERER'] ?? '/';
            $this->header = array_change_key_case( getallheaders(), CASE_LOWER );
            $this->get = $_GET;
            $this->post = $_POST;
            $this->file = $_FILES;
            $this->cookie = $_COOKIE;
            $this->session = Session::get() ?? [];
            // 获取请求 IP
            if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
                $this->ip = $_SERVER['HTTP_CLIENT_IP'];
            }else if ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }else {
                $this->ip = $_SERVER['REMOTE_ADDR'];
            }
            // 获取请求 UA
            $this->ua = $_SERVER['HTTP_USER_AGENT'];
            // 设置共享方法
            $this->share = [
                // 保存 ID
                'saveId' => function( $id ) {
                    setcookie( 'id', $id, time() + 30 * 24 * 60 * 60, '/' );
                },
                'echo' => function( $res, $header ) {
                    if ( is_array( $res ) && is_numeric( $res['code'] ) ) { http_response_code( $res['code'] ?? $this->code ); }
                    foreach ( $header as $key => $value ) { header( "{$key}: {$value}" ); }
                    return $res;
                }
            ];
            return true;
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
            return true;
        }
        /**
         * 修改构建的请求
         * - 传入一个数组用于自定义请求
         * - @param array $data 请求数据
         * - @return bool 构建结果
         */
        public function edit( $data ) {
            $this->type = $data['type'] ?? null;
            $this->router = $data['router'] ?? $this->router;
            $this->code = $data['code'] ?? $this->code;
            $this->share = $data['share'] ?? $this->share;
            $this->id = $data['id'] ?? $this->id;
            $this->ua = $data['ua'] ?? $this->ua;
            $this->ip = $data['ip'] ?? $this->ip;
            $this->method = $data['method'] ?? $this->method;
            $this->target = $data['target'] ?? $this->target;
            $this->source = $data['source'] ?? $this->source;
            $this->header = $data['header'] ?? $this->header;
            $this->get = $data['get'] ?? $this->get;
            $this->post = $data['post'] ?? $this->post;
            $this->file = $data['file'] ?? $this->file;
            $this->cookie = $data['cookie'] ?? $this->cookie;
            $this->share = $data['share'] ?? $this->share;
            return true;
        }
    }