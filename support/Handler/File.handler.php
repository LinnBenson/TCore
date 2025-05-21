<?php

namespace Support\Handler;

    class File {
        public $id = null; // 文件ID
        public $path, $ext, $name, $size; // 文件路径, 文件扩展名, 文件名称, 文件大小
        /**
         * 构造函数
         * - new File( $file:string(文件路径) );
         * - return void
         */
        public function __construct( $file ) {
            if ( !is_string( $file ) || !file_exists( $file ) ) { return null; }
            $this->id = pathinfo( $file, PATHINFO_FILENAME );
            $this->path = $file;
            $this->ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
            $this->name = pathinfo( $file, PATHINFO_BASENAME );
            $this->size = filesize( $file );
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
            $to = inFolder( $to );
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
         * 生成文件获取链接
         * - 用于生成文件获取链接
         * - @return string|null 文件链接
         */
        function link() {
            if ( !startsWith( $this->path, 'storage/media' ) ) { return "/{$this->path}"; }
            $path = explode( '/', $this->path );
            return "/storage/{$path[2]}/".str_replace( '.', '_', $path[3] );
        }
        /**
         * 输出文件
         * - 用于输出文件
         * - @param int $expires 缓存时间
         * - @return null
         */
        function echo( $expires = 2592000 ) {
            if ( !file_exists( $this->path ) ) { return null; }
            $finfo = finfo_open( FILEINFO_MIME_TYPE );
            $mimeType = finfo_file( $finfo, $this->path );
            finfo_close( $finfo );
            // 清除干扰缓存的头（如果已发送过就无效）
            header_remove( 'Pragma' );
            header_remove( 'Cache-Control' );
            header_remove( 'Expires' );
            // 设置响应头
            header( 'Content-Type: ' . $mimeType );
            header( 'Content-Length: ' . filesize( $this->path ) );
            // 设置缓存
            header( 'Cache-Control: public, max-age=' . $expires );
            header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + $expires ) . ' GMT' );
            // 输出文件内容
            readfile( $this->path );
            return '';
        }
    }