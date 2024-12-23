<?php
namespace support\method;

/**
 * ---
 * 网络请求工具
 * ---
 */

    class web {
        /**
         * GET 请求
         * - $url href 请求链接
         * - $send array([]) 发送参数
         * ---
         * return any
         */
        public static function get( $url, $send = [] ) {
            // 必须包含请求链接
            if ( empty( $url ) ) { return false; }
            if ( !empty( $send ) && is_array( $send ) ) {
                $url .= '?'.http_build_query( $send );
            }
            return self::custom([
                'type' => 'GET',
                'url' => $url
            ]);
        }
        /**
         * POST 请求
         * - $url href 请求链接
         * - $send array/json([]) 发送参数
         * ---
         * return any
         */
        public static function post( $url, $send = [] ) {
            // 必须包含请求链接
            if ( empty( $url ) ) { return false; }
            return self::custom([
                'type' => 'POST',
                'url' => $url,
                'send' => $send
            ]);
        }
        /**
         * 复杂请求
         * - $data array 请求数据
         * {
         *    'type': GET|POST, 请求方式
         *    'url': string, 请求链接
         *    'send': array/json, 发送参数
         *    'header': array([]), 请求头
         *    'cookie': array([]), Cookie
         *    'timeout': number(30), 超时
         *    'ssl': boolean(true) 证书验证
         * }
         * ---
         * return any
         */
        public static function custom( $data ) {
            // 必须包含请求方式，请求链接
            if ( empty( $data['type'] ) || empty( $data['url'] ) ) { return false; }
            // 初始化参数
            $data['header'] = is_array( $data['header'] ) ? $data['header'] : [];
            $data['ssl'] = isset( $data['ssl'] ) ? $data['ssl'] : true;
            $data['timeout'] = isset( $data['timeout'] ) ? intval( $data['timeout'] ) : 30;
            // 开始发送请求
            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, $data['url'] );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_TIMEOUT, $data['timeout'] );
            if ( $data['type'] === 'POST' ) {
                curl_setopt( $ch, CURLOPT_POST, true );
                if ( is_array( $data['send'] ) ) {
                    curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $data['send'] ) );
                    $data['header'][count( $data['header'] )] = 'Content-Type: application/x-www-form-urlencoded; charset=utf-8';
                }else if ( is_json( $data['send'] ) ) {
                    curl_setopt( $ch, CURLOPT_POSTFIELDS, $data['send'] );
                    $data['header'][count( $data['header'] )] = 'Content-Type: application/json; charset=utf-8';
                }
            }
            // Cookie 参数
            if ( isset( $data['cookie'] ) ) { curl_setopt( $ch, CURLOPT_COOKIE, $data['cookie'] ); }
            // 请求头
            if ( !empty( $data['header'] ) ) { curl_setopt( $ch, CURLOPT_HTTPHEADER, $data['header'] ); }
            // 证书验证
            if ( $data['ssl'] === false ) {
                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            }
            // 回调结果
            $response = curl_exec( $ch );
            if ( curl_errno( $ch ) ) {
                core::log( 'CURL Error: '.curl_error( $ch ), 'core' );
                curl_close( $ch );
                return false;
            }
            curl_close( $ch );
            return $response;
        }
    }