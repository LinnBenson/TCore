<?php

use application\model\admin_menu;
use application\model\users;
use application\model\users_login;
use application\server\serviceServer;
use support\method\RE;
use support\method\tool;
use support\middleware\request;
use support\middleware\storage;

    class settingController {
        // 查看日志
        public function log() {
            $res = request::get([
                'name' => 'must:true'
            ]);
            $config = config( "log.{$res['name']}" );
            if ( empty( $config ) || !is_array( $config ) ) { return task::echo( 2, ['error.input'] ); }
            // 检查日志文件
            $log = '';
            if ( file_exists( $config['file'] ) ) {
                $file = fopen( $config['file'], 'r' );
                if ( !$file ) { return task::echo( 2, ['error.500'] ); }
                $fileSize = filesize( $config['file'] );
                $startPos = max( 0, $fileSize - 5000 );
                fseek( $file, $startPos );
                $log = fread( $file, 5000 );
                fclose($file);
            }
            return task::echo( 0, $log );
        }
        public function log_clear() {
            $res = request::get([
                'name' => 'must:true'
            ]);
            $config = config( "log.{$res['name']}" );
            if ( empty( $config ) || !is_array( $config ) ) { return task::echo( 2, ['error.input'] ); }
            $run = file_put_contents( $config['file'], '' );
            return task::result( 'clear', $run );
        }
        // 修改自身资料
        public function user() {
            $res = request::get([
                'avatar' => '',
                'username' => 'must:true,type:username,min:4,max:12',
                'email' => 'must:true,type:email',
                'phone' => 'type:phone',
                'nickname' => 'must:true',
                'password' => ''
            ]);
            $upload = [];
            // 参数处理
            if ( !empty( $res['username'] ) ) { $upload['username'] =  $res['username']; }
            if ( !empty( $res['email'] ) ) { $upload['email'] =  $res['email']; }
            $upload['phone'] =  $res['phone'];
            if ( !empty( $res['nickname'] ) ) { $upload['nickname'] =  $res['nickname']; }
            if ( !empty( $res['password'] ) ) { $upload['password'] = task::$user->setPassword( $res['password']  ); }
            // 修改用户
            $run = users::where( 'id', task::$user->uid )->update( $upload );
            if ( $run && !empty( $res['avatar'] ) ) {
                // 检查是否上传头像
                $storage = new storage( 'avatar' );
                $run = $storage->cacheSave( $res['avatar'], [
                    'name' => task::$user->uid.".png"
                ]);
            }
            // 如果修改了密码，则让所有管理员下线
            if ( !empty( $res['password'] ) ) {
                users_login::where( 'uid', task::$user->uid )->update([ 'enable' => 0 ]);
            }
            return task::result( 'edit', $run );
        }
        // 清除缓存配置文件
        public function del_cache() {
            $run = true;
            $file = "config/cache.config.php";
            if ( file_exists( $file ) ) {
                $run = unlink( $file );
            }
            return task::result( 'clear', $run );
        }
        // 修改配置信息
        public function edit_config() {
            $res = request::get([
                'name' => 'must:true',
                'record' => 'type:boolean',
                'debug' => 'type:boolean',
                'host' => 'must:true',
                'lang' => 'must:true',
                'version' => 'must:true',
                'timezone' => 'must:true',
                'fav' => 'must:true',
                'logo_b' => 'must:true',
                'logo_w' => 'must:true'
            ]);
            $cache = config( 'cache', [] );
            $checkEdit = function( $name, $config )use( $res ) {
                $val1 = $res[$name];
                $val2 = config( $config );
                if ( $val1 !== $val2 ) { return $val1; }
                return null;
            };
            // 检查修改项目
            $check = $checkEdit( 'name', 'app.name' );
            if ( $check !== null ) { $cache['app']['name'] = $check; }
            $check = $checkEdit( 'debug', 'app.debug' );
            if ( $check !== null ) { $cache['app']['debug'] = $check; }
            $check = $checkEdit( 'host', 'app.host' );
            if ( $check !== null ) { $cache['app']['host'] = $check; }
            $check = $checkEdit( 'lang', 'app.lang' );
            if ( $check !== null ) { $cache['app']['lang'] = $check; }
            $check = $checkEdit( 'timezone', 'app.timezone' );
            if ( $check !== null ) { $cache['app']['timezone'] = $check; }
            $check = $checkEdit( 'version', 'app.version' );
            if ( $check !== null ) { $cache['app']['version'] = $check; }
            $check = $checkEdit( 'record', 'app.record' );
            if ( $check !== null ) { $cache['app']['record'] = $check; }
            // 文件修改
            if ( preg_match( '/^\/storage\/cache/', $res['fav'] ) ) {
                $fav = "storage/media/cache/".ltrim( $res['fav'], '/storage/cache/' );
                if ( file_exists( $fav ) ) {
                    rename( $fav, "public/favicon.png" );
                }
            }
            if ( preg_match( '/^\/storage\/cache/', $res['logo_b'] ) ) {
                $icon = "storage/media/cache/".ltrim( $res['logo_b'], '/storage/cache/' );
                if ( file_exists( $icon ) ) {
                    rename( $icon, "public/library/icon/logo_b.png" );
                }
            }
            if ( preg_match( '/^\/storage\/cache/', $res['logo_w'] ) ) {
                $icon = "storage/media/cache/".ltrim( $res['logo_w'], '/storage/cache/' );
                if ( file_exists( $icon ) ) {
                    rename( $icon, "public/library/icon/logo_w.png" );
                }
            }
            // 开始注入配置缓存
            $run = tool::coverConfig( "config/cache.config.php", $cache );
            return task::result( 'save', $run );
        }
        // 修改主题
        public function edit_theme() {
            $res = request::get([
                'name' => 'must:true,type:username',
                'logo' => 'must:true',
                'fav' => 'must:true',
                'r0' => 'must:true,safe:false',
                'r1' => 'must:true,safe:false',
                'r2' => 'must:true,safe:false',
                'r2c' => 'must:true,safe:false',
                'r3' => 'must:true,safe:false',
                'r3c' => 'must:true,safe:false',
                'r4' => 'must:true,safe:false',
                'r4c' => 'must:true,safe:false',
                'r5' => 'must:true,safe:false',
                'r5c' => 'must:true,safe:false',
                'r6' => 'must:true,safe:false'
            ]);
            // 数据整理
            $new = []; $name = $res['name'];
            foreach( $res as $key => $value ) {
                $noVal = [ 'name', 'logo', 'fav' ];
                if ( !in_array( $key, $noVal ) ) {
                    $new["--{$key}"] = $value;
                }else {
                    $new[$key] = $value;
                }
            }
            unset( $new['name'] );
            // 开始写入数据
            $theme = config( 'theme' ); $theme[$name] = $new;
            $run = tool::coverConfig( 'config/theme.config.php', $theme );
            return task::result( 'save', $run );
        }
        // 删除主题
        public function del_theme() {
            $res = request::get([
                'name' => 'must:true,type:username'
            ]);
            // 检查主题
            $theme = config( 'theme' );
            if ( empty( $theme[$res['name']] ) ) { return task::echo( 2, ['error.null'] ); }
            // 开始写入数据
            unset( $theme[$res['name']] );
            $run = tool::coverConfig( 'config/theme.config.php', $theme );
            return task::result( 'delete', $run );
        }
        // 修改访问配置
        public function edit_access_config() {
            $res = request::get([
                'max_record' => 'must:true,type:number',
                'max_access_id' => 'must:true,type:number',
                'max_access_ip' => 'must:true,type:number',
                'max_access_uid' => 'must:true,type:number',
                'black_time' => 'must:true,type:number'
            ]);
            // 重写配置
            $config = config( 'access' );
            $config['maxRecord'] = $res['max_record'];
            $config['maxAccess_id'] = $res['max_access_id'];
            $config['maxAccess_ip'] = $res['max_access_ip'];
            $config['maxAccess_uid'] = $res['max_access_uid'];
            $config['blackTime'] = $res['black_time'];
            $run = tool::coverConfig( 'config/access.config.php', $config );
            ob_start();
                serviceServer::restart( 'async' );
            ob_get_clean();
            return task::result( 'save', $run );
        }
        // 添加黑名单
        public function add_black() {
            $res = request::get([
                'type' => 'must:true,type:username',
                'target' => 'must:true'
            ]);
            // 允许添加的黑名单
            $allow = [ 'id', 'ip', 'uid' ];
            if ( !in_array( $res['type'], $allow ) ) {
                return task::echo( 2, ['error.input'] );
            }
            // 开始添加黑名单
            $run = RE::setCache( "black_{$res['type']}_{$res['target']}", '1', config( 'access.blackTime' ) );
            return task::result( 'add', $run );
        }
        // 删除黑名单
        public function del_black() {
            $res = request::get([
                'type' => 'must:true,type:username',
                'target' => 'must:true'
            ]);
            // 允许操作的黑名单
            $allow = [ 'id', 'ip', 'uid' ];
            if ( !in_array( $res['type'], $allow ) ) {
                return task::echo( 2, ['error.input'] );
            }
            // 开始删除黑名单
            $run = RE::delete( 0, "cache_black_{$res['type']}_{$res['target']}" );
            return task::result( 'delete', $run );
        }
        // 获取黑名单列表
        public function black_list() {
            $list = RE::getAll( 0, "cache_black_" );
            return task::echo( 0, $list );
        }
        // Reame.md
        public function readme() {
            $data = file_get_contents( 'Readme.md' );
            return task::echo( 0, $data );
        }
        // 获取菜单
        public function get_menu() {
            $data = admin_menu::all()->toArray();
            foreach( $data as $key => $value ) {
                $data[$key]['created_at'] = toTime( $value['created_at'] );
                $data[$key]['updated_at'] = toTime( $value['updated_at'] );
            };
            return task::echo( 0, $data );
        }
    }