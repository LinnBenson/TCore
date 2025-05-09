<?php
    /**
     * 获取系统文件路径
     * - 优先查询系统文件，如果没有，则使用传入的路径
     * - @param string $dir 传入的目录路径
     * - @return string 文件路径
     */
    function __file( $dir ) {
        return !file_exists( $dir ) && file_exists( "support/System/{$dir}" ) ? "support/System/{$dir}" : $dir;
    }
    /**
     * 删除目录
     * - 删除一个指定目录及其所有子目录和文件
     * - @param string $dir 目录路径
     * - @return bool 删除结果
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
     * 路径文件夹保护
     * - 检查传入的路径是否存在，如果不存在则创建
     * - @param string $dir 传入的路径
     * - @return string|false 创建成功的路径或 false
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
     * 检查是否为 JSON 格式
     * - 检查传入的值是否为有效的 JSON 格式
     * - @param mixed $val 传入的值
     * - @return bool 判断结果
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
     * - 检查传入的值是否为有效的 UUID 格式
     * - @param mixed $val 传入的值
     * - @return bool 判断结果
     */
    function is_uuid( $val ) {
        if ( !is_string( $val ) ) { return false; }
        $pattern = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/';
        return preg_match( $pattern, $val ) === 1;
    }
    /**
     * 生成 UUID
     * - 生成一个随机的 UUID 字符串
     * - @return string UUID
     */
    function uuid() {
        $data = random_bytes( 16 );
        $data[6] = chr( ord($data[6]) & 0x0f | 0x40 );
        $data[8] = chr( ord($data[8]) & 0x3f | 0x80 );
        return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );
    }
    /**
     * 调试参数
     * - 打印传入的参数，并在最后选择性退出
     * - @param mixed $val 传入的值
     * - @param boolean $exit 是否退出，默认为 true
     * - @return void
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