<?php
    /**
     * 示例插件
     */
    return new class {
        /**
         * 每个插件都需要在返回的对象中包含这样一个公共属性
         * - info: 插件信息
         * - auto: 插件自启动声明
         */
        public $config = [
            'info' => [
                'name' => 'Example',
                'title' => '示例插件',
                'version' => '1.0.0',
                'author' => 'Author',
                'description' => 'This is an example plugin.',
            ],
            'auto' => [
                'InitializationCompleted' => 'test',
            ],
        ];
        /**
         * 测试方法
         */
        public function test( $data ) {
            return '插件调用成功！';
        }
    };