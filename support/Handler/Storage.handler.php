<?php

namespace Support\Handler;

use Support\Helper\Tool;

    class Storage {
        public $type, $path, $allow, $maxSize, $delete; // 存储器类型, 存储器路径, 允许的文件类型, 最大文件大小, 删除时间
        /**
         * 构造存储器
         * - Storage::__construct( $config:string(存储器名称) );
         * - return void
         */
        public function __construct( $config ) {
            $config = config( "storage.{$config}" );
            if ( !is_array( $config ) || empty( $config['path'] ) ) { $config = config( 'storage.cache' ); }
            $this->type = $config['type'] ?? 'public';
            $this->path = $config['path'] ?? '/storage/media/cache';
            Tool::inFolder( $this->path );
            $this->allow = $config['allow'] ?? '*';
            $this->maxSize = $config['maxSize'] ?? 1024 * 1024 * 10;
            $this->delete = $config['delete'] ?? 3 * 24 * 60 * 60;
        }
        /**
         * 上传文件
         */
        public function upload( $file ) {
            // 获取上传的内容
            if ( !is_array( $file ) ) { return false; }
            // 文件检查

            // 上传完成
            return true;
        }
    }