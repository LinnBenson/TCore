<?php

namespace App\Controller;

use Support\Handler\Request;

    class Test {
        // 预检接口
        public function __() {

        }
        // 主测接口
        public function index( Request $request ) {
            return $request->echo( 0, 'Test Controller Pass.' );
        }
    }