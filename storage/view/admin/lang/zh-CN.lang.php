<?php
    return [
        'name' => '简体中文',
        'index' => [
            'title' => '控制面板',
            'deleteSure' => '您确定要删除 {{id}} 这条内容吗？'
        ],
        'page' => [
            '/home' => '主页',
            'user' => '用户管理',
            '/user' => '当前管理员',
            '/user/list' => '用户列表',
            '/user/login' => '登录状态管理',
            'article' => '内容管理',
            '/article/list' => '所有文章',
            '/article/sort' => '分类目录',
            '/article/new' => '发布文章',
            '/article/edit' => '修改文章',
            'media' => '媒体库',
            '/media/user' => '用户素材',
            '/media/file' => '文件管理',
            '/access' => '访问控制',
            '/push' => '推送中心',
            '/service' => '服务项管理',
            'setting' => '系统设置',
            '/setting/common' => '通用配置',
            '/setting/theme' => '主题设置',
            '/setting/menu' => '菜单管理',
            'dev' => '开发者工具',
            '/dev/icon' => '图标库',
            '/dev/api' => '接口测试',
            '/dev/log' => '日志记录',
            '/dev/info' => '系统信息',
            '/about' => '关于框架',
            'logout' => '退出登录'
        ],
        'home' => [
            'welcome1' => '在此管理您的',
            'welcome2' => '项目！',
            'user' => '管理员资料',
            'environment' => '环境检查',
            'version' => 'PHP 版本',
            'expand' => 'PHP 扩展',
            'functions' => '启用函数',
            'accessingData' => '访问数据',
            'accessingTitle' => '数据统计',
            'totalAccess' => '总计访问量',
            'todayAccess' => '今日访问',
            'totalFailAccess' => '总计拦截量',
            'todayFailAccess' => '今日拦截',
            'accessin' => '查看详细信息'
        ],
        'user' => [
            'edit' => '修改资料',
            'avatar' => '头像',
            'username' => '用户名',
            'email' => '邮箱',
            'phone' => '手机号',
            'nickname' => '昵称',
            'password' => '密码',
            'info' => '信息设定',
            'status' => '权限级别',
            'id' => '识别码',
            'refresh_id' => '强制刷新识别码',
            'refresh_set' => '还原个性化设置',
            'ip' => '访问网络',
            'ua' => '设备信息',
            'theme' => '主题',
            'time' => '时区',
            'lang' => '语言设置',
            'set' => '个性化设置'
        ],
        'user_list' => [
            'total' => '用户总数',
            'userTotal' => '注册用户',
            'userToday' => '今日注册',
            'toadyAction' => '今日活跃',
            'virtual' => '添加虚拟用户',
            'edit_config' => '修改配置信息',
            'operate' => '更多设置',
            'allow_login' => '允许登录',
            'allow_register' => '允许注册',
            'verify_email' => '验证邮箱',
            'verify_phone' => '验证手机号',
            'invite' => '需要邀请',
            'expired' => '登录过期时间'
        ],
        'user_login' => [
            'control' => '控制中心',
            'down_uid' => '下线指定用户',
            'down_login_ip' => '下线指定网络',
            'down_login_id' => '下线指定设备',
            'down_all' => '下线所有',
            'down_all_text' => '您确定要下线平台所有用户吗？'
        ],
        'article_sort' => [
            'other' => '分类控制',
            'add' => '添加分类'
        ],
        'article_list' => [
            'other' => '文章操作',
            'add' => '发布文章',
            'edit' => '修改文章',
            'total' => '总发布',
            'total_today' => '今日发布'
        ],
        'access' => [
            'today' => '今日调用次数',
            'today_id' => '今日访问设备',
            'total_id' => '总计访问设备',
            'warn' => '路由记录功能已关闭，如需使用此功能，请前往 系统设置-通用配置 中开启，并重启 Async Service 。',
            'edit' => '限制调整',
            'config' => '限制调整',
            'black' => '黑名单',
            'add_black' => '添加黑名单',
            'max_record' => '每分钟最大记录',
            'max_access_id' => '每分钟设备最大访问',
            'max_access_ip' => '每分钟 IP 最大访问',
            'max_access_uid' => '每分钟用户最大访问',
            'black_time' => '禁用时间',
            'type' => '类型',
            'target' => '目标',
            'delete' => '点击可进行删除'
        ],
        'media_user' => [
            'state' => '素材状态',
            'total' => '总上传',
            'total_today' => '今日上传'
        ],
        'media_file' => [
            'storage' => '存储器',
            'public' => '公开性',
            'dir' => '存储路径',
            'size' => '大小限制',
            'file_list' => '文件列表',
            'upload' => '选择一个文件',
            'up' => '上一页',
            'down' => '下一页',
        ],
        'push' => [
            'control' => '控制中心',
            'add' => '建立推送',
            'async' => '异步请求',
            'type' => '推送类型',
            'to' => '接收 ID',
            'title' => '标题',
            'content' => '内容',
            'config' => '配置信息',
            'default' => '默认接收',
            'host' => '服务主机 / API',
            'port' => '端口号',
            'username' => '用户名',
            'password' => '密码',
            'from' => '发件人',
            'encrypt' => '加密方式'
        ],
        'service' => [
            'key' => '唯一 ID',
            'name' => '服务名称',
            'time' => '运行时长',
            'protocol' => '连接协议',
            'port' => '端口号',
            'run' => '运行用户',
            'thread' => '最大线程',
            'public' => '公开链接',
            'true' => '操作完成，服务响应中',
            'control' => '控制中心',
            'add' => '添加服务',
            'open_all' => '启动所有',
            'close_all' => '关闭所有',
            'restart_all' => '重启所有'
        ],
        'setting_common' => [
            'fast' => '快捷设置',
            'delCache' => '删除配置缓存',
            'delCacheHint' => '清除缓存配置将导致已设置的信息全部失效，是否继续？',
            "delCacheWarn" => '注意：配置缓存会影响 项目配置/推送配置 ，如果您删除缓存，请检查是否需要重新调整以上内容！',
            'config' => '项目配置',
            'name' => '项目名称',
            'host' => '主链接',
            'record' => '路由记录',
            'debug' => '调试模式',
            'timezone' => '默认时区',
            'lang' => '默认语言',
            'version' => '版本号',
            'fav' => '项目标志',
            'logo_b' => '深色 LOGO',
            'logo_w' => '浅色 LOGO'
        ],
        'setting_theme' => [
            'list' => '主题列表',
            'color' => '颜色配置',
            'name' => '主题名',
            'r0' => '背景色',
            'r1' => '文本色',
            'r2' => '链接色',
            'r2c' => '链接文本色',
            'r3' => '品牌色',
            'r3c' => '品牌文本色',
            'r4' => '成功色',
            'r4c' => '成功文本色',
            'r5' => '错误色',
            'r5c' => '错误文本色',
            'r6' => '卡片色'
        ],
        'dev_api' => [
            'type' => '测试类型',
            'test_link' => 'API 测试',
            'test_ws' => 'Websocket 测试',
            'link' => '链接',
            'post' => 'POST 数据',
            'header' => '请求头',
            'address' => '地址',
            'send' => '发送测试',
            'json' => '传递数据',
            'send_null' => '发送数据为空',
            'ws_null' => 'Websocket 未连接',
            'ws_start' => '服务已连接',
            'ws_close' => '服务已关闭'
        ],
        'dev_log' => [
            'title' => '所有日志',
            'current' => '当前显示',
            'clear' => '清空当前日志'
        ],
        'dev_info' => [
            'php' => 'PHP 信息',
            'config' => '配置信息',
            'extensions' => 'PHP 插件',
            'functions' => '定义函数',
            'classes' => '声明类'
        ]
    ];