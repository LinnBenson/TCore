<?php
    namespace App\Controller;

    use Support\Handler\Request;

    /**
     * {{name}} 控制器
     */
    class {{name}} {
        /**
         * 初始化方法
         * - $request: 请求对象[Request]
         * - return null 时表示不拦截请求
         */
        public function init( Request $request ) {
            return null;
        }
    }