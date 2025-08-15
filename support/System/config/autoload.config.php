<?php
    /**
     * 自动加载声明
     */
    return [
        // 基础项，系统会自动导入
        'base' => [
            'Support\Handler\Account' => 'support/Handler/Account.handler.php',
            'Support\Handler\File' => 'support/Handler/File.handler.php',
            'Support\Handler\Redis' => 'support/Handler/Redis.handler.php',
            'Support\Handler\Request' => 'support/Handler/Request.handler.php',
            'Support\Handler\Router' => 'support/Handler/Router.handler.php',
            'Support\Handler\RouterBuilder' => 'support/Handler/RouterBuilder.handler.php',
            'Support\Handler\Session' => 'support/Handler/Session.handler.php',
            'Support\Handler\Vaildata' => 'support/Handler/Vaildata.handler.php',
            'Support\Helper\Tool' => 'support/Helper/Tool.helper.php',
            'Support\Provider\Http' => 'support/Provider/Http.provider.php',
            'Support\Provider\Shell' => 'support/Provider/Shell.provider.php',
            'Support\Slots\Plug' => 'support/Slots/Plug.slots.php',
            'Support\Slots\Mysql' => 'support/Slots/Mysql.slots.php',
            'Support\Slots\MysqlGlobal' => 'support/Slots/MysqlGlobal.slots.php',
        ]
    ];