<?php
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
        if ( is_string( $file ) ) { return require $file; }
        if ( is_array( $file ) ) {
            $result = [];
            for ( $i = 0; $i < 9999; $i++ ) {
                if ( empty( $file[$i] ) ) { break; }
                $quote = require_once $file[$i];
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