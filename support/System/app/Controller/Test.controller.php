<?php

    namespace App\Controller;

use Support\Handler\Request;

    class Test {
        public function index( Request $request ) {
            $result = 'Test Pass.';
            return $request->echo( 0, $result );
        }
        public function request( Request $request ) {
            dd( $request );
        }
        public function parameter( Request $request, $p1, $p2 ) {
            return $request->echo( 0, [
                'P1' => $p1,
                'P2' => $p2
            ] );
        }
        public function view() {
            return view( 'error', [ 'code' => '300', 'message' => '222' ] );
            return 'ok';
        }
    }