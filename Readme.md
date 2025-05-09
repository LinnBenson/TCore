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
- 应用缓存 : array `Bootstrap::$cache`
- 构建应用 - 用于构建并初始化应用，此方法只需在启动应用时调用一次
  - `Bootstrap::build( $method|null[function]传入方法 )`
  - return mixed 回调结果
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

### 开发者说明
#### 核心驱动器缓存名称申明
- ENV 环境变量 : file | env.php
- 配置缓存 : thread | config:xxx
- 自动加载类 : thread | autoload
- 插件缓存 : thread | plug:xxx
#### 日志名称申明