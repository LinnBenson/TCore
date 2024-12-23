<?php
use application\model\users;

    class baseController {
        /**
         * 主菜单
         */
        public function index() {
            $debug = config( 'app.debug' ) ? 'true' : 'false';
            $user = task::$user;
            $user = $user->state ? "{$user->info['nickname']} {$user->uid}" : "无";
            return task::menu([
                "title" => config( 'app.name' )." Shell Control",
                "Version: ".config( 'app.version' ),
                "Debug: ".$debug,
                "Lang: ".config( 'app.lang' ),
                "Timezone: ".config( 'app.timezone' ),
                "{-}{-}",
                "",
                "=> 系统设置",
                "挂载用户 {cc}{$user}{end}" => function(){ return $this->loadUser(); },
                "更新系统" => function() { return $this->update(); },
                "",
                "=> 服务项设置",
                "服务项列表" => function() { return router::search( '/service' ); },
                "创建服务项" => function() { return router::search( '/service/create' ); },
                "启动所有服务项" => function() { return router::search( '/service/start' ); },
                "停止所有服务项" => function() { return router::search( '/service/stop' ); },
                "重启所有服务项" => function() { return router::search( '/service/restart' ); },
                "",
                "=> 接口服务管理",
                "创建接口" => function() { return router::search( '/manage/create_server' ); },
                "删除接口" => function() { return router::search( '/manage/delete_server' ); },
                "",
                "=> 数据库模型",
                "创建模型" => function() { return router::search( '/manage/create_model' ); },
                "删除模型" => function() { return router::search( '/manage/delete_model' ); },
                "重建模型" => function() { return router::search( '/manage/reset_model' ); },
                "重建所有模型" => function() { return router::search( '/manage/reset_all_model' ); },
            ]);
        }
        /**
         * 挂载用户
         */
        private function loadUser() {
            $id = task::input( "请输入用户 UID" );
            $user = users::find( $id );
            if ( !$user ) { return task::echo( 2, "此用户不存在！" ); }
            task::$user->setUser( $user->toArray() );
            return router::search( '/' );
        }
        /**
         * 更新系统
         */
        private function update() {
            $state = core::async( 'async', 'update', [], 3 );
            if ( $state ) {
                return task::echo( 0, '更新任务下发完成，请在异步服务日志中查看结果！' );
            }
            return task::result( 'update', $state );
        }
    }