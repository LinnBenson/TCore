<?php
namespace application\server;

use support\method\RE;
use support\method\tool;

    class serviceServer {
        /**
         * 获取所有服务项
         */
        public static function getAllService() {
            $list = config( 'service' );
            foreach( $list as $name => $info ) {
                $state = RE::getCache( "service_state_{$name}" );
                $list[$name]['state'] = is_numeric( $state ) ? intval( $state ) : 0;
                $list[$name]['file'] = getcwd()."/support/service/{$name}.service.php";
            }
            return $list;
        }
        /**
         * 创建服务项
         */
        public static function create( $data ) {
            if ( empty( $data ) || !is_array( $data ) ) { return false; }
            // 开始创建 - service.config.php
            $config = config( 'service' );
            if ( !empty( $config[$data['key']] ) ) { return false; }
            $config[$data['key']] = [
                'public' => $data['public'],
                'protocol' => $data['protocol'],
                'port' => $data['port'],
                'name' => $data['name'],
                'run' => $data['run'],
                'thread' => $data['thread']
            ];
            $create = tool::coverConfig( 'config/service.config.php', $config );
            if ( !$create ) { return false; }
            // 开始创建 - log
            $config = config( 'log' );
            $config["service_{$data['key']}"] = [
                'file' => "storage/log/service_{$data['key']}.log",
                'maxSize' => 3000000
            ];
            $create = tool::coverConfig( 'config/log.config.php', $config );
            if ( !$create ) { return false; }
            // 开始创建 - 路由文件
            $config = config( 'router' );
            $config["service_{$data['key']}"] = [ "router/service_{$data['key']}.router.php" ];
            $create = tool::coverConfig( 'config/router.config.php', $config );
            if ( !$create ) { return false; }
            $file = file_get_contents( 'storage/backup/service_router.template.txt' );
            $create = file_put_contents( "router/service_{$data['key']}.router.php", $file );
            if ( !$create ) { return false; }
            // 开始创建 - 入口文件
            $file = file_get_contents( 'storage/backup/service.template.txt' );
            $file = str_replace( "{{name}}", $data['key'], $file );
            $create = file_put_contents( "support/service/{$data['key']}.service.php", $file );
            return $create;
        }
        /**
         * 开始运行
         */
        public static function start( $name = false ) { return self::shell( 'start -d', $name ); }
        /**
         * 停止运行
         */
        public static function stop( $name = false ) { return self::shell( 'stop', $name ); }
        /**
         * 重启服务
         */
        public static function restart( $name = false ) { return self::shell( 'start -d', $name ); }
        /**
         * 调试服务
         */
        public static function debug( $name ) {
            $list = self::getAllService();
            if ( empty( $name ) || empty( $list[$name] ) ) { return false; }
            for ( $i=0; $i < 9999; $i++ ) {
                shell_exec( "php {$list[$name]['file']} stop" );
                echo shell_exec( "php {$list[$name]['file']} start -d" );
                echo "\n输入 1 停止调试，否则将重新执行 => ";
                $input = trim( fgets( STDIN ) );
                if ( $input === '1' || $input === 1 ) { break; }
            }
            return true;
        }
        /**
         * 删除服务
         */
        public static function delete( $name ) {
            $list = self::getAllService();
            if ( empty( $name ) || empty( $list[$name] ) ) { return false; }
            shell_exec( "php {$list[$name]['file']} stop" );
            // 开始删除 - 入口文件
            $file = "support/service/{$name}.service.php";
            if ( file_exists( $file ) ) {
                $check = unlink( $file ); if ( !$check ) { return false; }
            }
            $config = config( 'service' ); unset( $config[$name] );
            $check = tool::coverConfig( 'config/service.config.php', $config );
            if ( !$check ) { return false; }
            // 开始删除 - 日志文件
            $config = config( 'log' );
            if ( file_exists( $config["service_{$name}"]['file'] ) ) {
                $check = unlink( $config["service_{$name}"]['file'] ); if ( !$check ) { return false; }
            }
            unset( $config["service_{$name}"] );
            $check = tool::coverConfig( 'config/log.config.php', $config );
            if ( !$check ) { return false; }
            // 开始删除 - 路由配置
            $config = config( 'router' );
            if ( !empty( $config["service_{$name}"] ) ) {
                foreach( $config["service_{$name}"] as $file ) {
                    if ( file_exists( $file ) ) {
                        $check = unlink( $file ); if ( !$check ) { return false; }
                    }
                }
            }
            unset( $config["service_{$name}"] );
            $check = tool::coverConfig( 'config/router.config.php', $config );
            if ( !$check ) { return false; }
            // 开始删除 - 主配置
            $config = config( 'service' );
            unset( $config[$name] );
            $check = tool::coverConfig( 'config/service.config.php', $config );
            if ( !$check ) { return false; }
            // 删除完成
            return true;
        }
        /**
         * 执行命令
         */
        private static function shell( $type, $name ) {
            $list = self::getAllService();
            if ( !empty( $name ) ) {
                if ( empty( $list[$name] ) ) { return false; }
                echo shell_exec( "php {$list[$name]['file']} stop" );
                echo shell_exec( "php {$list[$name]['file']} {$type}" );
                return true;
            }
            $async = true;
            foreach( $list as $item ) {
                echo shell_exec( "php {$item['file']} stop" );
                echo shell_exec( "php {$item['file']} {$type}" );
                if ( $async ) { sleep( 2 ); $async = false; }
            }
            return true;
        }
    }