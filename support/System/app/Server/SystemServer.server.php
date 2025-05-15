<?php

namespace App\Server;

    class SystemServer {
        /**
         * 重置密钥
         */
        public static function resetKey() {
            $env = file_get_contents( '.env' );
            if ( strpos( $env, 'APP_KEY=' ) === false ) { return false; }
            $newKey = strtoupper( h( uuid() ) );
            $env = str_replace( 'APP_KEY="'.env( 'APP_KEY' ).'"', 'APP_KEY="'.$newKey.'"', $env );
            $state = !empty( file_put_contents( '.env', $env ) ) ? true : false;
            self::clearCache();
            return $state;
        }
        /**
         * 清理系统缓存
         */
        public static function clearCache() {
            $cache = 'storage/cache/';
            return !empty( deleteDir( $cache ) ) ? true : false;
        }
        /**
         * 清理上传缓存
         */
        public static function clearUpload() {
            $cache = config( 'storage.cache.path' );
            return !empty( deleteDir( $cache ) ) ? true : false;
        }
        /**
         * 修改 Debug 模式
         */
        public static function editDebug() {
            $env = file_get_contents( '.env' );
            if ( strpos( $env, 'APP_DEBUG=' ) === false ) { return false; }
            $oldValue = config( 'app.debug' ) ? 'true' : 'false';
            $newValue = config( 'app.debug' ) ? 'false' : 'true';
            $env = str_replace( 'APP_DEBUG='.$oldValue, 'APP_DEBUG='.$newValue, $env );
            $state = !empty( file_put_contents( '.env', $env ) ) ? true : false;
            self::clearCache();
            return $state;
        }
    }