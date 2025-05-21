# TCore For PHP
---
> 轻巧便携的 PHP 开发框架

### 安装
1. 修改运行目录为 `public`
2. 设置伪静态
    ```
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    ```
3. 在根目录执行命令安装扩展
    ```
    composer install
    ```

### 基础方法
#### 核心驱动器 support/Bootstrap.php
- 初始化状态 : bool `Bootstrap::$init`
- 数据库连接 : object `Bootstrap::$db`
- 应用缓存 : array `Bootstrap::$cache`
- 构建应用 - 用于构建并初始化应用，此方法只需在启动应用时调用一次
  - `Bootstrap::build( $method|null[function]传入方法 )`
  - return mixed 回调结果
- 系统干预流程 - 用于执行系统干预流程的任务
  - `Bootstrap::processRun( $type[string]任务类型, $parameter|null[mixed]传递参数 )`
  - return mixed 处理结果
- 获取应用缓存 - 用于获取和设置应用缓存，名称以 php 结尾则以 php 保存，否则保存为 txt ，加载方法可以输出一段代码或文本或数组
  - `Bootstrap::cache( $type[thread|file]缓存类型, $name[string]缓存名称, $load|null[function]加载方法 )`
  - return mixed 缓存结果
- 写入日志 - 用于写入核心程序执行日志
  - `Bootstrap::log( $title[string]日志标题, $content[string]日志内容 )`
  - return boolean 写入结果
- 抛出一个错误 - 用于抛出一个错误，通常用于调试或异常处理
  - `Bootstrap::error( $data[string]错误信息 )`
  - throws Exception 抛出异常
- 注册自动加载 - 用于注册自动加载类文件
  - `Bootstrap::autoload( $config[array]自动加载配置 )`
  - return boolean 注册结果
#### 全局基础函数 support/Helper/Global.helper.php
- 获取系统文件路径 - 优先查询系统文件，如果没有，则使用传入的路径
  - `__file( $dir[string]传入的目录路径 )`
  - return string 文件路径
- 删除目录 - 删除一个指定目录及其所有子目录和文件
  - `deleteDir( $dir[string]目录路径 )`
  - return bool 删除结果
- 路径文件夹保护 - 检查传入的路径是否存在，如果不存在则创建
  - `inFolder( $dir[string]传入的路径 )`
  - return string|false 创建成功的路径或 false
- 检查是否为 JSON 格式 - 检查传入的值是否为有效的 JSON 格式
  - `is_json( $val[mixed]检查对象 )`
  - return bool 判断结果
- 检查是否为 UUID 格式 - 检查传入的值是否为有效的 UUID 格式
  - `is_uuid( $val[mixed]检查对象 )`
  - return bool 判断结果
- 生成 UUID - 生成一个随机的 UUID 字符串
  - `uuid()`
  - return string UUID
- 调试参数 - 打印传入的参数，并在最后选择性退出
  - `dd( $val[mixed]传入的值, $exit|true[bool]是否退出 )`
  - return void
- 拼接长文本 - 将传入的数组或字符串拼接成一个长文本
  - `toString( $text[mixed]传入的文本 )`
  - return string 拼接后的文本
#### 系统基础函数 support/Helper/System.helper.php
- ENV 变量 - 获取 ENV 环境变量
  - `env( $key[string]变量名, $default|null[mixed]默认值 )`
  - return mixed
- 配置信息 - 获取配置信息
  - `config( $key[string]变量名, $default|null[mixed]默认值 )`
  - return mixed
- 批量引用文件 - 用于引用一个或多个文件
  - `import( $file[string|array]文件名或文件数组 )`
  - return mixed 引用结果
- 使用语言包 - 用于使用语言包
  - `__( $key[string]语言包键, $replace[array]替换内容, $locale[string]语言 )`
  - return string 语言包内容
- 格式化时间 - 将时间戳转换为日期格式
  - `toDate( $time|false[int|object|false]时间戳或时间对象，为 false 时使用当前时间 )`
  - return string 格式化后的日期字符串
