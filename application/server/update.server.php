<?php
namespace application\server;

use support\method\web;

    class updateServer {
        /**
         * 配置信息
         */
        public static $github = [
            'username' => 'LinnBenson',
            'repository' => 'TCore',
            'token' => ''
        ];
        /**
         * 执行任务
         */
        public static function index( $method ) {
            $result = [];
            $config = self::file( 'storage/backup/update.config.json' );
            if ( !is_json( $config ) ) { return $result; }
            $config = json_decode( $config, true );
            foreach( $config as $item ) {
                $file = self::file( $item );
                if ( !empty( $file ) ) {
                    // 本地保存
                    $savePath = getcwd().'/'.$item;
                    $saveDir = dirname( $savePath );
                    if ( !is_dir( $saveDir ) ) { mkdir( $saveDir ); }
                    // 写入文件
                    $data = file_put_contents( $item, $file ) ? 'Success.' : 'Write failed.';
                }else {
                    $data = 'Failed to obtain.';
                }
                if ( is_callable( $method ) ) { $method( $item, $data ); }
                $result[$item] = $data;
            }
            return $result;
        }
        /**
         * 获取文件
         */
        private static function file( $file ) {
            $url = "https://api.github.com/repos/".self::$github['username']."/".self::$github['repository']."/contents/{$file}";
            $header = [ 'User-Agent: PHP-GitHub-API-Client' ];
            if ( !empty( self::$github['token'] ) ) {
                $header[] = "Authorization: token ".self::$github['token'];
            }
            $get = web::custom([
                'type' => 'GET',
                'url' => $url,
                'header' => $header
            ]);
            if ( is_json( $get ) ) {
                $data = json_decode( $get, true );
                if ( isset( $data['content'] ) ) {
                    return base64_decode( $data['content'] );
                }
            }
            return false;
        }
    }