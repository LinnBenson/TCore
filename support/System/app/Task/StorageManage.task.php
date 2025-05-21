<?php

namespace App\Task;

    class StorageManage {
        /**
         * 过期检查
         */
        public function expired( $storage = null ) {
            $storages = is_array( $storage ) ? [ $storage ] : config( 'storage' );
            $delete = function( $file ) {
                return $file->delete;
            };
            foreach( $storages as $storage ) {
                $path = $storage['path'];
                // 获取所有文件
                if ( !is_dir( $path ) ) { continue; }
                $files = array_filter( scandir( $path ), function( $file ) use ( $path ) {
                    return is_file( $path.'/'.$file );
                });
                foreach( $files as $file ) {
                    $file = ToFile( "{$path}{$file}" );
                    if ( empty( $file ) ) { continue; }
                    // 验证大小
                    if ( $file->size > $storage['maxSize'] ) {
                        $delete( $storage, $file ); continue;
                    }
                    // 验证文件类型
                    if ( $storage['allow'] !== '*' && !in_array( $file->ext, $storage['allow'] ) ) {
                        $delete( $storage, $file ); continue;
                    }
                    // 验证过期时间
                    $mtime = filemtime( $file->path );
                    if ( is_numeric( $storage['delete'] ) && time() - $mtime > $storage['delete'] ) {
                        $delete( $storage, $file ); continue;
                    }
                }

            }
            return true;
        }
    }