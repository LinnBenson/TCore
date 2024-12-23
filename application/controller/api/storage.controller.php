<?php
use support\middleware\storage;

    class storageController {
        // 文件上传接口
        public function upload( $name ) {
            $config = config( "storage.{$name}" );
            if ( empty( $config ) ) { return task::echo( 2, ['false',['type' => 'upload']] ); }
            $storage = new storage( $name );
            $files = $storage->upload();
            if ( $files ) {
                return task::echo( 0, $files[0] );
            }
            return task::echo( 2, ['false',['type' => 'upload']] );
        }
        /**
         * 获取缓存文件
         */
        public function cache( $file ) {
            $fileDir = "storage/media/cache/{$file}";
            if ( file_exists( $fileDir ) ) {
                $mimeType = mime_content_type( $fileDir );
                header( "Content-Type: {$mimeType}" );
                $expired = 30 * 24 * 60 * 60;
                header( "Cache-Control: max-age={$expired}, public" );
                header( "Expires: ".gmdate( 'D, d M Y H:i:s', time() + $expired )." GMT" );
                readfile( $fileDir );
                return;
            }
            return router::error( 404 );
        }
        /**
         * 获取存储文件
         */
        public function media( $name, $file ) {
            $config = config( 'storage' );
            if ( empty( $config[$name] ) ) { return router::error( 404 ); }
            $storage = new storage( $name );
            $check = $storage->show( $file );
            return $check;
        }
        /**
         * 获取头像
         */
        public function avatar( $uid ) {
            if ( empty( $uid ) ) { return router::error( 404 ); }
            $storage = new storage( 'avatar' );
            $check = $storage->show( "{$uid}.png", 'default.png' );
            return $check;
        }
    }