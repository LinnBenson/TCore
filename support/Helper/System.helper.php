<?php

use App\Bootstrap\MainProcess;
use Illuminate\Support\Str;
use Blocktrail\CryptoJSAES\CryptoJSAES;
use Support\Handler\View;
use Support\Helper\Tool;

    /**
     * 获取环境变量
     * - env( $key:string(键名), $default:any(默认值) )
     * - return any(对应值)
     */
    function env( $key, $default = null ) {
        $value = isset( $_ENV[$key] ) && $_ENV[$key] !== '' ? $_ENV[$key] : $default;
        if ( $value === 'true' ) { return true; }
        if ( $value === 'false' ) { return false; }
        if ( $value === 'null' ) { return null; }
        return $value;
    }
    /**
     * 引用全部文件
     * - import( $file:string|array(文件路径或路径数组) )
     * - return boolean(引用结果)
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
        return false;
    }
    /**
     * 查询配置信息
     * - config( $key:string(键名), $default:any(默认值) )
     * - return any(对应值)
     */
    function config( $key, $default = null ) {
        // 覆盖值查询
        $cover = MainProcess::ConfigCover( $key );
        if ( $cover !== null ) { return $cover; }
        // 解构键名
        $keys = explode( '.', $key );
        // 是否为初次加载
        if ( !isset( Bootstrap::$cache['config'][$keys[0]] ) ) {
            Bootstrap::$cache['config'][$keys[0]] = import( "config/{$keys[0]}.config.php" );
        }
        // 查询配置
        $value = Bootstrap::$cache['config'][$keys[0]];
        if ( count( $keys ) === 1 ) { return $value; }
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
     * 全局语言包
     * - __( $text:string(语言包键名), $replace|[]:array(替换内容), $lang|false:string(语言包目录) )
     * - return string(语言包内容)
     */
    function __( $text, $replace = [], $lang = false ) {
        // 检查是否为双重语言调用
        $check = explode( ':', $text );
        if ( count( $check ) === 2 && empty( $replace ) ) { return __( $check[0], [], $lang ).__( $check[1], [], $lang ); }
        // 语言包目录
        $langDir = 'resource/lang';
        if ( empty( $lang ) || !is_dir( "{$langDir}/{$lang}" ) ) { $lang = config( 'app.lang' ); }
        // 解构键名
        $keys = explode( '.', $text );
        // 检查语言包是否加载
        if ( !isset( Bootstrap::$cache['lang'][$lang] ) || !isset( Bootstrap::$cache['lang'][$lang][$keys[0]] ) ) {
            $langFile = __file( "{$langDir}/{$lang}/{$keys[0]}.lang.php" );
            if ( !file_exists( $langFile ) ) { return $text; }
            Bootstrap::$cache['lang'][$lang][$keys[0]] = import( $langFile );
        }
        // 查询语言包
        $value = Bootstrap::$cache['lang'][$lang][$keys[0]];
        array_shift( $keys ); foreach ( $keys as $k ) {
            if ( isset( $value[$k] ) ) {
                $value = $value[$k];
            }else {
                return $text;
            }
        }
        if ( !is_string( $value ) ) { return $text; }
        if ( empty( $replace ) ) { return $value; }
        // 替换占位符
        foreach ( $replace as $k => $v ) { $value = str_replace( "{{".$k."}}", $v, $value ); }
        return $value;
    }
    /**
     * 获取格式化时间
     * - toDate( $time:string|integer(待转换的时间) )
     * return string 格式化时间
     */
    function toDate( $time = false ) {
        if ( !empty( $time ) && is_numeric( $time ) ) { return date( 'Y-m-d H:i:s', $time ); }
        if ( is_string( $time ) ) { return \Carbon\Carbon::parse( $time )->timezone( config('app.timezone') ); }
        return date( 'Y-m-d H:i:s' );
    };
    /**
     * Controller 方法请求
     * - controller( $class:array|string(请求对象), ...$parameter:any(传递数据) )
     * - return any(请求结果)
     */
    function controller( $class, ...$parameter ) {
        try {
            return Tool::runMethod( 'Controller', $class, ...$parameter );
        } catch ( \Exception $th ) {
            Log::to( 'Bootstrap' )->error([ "Controller Run", $th ]);
            return null;
        }
    }
    /**
     * Task 方法请求
     * - task( $class:array|string(请求对象), ...$parameter:any(传递数据) )
     * - return any(请求结果)
     */
    function task( $class, ...$parameter ) {
        try {
            return Tool::runMethod( 'Task', $class, ...$parameter );
        } catch ( \Exception $th ) {
            Log::to( 'Bootstrap' )->error([ "Task Run", $th ]);
            return null;
        }
    }
    /**
     * 视图渲染器
     * - view( $file:string(视图文件), $share|[]:array(共享参数), $request|null:object(Request), $render|true:boolean(是否渲染) )
     * - return string 视图代码
     */
    function view( $file, $share = [], $request = null, $render = true ) {
        $file = str_replace( '.', '/', $file );
        $fileCheck = __file( config( 'view.path' )."{$file}.view.html" );
        if (  !file_exists( $fileCheck ) ) { return null; }
        $view = new View( $file, $share, $request, $render );
        return $view->show();
    }
    /**
     * 哈希内容
     * - h( $content:string(待哈希的内容) )
     * - return string(哈希值)
     */
    function h( $content ) {
        if ( !is_string( $content ) ) { return null; }
        return hash( 'sha256', $content.env( 'APP_KEY', 'DefaultKey' ) );
    }
    /**
     * 加密内容
     * - encrypt( $content:string(待加密的内容), $key|DefaultKey:string(加密密钥) )
     * - return string(加密后的内容)
     */
    function encrypt( $content, $key = 'DefaultKey' ) {
        if ( !is_string( $content ) || !is_string( $key ) ) { return null; }
        try {
            return CryptoJsAes::encrypt( $content, $key === 'DefaultKey' ? env( 'APP_KEY', 'DefaultKey' ) : $key );
        } catch ( \Exception $e ) {
            Log::to( 'Bootstrap' )->error([ "Encrypt Data", $e ]);
        }
        return null;
    }
    /**
     * 解密内容
     * - decrypt( $content:string(待解密的内容), $key|DefaultKey:string(解密密钥) )
     * - return string(解密后的内容)
     */
    function decrypt( $content, $key = 'DefaultKey' ) {
        if ( !is_string( $content ) || !is_string( $key ) ) { return null; }
        try {
            return CryptoJsAes::decrypt( $content, $key === 'DefaultKey' ? env( 'APP_KEY', 'DefaultKey' ) : $key );
        } catch ( \Exception $e ) {
            Log::to( 'Bootstrap' )->error([ "Decrypt Data", $e ]);
        }
        return null;
    }
    // 判断字符串是否以指定内容开始
    function startsWith( $string, $val ) { return Str::startsWith( $string, $val ); }
    // 判断字符串是否以指定内容结尾
    function endsWith( $string, $val ) { return Str::endsWith( $string, $val ); }
    // 截断字符 [ Chinese ]
    function limitCn( $value, $length, $end = '' ) { return Str::limit( $value, $length, $end ); }
    // 截断字符 [ English ]
    function limitEn( $value, $length, $end = '' ) { return Str::words( $value, $length, $end ); }