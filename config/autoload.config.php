<?php
    return [
        /**
         * 默认激活
         */
        'default' => [
            'App\Bootstrap\MainProcess' => 'app/Bootstrap/MainProcess.php',
            'Support\Handler\Log' => 'support/Handler/Log.handler.php',
            'Support\Handler\Request' => 'support/Handler/Request.handler.php',
            'Support\Handler\Router' => 'support/Handler/Router.handler.php',
            'Support\Handler\Storage' => 'support/Handler/Storage.handler.php',
            'Support\Handler\Vaildata' => 'support/Handler/Vaildata.handler.php',
            'Support\Handler\View' => 'support/Handler/View.handler.php',
            'Support\Helper\Tool' => 'support/Helper/Tool.helper.php',
            'Support\Provider\HttpProvider' => 'support/Provider/Http.provider.php',
            'Support\Slots\RequestBuild' => 'support/Slots/RequestBuild.slots.php',
            'Support\Slots\RouterBuild' => 'support/Slots/RouterBuild.slots.php',
        ]
    ];