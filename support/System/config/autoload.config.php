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
            'Support\Handler\Session' => 'support/Handler/Session.handler.php',
            'Support\Handler\Storage' => 'support/Handler/Storage.handler.php',
            'Support\Handler\Vaildata' => 'support/Handler/Vaildata.handler.php',
            'Support\Helper\Push' => 'support/Helper/Push.helper.php',
            'Support\Helper\Tool' => 'support/Helper/Tool.helper.php',
            'Support\Helper\Web' => 'support/Helper/Web.helper.php',
            'Support\Provider\Http' => 'support/Provider/Http.provider.php',
            'Support\Provider\Shell' => 'support/Provider/Shell.provider.php',
            'Support\Slots\Mysql' => 'support/Slots/Mysql.slots.php',
            'Support\Slots\MysqlGlobal' => 'support/Slots/MysqlGlobal.slots.php',
            'Support\Slots\RequestBuild' => 'support/Slots/RequestBuild.slots.php',
            'Support\Slots\RouterBuild' => 'support/Slots/RouterBuild.slots.php',
        ]
    ];