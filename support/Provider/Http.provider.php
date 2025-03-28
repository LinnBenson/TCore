<?php

namespace Support\Provider;

use App\Bootstrap\MainProcess;
use Support\Handler\Request;
use Support\Handler\Router;

    class HttpProvider {
        /**
         * 初始化
         */
        public static function init() {
            $request = MainProcess::InitRequest( new Request( 'http' ) );
            return Router::start( $request );
        }
    }