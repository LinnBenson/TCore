<?php

namespace App\Server;

    class CustomerServiceServer {
        /**
         * 获取客服链接
         */
        public function getLink() {
            if ( !empty( config( 'api.service' ) ) ) { return config( 'api.service' ); }
            return null;
        }
    }