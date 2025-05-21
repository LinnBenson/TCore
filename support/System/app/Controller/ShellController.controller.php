<?php

namespace App\Controller;

use App\Server\SystemServer;
use Support\Handler\Request;
use Support\Provider\Shell;

    class ShellController {
        /**
         * Shell 菜单
         */
        public function menu( Request $request ) {
            $debug = config( 'app.debug' ) ? "{cr}true{end}" : "{cg}false{end}";
            return Shell::menu( $request, [
                // 菜单标题
                'title' => config( 'app.title' )." Shell Control",
                // 版本号
                "version ".config( 'app.version' ),
                '{-}',
                // 系统项调整
                "{cc}{$request->t( 'cmd.menu.system' )}{end}",
                // 重置密钥
                $request->t( 'cmd.menu.resetKey' ) => function()use ( $request ) {
                    return Shell::confirm( $request, $request->t( 'cmd.menu.resetKeyConfirm' ), function()use ( $request ) {
                        return $request->echo( SystemServer::resetKey(), ['base.reset'] );
                    });
                },
                // 清理系统缓存
                $request->t( 'cmd.menu.clearCache' ) => function()use ( $request ) {
                    return $request->echo( SystemServer::clearCache(), ['base.clear'] );
                },
                // 清理存储器
                $request->t( 'cmd.menu.clearUpload' ) => function()use ( $request ) {
                    return $request->echo( Task( 'StorageManage@expired' ), ['base.clear'] );
                },
                // 切换调试模式
                "{$request->t( 'cmd.menu.editDebug' )} [{$debug}]" => function()use ( $request ) {
                    return $request->echo( SystemServer::editDebug(), ['base.edit'] );
                },
                // 功能管理
                "{cc}{$request->t( 'cmd.menu.FeatureManagement' )}{end}",
                // 所有模型
                $request->t( 'cmd.menu.model' ) => function()use ( $request ) {
                    return $this->modelList( $request );
                },
            ]);
        }
        /**
         * 所有模型
         */
        public function modelList( Request $request ) {
            $dir = [];
            $dir1 = "support/System/app/Model/";
            if ( is_dir( $dir1 ) ) { $dir = array_merge( $dir, glob( "{$dir1}*.model.php" ) ); }
            $dir2 = "app/Model/";
            if ( is_dir( $dir2 ) ) { $dir = array_merge( $dir, glob( "{$dir2}*.model.php" ) ); }
            $menu = [
                // 所有模型
                'title' => $request->t( 'cmd.menu.model' ),
                '{-}',
            ];
            // 遍历所有模型
            foreach( $dir as $file ) {
                $model = basename( $file, ".model.php" );
                $model = "App\Model\\{$model}";
                $menu[$model::$name] = function()use ( $request, $model ) {
                    return Shell::menu( $request, [
                        'title' => $model::$name,
                        '{-}',
                        // 重装模型
                        $request->t( 'cmd.menu.upModel' ) => function()use ( $request, $model ) {
                            return $request->echo( $model::up(), ['base.reset'] );
                        },
                        // 卸载模型
                        $request->t( 'cmd.menu.downModel' ) => function()use ( $request, $model ) {
                            return $request->echo( $model::down(), ['base.delete'] );
                        }
                    ]);
                };
            }
            // 创建新模型
            $menu[$request->t( 'cmd.menu.createModel' )] = [ '999', function()use ( $request ) {
                $name = Shell::input( $request->t( 'cmd.menu.createModelInput' ) );
                return $request->echo( SystemServer::template( 'model', $name ), ['base.create'] );
            }];
            return Shell::menu( $request, $menu );
        }
        /**
         * 运行插件
         */
        public function plug( Request $request, $parameter ) {
            $parameter = explode( '/', $parameter );
            $name = $parameter[0]; $method = $parameter[1];
            if ( empty( $method ) ) { $method = Shell::input( $request->t( 'cmd.plug.inputMethod' ) ); }
            try {
                $plug = Plug( $name );
                if ( empty( $plug ) ) { return $request->echo( 2, $request->t( 'cmd.plug.nullPlug' ) ); }
                $data = $plug->$method( $request->post );
                if ( is_bool( $data ) ) {
                    return $request->echo( $data, ['base.operate'] );
                }
                return $request->echo( 0, $data );
            } catch ( \Throwable $th ) {
                return $request->echo( 2, $th->getMessage() );
            }
        }
    }