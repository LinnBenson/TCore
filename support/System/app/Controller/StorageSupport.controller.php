<?php

    namespace App\Controller;

use Support\Handler\Request;
use Support\Handler\Storage;

    class StorageSupport {
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
            if ( empty( $storageName ) ) { $storageName = 'cache'; }
            $storage = new Storage( $storageName );
            // 处理上传
            $files = [];
            dd( $request->file );
            foreach( $request->file as $key => $value ) {
                $file = $storage->upload( $value );
                if ( is_uuid( $file ) ) { $files[$key] = $file; }
            }
            return $request->echo( !empty( $files ) ? 0 : 1, !empty( $files ) ? $files : ['base.upload:base.false'] );
        }
    }