# TCore 3.0.0

> 轻巧便携的 PHP 开发框架

### 准备工作
- 设置工作目录为 /public
- 设置伪静态
  ```
    # Chat service
    location /chat
    {
        proxy_pass http://127.0.0.1:36002;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
    # File
    location ~ .*\.(ico|svg|ttf|otf|wotf|woff|woff2|ttc|json)$ {
        expires      30d;
        error_log /dev/null;
        access_log /dev/null;
    }
    # Entrance
    location / {
        try_files $uri /index.php?$query_string;
    }
  ```
- 创建 .env （ /example.env ）
- 进入 support 目录下执行 `composer install`
- 在根目录下执行 `php cmd` 重新构建数据库
- 添加计划任务维持服务运行（ Shell | 每分钟检查一次 ）
  ```
  // 可自动扩展需要维持的服务，以 , 隔开
  cd /www/wwwroot/tcore && php cmd service run async,chat
  ```

### 程序运行须知
#### Header 接受参数
- `token` string 用户密钥
- `identifier` uuid 识别码
- `lang` string 当前语言 ( 如：zh-CN )
- `timezone` number 当前时区
- `theme` string 当前主题

### 后端工作方法
##### 全局函数 support/method/global.method.php
- `env( $key, $default = null )`
  - 查询 .env 配置
    - $key string 键名
    - $default string 默认值
    - return string|null 查询结果
- `import( $file )`
  - 引用全部文件
    - $file string|array 引用文件
    - return boolean 引用结果
- `t( $word, $replace = [], $textData = false )`
  - 语言包调用
    - $word string 语言包键值
    - $replace array 替换数据
    - $textData array 调用语言包
    - return string 调用结果
- `config( $key, $default = null )`
  - 查询配置信息
    - $key string 键名
    - $default any 默认值
    - return any|null 查询结果
- `isPrivate( $class, $method )`
  - 检查动作是否为私有方法
    - $class string 类名
    - $method string 方法名
    - return boolean 判断结果
- `toTime( $time, $add = false )`
  - 时间转换
    - $time string 待转换的时间
    - $add string 时区转换
    - return string 转换后的时间
- `getTime( $time = false )`
  - 获取格式化时间
    - $time string 待转换的时间
    - return string 格式化时间
- `is_json( $val )`
  - 判断变量是否为 json 格式
    - $val any 需要检查的内容
    - return boolean 判断结果
- `toString( $val )`
  - 强行将变量转为可视字符串
    - $val any 数据
    - return string 转换结果
- `UUID()`
  - 生成 UUID
    - return string UUID
- `is_uuid( $string )`
  - 判断字符串是否为 UUID
    - $string string 变量
    - return boolean 判断结果
- `blog( $text )`
  - 拼接长文本
    - $text array 待拼接的内容
    - return string 拼接后的内容
- `encrypt( $content, $key = false )`
  - 加密内容
    - $$content string 内容
    - $key string 密钥（默认使用 .env 密钥）
    - return string 加密后的内容
- `decrypt( $content, $key = false )`
  - 解密内容
    - $content string 内容
    - $key string 密钥（默认使用 .env 密钥）
    - return string 解密后的内容
##### 核心驱动器 loader/core.loader.php
- `core::$cache` array 系统运行缓存
- `core::$text` array 语言包
- `core::$textLang` string 当前语言
- `core::$db` object Mysql OEM 对象
- `__construct( $task = false )`
  - 构造函数
    - $task function 构造完成执行方法
    - return null
- `core::autoload( $data = [] )`
  - 动态加载
    - $data array 类映射文件（class=>file）
    - return true
- `core::loadLang( $set = null )`
  - 加载语言包
    - $set string 语言
    - return boolean 加载结果
- `core::log( $val, $logFile = 'debug' )`
  - 记录运行日志
    - $val any 日志内容（可以传递一个两个值的数据，则区分为两行）
    - $logFile string 日志名称
    - return boolean 记录结果
- `core::error( $data )`
  - 抛出一个错误
    - $data any 错误信息
    - return null
- `core::async( $name, $action, $res = [], $thread = false )`
  - 执行异步任务
    - $name string 服务项名称
    - $action string 传递方法
    - $res array 传递数据
    - $thread number 执行线程（false:随机一个线程|true:全部线程）
    - return boolean 异步服务器响应状态（不保证任务一定完成）
