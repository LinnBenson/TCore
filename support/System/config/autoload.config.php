<?php
    /**
     * 自动加载声明
     */
    return [
        // 基础项，系统会自动导入
        'base' => [
            'Support\Handler\Request' => 'support/Handler/Request.handler.php',
            'Support\Handler\Router' => 'support/Handler/Router.handler.php',
            'Support\Handler\Session' => 'support/Handler/Session.handler.php',
            'Support\Helper\Tool' => 'support/Helper/Tool.helper.php',
            'Support\Provider\HttpProvider' => 'support/Provider/Http.provider.php',
            'Support\Slots\RequestBuild' => 'support/Slots/RequestBuild.slots.php',
            'Support\Slots\RouterBuild' => 'support/Slots/RouterBuild.slots.php',
        ]
    ];