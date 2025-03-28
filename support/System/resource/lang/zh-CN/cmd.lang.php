<?php
    return [
        'input' => '请输入',
        'confirm' => '确认执行？',
        'error' => [
            'input' => '输入错误',
            'select' => '选择错误'
        ],
        'menu' => [
            'addfile' => '创建项目',
            'database' => '数据库管理',
            'cahce' => '系统缓存',
            'task' => '运行任务',
            'worker' => '持久化应用',
            'tool' => '应用工具',
            'other' => '其它选项'
        ],
        'addfile' => [
            'controller' => '创建控制器',
            'controllerName' => '请输入控制器名称',
            'model' => '创建数据模型',
            'modelName' => '请输入模型名称',
            'database' => '创建数据表',
            'databaseName' => '请输入数据表名',
            'service' => '创建服务项',
            'serviceName' => '请输入服务项名称',
            'middleware' => '创建验证器',
            'middlewareName' => '请输入验证器名称',
            'task' => '创建任务',
            'taskName' => '请输入任务名称',
            'worker' => '创建持久化服务',
            'workerName' => '请输入持久化服务名称',
            'has' => '目标文件为空或者已经存在！',
        ],
        'database' => [
            'reset' => '重置所有表',
            'resetHint' => '确定要重置所有表吗？',
            'manage' => '管理表',
            'up' => '创建表',
            'has' => '这个表在数据库中已存在！',
            'down' => '删除表',
            'downHint' => '确定要删除这个表吗？'
        ],
        'cache' => [
            'clearAll' => '清除所有缓存',
            'clearLog' => '清除日志缓存',
            'clearView' => '清除视图缓存',
            'clearRedis' => '清除 Redis 缓存',
        ],
        'task' => [
            'parameter' => '请输入第 {{i}} 个参数，输入 stop 结束',
        ],
        'worker' => [
            'start' => '启动服务',
            'stop' => '停止服务',
            'restart' => '重启服务',
            'status' => '服务状态',
            'debug' => '调试服务',
            'delete' => '删除服务',
            'debugHint' => '持久化服务已重新加载，输入 stop 结束调试',
            'startAll' => '启动所有服务',
            'stopAll' => '停止所有服务',
            'restartAll' => '重启所有服务',
        ],
        'tool' => [
            'generatekey' => '生成密钥',
            'keyHint' => '此随机密钥用于加密数据，请填写到 .env 文件中的 APP_KEY 中。请仅在刚开始使用系统时修改一次密钥，不要重复修改。',
            'hash' => '哈希测试',
            'hashHint' => '请输入要哈希的内容',
            'encrypt' => '加密测试',
            'encryptHint' => '请输入要加密的内容',
            'decrypt' => '解密测试',
            'decryptHint' => '请输入要解密的内容',
            'schedule' => '进度条展示'
        ]
    ];