<?php
namespace support\method;

/**
 * ---
 * 工具函数
 * ---
 */

    class tool {
        /**
         * 配置文件覆盖
         * - $file string 接受一个文件路径
         * - $arr array 接受一个数组
         * ---
         * return boolean
         */
        public static function coverConfig( $file, $arr ) {
            // 判断配置类型
            if ( self::endsWith( $file, '.php' ) ) {
                $type = 'php';
            } elseif ( self::endsWith( $file, '.json' ) ) {
                $type = 'json';
            } else {
                return false;
            }
            // 生成新的配置信息
            if ( $type === 'php' ) {
                $newConfig = array_merge( $arr );
                $content = '<?php'.PHP_EOL.'return '.var_export( $newConfig, true ).';'.PHP_EOL;
                $content = str_replace( '  ', "\t", $content);
                $content = preg_replace( '/=>\s*array \(/', '=> array (', trim( $content ) );
            }else {
                $content = json_encode( $arr, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES );
            }
            // 写入配置信息
            $result = file_put_contents( $file, $content );
            // 刷新文件
            if ( $type === 'php' ) {
                opcache_invalidate( $file, true );
            }else {
                flush();
            }
            return $result !== false ? true : false;
        }
        /**
         * 配置文件编辑
         * - $file string 接受一个文件路径
         * - $key string 要修改的键
         * - $value string 要修改的值
         * ---
         * return boolean
         */
        public static function editConfig( $file, $key, $value ) {
            // 判断配置类型
            if ( self::endsWith( $file, '.php' ) ) {
                $type = 'php';
            } elseif ( self::endsWith( $file, '.json' ) ) {
                $type = 'json';
            } else {
                return false;
            }
            // 检查文件是否存在
            if ( !file_exists( $file ) ) {
                return false;
            }
            // 读取配置
            if ( $type === 'php' ) {
                $arr = require $file;
            }else {
                $arr = json_decode( file_get_contents( $file ), true );
            }
            // 修改配置
            $arr[$key] = $value;
            // 生成新的配置信息
            if ( $type === 'php' ) {
                $newConfig = array_merge( $arr );
                $content = '<?php'.PHP_EOL.'return '.var_export( $newConfig, true ).';'.PHP_EOL;
                $content = str_replace( '  ', "\t", $content);
                $content = preg_replace( '/=>\s*array \(/', '=> array (', trim( $content ) );
            }else {
                $content = json_encode( $arr, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES );
            }
            // 写入配置信息
            $result = file_put_contents( $file, $content );
            // 刷新文件
            if ( $type === 'php' ) {
                opcache_invalidate( $file, true );
            }else {
                flush();
            }
            return $result !== false ? true : false;
        }
        /**
         * 匹配结尾字符
         * - $haystack string 匹配对象
         * - $needle string 匹配内容
         * ---
         * return boolean
         */
        public static function endsWith( $haystack, $needle ) {
            return substr( $haystack, -strlen( $needle ) ) === $needle;
        }
        /**
         * 随机数生成器
         * - $length num 生成长度
         * - $type string(all|number|letter) 随机生成类型
         * ---
         * return false/string
         */
        public static function rand( $length, $type = 'all' ) {
            if ( $type === 'all' ) {
                $data = array( 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
                'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's',
                't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D',
                'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O',
                'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z',
                '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
            }else if ( $type === 'number' ) {
                $data = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
            }else if ( $type === 'letter' ) {
                $data = array( 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
                'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's',
                't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D',
                'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O',
                'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z' );
            }else {
                return false;
            }
            $dataLength = count( $data ) - 1;
            $result = '';
            for( $i = 0; $i < $length; $i++ ) {
                $result .= $data[rand( 0, $dataLength )];
            }
            return $result;
        }
        /**
         * 删除指定目录
         * - $path string 需要删除的路径
         * ---
         * return boolead
         */
        public static function delDir( $path ) {
            try {
                if ( is_dir( $path ) ) {
                    $files = scandir( $path );
                    foreach ( $files as $file ) {
                        if ( $file != "." && $file != ".." ) {
                            $currentPath = $path . DIRECTORY_SEPARATOR . $file;
                            if ( is_dir($currentPath ) ) {
                                tool::delDir( $currentPath );
                            } else {
                                unlink( $currentPath );
                            }
                        }
                    }
                    rmdir( $path );
                    return true;
                }else {
                    return false;
                }
            }catch ( Exception $e ) {
                core::log( $e, 'core' );
                return false;
            }
        }
        /**
         * 遍历目录
         * - $dir string 目标目录
         * - $type string(all|dir|file) 输出类型
         * ---
         * return false/array
         */
        public static function getDir( $dir, $type = 'all' ) {
            if ( !is_dir( $dir ) ) { return false; }
            $files = scandir( $dir );
            $result = array( 'dir' => [], 'file' => [] );
            foreach ( $files as $file ) {
                if ( $file == '.' || $file == '..' ) { continue; }
                if ( is_dir( "{$dir}/{$file}" ) ) {
                    $result['dir'][] = $file;
                }else {
                    $result['file'][] = $file;
                }
            }
            if ( $type === 'dir' ) { return $result['dir']; }
            if ( $type === 'file' ) { return $result['file']; }
            return $result;
        }
    }