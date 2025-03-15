<?php

namespace Support\Helper;

    class Tool {
        /**
         * 路径文件夹保护
         * - Tool::inFolder( $dir:string(文件夹路径) )
         * - return boolean(创建结果)
         */
        public static function inFolder( $dir ) {
            $dir = str_replace( '\\', '/', $dir );
            $dir = explode( '/', $dir );
            if ( count( $dir ) <= 1 ) { return false; }
            array_pop( $dir ); $current = '';
            foreach( $dir as $value ) {
                if ( $value === '' ) { continue; }
                $current .= $value.DIRECTORY_SEPARATOR;
                if ( !is_dir( $current ) ) { mkdir( $current, 0777, true ); }
            }
            return true;
        }
    }