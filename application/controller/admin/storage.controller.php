<?php
use support\middleware\request;
use support\middleware\storage;

    class storageController {
        // 上传文件
        public function upload() {
            $res = request::get([
                'storage' => 'must:true,type:username'
            ]);
            $name = $res['storage'];
            $storageConfig = config( "storage.{$name}" );
            if ( empty( $storageConfig ) || !is_array( $storageConfig ) ) { return task::echo( 2, ['error.input'] ); }
            // 开始处理文件
            $atorage = new storage( $name );
            $file = $atorage->upload( 'file' );
            if ( $name !== 'cache' ) {
                $file = $atorage->cacheSave( $file[0], [
                    'uid' => task::$user->uid,
                    'remark' => 'Uploaded by admin.'
                ]);
            }else {
                $file = $file[0];
            }
            return task::echo( 0, $file );
        }
        // 获取文件列表
        public function get() {
            $res = request::get([
                'storage' => 'must:true,type:username',
                'page' => 'must:true,type:number,min:1',
            ]);
            $size = 20;
            $name = $res['storage'];
            $storageConfig = config( "storage.{$name}" );
            if ( empty( $storageConfig ) || !is_array( $storageConfig ) ) { return task::echo( 2, ['error.input'] ); }
            // 开始获取全部文件
            $result = [ 'total' => 0, 'data' => [] ];
            $dir = "storage/media{$storageConfig['dir']}";
            if ( is_dir( $dir ) ) {
                $get = scandir( $dir );
                $get = array_diff( $get, [ '.', '..'  ] );
                // 过滤文件夹
                $files = array_filter( $get, function( $file )use ( $dir ) {
                    return is_file( $dir.DIRECTORY_SEPARATOR.$file );
                });
                $result['total'] = count( $files );
                // 按修改时间倒序排列
                usort( $files, function( $a, $b ) use ( $dir ) {
                    return filemtime( $dir.DIRECTORY_SEPARATOR.$b) - filemtime( $dir.DIRECTORY_SEPARATOR.$a );
                });
                // 分页
                $files = array_slice( $files, ( $res['page'] * $size ) - $size, $res['page'] * $size );
                foreach( $files as $file ) {
                    $result['data'][] = [
                        'name' => $file,
                        'link' => "/api/admin/storage/file/{$name}/{$file}?type=abbreviation"
                    ];
                }
            }
            return task::echo( 0, $result );
        }
        // 读取文件
        public function file( $stroage, $filename ) {
            $file = "storage/media/{$stroage}/{$filename}";
            $abbreviation = "storage/media/abbreviation/{$filename}";
            if ( $_GET['type'] === 'abbreviation' && file_exists( $abbreviation ) ) {
                $file = $abbreviation;
            }
            if ( !file_exists( $file ) ) { return router::error( 404 ); }
            $mimeType = mime_content_type( $file );
            header( "Content-Type: {$mimeType}" );
            $expired = 30 * 24 * 60 * 60;
            header( "Cache-Control: max-age={$expired}, public" );
            header( "Expires: ".gmdate( 'D, d M Y H:i:s', time() + $expired )." GMT" );
            $handle = fopen( $file, 'rb' );
            if ( $handle ) {
                while ( !feof( $handle ) ) {
                    echo fread( $handle, 8192 );
                    flush();
                }
                fclose( $handle );
            }
            return;
        }
        // 删除文件
        public function delete() {
            $res = request::get([
                'storage' => 'must:true,type:username',
                'file' => 'must:true',
            ]);
            $name = $res['storage'];
            $storageConfig = config( "storage.{$name}" );
            if ( empty( $storageConfig ) || !is_array( $storageConfig ) ) { return task::echo( 2, ['error.input'] ); }
            $storage = new storage( $name );
            $delete = $storage->delete( $res['file'] );
            return task::echo( $delete ? 0 : 2, [$delete ? 'true' : 'false',['type'=>'delete']] );
        }
    }