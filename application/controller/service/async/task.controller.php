<?php

use application\server\updateServer;
use support\middleware\access;

    class taskController {
        /**
         * 异步服务器启动
         */
        public function index( $ref ) {
            // 线程 2
            if ( $ref->id === 1 ) {
                // 激活定时任务
                $this->addTimerTask( $ref );
            }
        }
        /**
         * 启动定时器任务
         */
        private function addTimerTask() {
            // 定时清理媒体缓存
            task::addTimer( 'clear_media_cache', 60, function() {
                $cache = "storage/media/cache";
                if ( !is_dir( $cache ) ) { return true; }
                $files = scandir( $cache );
                $threshold = 24 * 60 * 60;
                foreach ( $files as $file ) {
                    if ( $file === '.' || $file === '..' ) { continue; }
                    $filePath = $cache.DIRECTORY_SEPARATOR.$file;
                    if ( !is_file( $filePath ) ) { continue; }
                    $fileCreationTime = filectime( $filePath );
                    if ( ( time() - $fileCreationTime ) > $threshold ) {
                        unlink( $filePath );
                        task::log( "Remove Cache: {$filePath}" );
                    }
                }
            });
            // 写入路由记录
            if ( config( 'app.record' ) ) {
                task::addTimer( 'router_record', 60, function() { access::insert(); });
            }
        }
        /**
         * 启动更新任务
         */
        public function update() {
            $result = updateServer::index(function( $file, $data ) {
                echo getTime() . " [ Thread_" . task::$thread . " ] File Update: {$file} | {$data}\n";
            });
            if ( empty( $result ) ) {
                echo getTime() . " [ Thread_" . task::$thread . " ] No files updated.\n";
            }
        }
    }