- 哈希一个参数 - 用于对一个参数进行哈希处理
  - `h( $content[string]要哈希的内容 )`
  - return string 哈希后的字符串
- 判断字符串是否以指定内容开始 - 用于判断一个字符串是否以指定内容开始
  - `startsWith( $string[string]要判断的字符串, $val[string]要判断的内容 )`
  - return bool 判断结果
- 判断字符串是否以指定内容结尾 - 用于判断一个字符串是否以指定内容结尾
  - `endsWith( $string[string]要判断的字符串, $val[string]要判断的内容 )`
  - return bool 判断结果
- 截断字符 [ Chinese ] - 用于截断中文字符
  - `limitCn( $value[string]要截断的字符串, $length[int]截断长度, $end|''[string]截断后缀 )`
  - return string 处理后的内容
- 截断字符 [ English ] - 用于截断英文字符
  - `limitEn( $value[string]要截断的字符串, $length[int]截断长度, $end|''[string]截断后缀 )`
  - return string 处理后的内容
- 使用系统插件 - 用于使用系统插件
  - `Plug( $name[string]插件名称, $target|class[class|config|folder]访问目标 )`
  - return object 插件对象
- 访问接口控制器 - 用于访问接口控制器
  - `Controller( $class[string|array]控制器方法, ...$parameter[mixed]传递参数 )`
  - return mixed 控制器返回值
- 访问任务控制器 - 用于访问务控制器
  - `Task( $class[string|array]控制器方法, ...$parameter[mixed]传递参数 )`
  - return mixed 控制器返回值
- 访问视图 - 用于访问视图
  - `View( $view[string]视图名称, $parameter|[][array]传递参数, $cache|false[bool]是否缓存 )`
  - return string 视图内容
- 链接跳转方式 - 用于实现网页链接跳转
  - `ToUrl( $url[string]跳转地址 )`
  - rreturn string 跳转代码
- 访问文件 - 用于访问文件， 可传入文件链接或文件路径
  - `ToFile( $file[string]文件路径 )`
  - return File|null 文件对象
- 访问存储器 - 用于访问存储器
  - `ToStorage( $name[string]存储器名称 )`
  - return Storage|null 存储器对象
#### 工具集 support/Helper/Tool.helper.php
- 方法请求 - 用于请求控制器中的方法
  - `Tool::runMethod( $type[string]控制器类型, $class[string|array]类名和方法, ...$parameter[mixed]传递参数 )`
  - return mixed 方法返回值
- 将字符串转换为数组 - 用于将 a:1|b:2 字符串转换为数组
  - `Tool::toArray( $str[string]字符串 )`
  - return array 数组
- 加载配置 - 用于插件加载自身配置信息
  - `Tool::plugLoadConfig( $obj[object]操作对象, $key[string]键, $file[string]访问文件 )`
  - return array 配置数组
- 随机数生成器 - 用于生成一段随机数
  - `Tool::rand( $length[int]操作对象, $type[all|number|letter]随机生成类型 )`
  - return string 随机内容
- 配置文件覆盖 - 用于覆写一个 PHP/JSON 配置文件
  - `Tool::coverConfig( $file[string]文件路径, $arr[array]配置数组 )`
  - return boolean 覆写结果
#### 网络请求工具 support/Helper/Web.helper.php
- 发起 GET 请求 - 用于发起一个简单的 GET 请求
  - `Web::get( $url[string]请求地址, $send|[][array]发送参数 )`
  - return false|array 请求结果
- 发起 POST 请求 - 用于发起一个简单的 POST 请求
  - `Web::post( $url[string]请求地址, $send|[][array]发送参数 )`
  - return false|array 请求结果
- 复杂请求 - 用于发起一个复杂的请求
  - `Web::custom( $data|[][array]发送参数 )`
  - return false|array 请求结果
#### 推送工具 support/Helper/Push.helper.php
- 推送到 Email
  - `Push::email( $data[array]请求参数, $async|false[boolean]异步方式请求 )`
  - return boolean 推送结果
