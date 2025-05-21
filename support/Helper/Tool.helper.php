<?php

namespace Support\Helper;

    class Tool {
        /**
         * 方法请求
         * - 用于请求控制器中的方法
         * - @param string $type 控制器类型
         * - @param string|array $class 控制器类名
         * - @param mixed ...$parameter 传递参数
         * - @return mixed 方法返回值
         */
        public static function runMethod( $type, $class, ...$parameter ) {
            if ( !is_string( $class ) && !is_array( $class ) ) { return null; }
            $execute = is_array( $class ) ? [ $class[0], $class[1] ] : explode( '@', $class );
            $controllerName = $execute[0];
            if ( strpos( $controllerName, '\\' ) === false ) {
                $controllerName = str_replace( '.', '\\', $controllerName );
                $controllerName = "App\\{$type}\\{$controllerName}";
            }
            $methodName = $execute[1];
            if ( !empty( $methodName ) ) {
                $controller = new $controllerName; // 构造控制器
                if ( !method_exists( $controller, $methodName ) || (new \ReflectionMethod( $controller, $methodName ))->isPrivate() ) {
                    return null;
                }
                if ( method_exists( $controller, '__' ) && !(new \ReflectionMethod( $controller, '__'))->isPrivate() ) {
                    $check =  $controller->__( ...$parameter );
                    if ( $check !== null ) { return $check; }
                }
                return $controller->{$methodName}( ...$parameter );
            }
            return null;
        }
        /**
         * 将字符串转换为数组
         * - 用于将 a:1|b:2 字符串转换为数组
         * - @param string $str 字符串
         * - @return array 数组
         */
        public static function toArray( $str ) {
            $result = [];
            foreach ( explode( '|', $str ) as $item ) {
                [ $key, $value ] = explode( ':', $item, 2 );
                // 可选：转换布尔/数字
                if ( empty( $value ) && $value !== 0 && $value !== '0' ) { $value = true; }
                if ( $value === 'true' ) {
                    $value = true;
                }elseif ( $value === 'false' ) {
                    $value = false;
                }elseif ( $value === 'null' ) {
                    $value = null;
                }elseif ( is_numeric( $value ) ) {
                    $value = $value + 0;
                }
                $result[$key] = $value;
            }
            return $result;
        }
        /**
         * 加载配置
         * - 用于插件加载自身配置信息
         * - @param object $obj 操作对象
         * - @param string $key 键
         * - @param string $file 访问文件
         * - @return array 配置数组
         */
        public static function plugLoadConfig( $obj, $key, $file ) {
            if ( !empty( $obj->$key ) && is_array( $obj->$key ) ) { return $obj->$key; }
            $obj->$key = [];
            $file1 = $obj->path.$file;
            if ( file_exists( $file1 ) ) { $obj->$key = array_merge( $obj->$key, require $file1 ); }
            $files = explode( '/', $file );
            $file2 = "config/{$obj->plug['name']}/{$files[count( $files ) - 1]}";
            if ( file_exists( $file2 ) ) { $obj->$key = array_merge( $obj->$key, require $file2 ); }
            return $obj->$key;
        }
        /**
         * 随机数生成器
         * - 用于生成一段随机数
         * - @param int $length 生成长度
         * - @param [all|number|letter] $type 随机生成类型 [ all|number|letter ]
         * - @return string 随机内容
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
         * 配置文件覆盖
         * - 用于覆写一个 PHP/JSON 配置文件
         * - @param string $file 文件路径
         * - @param array $arr 配置数组
         * - @return boolean 覆写结果
         */
        public static function coverConfig( $file, $arr ) {
            // 判断配置类型
            if ( endsWith( $file, '.php' ) ) {
                $type = 'php';
            } elseif ( endsWith( $file, '.json' ) ) {
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
    }