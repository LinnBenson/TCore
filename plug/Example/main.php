<?php
    /**
     * 示例插件
     */
    return new class {
        public function test( $data ) {
            return '插件调用成功！';
        }
    };