- `core::online( $type, $user )`
  - 检查用户是否在线
    - $type string 检查类型（id|uid）
    - $user string 检查用户
    - return boolean 检查结果
##### 路由加载器 loader/router.loader.php
- `router::$type` string 路由类型
- `router::$argv` string 请求查询参数
- `router::$config` array 路由配置
- `router::start( $argv = [], $sendData = false )`
  - 路由开始
    - $argv string 路由查询参数
    - $sendData array 发送数据
    - return any 路由搜索结果
- `router::search( $argv, $sendData = false, $record = [] )`
  - 路由搜索
    - $argv string 路由查询参数
    - $sendData array 发送数据
    - $record array 记录用户覆盖数据
    - return any 路由搜索结果
- `router::add( $name, $type = 'ANY', $add = '' )`
  - 注册路由
    - $name string 匹配路径
    - $type string 匹配类型
    - $add string 在匹配路径前添加值
    - return object 路由配置实体
- `router::error( $code, $content = '' )`
  - 输出错误
    - $code number 错误类型
    - $content string 错误内容
    - return string 错误信息
##### 用户加载器 loader/user.loader.php
- `$this->code` number 返回代码
- `$this->type` string 当前构造类型
- `$this->id` uuid 用户识别码
- `$this->ip` string 用户 IP
- `$this->ua` string 用户 UA
- `$this->lang` string 用户语言
- `$this->token` string 用户密钥
- `$this->time` number 用户时区
- `$this->statusList` array 用户权限级别
- `$this->timezoneList` array 语言映射时区
- `$this->state` boolean 登录状态
- `$this->uid` number 用户 UID
- `$this->status` string 用户身份
- `$this->level` uuid 用户级别
- `$this->info` array 用户信息
- `__construct( $type )`
  - 构造函数
    - $type string 构造类型
    - return null
- `$this->loadUser_localhost()`
  - 初始化用户 - localhost
    - return null
- `$this->loadUser_service_link()`
  - 初始化用户 - service_link
    - return null
- `$this->reset( $data )`
  - 修改访问信息
    - $data array 修改内容
    - return null
- `$this->setUser( $data )`
  - 修改用户信息
    - $data array 用户数据
    - return boolean 修改结果
- `$this->authToken()`
  - 验证用户 Token
    - return boolean 验证结果
- `$this->logout()`
  - 要求用户注销登录
    - return boolean 注销结果
- `$this->header( $key )`
  - 获取用户 Header
    - $key string 键名
    - return string 查询结果
- `$this->setPassword( $text )`
  - 生成用户密码
    - $text string 用户原密码
    - return string 加密后的密码
- `$this->setPhone( $qv, $phone )`
  - 生成手机号
    - $qv number 区号
    - $phone number 手机号
    - return string 组合后的号码
- `$this->getLevel( $status = false )`
  - 获取用户级别信息
    - $status false|number|string 级别（传入数字则直接查询用户对应等级，否则需要传入用户身份信息）
    - return number 级别参数
- `$this->userinfo( $id = false )`
  - 查询用户信息
    - $id number 用户 ID
    - return array 用户开放信息
- `$this->authLevel( $allow )`
  - 验证用户级别
    - $allow array 允许的范围（如 [0, 100]）
    - return boolean 验证结果
##### 网页加载器 loader/web.loader.php
- `task::$user` object 运行用户
- `task::result( $type, $state )`
  - 输出结果
    - $type string 输出类型
    - $state any 修改结果
    - return string 结果
- `task::echo( $state, $content, $code = 200 )`
  - 输出函数
    - state number 回调状态
    - $content string 回调内容
    - $code number 响应代码
    - return json 结果
##### 服务项加载器 loader/service.loader.php
- `task::$user` object 运行用户
- `task::$ref` object 服务项对象
- `task::$name` string 服务项名称
- `task::$config` array 服务项配置
- `task::$thread` number 所属线程
- `task::$group` array 群组信息
- `task::channelStart()`
  - 唤醒 Channel Core
    - return boolean 唤醒结果
- `task::channelLink()`
  - 连接到 Channel Core
    - return boolean 连接结果
