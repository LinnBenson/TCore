<?php
use application\server\serviceServer;

    class serviceController {
        /**
         * 所有服务项
         */
        public function index() {
            $service = serviceServer::getAllService();
            $menu = [
                "title" => "所有服务项"
            ];
            foreach( $service as $name => $item ) {
                $state = !empty( $item['state'] ) ? '{cg}Running{end}' : '{cr}Stopping{end}';
                $menu["[ {$name} ] {$item['name']} => {$state}"] = function()use( $name ) { return $this->set( $name ); };
            }
            return task::menu( $menu );
        }
        /**
         * 创建服务项
         */
        public function create() {
            $data = [];
            $data['key'] = task::input( "请输入服务名称" );
            if ( empty( $data['key'] ) ) { return task::echo( 2, ['error.input'] ); }
            $data['name'] = task::input( "请输入服务备注" );
            if ( empty( $data['name'] ) ) { return task::echo( 2, ['error.input'] ); }
            $data['protocol'] = task::menu([
                "title" => '请选择服务协议',
                'websocket' => function() { return 'websocket'; },
                'http' => function() { return 'http'; },
                'text' => function() { return 'text'; }
            ]);
            if ( empty( $data['protocol'] ) ) { return task::echo( 2, ['error.input'] ); }
            $data['port'] = task::input( "请输入服务端口" );
            if ( empty( $data['port'] ) ) { return task::echo( 2, ['error.input'] ); }
            $data['port'] = intval( $data['port'] );
            $data['run'] = task::menu([
                "title" => '请选择运行用户',
                'root' => function() { return 'root'; },
                'www' => function() { return 'www'; }
            ]);
            if ( empty( $data['run'] ) ) { return task::echo( 2, ['error.input'] ); }
            $data['thread'] = task::input( "请输入线程数量" );
            if ( empty( $data['thread'] ) ) { return task::echo( 2, ['error.input'] ); }
            $data['thread'] = intval( $data['thread'] );
            $data['public'] = task::input( "请输入公开链接" );
            return task::result( 'create', serviceServer::create( $data ) );
        }
        /**
         * 操作服务项
         */
        public function set( $name = false ) {
            if ( !$this->hasService( $name ) ) { return task::echo( 2, ['error.null'] ); }
            // 返回菜单
            return task::menu([
                'title' => "操作服务项 {$name}",
                '启动服务' => function()use( $name ) { return $this->start( $name ); },
                '停止服务' => function()use( $name ) { return $this->stop( $name ); },
                '重启服务' => function()use( $name ) { return $this->restart( $name ); },
                '调试服务' => function()use( $name ) { return $this->debug( $name ); },
                '删除服务' => function()use( $name ) { return $this->delete( $name ); }
            ]);
        }
        //启动服务
        public function start( $name = false ) { return task::result( 'open', serviceServer::start( $name ) ); }
        //停止服务
        public function stop( $name = false ) { return task::result( 'stop', serviceServer::stop( $name ) ); }
        //重启服务
        public function restart( $name = false ) { return task::result( 'restart', serviceServer::restart( $name ) ); }
        //调试服务
        public function debug( $name ) {
            if ( !$this->hasService( $name ) ) { return task::echo( 2, ['error.null'] ); }
            return serviceServer::debug( $name );
        }
        //删除服务
        public function delete( $name ) {
            return task::confirm(
                "确定要删除 {$name} 这个服务项吗？", function()use ( $name ) {
                    return task::result( 'delete', serviceServer::delete( $name ) );
                }
            );
        }
        /**
         * 服务维持器
         */
        public function run( $name ) {
            $names = explode( ',', $name );
            $all = serviceServer::getAllService();
            foreach( $names as $name ) {
                $item = $all[$name];
                if ( !empty( $item ) ) {
                    if ( !$item['state'] ) {
                        ob_start();
                            serviceServer::start( $name );
                        ob_get_clean();
                        echo  getTime()." [ {$name} ] The service has been started.\n";
                    }
                    if ( $item['state'] > 86400 ) {
                        ob_start();
                            serviceServer::restart( $name );
                        ob_get_clean();
                        echo  getTime()." [ {$name} ] The service has been restarted.\n";
                    }
                }else {
                    echo getTime()." [ {$name} ] Service does not exist!\n";
                }
            }
            echo getTime()." Finish.\n";
        }
        /**
         * 检查服务是否存在
         */
        private function hasService( $name ) {
            $service = config( "service.{$name}" );
            return !empty( $service ) ? true : false;
        }
    }