- 推送到 Telegram
  - `Push::telegram( $data[array]请求参数, $async|false[boolean]异步方式请求 )`
  - return boolean 推送结果
- 推送到 Bark
  - `Push::bark( $data[array]请求参数, $async|false[boolean]异步方式请求 )`
  - return boolean 推送结果
#### Session support/Handler/Session.helper.php
- 初始化 Session - 使用 Session 时会自动调用此方法
  - `Session::init()`
  - return boolean 初始化结果
- 获取 Session - 键名为 null 时返回所有 Session
  - `Session::get( $key|null[string]键名 )`
  - return mixed Session 值
- 删除 Session - 用于删除 Session
  - `Session::del( $key[string]键名 )`
  - return boolean 删除结果
- 设置 Session - 值为数组时会自动转换为 JSON
  - `Session::set( $key[string]键名，$value[string|array]值 )`
  - return boolean 设置结果
- 重置 Session - 清空 Session
  - `Session::reset()`
  - return boolean 重置结果
#### 请求构建器 support/Handler/Request.helper.php
- 构造请求 - $type 传入数组可自定义构造
  - `new Request( $type|null[string|array]请求类型 )`
  - return void
- 初始化请求 - 用于初始化一个请求
  - `$request->init( $type|null[string]请求类型 )`
  - return void
- 验证请求 - 用于验证请求参数是否正常
  - `$request->verifyRequest()`
  - return bool 验证结果
- 自动构建 - 根据请求类型自动构建请求
  - `$request->autoBuild()`
  - return bool 构建结果
- 修改构建的请求 - 传入一个数组用于自定义请求
  - `$request->edit( $data[array]请求数据 )`
  - return RequestBuild 返回当前对象
- 数据验证 - 用于验证请求参数
  - `$request->vaildata( $rules[array]验证规则, $data|null[array]验证数据 )`
  - return array 验证结果
- 使用语言包 - 用于针对用户语言使用语言包
  - `$request->t( $key[string]语言包键, $replace[array]替换内容 )`
  - return string 语言包内容
- 回调数据结果 - $state 支持 Boolean / Array / Int
  - `$request->echo( $state[int|array|bool]返回状态, $data[mixed]返回数据, $code|null[mixed]返回状态码, $header|[ 'Content-Type' => 'application/json' ][array]返回头部 )`
  - return string 返回结果
#### 路由驱动器 support/Handler/Router.helper.php
- 路由缓存 : array Router::$cache
- 路由初始化 - 用于初始化加载路由
  - `Router::init( $request[Request] )`
  - return string 路由结果
- 路由加载 - 用于加载路由文件
  - `Router::load( $router[string]路由名称 )`
  - return bool 加载结果
- 路由构建器 - 用于构建路由
  - `Router::add( $target[string]路由名称, $method|null[string]路由方法 )`
  - return RouterBuild 路由构建器对象
- 路由搜索 - 用于搜索路由
  - `Router::search( $request[Request], $router[string]路由名称, $target[string]路由目标, $method|'ANY'[string]路由方法, $parameter|[][array]传递参数 )`
  - return mixed 路由结果
- 运行路由 - 用于运行指定路由配置
  - `Router::runRouter( $request[Request], $config[array]路由配置, ...$parameter[mixed]传递参数 )`
  - return mixed 路由结果
- 路由错误处理 - 用于输出由于路由导致的错误
  - `Router::error( $request[Request], $code[int]错误代码, $msg[mixed]错误内容 )`
  - return string 错误结果
