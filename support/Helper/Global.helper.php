<?php
    /**
     * 检查是否为 JSON 格式
     * - $val: 传入的值[mixed]
     * - return bool 返回是否为 JSON 格式
     */
    function is_json( $val ) {
        if ( !is_string( $val ) || $val === '' ) { return false; }
        $jsonData = json_decode( $val );
        if ( json_last_error() === JSON_ERROR_NONE && ( is_object( $jsonData ) || is_array( $jsonData ) ) ) {
            return true;
        }
        return false;
    }
    /**
     * 检查是否为 UUID 格式
     * - $val: 传入的值[mixed]
     * - return bool 返回是否为 UUID 格式
     */
    function is_uuid( $val ) {
        if ( !is_string( $val ) ) { return false; }
        $pattern = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/';
        return preg_match( $pattern, $val ) === 1;
    }
    /**
     * 生成 UUID
     * - return string 返回生成的 UUID 字符串
     */
    function uuid() {
        $data = random_bytes( 16 );
        $data[6] = chr( ord($data[6]) & 0x0f | 0x40 );
        $data[8] = chr( ord($data[8]) & 0x3f | 0x80 );
        return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );
    }
    /**
     * 拼接长文本
     * - $text: 传入的文本[mixed]
     * - return string 返回拼接后的文本
     */
    function toString( $text ) {
        if ( !is_array( $text ) && !is_string( $text ) ) { return ''; }
        if ( is_string( $text ) ) { return $text; }
        return implode( "\n", $text );
    }
    /**
     * 调试参数
     * - $val: 传入的值[mixed]
     * - $exit: 是否退出程序[bool]|true
     * - return void
     */
    function dd( $val, $exit = true ) {
        $echo = $val;
        if ( is_json( $val ) ) {
            $echo = "Json ".print_r( json_decode( $val ), true);
        }else if ( is_uuid( $val ) ) {
            $echo = "UUID( '{$val}' )";
        }else if ( is_string( $val ) ) {
            $echo = "String( '{$val}' )";
        }else if ( is_numeric( $val ) ) {
            $echo = "Number( '{$val}' )";
        }else if ( is_bool( $val ) ) {
            $val = $val ? 'true' : 'false'; $echo = "Boolean( '{$val}' )";
        }else if ( $val === null ) {
            $echo = "Null( '' )";
        }
        print_r( $echo ); echo PHP_EOL.PHP_EOL; if ( $exit ) { exit(); }
    }
    /**
     * 路径文件夹保护
     * - $dir: 传入的路径[string]
     * - return string|false 返回处理后的目录路径或 false
     */
    function inFolder( $dir ) {
        $check = preg_match( '/[^\/]+\.\w+$/', $dir ) ? dirname( $dir ) : $dir;
        if ( is_dir( $check ) ) { return $dir; }
        $check = str_replace( '\\', '/', $check );
        $check = explode( '/', $check );
        if ( count( $check ) <= 1 ) { return false; }
        $current = '';
        foreach( $check as $value ) {
            if ( $value === '' ) { continue; }
            $current .= $value.DIRECTORY_SEPARATOR;
            if ( !is_dir( $current ) ) { mkdir( $current, 0777, true ); }
        }
        return $dir;
    }
    /**
     * 删除目录
     * - $dir: 传入的目录路径[string]
     * - return bool 返回是否成功删除目录
     */
    function deleteDir( $dir ) {
        if ( !is_dir( $dir ) ) { return false; }
        foreach( scandir( $dir ) as $file ) {
            if ( $file === '.' || $file === '..' ) { continue; }
            $path = "{$dir}/{$file}";
            is_dir( $path ) ? deleteDir( $path ) : unlink( $path );
        }
        return rmdir( $dir );
    }
    /**
     * 获取原名称
     * - $str: 传入的字符串[string]
     * - $symbol: 分隔符[string]|'_'
     * - return string 返回处理后的名称
     */
    function toName( $str, $symbol = '_' ) {
        $pattern = '/([a-z])([A-Z])/';
        $replacement = '$1' . $symbol . '$2';
        return strtolower( preg_replace( $pattern, $replacement, $str ) );
    }