<?php

namespace App\Controller;

use Support\Handler\Redis;
use Support\Handler\Request;
use Gregwar\Captcha\CaptchaBuilder;

    class BaseController {
        /**
         * 程序测试
         */
        public function test( Request $request ) {
            // dd( Push::telegram([ 'content' => '测试内容' ]) );
            return View( 'test', [ 'request' => $request ] );
            return $request->echo( 0, 'Test Pass.' );
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
         * 获取验证码
         */
        public function verify( Request $request, $name = null ) {
            if ( empty( $name ) || !ctype_alnum( $name ) ) { return null; }
            $builder = new CaptchaBuilder();
            $builder->setMaxBehindLines( 8 );
            $builder->setMaxFrontLines( 8 );
            $builder->setDistortion( true );
            $builder->build( 120, 32 );
            Redis::setCache( "VerifyImg_{$name}_{$request->id}", strtoupper( $builder->getPhrase() ), 600 );
            header( 'Content-type: image/jpeg' );
            $builder->output();
            return '';
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
            $storage = ToStorage( $storageName );
            if ( empty( $storage ) ) { return $request->echo( 2, ['base.upload:base.false'] ); }
            // 处理上传
            $files = [];
            foreach( $request->file as $key => $value ) {
                $file = $storage->upload( $value );
                if ( $file && $file->id ) { $files[$key] = $file->link(); }
            }
            return $request->echo( !empty( $files ) ? 0 : 1, !empty( $files ) ? $files : ['base.upload:base.false'] );
        }
        /**
         * 展示文件
         */
        public function file( Request $request, $storageName = null, $fileName = null ) {
            $storage = ToStorage( $storageName );
            if ( empty( $storage ) ) { return null; }
            $file = $storage->file( $fileName );
            return $file ? $file->echo() : null;
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
                'user' => $request->user->state ? $request->user->share() : null,
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