<?php
    namespace App\Controller;

    use Support\Handler\File;
    use Support\Handler\Request;

    /**
     * 存储管理控制器
     */
    class StorageController {
        /**
         * 上传文件
         */
        public function upload( Request $request, $name ) {
            $storage = config( "storage.{$name}", null );
            if ( !is_array( $storage ) || $storage['type'] !== 'public' ) { return $request->echo( 2, ['base.error.prohibit'] ); }
            foreach( $request->file as $file ) {
                $ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
                $size = $file['size'];
                if ( $storage['allow'] !== '*' && is_array( $storage['allow'] ) && !in_array( $ext, $storage['allow'] ) ) {
                    return $request->echo( 2, ['vaildata.type.upload'] );
                }
                if ( is_numeric( $storage['size'] ) && $size > $storage['size'] ) {
                    return $request->echo( 2, ['vaildata.type.upload'] );
                }
                // 保存文件
                $id = uuid();
                $path = "{$storage['path']}{$id}.{$ext}";
                if ( move_uploaded_file( $file['tmp_name'], $path ) ) {
                    return $request->echo( 0, "/storage/file/{$name}/{$id}_{$ext}" );
                }
                return $request->echo( 1, ['base.upload:base.false'] );
            }
            return $request->echo( 2, ['base.error.input'] );
        }
        /**
         * 访问文件
         */
        public function file( Request $request, $name, $file ) {
            $file = new File( "storage.{$name}|{$file}" );
            if ( !$file->state ) { return null; }
            return $file->echo();
        }
        /**
         * 访问头像
         */
        public function avatar( Request $request, $uid ) {
            $avatar = "storage/media/avatar/{$uid}";
            if ( !file_exists( $avatar ) ) {
                $avatar = "public/library/avatar.png";
            }
            $file = new File( $avatar );
            if ( !$file->state ) { return null; }
            return $file->echo( 259200 );
        }
    }