#### 路由构造器 support/Slots/RouterBuild.slots.php
```
Router::add( '/test/{{必填}}{{选填}}' )
->auth( $callable[function]验证方法 ) // 添加验证项
->to( $callable[function]回调方法 ) // 以函数形式回调
->url( $url[string]跳转地址 ) // 以地址回调
->controller( $class[string|array]类方法 ) // 以接口控制器回调
->task( $class[string|array]类方法 ) // 以任务控制器回调
->file( $file[string]文件路径 ) // 以 public 下的文件回调
->view( $view[string]视图路径, $share[][array]共享参数 ) // 访问视图文件
->assets( $path[string]资源路径 ) // 动态调用资产
->group( $callable[function]函数形式书写 ) // 添加子路由
->save() // 保存路由
```
#### 命令行工具 support/Provider/Shell.provider.php
- 格式化参数 - 用于处理个性化文字
  - `Shell::format( $content[string|array]内容 )`
  - return string 格式化后的内容
- 要求输入 - 用于要求用户输入
  - `Shell::input( $content[string|array]提示内容 )`
  - return string 返回输入内容
- 输出菜单内容 - 用于输出一个选项菜单内容
  - `Shell::menu( $request[Request], $data[array]菜单数据, $inputText|false[string|array]输入提示内容, $run|false[string]运行数据 )`
  - return mixed 返回运行结果
- 确认执行 - 用于确认执行操作
  - `Shell::confirm( $request[Request], $content[string|array]提示内容, $method[function]执行方法 )`
  - return mixed 返回执行结果
- 循环输出 - 用于循环输出内容
  - `Shell::loop( $text[string|array]输出内容 )`
  - return string 返回格式化后的内容
- 进度条 - 用于输出进度条
  - `Shell::schedule( $current[int]当前进度, $total|100[int]总进度 )`
  - return string 返回格式化后的进度条
#### 存储管理器 support/Handler/Storage.handler.php
- 存储器类型 : string `$storage->type`
- 存储器路径 : string `$storage->path`
- 允许的文件类型 : array `$storage->allow`
- 最大文件大小 : int `$storage->maxSize`
- 删除时间 : int `$storage->delete`
- 构造存储器
  - `new Storage( $name[string]存储器名称 )`
  - return void
- 上传文件 - 用于处理 form 上传的文件
  - `$storage->upload( $file[array|string]上传的文件 )`
  - return File|boolean 上传结果
- 获取存储器中的文件 - 用于获取存储器中的文件
  - `$storage->file( $name[string]文件名称 )`
  - return File|null 文件对象
- 生成存储器中的文件路径 - 用于生成存储器中的文件路径
  - `$storage->filePath( $name[string]文件名称 )`
  - return string|null 文件路径
#### 文件处理器 support/Handler/File.handler.php
- 文件标识 : UUID `$file->id`
- 文件路径 : string `$file->path`
- 文件扩展名 : string `$file->ext`
- 文件名称 : string `$file->name`
- 文件大小 : int `$file->size`
- 构造文件
  - `new File( $file[string]文件路径 )`
  - return void
- 删除文件 - 用于删除当前文件
  - `$file->delete()`
  - return boolean 删除结果
- 复制文件 - 用于复制当前文件
  - `$file->copy( $to[string]目标路径, $delete|false[bool]是否删除源文件 )`
- 生成文件获取链接 - 用于生成文件获取链接
  - `$file->link()`
  - return string 文件链接
- 输出文件 - 用于输出文件
  - `$file->echo( $expires|2592000[int]过期时间（秒） )`
  - return null
#### Redis 操作工具 support/Handler/Redis.handler.php
- Redis 实体 : object `Redis::$ref`
- 获取 Redis 连接 - 用于获取原生 Redis 连接
  - `Redis::link()`
  - return object|null 连接
- 注册 Redis - 用于初始化注册
  - `Redis::register()`
  - return boolean 注册结果
- 切换 Redis 库 - 用于切换当前选择的库
  - `Redis::select( $number|0[int]库编号 )`
  - return boolean 切换结果
- 手动关闭连接 - 用于关闭 Redis 连接
  - `Redis::close()`
  - return true 关闭结果
- 添加前缀 - 用于给键名加上指定头
  - `Redis::name( $text[string]字段名 )`
  - return string 加工后的名称
- 过滤输入内容 - 格式化检查输入的内容
  - `Redis::filter( $text[any]输入内容 )`
  - return string 过滤结果
