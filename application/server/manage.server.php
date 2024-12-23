<?php
namespace application\server;

use core;
use support\method\tool;

    class manageServer {
        /**
         * 创建服务
         */
        public static function createServer( $name ) {
            // 创建配置文件
            $config = config( 'autoload' );
            $config['server']["application\server\\".$name."Server"] = "application/server/{$name}.server.php";
            $check = tool::coverConfig( 'config/autoload.config.php', $config );
            if ( !$check ) { return false; }
            // 创建服务文件
            $file = file_get_contents( 'storage/backup/server.template.txt' );
            $file = str_replace( "{{name}}", $name, $file );
            $check = file_put_contents( "application/server/{$name}.server.php", $file );
            return $check ? true : false;
        }
        /**
         * 删除服务
         */
        public static function deleteServer( $name ) {
            // 删除服务文件
            $file = "application/server/{$name}.server.php";
            if ( file_exists( $file ) ) {
                $check = unlink( $file ); if ( !$check ) { return false; }
            }
            // 删除配置
            $config = config( 'autoload' );
            unset( $config['server']["application\server\\".$name."Server"] );
            $check = tool::coverConfig( 'config/autoload.config.php', $config );
            return $check ? true : false;
        }
        /**
         * 重建模型
         */
        public static function resetModel( $name ) {
            $class = "application\model\\".$name;
            return $class::reset();
        }
        /**
         * 创建模型
         */
        public static function createModel( $name ) {
            // 创建配置文件
            $config = config( 'autoload' );
            $config['model']["application\model\\".$name] = "application/model/{$name}.model.php";
            $check = tool::coverConfig( 'config/autoload.config.php', $config );
            if ( !$check ) { return false; }
            // 创建服务文件
            $file = file_get_contents( 'storage/backup/model.template.txt' );
            $file = str_replace( "{{name}}", $name, $file );
            $check = file_put_contents( "application/model/{$name}.model.php", $file );
            return $check ? true : false;
        }
        /**
         * 删除模型
         */
        public static function deleteModel( $name ) {
            // 删除服务文件
            $file = "application/model/{$name}.model.php";
            if ( file_exists( $file ) ) {
                $check = unlink( $file ); if ( !$check ) { return false; }
            }
            // 删除表
            if ( core::$db::schema()->hasTable( $name ) ) { core::$db::schema()->drop( $name ); }
            // 删除配置
            $config = config( 'autoload' );
            unset( $config['model']["application\model\\".$name] );
            $check = tool::coverConfig( 'config/autoload.config.php', $config );
            return $check ? true : false;
        }
    }