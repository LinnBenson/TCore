<?php

use application\server\serviceServer;
use support\method\tool;
use support\middleware\request;

    class serviceController {
        // 启动服务
        public function open() {
            // 请求来源检查
            $res = request::get([ 'name' => 'must:true,type:username' ]);
            $name = $res['name'];
            $config = config( 'service' );
            if ( $name !== 'all' && empty( $config[$name] ) ) { return task::echo( 2, ['error.input'] ); }
            // 开始执行
            if ( $name === 'all' ) { $name = false; }
            ob_start();
                $run = serviceServer::start( $name );
            ob_get_clean();
            return task::result( 'open', $run );
        }
        // 关闭服务
        public function close() {
            // 请求来源检查
            $res = request::get([ 'name' => 'must:true,type:username' ]);
            $name = $res['name'];
            $config = config( 'service' );
            if ( $name !== 'all' && empty( $config[$name] ) ) { return task::echo( 2, ['error.input'] ); }
            // 开始执行
            if ( $name === 'all' ) { $name = false; }
            ob_start();
                $run = serviceServer::stop( $name );
            ob_get_clean();
            return task::result( 'close', $run );
        }
        // 重启服务
        public function restart() {
            // 请求来源检查
            $res = request::get([ 'name' => 'must:true,type:username' ]);
            $name = $res['name'];
            $config = config( 'service' );
            if ( $name !== 'all' && empty( $config[$name] ) ) { return task::echo( 2, ['error.input'] ); }
            // 开始执行
            if ( $name === 'all' ) { $name = false; }
            ob_start();
                $run = serviceServer::restart( $name );
            ob_get_clean();
            return task::result( 'restart', $run );
        }
        // 删除服务
        public function delete() {
            // 请求来源检查
            $res = request::get([ 'name' => 'must:true,type:username' ]);
            $name = $res['name'];
            $config = config( 'service' );
            if ( empty( $config[$name] ) ) { return task::echo( 2, ['error.input'] ); }
            // 开始执行
            ob_start();
                $run = serviceServer::delete( $name );
            ob_get_clean();
            return task::result( 'delete', $run );
        }
        // 创建服务
        public function create() {
            $res = request::get([
                'key' => 'must:true,type:username',
                'name' => 'must:true',
                'protocol' => 'must:true,type:username',
                'port' => 'must:true,type:number,min:10000',
                'run' => 'must:true,type:username',
                'thread' => 'must:true,type:number,min:1',
                'public' => ''
            ]);
            // 开始执行
            $run = serviceServer::create( $res );
            return task::result( 'create', $run );
        }
        // 修改服务
        public function edit() {
            $res = request::get([
                'key' => 'must:true,type:username',
                'name' => 'must:true',
                'protocol' => 'must:true,type:username',
                'port' => 'must:true,type:number,min:10000',
                'run' => 'must:true,type:username',
                'thread' => 'must:true,type:number,min:1',
                'public' => ''
            ]);
            // 开始修改
            $config = config( 'service' );
            if ( empty( $config[$res['key']] ) ) { return task::echo( 2, ['error.input'] ); }
            $config[$res['key']] = [
                'public' => $res['public'],
                'protocol' => $res['protocol'],
                'port' => $res['port'],
                'name' => $res['name'],
                'run' => $res['run'],
                'thread' => $res['thread']
            ];
            $run = tool::coverConfig( 'config/service.config.php', $config );
            return task::result( 'edit', $run );
        }
    }