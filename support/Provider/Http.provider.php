<?php
    /**
     * HTTP 服务提供者
     */
    namespace Support\Provider;

    use Support\Handler\Request;
    use Support\Handler\Router;

    class Http {
        /**
         * 初始化 HTTP 服务
         * - return string 返回初始化结果
         */
        public static function init() {
            // 初始化请求数据
            $request = new Request([
                'type' => 'Http',
                'method' => $_SERVER['REQUEST_METHOD'],
                'target' => parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ),
                'source' => $_SERVER['HTTP_REFERER'],
                'header' => array_change_key_case( getallheaders(), CASE_LOWER ),
                'get' => $_GET,
                'post' => self::getPostData(),
                'cookie' => $_COOKIE,
                'file' => $_FILES,
                'session' => [],
                'ip' => self::ip(),
                'share' => self::setShare()
            ]);
            // 加载路由
            $filter = explode( '/', $request->target );
            Router::load( $request->router, isset( $filter[1] ) ? "/{$filter[1]}" : "/" );
            return Router::search( $request, $request->router, $request->target, $request->method );
        }
        /**
         * 设置共享数据
         * - return array 返回共享数据
         */
        private static function setShare() {
            return [
                'SaveRequestId' => function( $id ) {
                    setcookie( 'id', $id, time() + 30 * 24 * 60 * 60, '/' );
                },
                'FormattingReturnedData' => function( Request $request, $res, $setHeader ) {
                    if ( is_array( $res ) && is_numeric( $res['code'] ) ) { http_response_code( $res['code'] ?? $request->code ); }
                    foreach ( $setHeader as $key => $value ) { header( "{$key}: {$value}" ); }
                    return $res;
                }
            ];
        }
        /**
         * 获取 POST 数据
         * - return array 返回 POST 数据
         */
        private static function getPostData() {
            if ( !empty( $_POST ) ) { return $_POST; }
            $rawBody = file_get_contents( 'php://input' );
            if ( empty( $rawBody ) ) { return []; }
            $json = json_decode( $rawBody, true );
            if ( json_last_error() === JSON_ERROR_NONE && is_array( $json ) ) { return $json; }
            parse_str( $rawBody, $parsed );
            return is_array( $parsed ) ? $parsed : [];
        }
        /**
         * 获取客户端 IP 地址
         * - return string 返回客户端 IP 地址
         */
        private static function ip() {
            if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
                return $_SERVER['HTTP_CLIENT_IP'];
            }else if ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }else {
                return $_SERVER['REMOTE_ADDR'];
            }
            return 'UNKNOWN';
        }
    }