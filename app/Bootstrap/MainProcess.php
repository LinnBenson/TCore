<?php

namespace App\Bootstrap;

use Support\Handler\Request;

    class MainProcess {
        /**
         * 配置覆盖
         * - 调用 config( ... ) 时触发，如果返回 null 则继续查询配置文件，否则返回对应值。
         * - $key string 配置键名
         */
        public static function ConfigCover( $key ) {
            return null;
        }
        /**
         * 启动回调
         * - 处理 Bootstrap 必须构造时传入的回调函数。
         * - $callback function 回调函数
         */
        public static function BootstrapCallback( $callback ) {
            return $callback;
        }
        /**
         * 初始化请求
         * - 请求构造时触发，必须返回请求对象。
         * - $request object 请求实体
         */
        public static function InitRequest( Request $request ) {
            return $request;
        }
        /**
         * 返回结果
         * - 请求结束时触发，必须返回一个响应结果。
         * - $request object 请求实体
         * - $data any 响应结果
         */
        public static function ResponseResult( Request $request, $data ) {
            return $data;
        }
        /**
         * 数据回调响应格式
         * - 请求回调时触发，返回数据。（ 返回 null 时由系统执行 ）
         * - $request object 请求实体
         * - $res array 系统回调格式
         */
        public static function EchoResult( Request $request, $res ) {
            return $res;
        }
    }