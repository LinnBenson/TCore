<?php
namespace support\middleware;

use application\model\media;
use core;
use router;
use task;
use Intervention\Image\ImageManagerStatic as Image;

    class storage {
        public $name = ''; // 存储名称
        public $config = []; // 存储配置
        public $dir = ''; // 存储路径
        private $cache = "storage/media/cache"; // 缓存路径
        /**
         * 构造函数
         */
        public function __construct( $name ) {
            $config = config( "storage.{$name}" );
            if ( empty( $config ) ) {
                core::log( "[ {$name} ] Storage configuration does not exist.", 'core' );
                return core::error( task::echo( 2, ['error.500'], 500 ) );
            }
            // 检查存储
            if ( !is_dir( $this->cache ) ) { mkdir( $this->cache ); }
            if ( !is_dir( "storage/media{$config['dir']}" ) ) { mkdir( "storage/media{$config['dir']}" ); }
            if ( !is_dir( "storage/media/abbreviation" ) ) { mkdir( "storage/media/abbreviation" ); }
            $this->name = $name;
            $this->config = $config;
            $this->dir = "storage/media{$config['dir']}";
        }
        /**
         * 上传内容
         * - $input string input键名
         * - $num number 获取文件的最大数量
         * ---
         * return array 缓存访问链接
         */
        public function upload( $input = false, $num = 1 ) {
            // 获取上传的输入框
            $file = false;
            if ( !empty( $input ) ) {
                $file = $_FILES[$input];
            }else {
                foreach( $_FILES as $name => $item ) { $input = $name; $file = $item; }
            }
            // 定义处理逻辑
            $tmps = [];
            $getFileCheck= function( $file )use ( $input ) {
                if ( empty( $file ) ) { return false; }
                // 检查文件
                if ( $file['error'] === UPLOAD_ERR_OK ) {
                    $fileTmp = $file['tmp_name'];
                    $finfo = finfo_open( FILEINFO_MIME_TYPE );
                    $fileType = finfo_file( $finfo, $fileTmp );
                    $fileType = explode( '/', $fileType )[1] ?? '';
                    $fileSize = $file['size'];
                    finfo_close( $finfo );
                } else {
                    return ccore::error( task::echo( 2, ['false',['type' => ['upload']]] ) );
                }
                // 检查文件格式
                if ( $this->config['allow'] && ( empty( $fileType ) || !in_array( $fileType, $this->config['allow'] ) ) ) {
                    return core::error( task::echo( 2, ['form.fileType',['name' => $input]] ) );
                }
                // 检查文件大小
                if ( empty( $fileSize ) || $fileSize < $this->config['size'][0] ) {
                    return core::error( task::echo( 2, ['form.fileMin',['name' => $input,'set'=>$this->config['size'][0]]] ) );
                }
                if ( empty( $fileSize ) || $fileSize > $this->config['size'][1] ) {
                    return core::error( task::echo( 2, ['form.fileMax',['name' => $input,'set'=>$this->config['size'][1]]] ) );
                }
                // 检查完成
                return [
                    'tmp_name' => $fileTmp,
                    'type' => $fileType
                ];
            };
            // 判断用户是否在上传多个文件
            if ( is_array( $file['name'] ) ) {
                for ( $i=0; $i <= ( $num - 1 ); $i++ ) {
                    $cache = $getFileCheck([
                        'tmp_name' => $file['tmp_name'][$i],
                        'size' => $file['size'][$i],
                        'error' => $file['error'][$i]
                    ]);
                    if ( $cache ) { $tmps[] = $cache; }
                }
            }else {
                $cache = $getFileCheck([
                    'tmp_name' => $file['tmp_name'],
                    'size' => $file['size'],
                    'error' => $file['error']
                ]);
                if ( $cache ) { $tmps[] = $cache; }
            }
            // 输出文件
            $result = [];
            foreach( $tmps as $tmp ) {
                $fileName = uniqid().".{$tmp['type']}";
                // 输出文件
                if ( move_uploaded_file( $tmp['tmp_name'], "{$this->cache}/{$fileName}" ) ) {
                    $result[] = "/storage/cache/{$fileName}";
                }
            }
            return $result;
        }
        /**
         * 从缓存迁移文件
         * - $from string 源文件地址
         * - $set array 写入设置
         * ---
         * return boolean false|访问链接
         */
        public function cacheSave( $file, $set = false ) {
            if ( strpos( $file, '/storage/media' ) !== false ) { return $file; }
            $fileName = explode( '/', $file );
            $fileName = $fileName[count( $fileName ) - 1];
            // 生成缓存路径
            $cacheFile = "{$this->cache}/{$fileName}";
            if ( !file_exists( $cacheFile ) ) { return false; }
            // 转移目录
            $toName = is_array( $set ) && !empty( $set['name'] ) ? $set['name'] : $fileName;
            $to = "{$this->dir}/$toName";
            // 开始迁移文件
            if ( rename( $cacheFile, $to ) ) {
                if ( is_array( $set ) && isset( $set['uid'] ) ) {
                    $check = $this->save( $toName, $set );
                    if ( !$check ) { rename( $to, $cacheFile ); return false;  }
                }else if ( $this->name === 'avatar' ) {
                    if ( file_exists( $to ) && preg_match( '/\.(jpg|jpeg|png|gif|bmp|webp|svg)$/i', $to ) ) {
                        Image::make( $to )->resize( 800, null, function( $constraint ) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })->save( $to, 65 );
                    }
                }
                return "/storage/media/{$this->name}/{$toName}";
            }
            return false;
        }
        /**
         * 写入文件权限
         * - $file string 文件名
         * - $set array 写入配置
         * ---
         * return boolean 写入结果
         */
        public function save( $file, $set ) {
            // 先检查文件是否存在
            $check = media::where( 'file', $file )->exists();
            if ( $check ) { return false; }
            // 压缩图片
            if ( file_exists( "{$this->dir}/{$file}" ) && preg_match('/\.(jpg|jpeg|png|gif|bmp|webp|svg)$/i', $file ) ) {
                Image::make( "{$this->dir}/{$file}" )->resize( 800, null, function( $constraint ) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->save( "storage/media/abbreviation/{$file}", 75);
            }
            // 开始写入
            $uid = isset( $set['uid'] ) ? $set['uid'] : task::$user->uid; // UID
            $public = !empty( $set['public'] ) ? 1 : 0; // 权限
            $application = !empty( $set['application'] ) ? $set['application'] : ''; // 用途
            $update = [
                'uid' => $uid,
                'storage' => $this->name,
                'file' => $file,
                'public' => $public,
                'application' => $application,
                'remark' => $set['remark'] ?? ''
            ];
            if ( !file_exists( "{$this->dir}/{$file}" ) ) { return false; }
            $check = media::create( $update );
            return $check ? true : false;
        }
        /**
         * 检查文件是否存在
         * - $file string 源文件地址
         * ---
         * return boolean 查询结果
         */
        public function hasFile( $file ) {
            $fileName = explode( '/', $file );
            $fileName = $fileName[count( $fileName ) - 1];
            return file_exists( $this->dir."/".$fileName );
        }
        /**
         * 输出文件
         * - $file string 文件名
         * - $default string 默认文件
         * ---
         * return 输出文件
         */
        public function show( $file, $default = false ) {
            $result = $this->get( $file, $default );
            if ( $result === false ) { return router::error( 404 ); }
            $abbreviation = "storage/media/abbreviation/{$file}";
            if ( $_GET['type'] === 'abbreviation' && file_exists( $abbreviation ) ) {
                $result = $abbreviation;
            }
            $mimeType = mime_content_type( $result );
            header( "Content-Type: {$mimeType}" );
            $expired = 30 * 24 * 60 * 60;
            header( "Cache-Control: max-age={$expired}, public" );
            header( "Expires: ".gmdate( 'D, d M Y H:i:s', time() + $expired )." GMT" );
            $handle = fopen( $result, 'rb' );
            if ( $handle ) {
                while ( !feof( $handle ) ) {
                    echo fread( $handle, 8192 );
                    flush();
                }
                fclose( $handle );
            }
            return;
        }
        /**
         * 获取文件
         * - $file string 文件名
         * - $default string 默认文件
         * ---
         * return false|真实文件路径
         */
        public function get( $file, $default = false ) {
            $dir = "{$this->dir}/{$file}";
            // 使用默认文件
            $useDefault = function()use ( $default ) {
                if ( $default === false ) { return false; }
                $dir = "{$this->dir}/{$default}";
                if ( file_exists( $dir ) ) { return $dir; }
                return false;
            };
            // 检查文件是否存在
            if ( !file_exists( $dir ) ) { return $useDefault(); }
            // 检查文件类型
            $fifleType = pathinfo( $dir, PATHINFO_EXTENSION );
            if ( $this->config['allow'] && !in_array( $fifleType, $this->config['allow'] ) ) {
                return $useDefault();
            }
            // 检查文件权限
            if ( !$this->config['public'] || task::$user->level >= 1000 ) {
                $info = media::where( 'file', $file )->first();
                if ( $info ) {
                    if ( empty( $info->public ) && task::$user->uid !== $info->uid ) {
                        return false;
                    }
                }
            }
            return $dir;
        }
        /**
         * 删除文件
         * - $file string 文件名
         * ---
         * return boolean 删除结果
         */
        public function delete( $file ) {
            $fileName = explode( '/', $file );
            $fileName = $fileName[count( $fileName ) - 1];
            $filePath = "{$this->dir}/{$fileName}";
            if ( file_exists( "storage/media/abbreviation/{$fileName}" ) ) {
                if ( !unlink( "storage/media/abbreviation/{$fileName}" ) ) { return false; }
            }
            if ( file_exists( $filePath ) ) {
                if ( !unlink( $filePath ) ) { return false; }
            }
            if ( file_exists( $filePath ) ) { return false; }
            media::where( 'file', $fileName )->delete();
            return true;
        }
        /**
         * 删除用户名下文件
         * - $uid number UID
         * ---
         * return boolean 删除结果
         */
        public function deleteByUser( $uid ) {
            if ( empty( $uid ) || !is_numeric( $uid ) ) { return false; }
            $files = media::where( 'uid', $uid )->where( 'storage', $this->name )->get();
            foreach ( $files as $file ) {
                $filePath = "{$this->dir}/{$file->file}";
                if ( file_exists( $filePath ) ) {
                    if ( !unlink( $filePath ) ) { return false; }
                }
                if ( file_exists( $filePath ) ) { return false; }
            }
            media::where( 'uid', $uid )->delete();
            return true;
        }
    }