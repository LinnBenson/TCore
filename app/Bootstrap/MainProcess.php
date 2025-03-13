<?php
    class MainProcess {
        /**
         * 配置覆盖
         * - 调用 config( ... ) 时触发，如果返回 null 则继续查询配置文件，否则返回对应值。
         */
        public static function ConfigCover( $key ) {
            return null;
        }
        /**
         * 启动回调
         * - 处理 Bootstrap 构造时传入的回调函数。
         */
        public static function BootstrapCallback( $callback ) {
            return $callback;
        }
    }