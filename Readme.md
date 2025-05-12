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
#### 工具集 support/Helper/Tool.helper.php
- 方法请求 - 用于请求控制器中的方法
  - `Tool::runMethod( $type[string]控制器类型, $class[string|array]类名和方法, ...$parameter[mixed]传递参数 )`
  - return mixed 方法返回值
- 将字符串转换为数组 - 用于将 a:1|b:2 字符串转换为数组
  - `Tool::toArray( $str[string]字符串 )`
  - return array 数组
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
  - return bool 构建结果
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
->html( $file[string]文件路径 ) // 以 public 下的文件回调
->group( $callable[function]函数形式书写 ) // 添加子路由
->save() // 保存路由
```

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