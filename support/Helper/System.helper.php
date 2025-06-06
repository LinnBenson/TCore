<?php

use Illuminate\Support\Str;
use Support\Handler\File;
use Support\Handler\Storage;
use Support\Helper\Tool;

    /**
     * ENV 变量
     * - 获取 ENV 环境变量
     * - @param string $key 变量名
     * - @param mixed $default 默认值
     * - @return mixed
     */
    function env( $key, $default = null ) {
        $value = isset( $_ENV[$key] ) && $_ENV[$key] !== '' ? $_ENV[$key] : $default;
        if ( $value === 'true' ) { return true; }
        if ( $value === 'false' ) { return false; }
        if ( $value === 'null' ) { return null; }
        return $value;
    }
    /**
     * 配置信息
     * - 获取配置信息
     * - @param string $key 变量名
     * - @param mixed $default 默认值
     * - @return mixed
     */
    function config( $key, $default = null ) {
        // 三方程序干预
        if ( Bootstrap::$init ) {
            $cover = Bootstrap::cache( 'thread', "config_cover:{$key}", function()use( $key ) {
                return Bootstrap::processRun( 'QueryConfiguration', $key );
            });
            if ( $cover !== $key && $cover !== null ) { return $cover; }
        }
        // 键拆分
        $keys = explode( '.', $key );
        // 获取配置文件
        $value = Bootstrap::cache( 'thread', "config:{$keys[0]}", function()use( $keys ) {
            $result = [];
            $systemFile = "support/System/config/{$keys[0]}.config.php";
            if ( file_exists( $systemFile ) ) { $result = require $systemFile; }
            $userFile = "config/{$keys[0]}.config.php";
            if ( file_exists( $userFile ) ) { $result = array_merge( $result, require $userFile ); }
            return $result;
        });
        // 获取配置值
        if ( !is_array( $value ) ) { return $default; }
        if ( count( $keys ) === 1 && !empty( $value ) ) { return $value; }
        if ( empty( $value ) ) { return $default;}
        array_shift( $keys ); foreach ( $keys as $k ) {
            if ( isset( $value[$k] ) ) {
                $value = $value[$k];
            }else {
                return $default;
            }
        }
        return $value;
    }
    /**
     * 批量引用文件
     * - 用于引用一个或多个文件
     * - @param string|array $file 文件名或文件数组
     * - @return mixed 引用结果
     */
    function import( $file ) {
        if ( is_string( $file ) ) { return require __file( $file ); }
        if ( is_array( $file ) ) {
            $result = [];
            for ( $i = 0; $i < 9999; $i++ ) {
                if ( empty( $file[$i] ) ) { break; }
                $quote = require_once __file( $file[$i] );
                if ( is_array( $quote ) ) { $result = array_merge( $result, $quote ); }
            }
            return $result;
        }
        return null;
    }
    /**
     * 使用语言包
     * - 用于使用语言包
     * - @param string $key 语言包键
     * - @param array $replace 替换内容
     * - @param string|null $locale 语言
     * - @return string 语言包内容
     */
    function __( $key, $replace = [], $locale = null ) {
        // 检查是否为双重语言调用
        $check = explode( ':', $key );
        if ( count( $check ) === 2 && empty( $replace ) ) { return __( $check[0], [], $locale ).__( $check[1], [], $locale ); }
        // 整理参数
        $keys = explode( '.', $key );
        if ( empty( $locale ) ) { $locale = config( 'app.lang' ); }
        // 加载使用的语言包
        $text = Bootstrap::cache( 'thread', "lang:{$locale}|{$keys[0]}", function()use( $locale, $keys ) {
            $result = Bootstrap::processRun( 'QueryLanguagePackage', [ 'lang' => $locale, 'target' => $keys[0] ] );
            if ( !is_array( $result ) ) { $result = []; }
            $folder1 = "support/System/resource/lang/{$locale}/{$keys[0]}.lang.php";
            $folder2 = "resource/lang/{$locale}/{$keys[0]}.lang.php";
            if ( !file_exists( $folder1 ) && !file_exists( $folder2 ) ) {
                $locale = config( 'app.lang' );
                $folder1 = "support/System/resource/lang/{$locale}/{$keys[0]}.lang.php";
                $folder2 = "resource/lang/{$locale}/{$keys[0]}.lang.php";
            }
            if ( file_exists( $folder1 ) ) { $result = array_merge( $result, require $folder1 ); }
            if ( file_exists( $folder2 ) ) { $result = array_merge( $result, require $folder2 ); }
            return is_array( $result ) ? $result : [];
        });
        // 查询语言包
        array_shift( $keys ); foreach ( $keys as $k ) {
            if ( !isset( $text[$k] ) ) { return $key; }
            $text = $text[$k];
        }
        if ( !is_string( $text ) ) { return $key; }
        if ( empty( $replace ) ) { return $text; }
        // 替换占位符
        foreach ( $replace as $k => $v ) { $text = str_replace( "{{".$k."}}", $v, $text ); }
        return $text;
    }
    /**
     * 格式化时间
     * - 将时间戳转换为日期格式
     * - @param int|object|false $time 时间戳或时间对象，为 false 时使用当前时间
     * - @return string 格式化后的日期字符串
     */
    function toDate( $time = false ) {
        if ( !empty( $time ) && is_numeric( $time ) ) { return date( 'Y-m-d H:i:s', $time ); }
        if ( is_object( $time ) ) { return \Carbon\Carbon::parse( $time )->timezone( config('app.timezone') ); }
        return date( 'Y-m-d H:i:s' );
    };
    /**
     * 哈希一个参数
     * - 用于对一个参数进行哈希处理
     * - @param string $content 要哈希的内容
     * - @return string 哈希后的字符串
     */
    function h( $content ) {
        if ( !is_string( $content ) ) { return null; }
        return hash( 'sha256', $content.env( 'APP_KEY', 'DefaultKey' ) );
    }
    /**
     * 判断字符串是否以指定内容开始
     * - 用于判断一个字符串是否以指定内容开始
     * - @param string $string 要判断的字符串
     * - @param string $val 要判断的内容
     * - @return bool 判断结果
     */
    function startsWith( $string, $val ) { return Str::startsWith( $string, $val ); }
    /**
     * 判断字符串是否以指定内容结尾
     * - 用于判断一个字符串是否以指定内容结尾
     * - @param string $string 要判断的字符串
     * - @param string $val 要判断的内容
     * - @return bool 判断结果
     */
    function endsWith( $string, $val ) { return Str::endsWith( $string, $val ); }
    /**
     * 截断字符 [ Chinese ]
     * - 用于截断中文字符
     * - @param string $value 要截断的字符串
     * - @param int $length 截断长度
     * - @param string $end 截断后缀
     * - @return string 处理后的内容
     */
    function limitCn( $value, $length, $end = '' ) { return Str::limit( $value, $length, $end ); }
    /**
     * 截断字符 [ English ]
     * - 用于截断英文字符
     * - @param string $value 要截断的字符串
     * - @param int $length 截断长度
     * - @param string $end 截断后缀
     * - @return string 处理后的内容
     */
    function limitEn( $value, $length, $end = '' ) { return Str::words( $value, $length, $end ); }
    /**
     * 使用系统插件
     * - 用于使用系统插件
     * - @param string $name 插件名称
     * - @param string $target 访问目标，class|config|folder，默认为 class
     * - @return object 插件对象
     */
    function Plug( $name, $target = 'class' ) {
        $config = Bootstrap::cache( 'thread', "plug:{$name}", function()use( $name ) {
            $name =str_replace( '.', '/', $name );
            $plugFolder = "support/System/plug/{$name}/";
            if ( !is_dir( $plugFolder ) ) { $plugFolder = "plug/{$name}/"; }
            if ( !is_dir( $plugFolder ) ) { return null; }
            $plugMain = "{$plugFolder}/main.php";
            if ( !file_exists( $plugMain ) ) { return null; }
            $plugClass = require $plugMain;
            if ( !is_object( $plugClass ) ) { return null; }
            if ( $plugClass->path === true ) { $plugClass->path = $plugFolder; }
            if ( method_exists( $plugClass, 'init' ) && !(new \ReflectionMethod( $plugClass, 'init'))->isPrivate() ) {
                $plugClass->init();
            }
            return [
                'folder' => $plugFolder,
                'class' => $plugClass
            ];
        });
        if ( !is_array( $config ) ) { return null; }
        switch ( $target ) {
            case 'class':
                return $config['class'];
                break;
            case 'config':
                return $config['class']->plug;
                break;
            case 'folder':
                return $config['folder'];
                break;
            default:
                return null;
                break;
        }
    }
    /**
     * 访问接口控制器
     * - 用于访问接口控制器
     * - @param string|array $class 控制器方法
     * - @param mixed ...$parameter 传递参数
     * - @return mixed 控制器返回值
     */
    function Controller( $class, ...$parameter ) { return Tool::runMethod( 'Controller', $class, ...$parameter ); }
    /**
     * 访问任务控制器
     * - 用于访问务控制器
     * - @param string $class 控制器方法
     * - @param mixed ...$parameter 控制器参数
     * - @return mixed 控制器返回值
     */
    function Task( $class, ...$parameter ) { return Tool::runMethod( 'Task', $class, ...$parameter ); }
    /**
     * 访问视图
     * - 用于访问视图
     * - @param string $view 视图名称
     * - @param array $parameter 视图参数
     * - @param bool $cache 是否缓存
     * - @return string 视图内容
     */
    function View( $view, $parameter = [], $cache = true ) {
        $view = str_replace( '.', '/', $view );
        if ( is_string( config( 'drive.view' ) ) ) {
            return Plug( config( 'drive.view' ) )->show( $view, $parameter, $cache );
        }else {
            $file = __file( "resource/view/{$view}.view.php" );
            if ( file_exists( $file ) ) {
                ob_start();
                    extract( $parameter );
                    require $file;
                return ob_get_clean();
            }
            $file = __file( "resource/view/{$view}.view.html" );
            if ( file_exists( $file ) ) {
                return file_get_contents( $file );
            }
            return null;
        }
    }
    /**
     * 链接跳转方式
     * - 用于实现网页链接跳转
     * - @param string $url 跳转地址
     * - @return string 跳转代码
     */
    function ToUrl( $url ) {
        return "<script type=\"text/javascript\">window.location.href='{$url}';</script>";
    }
    /**
     * 访问文件
     * - 用于访问文件， 可传入文件链接或文件路径
     * - @param string $file 文件路径
     * - @return File|null 文件对象
     */
    function ToFile( $file ) {
        if ( !is_string( $file ) ) { return null; }
        if ( !file_exists( $file ) && startsWith( $file, '/storage' ) ) {
            $file = explode( '/', $file );
            $file = "storage/media/{$file[2]}/".str_replace( '_', '.', $file[3] );
        }
        $file = new File( $file );
        if ( !is_string( $file->id ) ) { return null; }
        return $file;
    }
    /**
     * 访问存储器
     * - 用于访问存储器
     * - @param string $name 存储器名称
     * - @return Storage|null 存储器对象
     */
    function ToStorage( $name ) {
        if ( !is_string( $name ) ) { return null; }
        $storage = config( "storage.{$name}" );
        if ( !is_array( $storage ) || empty( $storage['path'] ) ) { return null; }
        return new Storage( $name );
    }