- `task::channel( $service, $action, $res, $thread = false )`
  - 传递消息到 Channel Core
    - $service string 分组
    - $action string 执行动作
    - $res array 传递数据
    - $thread number 执行线程（不为数字时则所有线程都会响应）
    - return null
- `task::addTimer( $name, $time, $method )`
  - 设置计时器
    - $name string 设置的计时器
    - $time number 间隔时间 （秒）
    - $method function 执行操作
    - return null
- `task::delTimer( $name )`
  - 删除计时器
    - $name string 设置的计时器
    - return boolean 删除结果
- `task::log( $content )`
  - 写入 Log
    - $content 错误内容
    - return boolean 写入结果
- `task::join( $name, $conn )`
  - 加入群组
    - $name string 群组名称
    - $conn object 用户连接
    - return boolean 加入结果
- `task::quit( $name, $conn )`
  - 退出群组
    - $name string 群组名称
    - $conn object 用户连接
    - return boolean 退出结果
- `task::send( $name, $res )`
  - 发送到群组
    - $name string 群组名称
    - $res array 发送的数据
    - return boolean 发送结果
- `task::sendAll( $name, $res )`
  - 发送到所有线程群组
    - $name string 群组名称
    - $res array 发送的数据
    - return boolean 发送结果
- `task::result( $type, $state )`
  - 输出结果
    - $type string 输出类型
    - $state any 修改结果
    - return string 结果
- `task::echo( $state, $content, $code = 200 )`
  - 输出函数
    - state number|array 回调状态（当为一个两个值的数组时，第二个值将作为回调方法）
    - $content string 回调内容
    - $code number 响应代码
    - return json 结果
##### 命令行加载器 loader/cmd.loader.php
- `task::$user` object 运行用户
- `task::format( $content )`
  - 格式化参数
    - $content string 参数
    - return string 格式化后的内容
- `task::input( $content )`
  - 要求输入
    - $content string 提示文字
    - return string
- `task::confirm( $content, $method )`
  - 确认执行
    - $content string 提示文字
    - $method function 执行方法
    - return string
- `task::menu( $data, $inputText = false, $run = false )`
  - 输出菜单内容
    - $data array 菜单数据
    - $inputText string 输入提示
    - $run string 直接运行参数
    - return string
- `task::result( $type, $state )`
  - 输出结果
    - $type string 输出类型
    - $state any 修改结果
    - return string 结果
- `task::echo( $state, $content, $code = 200 )`
  - 输出函数
    - state number 回调状态
    - $content string 回调内容
    - $code number 响应代码
    - return json 结果
##### 视图中间件 support/middleware/view.middleware.php
- `view::$project` string 项目名
- `view::$view` string 视图文件
- `view::$viewDir` string 视图文件存储路径
- `view::$val` string val 语法糖
- `view::show( $file, $argv = [], $val = [] )`
  - 显示视图
    - $file string 视图路径
    - $argv array 替换参数
    - $val array 传递变量
    - return string 视图
- `view::addArgv()`
  - 生成附加参数
    - return array 替换数组
- `view::link( $url )`
  - 调用跳转代码
    - $url string 跳转地址
    - return string 跳转代码
- `view::getLang( $name = false )`
  - 获取语言列表
    - $name string 项目名称
    - return array 结果
- `view::getTheme()`
  - 获取主题列表
    - return array 结果
- `view::getTimezone()`
  - 获取时区列表
    - return array 结果
##### 数据处理 support/middleware/request.middleware.php
- `request::get( $rule, $source = 'POST' )`
  - 获取请求数据
    - $rule array 验证规则
    - $source POST|GET|array 来源
    - return array 通过的数据
- `request::safe( $value )`
  - 安全性检查
    - $value string 检查的值
    - return boolean 检查结果
##### 存储中间件 support/middleware/storage.middleware.php
- `$this->name` string 存储器名称
- `$this->config` string 存储器配置
- `$this->dir` string 存储器路径
- `$this->cache` string 缓存路径
- `$this->upload( $input = false, $num = 1 )`
  - 上传内容
    - $input string input键名
    - $num number 获取文件的最大数量
    - return array 缓存访问链接
- `$this->cacheSave( $file, $set = false )`
  - 从缓存迁移文件
    - $from string 源文件地址
    - $set array 写入设置
    - return boolean false|访问链接
