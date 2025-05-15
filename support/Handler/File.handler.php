<?php

namespace Support\Handler;

    class File {
        public $id = null; // 文件ID
        public $path, $ext, $name, $storage; // 文件路径, 文件扩展名, 文件名称, 存储器名称
        /**
         * 构造函数
         * - new File( $file:string(文件路径) );
         * - return void
         */
        public function __construct( $file ) {
            if ( is_string( $file ) ) { $file = strtolower( $file ); }
            if ( is_string( $file ) && !file_exists( $file ) && str_starts_with( $file, '/storage' ) && str_contains( $file, '_' ) ) {
                $fileCheck = str_replace( '_', '.', $file );
                $fileCheck = explode( '/', $fileCheck );
                $storage = config( "storage.{$fileCheck[2]}" );
                if ( is_array( $storage ) || !empty( $storage['path'] ) ) {
                    $file = "{$storage['path']}/{$fileCheck[3]}";
                }
            }
            if ( !is_string( $file ) || !file_exists( $file ) ) { return; }
            $this->id = pathinfo( $file, PATHINFO_FILENAME );
            $this->path = $file;
            $this->ext = pathinfo( $file, PATHINFO_EXTENSION );
            $this->name = pathinfo( $file, PATHINFO_BASENAME );
            if ( is_uuid( $this->id ) ) {
                $fileCheck = dirname( $this->path );
                foreach( config( 'storage' ) as $key => $item ) {
                    if ( $item['path'] === $fileCheck ) {
                        $this->storage = $key;
                        break;
                    }
                }
            }
        }
        /**
         * 删除文件
         * - 用于删除当前文件
         * - @return boolean 删除结果
         */
        public function delete() {
            if ( empty( $this->id ) ) { return false; }
            if ( file_exists( $this->path ) ) {
                if ( unlink( $this->path ) ) {
                    $this->id = null;
                    return true;
                }
            }
            return false;
        }
        /**
         * 复制文件
         * - 用于复制当前文件
         * - @param string $to 目标路径
         * - @param boolean $delete 是否删除源文件
         * - @return File|boolean 复制结果
         */
        public function copy( $to, $delete = false ) {
            if ( empty( $this->id ) ) { return false; }
            if ( !is_string( $to ) || empty( $to ) ) { return false; }
            inFolder( $to );
            if ( copy( $this->path, $to ) ) {
                if ( $delete && file_exists( $this->path ) ) {
                    $this->id = null;
                    unlink( $this->path );
                }
                return new File( $to );
            }
            return false;
        }
        /**
         * 输出文件
         * - 用于输出文件
         * - @param string $file 文件路径
         * - @param int $expires 缓存时间
         * - @return null
         */
        public static function echo( $file, $expires = 2592000 ) {
            if ( !file_exists( $file ) ) { return null; }
            $finfo = finfo_open( FILEINFO_MIME_TYPE );
            $mimeType = finfo_file( $finfo, $file );
            finfo_close( $finfo );
            // 设置响应头
            header( 'Content-Type: ' . $mimeType );
            header( 'Content-Length: ' . filesize( $file ) );
            // 设置缓存
            header( 'Cache-Control: public, max-age=' . $expires );
            header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + $expires ) . ' GMT' );
            // 输出文件内容
            readfile( $file );
            exit;
        }
    }