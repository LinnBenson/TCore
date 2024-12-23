<?php
    return [
        /**
         * APP 基础信息
         */
        'enable'=> (bool)env( 'APP_ENABLE', false ),
        'name' => env( 'APP_NAME', 'TCore PHP' ),
        'host' => env( 'APP_HOST', 'http://localhost' ),
        'debug' => (bool)env( 'APP_DEBUG', false ),
        'timezone' => env( 'APP_TIMEZONE', 'UTC' ),
        'lang' => env( 'APP_LANG', 'zh-CN' ),
        'version' => env( 'APP_VERSION', '1.0.0' ),
        'record' => (bool)env( 'ROUTER_RECORD', false ),
        'channel' => 36000
    ];