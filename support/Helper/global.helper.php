<?php
    /**
     * 获取系统文件
     * - GetSystemFile( $dir:目录路径 )
     * - return string(系统目录路径)
     */
    function GetSystemFile( $dir ) {
        if ( !file_exists( $dir ) && file_exists( "support/System/{$dir}" ) ) {
            $dir = "support/{$dir}";
        }
        return $dir;
    }
    /**
     * 删除目录
     * - deleteDir( $dir:目录路径 )
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
     * 访问系统目录中的文件
     */
    function _config( $file ) { return GetSystemFile( "config/{$file}" ); }