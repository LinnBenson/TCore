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
                // 清理上传缓存
                $request->t( 'cmd.menu.clearUpload' ) => function()use ( $request ) {
                    return $request->echo( SystemServer::clearUpload(), ['base.clear'] );
                },
                // 切换调试模式
                "{$request->t( 'cmd.menu.editDebug' )} [{$debug}]" => function()use ( $request ) {
                    return $request->echo( SystemServer::editDebug(), ['base.edit'] );
                }
            ]);
        }
    }