<?php
    /**
     * 文件操作器
     */
    namespace Support\Handler;

    class File {
        public $state= false; // 文件状态
        public $storageName = null; // 存储配置名
        public $storage = null; // 存储配置
        public $path = ''; // 文件路径
        public $name = ''; // 文件名
        public $ext = ''; // 文件格式
        public $size = 0; // 文件大小
        public $create = 0; // 创建时间
        /**
         * 构造文件
         * $path: 文件路径[string]|null
         * - return void
         */
        public function __construct( $path = null ) { $this->init( $path ); }
        private function init( $path ) {
            // 路径 /storage/file/cache/test.txt
            if ( is_string( $path ) && startsWith( $path, '/storage/file/' ) ) {
                $set = explode( '/', $path );
                $setStorage = config( "storage.{$set[3]}", null );
                if ( is_array( $setStorage ) ) {
                    $path = "{$setStorage['path']}{$set[4]}";
                    $path = preg_replace( '/_/', '.', $path, 1 );
                }
            }
            $path = ltrim( $path, '/\\' ); // 去除路径前缀
            // 路径 storage.cache|test.txt
            if ( is_string( $path ) && startsWith( $path, 'storage.' ) ) {
                $set = explode( '|', $path );
                if (
                    count( $set ) === 2 &&
                    is_string( $set[0] ) &&
                    is_string( $set[1] ) &&
                    is_array( config( $set[0] ) )
                ) {
                    $path = config( $set[0] )['path'].$set[1];
                    $path = preg_replace( '/_/', '.', $path, 1 );
                }
            }
            // 普通路径
            if ( !is_string( $path ) || !file_exists( $path ) ) { $this->state = false; return false; }
            // 初始化文件操作器
            $this->state = true;
            $this->path = $path;
            $this->name = basename( $path, PATHINFO_BASENAME  );
            $this->ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
            $this->size = filesize( $path );
            $this->create = filectime( $path );
            if ( startsWith( $this->path, 'storage/media/' ) ) {
                $path = str_replace( $this->name, '', $this->path );
                foreach ( config( 'storage', [] ) as $key => $value ) {
                    if ( $path === $value['path'] ) {
                        $this->storageName = $key;
                        $this->storage = $value;
                        break;
                }
                }
                if ( !empty( $this->storage ) ) {
                    if (
                        // 文件格式必须被允许
                        ( $this->storage['allow'] !== '*' && !in_array( $this->ext, $this->storage['allow'] ?? [] ) ) ||
                        // 文件大小超过限制
                        $this->size > ( $this->storage['maxSize'] ?? 0 ) ||
                        // 文件创建时间超过删除时间
                        (
                            is_numeric( $this->storage['delete'] ) && is_numeric( $this->create ) &&
                            ( time() - $this->create ) > $this->storage['delete']
                        )
                    ) {
                        $this->state = false;
                    }
                }else {
                    $this->storage = $this->storageName = null; // 未找到存储配置
                }
            }
            return true;
        }
        /**
         * 移动文件
         * $path: 目标路径[string]
         * - return bool 是否成功移动
         * - 注意：如果目标路径已存在同名文件，则不会覆盖
         */
        public function move( $path ) {
            if ( !$this->state ) { return false; }
            // 检查目标路径
            $path = inFolder( "{$path}{$this->name}" );
            if ( file_exists( $path ) ) { return false; }
            // 移动文件
            if ( rename( $this->path, $path ) ) { return $this->init( $path ); }
            return false;
        }
        /**
         * 复制文件
         * $path: 目标路径[string]
         * - return File|false 返回新的文件操作器或false
         * - 注意：如果目标路径已存在同名文件，则不会覆盖
         */
        public function copy( $path ) {
            if ( !$this->state ) { return false; }
            // 检查目标路径
            $path = inFolder( "{$path}{$this->name}" );
            if ( file_exists( $path ) ) { return false; }
            // 复制文件
            if ( copy( $this->path, $path ) ) { return new self( $path ); }
            return false;
        }
        /**
         * 删除文件
         * - return bool 是否成功删除
         */
        public function delete() {
            if ( !$this->state ) { return false; }
            // 删除文件
            if ( unlink( $this->path ) ) { $this->state = false; return true; }
            return false;
        }
        /**
         * 输出文件内容
         * - $expires: 缓存过期时间[int]，默认30天
         * - return string|null 返回文件内容或null
         */
        public function echo( $expires = 2592000 ) {
            if ( !$this->state ) { return null; }
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
            header( 'Content-Disposition: inline; filename="'.basename( $this->path ).'"' );
            // 设置缓存
            header( 'Cache-Control: public, max-age=' . $expires );
            header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + $expires ) . ' GMT' );
            // 输出文件内容
            readfile( $this->path );
            return '';
        }
    }