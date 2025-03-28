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
        /**
         * 方法请求
         * - Tool::runMethod( $type:string(请求类型), $class:array|string(请求对象), ...$parameter:any(传递数据) )
         * - return any(请求结果)
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
    }