<?php

namespace App\Controller;

use Support\Handler\File;
use Support\Handler\Request;
use Support\Handler\Storage;

    class BaseController {
        /**
         * 程序测试
         */
        public function test( Request $request ) {
            return View( 'test', [ 'request' => $request ] );
        }
        /**
         * 请求系统信息
         */
        public function index( Request $request ) {
            $res = $request->vaildata([
                'type' => 'must|type:string'
            ]);
            switch ( $res['type'] ) {
                case 'server': return $request->echo( 0, $this->indexInfo( $request ) ); break;
                case 'text': return $request->echo( 0, $this->indexText( $request ) ); break;
                case 'all': return $request->echo( 0,[
                    'server' => $this->indexInfo( $request ),
                    'text' => $this->indexText( $request )
                ]); break;

                default: return $request->echo( 2, ['base.error.input'] ); break;
            }
        }
        /**
         * 文件上传
         */
        public function upload( Request $request, $storageName = null ) {
            $res = $request->vaildata([
                'storage|Storage Name' => 'type:alnum'
            ], [ 'storage' => ltrim( $storageName, '/' ) ]);
            // 存储器名称
            $storageName = $res['storage'];
            // 生成存储器
            $allow = [ 'cache' ];
            if ( empty( $storageName ) || !in_array( $storageName, $allow ) ) { $storageName = 'cache'; }
            $storage = new Storage( $storageName );
            // 处理上传
            $files = [];
            foreach( $request->file as $key => $value ) {
                $file = $storage->upload( $value );
                if ( $file && $file->id ) { $files[$key] = "/storage/cache/".str_replace( '.', '_', $file->name ); }
            }
            return $request->echo( !empty( $files ) ? 0 : 1, !empty( $files ) ? $files : ['base.upload:base.false'] );
        }
        /**
         * 展示文件
         */
        public function file( Request $request, $storageName = null, $fileName = null ) {
            $file = new File( "/storage/{$storageName}/{$fileName}" );
            return $file->id ? File::echo( $file->path ) : null;
        }
        /**
         * 系统信息
         */
        private function indexInfo( Request $request ) {
            $result = [
                'title' => config( 'app.title' ),
                'debug' => config( 'app.debug' ),
                'version' => config( 'app.version' ),
                'timezone' => config( 'app.timezone' ),
                'host' => config( 'app.host' ),
                'user' => $request->user ? $request->user->share() : null,
            ];
            return $result;
        }
        /**
         * 语言包
         */
        private function indexText( Request $request ) {
            $result = [];
            $texts = [ 'base', 'vaildata' ];
            foreach( $texts as $text ) {
                if ( empty( $result[$text] ) ) { $result[$text] = []; }
                $langFile1 = "resource/lang/{$request->lang}/{$text}.lang.php";
                if ( file_exists( $langFile1 ) ) { $result[$text] = array_merge( $result[$text], require $langFile1 ); }
                $langFile2 = "support/System/resource/lang/{$request->lang}/{$text}.lang.php";
                if ( file_exists( $langFile2 ) ) { $result[$text] = array_merge( $result[$text], require $langFile2 ); }
            }
            return $result;
        }
    }