<?php
    /**
     * 权限介入声明
     */
    return [
        // 系统启动运行
        'SYSTEM_STARTUP' => [
            'AdminPanel'
        ],
        // 修改或监听系统启动返回结果
        'RETURN_RESULTS' => [

        ],
        // 修改配置查询信息
        'QUERY_CONFIGURATION_INFORMATION' => [

        ],
        // 挂载语言包
        'QUERY_LANGUAGE_PACKAGE' => [
            'AdminPanel'
        ],
        // 监听主系统日志
        'MAIN_SYSTEM_LOG' => [

        ],
        // 监听初始化请求
        'REQUEST_INITIALIZATION_COMPLETED' => [

        ],
        // 修改或监听接口返回数据
        'RETURN_INTERFACE_DATA' => [

        ],
        // 注册路由
        'REGISTERING_ROUTES' => [
            'ViewRenderer', 'AdminPanel'
        ],
        // 修改或监听路由响应结果
        'ROUTING_RESPONSE_ERROR' => [

        ],
        // 注册插件命令行功能
        'PLUGIN_COMMAND_MENU' => []
    ];