<?php
    /**
     * 获取系统文件
     * - __file( $dir:string(目录路径) )
     * - return string(系统目录路径)
     */
    function __file( $dir ) {
        if ( !file_exists( $dir ) && file_exists( "support/System/{$dir}" ) ) {
            $dir = "support/System/{$dir}";
        }
        return $dir;
    }
    /**
     * 删除目录
     * - deleteDir( $dir:string(目录路径) )
     * - return boolean
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
     * 判断字符串是否为 JSON
     * - is_json( $val:any(待检查的内容) )
     * - return boolean
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
     * 判断字符串是否为 UUID
     * - is_uuid( $val:any(待检查的内容) )
     * - return boolean
     */
    function is_uuid( $val ) {
        if ( !is_string( $val ) ) { return false; }
        $pattern = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/';
        return preg_match( $pattern, $val ) === 1;
    }
    /**
     * 生成 UUID
     * - uuid()
     * - return string(UUID)
     */
    function uuid() {
        $data = random_bytes( 16 );
        $data[6] = chr( ord($data[6]) & 0x0f | 0x40 );
        $data[8] = chr( ord($data[8]) & 0x3f | 0x80 );
        return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );
    }
    /**
     * 转换为字符串
     * - toString( $text:any(待转换的内容) )
     * - return string(转换后的内容)
     */
    function toString( $text ) {
        if ( is_string( $text ) ) { return $text; }
        if ( is_numeric( $text ) ) { return (string) $text; }
        if ( is_bool( $text ) ) { return $text ? 'true' : 'false'; }
        if ( is_array( $text ) ) { return implode( "\n", $text ); }
        if ( is_object( $text ) ) { return '[Object Data]'; }
        if ( is_callable( $text ) ) { return '[Function Data]'; }
        return '[Null Data]';
    }
    /**
     * 调试参数
     * - dd( $val:any(待调试的内容), $exit|true:boolean(是否立即退出程序) )
     * - return null
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