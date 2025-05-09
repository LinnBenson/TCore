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
    }