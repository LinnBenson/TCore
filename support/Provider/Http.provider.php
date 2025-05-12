<?php

namespace Support\Provider;

use Bootstrap;
use Support\Handler\Request;
use Support\Handler\Router;

    /**
     * HTTP 服务端提供者
     */
    class HttpProvider{
        /**
         * HTTP 服务端入口
         */
        public static function start() {
            $request = Bootstrap::processRun( 'ConstructingRequest', new Request( 'http' ) );
            return Router::init( $request );
        }
    }