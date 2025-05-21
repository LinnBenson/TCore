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
        /**
         * 创建模版
         */
        public static function template( $type, $name ) {
            $types = [
                'model' => [
                    'file' => __file( 'support/System/resource/template/model.php' ),
                    'path' => 'app/Model/{{name}}.model.php'
                ]
            ];
            if ( empty( $types[$type] ) ) { return false; }
            $config = $types[$type];
            $template = file_get_contents( $config['file'] );
            $template = str_replace( '{{name}}', $name, $template );
            $config['path'] = inFolder( str_replace( '{{name}}', $name, $config['path'] ) );
            return !empty( file_put_contents( $config['path'], $template ) ) ? true : false;
        }
    }