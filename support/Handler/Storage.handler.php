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
            $this->path = inFolder( $config['path'] ?? '/storage/media/cache' );
            $this->allow = $config['allow'] ?? '*';
            $this->maxSize = $config['maxSize'] ?? 1024 * 1024 * 10;
            $this->delete = $config['delete'] ?? 3 * 24 * 60 * 60;
        }
        /**
         * 上传文件
         * - 用于处理 form 上传的文件
         * - @param array $file 上传的文件
         * - @return File|boolean 上传结果
         */
        public function upload( $file ) {
            // 获取上传的内容
            if ( !is_array( $file ) ) { return false; }
            // 文件检查
            $ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
            if ( $this->allow !== '*' && is_array( $this->allow ) && !in_array( $ext, $this->allow ) ) {
                return false;
            }
            $size = $file['size'];
            if ( $size > $this->maxSize ) { return false; }
            // 生成文件名
            $name = uuid();
            $path = "{$this->path}/{$name}.{$ext}";
            // 移动文件
            if ( !move_uploaded_file( $file['tmp_name'], $path ) ) {
                return false;
            }
            // 上传完成
            return new File( $path );
        }
        /**
         * 复制文件
         * - 用于复制当前文件
         * - @param File $file 文件对象
         * - @param string $to 目标路径
         * - @param boolean $delete 是否删除源文件
         * - @return File|boolean 复制结果
         */
        public function copy( $file, $to, $delete = false ) {
            if ( is_string( $file ) ) {
                $file = file_exists( $file ) ? $file : "{$this->path}/{$file}";
                $file = new File( $file );
            }
            if ( !is_object( $file ) || empty( $file->id ) || !is_string( $to ) || empty( $to ) ) { return false; }
            $config = config( "storage.{$to}" );
            if ( !is_array( $config ) || empty( $config['path'] ) ) { return false; }
            $to = "{$to}/{$file->name}";
            return $file->copy( $to, $delete );
        }
    }