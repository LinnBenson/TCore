<?php
use application\server\manageServer;

    class manageController {
        /**
         * 创建服务
         */
        public function create_server() {
            $name = task::input( "请输入服务名称" );
            if ( empty( $name ) ) { return task::echo( 2, ['error.input'] ); }
            return task::result( 'create', manageServer::createServer( $name ) );
        }
        /**
         * 删除服务
         */
        public function delete_server() {
            $name = task::input( "请输入服务名称" );
            if ( empty( $name ) ) { return task::echo( 2, ['error.input'] ); }
            return task::confirm(
                "确定要删除 {$name} 这个服务吗？", function()use ( $name ) {
                    return task::result( 'delete', manageServer::deleteServer( $name ) );
                }
            );
        }
        /**
         * 重建模型
         */
        public function reset_model() {
            $name = task::input( "请输入模型名称" );
            if ( empty( $name ) ) { return task::echo( 2, ['error.input'] ); }
            return task::confirm(
                "确定要重建 {$name} 这个模型吗？", function()use ( $name ) {
                    return task::result( 'reset', manageServer::resetModel( $name ) );
                }
            );
        }
        /**
         * 重建所有模型
         */
        public function reset_all_model() {
            return task::confirm(
                "确定要重建所有模型吗？", function() {
                    $list = scandir( "application/model/" );
                    $neglect = [ ".", ".." ];
                    foreach( $list as $name ) {
                        if ( in_array( $name, $neglect ) ) { continue; }
                        $name = str_replace( ".model.php", "", $name );
                        $check = manageServer::resetModel( $name );
                        echo $check ? "{$name} 重建完成\n" : "{$name} 重建失败\n";
                    }
                }
            );
        }
        /**
         * 创建模型
         */
        public function create_model() {
            $name = task::input( "请输入模型名称" );
            if ( empty( $name ) ) { return task::echo( 2, ['error.input'] ); }
            return task::result( 'create', manageServer::createModel( $name ) );
        }
        /**
         * 删除模型
         */
        public function delete_model() {
            $name = task::input( "请输入模型名称" );
            if ( empty( $name ) ) { return task::echo( 2, ['error.input'] ); }
            return task::confirm(
                "确定要删除 {$name} 这个模型吗？", function()use ( $name ) {
                    return task::result( 'delete', manageServer::deleteModel( $name ) );
                }
            );
        }
    }