- 提取输出内容 - 格式化处理输出的内容
  - `Redis::output( $result[string]输出结果 )`
  - return any 提取结果
- 给一个值添加过期时间 - 用于给一个缓存添加过期时间
  - `Redis::expire( $key[string]键名, $expire|false[int]过期时间秒 )`
  - return boolean 添加结果
- 查询缓存 - 用于查询一个缓存
  - `Redis::get( $key[string]键名 )`
  - return any 查询结果
- 设置缓存 - 用于设定一个缓存
  - `Redis::expire( $key[string]键名, $value[any]值, $expire|false[int]过期时间秒 )`
  - return boolean 设置结果
- 删除缓存 - 用于删除一个缓存
  - `Redis::del( $key[string]键名 )`
  - return number 删除数量
- 查询全部指定开头缓存 - 查询所有指定关键词开头的缓存
  - `Redis::getAll( $startKey[string]键名 )`
  - return any 查询结果
- 删除全部指定开头缓存 - 删除所有指定关键词开头的缓存
  - `Redis::delAll( $startKey[string]键名 )`
  - return number 删除数量
- 向数组中添加缓存 - 用于向数组缓存中添加数据
  - `Redis::push( $key[string]键名, $value[any]值, $expire|false[int]过期时间秒 )`
  - return boolean 设置结果
- 查询数组缓存 - 用于查询数组类的缓存
  - `Redis::array( $key[string]键名, $index|false[int]数组下标 )`
  - return any 查询结果
- 消费数组缓存 - 用于在数组中依次消费数据
  - `Redis::lpop( $key[string]键名 )`
  - return any 本次消费内容
#### 账户构造器 support/Handler/Account.handler.php
- 用户构造状态 : boolean `$user->state`
- 用户 UID : int `$user->uid`
- 用户级别 : int `$user->level`
- 用户级别名称 : string `$user->status`
- 用户信息 : object `$user->info`
- 构造用户 - 用于构造一个用户
  - `new Account( $request|null[mixed]登录方式 )`
  - return void
- 共享信息 - 用于输出用户可公开的信息
  - `$user->share()`
  - return array 公开信息
- 生成密钥 - 用于生成用户的可登录 Token
  - `$user->token( $request[Request], $remember|false[bool]是否记住登录状态 )`
  - return string Token
- 密码生成 - 用于规范密码的加密方式
  - `$user->password( $text[string]原始密码, $garble[string]混淆字符 )`
  - return string 新密码
- 级别转身份 - 用于输入用户可视化级别
  - `$user->levelToStatus( $level[int]用户级别 )`
  - return string|null 用户级别名称

### 开发者说明
#### 核心驱动器缓存名称申明
- ENV 环境变量 : file | env.php
- 配置缓存 : thread | config:xxx
- 配置键覆写缓存 : thread | config_cover:xxx
- 自动加载类 : thread | autoload
- 插件缓存 : thread | plug:xxx
- 系统干预流程 : thread | process
- 语言包缓存 : thread | lang:xxx
#### 允许的系统干预流程
- 系统初始化完成 :
  - InitializationCompleted
  - 系统初始化构建完成时调用
- 修改输出返回结果 :
  - OutputReturnResult => $data[返回数据]
  - 所有响应结果都会响应达此处，且可以修改返回内容。
- 修改查询配置信息 :
  - QueryConfiguration => $key[查询键]
  - 当输出内容不为 null 且不等于 $key 时，以输出内容覆盖查询。
- 请求构建完成 :
  - ConstructingRequest => $request[Request]
  - 可修改 $request，但必须输出 $request 。
- 请求回调结果 :
  - ResultCallback => $res[接口回调内容]
  - 可调整 $res 输出，但必须输出 $res 。
- 路由注册 :
  - RouteRegistration => $router[当前查询路由名称]
  - 可用于注册新的路由
- 查询语言包 :
  - QueryLanguagePackage => [ 'lang' => $locale, 'target' => $target ]
  - 输出一个数组以添加语言包
