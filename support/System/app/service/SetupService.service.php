<?php
    namespace App\Service;

    /**
     * 系统设置服务
     */
    class SetupService {
        /**
         * 重置密钥
         */
        public static function resetKey() {
            $env = file_get_contents( '.env' );
            if ( strpos( $env, 'APP_KEY=' ) === false ) { return false; }
            $newKey = strtoupper( h( uuid() ) );
            $env = str_replace( 'APP_KEY="'.env( 'APP_KEY' ).'"', 'APP_KEY="'.$newKey.'"', $env );
            $state = !empty( file_put_contents( '.env', $env ) ) ? true : false;
            return $state;
        }
        /**
         * 清理系统缓存
         */
        public static function clearCache() {
            $cache = [
                'storage/cache/',
                'storage/log/'
            ];
            // 清理缓存目录
            foreach ( $cache as $dir ) {
                if ( is_dir( $dir ) && empty( deleteDir( $dir ) ) ) { return false; }
            }
            // 清理过期文件
            $storages = config( 'storage', [] );
            foreach ( $storages as $storage ) {
                if ( is_numeric( $storage['delete'] ) ) {
                    $files = glob( "{$storage['path']}*.*" );
                    foreach ( $files as $file ) {
                        if ( is_file( $file ) && ( time() - filemtime( $file ) ) > $storage['delete'] ) {
                            unlink( $file );
                        }
                    }
                }
            }
            return true;
        }
        /**
         * 修改 Debug 模式
         */
        public static function editDebug() {
            $env = file_get_contents( '.env' );
            if ( strpos( $env, 'APP_DEBUG=' ) === false ) { return false; }
            $oldValue = config( 'app.debug' ) ? 'true' : 'false';
            $newValue = config( 'app.debug' ) ? 'false' : 'true';
            $env = str_replace( 'APP_DEBUG='.$oldValue, 'APP_DEBUG='.$newValue, $env );
            $state = !empty( file_put_contents( '.env', $env ) ) ? true : false;
            return $state;
        }
        /**
         * 创建模板
         */
        public static function createTemplate( $type, $name ) {
            $types = [
                'controller' => [
                    'file' => __file( 'storage/template/controller.php' ),
                    'path' => 'app/controller/{{name}}.controller.php'
                ],
                'service' => [
                    'file' => __file( 'storage/template/service.php' ),
                    'path' => 'app/service/{{name}}.service.php'
                ],
                'model' => [
                    'file' => __file( 'storage/template/model.php' ),
                    'path' => 'app/Model/{{name}}.model.php'
                ]
            ];
            if ( empty( $types[$type] ) ) { return false; }
            $config = $types[$type];
            $template = file_get_contents( $config['file'] );
            $template = str_replace( '{{name}}', $name, $template );
            if ( $type === 'model' ) {
                $template = str_replace( '{{table}}', strtolower( preg_replace( '/([A-Z])/', '_$1', lcfirst( $name ) ) ), $template );
            }
            $config['path'] = inFolder( str_replace( '{{name}}', $name, $config['path'] ) );
            return !empty( file_put_contents( $config['path'], $template ) ) ? true : false;
        }
        /**
         * 获取所有可用插件
         */
        public static function allPlug() {
            $path1 = 'plug/';
            $path2 = 'support/System/plug/';
            $plugs = [];
            foreach ( glob( "{$path1}*" ) as $plug ) {
                if ( is_dir( $plug ) && file_exists( $plug.'/index.php' ) ) {
                    $test = Plug( str_replace( $path1, '', $plug ) );
                    if ( is_object( $test ) ) { $plugs[] = $test->name; }
                }
            }
            foreach ( glob( "{$path2}*" ) as $plug ) {
                if ( is_dir( $plug ) && file_exists( $plug.'/index.php' ) ) {
                    $test = Plug( str_replace( $path2, '', $plug ) );
                    if ( is_object( $test ) ) { $plugs[] = $test->name; }
                }
            }
            return array_unique( $plugs );
        }
        /**
         * 获取插件权限
         */
        public static function plugPermissions( $plug ) {
            $config = config( 'permissions' );
            $permissions = [];
            foreach ( $plug->permissions as $permissionName => $permissionMethod ) {
                if ( !is_callable( $permissionMethod ) ) { continue; }
                $permissions[$permissionName] = in_array( $plug->name, $config[$permissionName] ) || $permissionName === 'PLUGIN_COMMAND_MENU';
            }
            return $permissions;
        }
        /**
         * 获取所有模型
         */
        public static function allModel() {
            $models = [];
            $path1 = 'app/model/';
            $path2 = 'support/System/app/model/';
            foreach ( glob( "{$path1}*.model.php" ) as $file ) {
                $name = str_replace( '.model.php', '', basename( $file ) );
                $class = "App\Model\\{$name}";
                $name = explode( '|', $class::$name );
                $models[] = [
                    'class' => $class,
                    'table' => $name[0],
                    'name' => $name[1] ?? $name[0],
                ];
            }
            foreach ( glob( "{$path2}*.model.php" ) as $file ) {
                $name = str_replace( '.model.php', '', basename( $file ) );
                $class = "App\Model\\{$name}";
                $name = explode( '|', $class::$name );
                $models[] = [
                    'class' => $class,
                    'table' => $name[0],
                    'name' => $name[1] ?? $name[0],
                ];
            }
            return $models;
        }
    }