- `$this->save( $file, $set )`
  - 写入文件权限
    - $file string 文件名
    - $set array 写入配置
    - return boolean 写入结果
- `$this->hasFile( $file )`
  - 检查文件是否存在
    - $file string 源文件地址
    - return boolean 查询结果
- `$this->show( $file, $default = false )`
  - 输出文件
    - $file string 文件名
    - $default string 默认文件
    - return 输出文件
- `$this->get( $file, $default = false )`
  - 获取文件
    - $file string 文件名
    - $default string 默认文件
    - return false|真实文件路径
- `$this->delete( $file )`
  - 删除文件
    - $file string 文件名
    - return boolean 删除结果
- `$this->deleteByUser( $uid )`
  - 删除用户名下文件
    - $uid number UID
    - return boolean 删除结果
##### 访问控制 support/middleware/access.middleware.php
- `access::$whitelist` array 白名单
- `access::$neglect` array 记录忽略路由
- `access::check( $set )`
  - 检查访问来源
    - $set array 覆写用户信息
    - return boolean 校验是否通过
- `access::record( $target, $set = [], $result = false )`
  - 记录访问
    - $target string 路由目标
    - $set array 覆写用户信息
    - $result boolean 校验结果
    - return boolean 记录结果
- `access::insert()`
  - 批量写入数据
    - return null
##### 推送工具 support/method/push.method.php
- `push::email( $data )`
  - 发送到邮件
    - $data array 推送数据
    - return boolean 推送结果
- `push::email( $data )`
  - 发送到 Bark
    - $data array 推送数据
    - return boolean 推送结果
- `push::email( $data )`
  - 发送到 Telegram
    - $data array 推送数据
    - return boolean 推送结果
##### Redis support/method/redis.methos.php
- `RE::$ref` object Redis 对象
- `RE::check()`
  - 检查连接状态
    - return boolean
- `RE::close()`
  - 手动关闭连接
    - return boolean
- `RE::name( 待处理的名称 )`
  - 添加前缀
    - return string
- `RE::filter( 待写入的内容 )`
  - 写入检查
    - return string
- `RE::set( 操作的库, 键名, 对应内容, 过期时间 = false )`
  - 增加或更新一个值
    - return boolean
- `RE::expire( 操作的库, 键名, 过期时间 = 64800 )`
  - 给一个值添加过期时间
    - return boolean
- `RE::get( 操作的库, 键名 )`
  - 获取一个值
    - return false/string/array
- `RE::delete( 操作的库, 键名 )`
  - 删除一个值
    - return boolean
- `RE::getAll( 操作的库, 键名 )`
  - 获取指定开始名的值
    - return false/number
- `RE::delAll( 操作的库, 键名 )`
  - 删除指定开始名的值
    - return boolean
- `RE::push( 操作的库, 键名, 对应内容 )`
  - 向数组键值中添加内容
    - return boolean
- `RE::show( 操作的库, 键名, [ 0, 2 ] 下标 = false )`
  - 获取数组键值
    - return any 查询结果
- `RE::lpop( 操作的库, 键名 )`
  - 消费数据
    - return any 查询结果
- `RE::setCache( 键名, 对应内容, 过期时间 = false )`
  - 设置缓存
    - return boolean
- `RE::getCache( 键名 )`
  - 查询缓存
    - return false/string/array
##### 工具集 support/method/tool.method.php
- `tool::coverConfig( 配置文件路径, 配置信息 )`
  - 配置文件覆盖
    - return boolean
- `tool::editConfig( 配置文件路径, 键, 值 )`
  - 配置文件编辑
    - return boolean
- `tool::endsWith( 匹配对象, 匹配内容 )`
  - 匹配结尾字符
    - return boolean
- `tool::rand( 长度, [all|number|letter] = 'all' )`
  - 随机数生成器
    - return false/string
- `tool::delDir( 目录路径 )`
  - 删除指定目录
    - return boolean
- `tool::getDir( 目录路径, [all|dir|file] = 'all' )`
  - 遍历目录
    - return false/array
##### 网络请求工具 support/method/web.method.php
- `web::get( 请求链接, 发送参数 )`
  - GET 请求
    - return any
