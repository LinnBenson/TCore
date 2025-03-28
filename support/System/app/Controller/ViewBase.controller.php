<?php

    namespace App\Controller;

use Support\Handler\Request;

    class ViewBase {
        /**
         * 请求系统信息
         */
        public function index( Request $request ) {
            $res = $request->vaildata([
                'type' => 'required|type:string'
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
         * 系统信息
         */
        private function indexInfo( Request $request ) {
            $result = [
                'title' => config( 'app.title' ),
                'debug' => config( 'app.debug' ),
                'version' => config( 'update.version' ),
                'timezone' => config( 'app.timezone' ),
                'host' => config( 'app.host' ),
                'copyright' => config( 'view.copyright' ),
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
                $langFile = __file( "resource/lang/{$request->lang}/{$text}.lang.php" );
                if ( file_exists( $langFile ) ) {
                    $result[$text] = require $langFile;
                }
            }
            return $result;
        }
    }