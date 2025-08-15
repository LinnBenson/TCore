<?php
    namespace App\Controller;

    use Support\Handler\Account;
    use Support\Handler\Request;

    /**
     * 基础应用接口
     */
    class BaseController {
        public function debug( Request $request, $val1 = null, $val2 = null ) {
            dd( Plug( 'AdminPanel' ) );
            $user = new Account( 1 );
            dd( $user );
        }
        /**
         * 请求系统信息
         */
        public function info( Request $request, $type = null ) {
            $type = empty( $type ) ? '/all' : $type;
            switch ( $type ) {
                case '/server': return $request->echo( 0, $this->indexInfo( $request ) ); break;
                case '/text': return $request->echo( 0, $this->indexText( $request ) ); break;
                case '/all': return $request->echo( 0,[
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
                'version' => config( 'app.version' ),
                'timezone' => config( 'app.timezone' ),
                'host' => config( 'app.host' ),
                'user' => $request->user() ? $request->user()->share() : null,
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