#### 视图模板语法
- 注释内容
  ```
  {{/* 这是一段注释 */}}
  ```
- 输出变量
  ```
  {{ $variable }}
  ```
- PHP 代码
  ```
  {{!! $variable = '1'; echo $variable; !!}}
  ```
- 判断语句
  ```
  @if( $variable === 1 ):
      ...
  @elseif( $variable === 2 ):
      ...
  @else:
      ...
  @endif
  ```
- foreach 循环
  ```
  @foreach( $variable as $key => $value ):
      ...
  @endforeach
  ```
- for 循环
  ```
  @for( $i = 0; $i < 5; $i++ ):
      ...
  @endfor
  ```
- while 循环
  ```
  @while( $variable < 5 ):
      ...
  @endwhile
  ```
- 组件调用
  ```
  // 调用 resource/view/module/ 目录下的组件
  <x-...></x-...>
  // 调用 resource/view/ 目录下的组件
  <x-_...></x-_...>
- 视图名称: String `{{ $ViewName }}`
- 当前主题: Array `{{ $theme['...'] }}`
- 当前主题名称: String `{{ $themeName }}`
- 文件版本: String `{{ $v }}`
- 系统版本: String `{{ $version }}`
- 当前语言: String `{{ $lang }}`
- 调用静态文件: Function `{{ $assets( '...' ) }}`
- 当前请求: Object `{{ $request }}`
- 语言包使用:
  ```
  {{ $t( '...' ) }} // 调用全局语言包
  {{ $t( '&...' ) }} // 调用视图同名下的语言包
  ```
#### Core.js 说明
- 页面加载状态: `tc.complete` boolean
- 语言包: `tc.text` object
- 服务器信息: `tc.server` object
- 用户信息: `tc.user` object
- 屏幕尺寸: `tc.size` object
- 复制对象: `tc.clipboard` object
- 初始化页面
  ```
  tc.SystemDefaultLoading()
  return null
  ```
- 从服务器刷新信息
  ```
  tc.refresh( type:string(类型) )
  return null
  // 类型支持: [ 'text', 'server', 'all' ]
  ```
- 缓存操作
  ```
  tc.cache( name:string(缓存名), value|false:string(缓存值) )
  return boolean:操作结果
  // 允许的缓存: [ 'id', 'lang', 'themeName', 'theme' ]
  ```
- 发起一个网络请求
  ```
  tc.send( data:array(请求数据), load|false:function( 加载函数 ) )
  return async:异步请求实体;
  // data 示例
  const data = {
      url: 链接[string],
      type: 请求方法[string],
      data: 传递参数[json/array],
      async: 是否为异步请求[boolean],
      check: 是否使用内部检查器[boolean],
      run: 请求成功执行方法[function],
      error: 请求失败执行方法[function],
      other: 其它传递给 ajax 的内容[array]
  };
  ```
- 验证 API 数据
  ```
  tc.res( data:array(请求配置), res:any(数据) )
  return any:数据
  // data 示例
  const data = {
      run: 验证成功执行方法[function],
      error: 验证失败执行方法[function]
  };
  ```
- 附加请求头
  ```
  tc.header()
  return array:请求头数据
  ```
- 登录账户
  ```
  tc.login( res:array(登录信息) )
  return boolean 通知结果
  ```
- 注销登录
  ```
  tc.logout( reload|false:boolean/string(注销完成行为) )
  return boolean 通知结果
  ```
- 视图操作
  ```
  tc.view( 'name' )
  // 添加代码 ( code 为 null 时返回组件内代码 )
  .html( code|null:string(代码) )
  // 检查元素是否存在
  .has()
  // 检查类名是否存在
  .hasClass()
  // 激活元素 ( state 为 null 时自动切换，并返回当前元素激活状态 )
  .action( state|null:boolean(状态) )
  // 类名交换
  .replace( params1:string(类名), params2:string(类名) )
  // 添加类名
  .addClass( name:string(类名) )
  // 移除类名
  .removeClass( name:string(类名) )
  // 设置类名
  .setClass( value:string(类名) )
  // 设置样式
  .style( data:array(样式集合) )
  // 设置属性
  .attr( name:string(属性名), value:string(属性值) )
  // 移除元素
  .remove()
  // 查询子元素
  .find( name:string(子元素) )
  // 为元素执行动画
  .animation( name:string(动画名称), time|180:number(动画时长) )
  // 为元素添加加载动画
  .load( state:boolean(状态), timeout|3000:number(自动消失时间) )
  // 渲染列表
  .list( list:array(列表数据), code:string(项目代码) )
  // 组件滚动
  .scroll( type:string(滚动方向), offset|null:number(偏移数值), timeout|180:number(偏移时间) )
  // 事件注册
  .on( type:string(事件类型), method:function(事件) )

  return ohject|链式调用
  ```
- Toast 通知
  ```
  tc.unit.toast( text:string(通知内容), error|false:boolean(错误消息), timeout|3000:number(自动消失时间) )
  return boolean:响应结果
  ```
- 加载动画
  ```
  tc.unit.load( state:boolean(状态), timeout|3000:number(自动消失时间) )
  return boolean:响应结果
  ```
- 弹窗组件
  ```
  tc.unit.popup( title|false:string(标题), body|'':string(主内容) option|{}:array(可选参数) )
  return boolean:响应结果
  // 使用示例
  tc.unit.popup( '这是一个标题', '弹窗内容代码', {
      width: '300px', // 弹窗宽度
      run: () => {} // 确认执行函数
      icon: 'bi-star', // 确认执行图标
      text: '确定', // 确认执行文本
      close: false // 点击确认后是否自动关闭
  });
  ```
- 查看大图
  ```
  tc.unit.bigImage( link:string(图片链接) )
  return boolean:响应结果
  ```
- 注册输入框数据
  ```
  tc.form.register( rid:string(RID), data:json(数据) )
  return boolean:注册结果
  ```
- 刷新验证码
  ```
  tc.form.verifyImg( rid:string(验证码name) )
  return boolean:刷新结果
  ```
- 提交表单
  ```
  tc.form.submit( id:string(表单ID), method|null:string(执行方法), link|null:string(提交地址) )
  return array:表单数据
  ```
- 验证表单
  ```
  tc.form.vaildata( data:object(表单数据), rules:object(表单规则) )
  return boolean:验证结果
  ```
- 判断变量是否存在
  ```
  empty( v:any(检查参数) )
  return boolean:判断结果
  ```
- 判断变量是否为 JSON
  ```
  is_json( v:any(检查参数) )
  return boolean:判断结果
  ```
- 判断变量是否为数组或者对象
  ```
  is_array( v:any(检查参数) )
  return boolean:判断结果
  ```
- 查询本地存储
  ```
  get( key:string(键名) )
  return any:查询结果
  ```
- 设置本地存储
  ```
  set( key:string(键名), value:any(值) )
  return boolean:设置结果
  ```
- 删除本地存储
  ```
  del( key:string(键名) )
  return boolean:删除结果
  ```
- 判断变量是否为数字
  ```
  is_number( v:any(检查参数) )
  return boolean:判断结果
  ```
- 生成 UUID
  ```
  uuid( check|false:boolean(检查参数) )
  return string:UUID
  ```
- 判断变量是否为 UUID
  ```
  is_uuid( v:any(检查参数) )
  return boolean:判断结果
  ```
- 判断变量是否为日期
  ```
  is_date( v:any(检查参数) )
  return boolean:判断结果
  ```
- 转为时间
  ```
  toTime( v:string(参数) )
  return string:转换结果
  ```
- 转为完整时间
  ```
  toDatetime( v:string(参数) )
  return string:转换结果
  ```
- Hash 256
  ```
  hash( v:string(参数) )
  return string:加密结果
  ```
- 使用语言包
  ```
  t( word:string(语言键), replace|{}:array(替换参数) )
  return string:传出语言
  ```