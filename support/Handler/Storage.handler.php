<?php

namespace Support\Handler;

    class Storage {
        public $type, $path, $allow, $maxSize, $delete; // 存储器类型, 存储器路径, 允许的文件类型, 最大文件大小, 删除时间
        /**
         * 构造存储器
         * - new Storage( $name:string(存储器名称) );
         * - return void
         */
        public function __construct( $name ) {
            $config = config( "storage.{$name}" );
            if ( !is_array( $config ) || empty( $config['path'] ) ) { $config = config( 'storage.cache' ); }
            $this->type = $config['type'] ?? 'public';
            $this->path = inFolder( $config['path'] ?? '/storage/media/cache/' );
            $this->allow = $config['allow'] ?? '*';
            $this->maxSize = $config['maxSize'] ?? 1024 * 1024 * 10;
            $this->delete = $config['delete'] ?? 3 * 24 * 60 * 60;
        }
        /**
         * 上传文件
         * - 用于处理 form 上传的文件
         * - @param array|string $file 上传的文件
         * - @return File|boolean 上传结果
         */
        public function upload( $file ) {
            $type = $ext = $size = null;
            if ( is_string( $file ) ) {
                $type = 'local';
                $file = ToFile( $file );
                if ( empty( $file ) ) { return false; }
                $ext = $file->ext;
                $size = $file->size;
            }
            if ( is_array( $file ) ) {
                $type = 'form';
                $ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
                $size = $file['size'];
            }
            // 文件检查
            if ( $this->allow !== '*' && is_array( $this->allow ) && !in_array( $ext, $this->allow ) ) {
                return false;
            }
            if ( is_numeric( $this->maxSize ) &&  $size > $this->maxSize ) { return false; }
            // 生成文件名
            $name = uuid();
            $path = "{$this->path}{$name}.{$ext}";
            // 移动文件
            if ( $type === 'local' && empty( $file->copy( $path, true ) ) ) { return false; }
            if ( $type === 'form' && !move_uploaded_file( $file['tmp_name'], $path ) ) { return false; }
            // 上传完成
            return ToFile( $path );
        }
        /**
         * 获取存储器中的文件
         * - 用于获取存储器中的文件
         * - @param string $name 文件名称
         * - @return File|null 文件对象
         */
        public function file( $name ) {
            return ToFile( $this->filePath( $name ) );
        }
        /**
         * 获取存储器中的文件路径
         * - 用于获取存储器中的文件路径
         * - @param string $name 文件名称
         * - @return string|null 文件路径
         */
        public function filePath( $name ) {
            if ( str_contains( $name, '/' ) ) {
                $name = explode( '/', $name );
                $name = $name[ count( $name ) - 1 ];
            }
            $name = str_replace( '_', '.', $name );
            return "{$this->path}{$name}";
        }
    }