-` web::post( 请求链接, 发送参数 )`
  - POST 请求
    - return any
- `web::custom({ 'type': GET|POST, 'url': string, 'send': array, 'header': array([]), 'cookie': array([]), 'timeout': number(30), 'ssl': boolean(true) })`
  - 复杂请求
    - return any

### 预设异步任务
###### Async Service
- `core::async( 'async', 'push_email', [], 2 )`
  - 发送到邮件
- `core::async( 'async', 'push_bark', [], 2 )`
  - 发送到 Bark
- `core::async( 'async', 'push_telegram', [], 2 )`
  - 发送到 Telegram
###### Chat Service
- `core::async( 'chat', 'send', [ 'name' => '群组名称', 'res' => '消息内容' ], true )`
  - 发送消息到群组

### 路由接口传值说明
###### API / APP / VIEW
- ( '/api/test/a/b' )->start( 3 )
- `function( $v1 = a, $v2 = b, $v3 = null )`
###### CMD
- ( 'php cmd test a b' )=>( /test/a/b )->start( 2 )
- `function( $v1 = a, $v2 = b, $v3 = null )`
###### SERVICE
- [ 'action': 'test', 'res' => [ a, b, c ] ]
- 通道：`function( $ref, $res )`
- 其它：`function( $conn, $res )`
- 通过连接读取：`$conn->post`


### 视图解析器
##### 视图变量
- `$user` object 当前用户
- `$val` array 外部导入变量
##### 本地存储
- `identifier` string 用户识别码
- `lang` string 当前语言
- `theme` string 当前主题
- `timezone` string 当前时区
- `token` string 当前 token
- `info` array 服务器信息
- `user` array 用户信息
##### 主要脚本 public/library/js/core.js
- `empty( v )` 判断变量是否存在
- `is_json( v )` 判断变量是否为 JSON
- `is_array( v )` 判断变量是否为数组或者对象
- `t( word, replace = {} )` 使用语言包
- `get( key )` 查询本地存储
- `set( key, value )` 设置本地存储
- `del( key )` 删除本地存储
- `is_number( v )` 判断变量是否为数字
- `uuid( check = false )` 生成 UUID
- `is_uuid( v )` 判断变量是否为 UUID
- `is_date( dateString )` 判断变量是否为日期
- `is_time( input )` 判断变量是否为时间
- `is_datetime( str )` 判断变量是否为完整时间
- `c.complete` boolean 页面加载状态
- `c.id` string 用户识别码
- `c.info` array 服务器信息
- `c.user` array 用户信息
- `c.themeConfig` array 所有主题信息
- `c.service` array 服务项
- `c.size` array 屏幕尺寸
- `c.send( data, load = false )` 发起一个网络请求
- `c.res( data, res )` 验证 API 数据
- `c.form( e, run = false )` 表单获取
- `c.serverInfo()` 刷新信息
-` c.view( vals, code, div = false )` 视图渲染
- `c.text( name, text )` 文本渲染
- `c.viewLoad( div, timeout = false )` 视图加载
- `c.header()` 附加请求头
- `c.login( res, to = false )` 登录到用户
- `c.logout()` 注销登录状态
- `c.showPassword( div )` 切换密码显示
- `c.setCache( type, v = false )` 设置缓存
- `c.hashParams()` 链接地址哈希解析
- `c.deleteUpload( id )` 删除上传文件
- `c.babel()` Babel 加载
- `unit.load( state, timeout = false )` 加载动画组件
- `unit.toast( text, error = false, timeout = 5000 )` Toast 通知组件
- `unit.checkBig( link )` 查看大图
- `unit.popup( title, content = '', button = false, width = '480px' )` Popup 弹窗组件
- `unit.action( div, name, time = 180 )` 为组件执行动画
- `unit.uploadPreview( id, file )` 渲染上传图片
- `val` object 共享变量
##### Markdown /library/javascript/markdown.js
- `md.deit( text )` 将内容转为可编辑文本
- `md.show( text )` 代码保护输出
- `md.to( text )` 将内容转为 HTML
##### 组件样式 public/library/css/unit.css
- 按钮组件
  - `a class='button'`
  - `button class='button'`
- 卡片组件
  - `div class='card'`
- 点击复制
  - `button class="button" data-clipboard-text=''`