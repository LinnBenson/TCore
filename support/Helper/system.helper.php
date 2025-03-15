<?php

use Illuminate\Support\Str;

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
     * 获取格式化时间
     * - toDate( $time:string|integer(待转换的时间) )
     * return string 格式化时间
     */
    function toDate( $time = false ) {
        if ( !empty( $time ) && is_numeric( $time ) ) { return date( 'Y-m-d H:i:s', $time ); }
        if ( is_string( $time ) ) { return \Carbon\Carbon::parse( $time )->timezone( config('app.timezone') ); }
        return date( 'Y-m-d H:i:s' );
    };
    // 判断字符串是否以指定内容开始
    function startsWith( $string, $val ) { return Str::startsWith( $string, $val ); }
    // 判断字符串是否以指定内容结尾
    function endsWith( $string, $val ) { return Str::endsWith( $string, $val ); }
    // 截断字符 [ Chinese ]
    function limitCn( $value, $length, $end = '' ) { return Str::limit( $value, $length, $end ); }
    // 截断字符 [ English ]
    function limitEn( $value, $length, $end = '' ) { return Str::words( $value, $length, $end ); }