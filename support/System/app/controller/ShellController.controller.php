<?php
    namespace App\Controller;

    use App\Service\SetupService;
    use Support\Handler\Request;
    use Support\Handler\Router;
    use Support\Provider\Shell;

    /**
     * 系统命令行接口
     */
    class ShellController {
        /**
         * Shell 控制菜单
         */
        public function menu( Request $request ) {
            $debug = config( 'app.debug' ) ? "{cr}true{end}" : "{cg}false{end}";
            return Shell::menu( $request, [
                'title' => config( 'app.title' ).' '.$request->t( 'shell.menu.title' ),
                'Version '.config( 'app.version' ),
                '{-}',
                // 系统调整项
                "{cc}{$request->t( 'shell.menu.setup' )}{end}",
                // 重置密钥
                $request->t( 'shell.menu.resetKey' ) => function()use ( $request ) {
                    return Shell::confirm( $request, $request->t( 'shell.menu.resetKeyConfirm' ), function()use ( $request ) {
                        return $request->echo( SetupService::resetKey(), ['base.reset'] );
                    });
                },
                // 清理系统缓存
                $request->t( 'shell.menu.clearCache' ) => function()use ( $request ) {
                    return $request->echo( SetupService::clearCache(), ['base.clear'] );
                },
                // 切换调试模式
                "{$request->t( 'shell.menu.editDebug' )} [{$debug}]" => function()use ( $request ) {
                    return $request->echo( SetupService::editDebug(), ['base.edit'] );
                },
                // 开发功能
                "{cc}{$request->t( 'shell.menu.development' )}{end}",
                // 创建模板
                $request->t( 'shell.menu.createTemplate' ) => function()use ( $request ) {
                    return self::createTemplate( $request );
                },
                // 模型管理
                $request->t( 'shell.menu.model' ) => function()use ( $request ) {
                    return self::model( $request );
                },
                // 插件管理
                $request->t( 'shell.menu.plug' ) => function()use ( $request ) {
                    return self::plug( $request );
                },
                // 其它选项
                "{cc}{$request->t( 'shell.menu.other' )}{end}",
                // 调试方法
                $request->t( 'shell.menu.debug' ) => ['999', function()use ( $request ) {
                    return Router::ToController( 'BaseController@debug', $request );
                }],
            ]);
        }
        /**
         * 创建模板
         */
        public function createTemplate( Request $request ) {
            return Shell::menu( $request, [
                'title' => $request->t( 'shell.menu.createTemplate' ),
                '{-}',
                // Controller
                $request->t( 'shell.menu.template.controller' ) => function()use ( $request ) {
                    $name = Shell::input( $request->t( 'shell.menu.createTemplateInput' ) );
                    if ( empty( $name ) ) { return $request->echo( 2, ['base.error.input'] ); }
                    return $request->echo( SetupService::createTemplate( 'controller', $name ), ['base.create'] );
                },
                // Service
                $request->t( 'shell.menu.template.service' ) => function()use ( $request ) {
                    $name = Shell::input( $request->t( 'shell.menu.createTemplateInput' ) );
                    if ( empty( $name ) ) { return $request->echo( 2, ['base.error.input'] ); }
                    return $request->echo( SetupService::createTemplate( 'service', $name ), ['base.create'] );
                },
                // Model
                $request->t( 'shell.menu.template.model' ) => function()use ( $request ) {
                    $name = Shell::input( $request->t( 'shell.menu.createTemplateInput' ) );
                    if ( empty( $name ) ) { return $request->echo( 2, ['base.error.input'] ); }
                    return $request->echo( SetupService::createTemplate( 'model', $name ), ['base.create'] );
                },
                // 其它选项
                "{cc}{$request->t( 'shell.menu.other' )}{end}",
                // 调试方法
                $request->t( 'shell.menu.backMenu' ) => ['999', function()use ( $request ) {
                    return self::menu( $request );
                }],
            ]);
        }
        /**
         * 插件管理
         */
        public function plug( Request $request ) {
            $menu = [
                'title' => $request->t( 'shell.menu.plug' ),
                '{-}',
            ];
            // 插件列表
            foreach( SetupService::allPlug() as $plug ) {
                $menu[ucfirst( toName( $plug, ' ' ) )] = function()use ( $request, $plug ) {
                    $plug = Plug( $plug );
                    $menu = [
                        'title' => ucfirst( toName( $plug->name, ' ' ) ),
                        "Version {$plug->version}",
                        '{-}',
                    ];
                    // 插件菜单
                    $menu = array_merge( $menu, [
                        // 插件信息
                        $request->t( 'shell.menu.plugInfo' ) => function()use ( $request, $plug ) {
                            return $request->echo( 0, [
                                "ID: {$plug->name}",
                                "Version: {$plug->version}",
                                "Author: {$plug->author}",
                                "Description: {$plug->description}",
                            ]);
                        },
                        // 插件权限
                        $request->t( 'shell.menu.plugPermissions' ) => function()use ( $request, $plug ) {
                            $permissions = SetupService::plugPermissions( $plug ); $text = [];
                            foreach ( $permissions as $name => $state ) {
                                $state = $state ? "{cg}Allow{end}" : "{cr}Reject{end}";
                                $text[] = "{$name} [{$state}]";
                            }
                            return $request->echo( 0, $text );
                        },
                        // 插件配置
                        $request->t( 'shell.menu.plugConfig' ) => function()use ( $request, $plug ) {
                            $plug->config( 'load' );
                            return $request->echo( 0, json_encode( is_array( $plug->configCache ) ? $plug->configCache : [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES ) );
                        }
                    ]);
                    // 插件注册菜单
                    if ( isset( $plug->permissions['PLUGIN_COMMAND_MENU'] ) && is_callable( $plug->permissions['PLUGIN_COMMAND_MENU'] ) ) {
                        $data = $plug->permissions['PLUGIN_COMMAND_MENU']( $request );
                        if ( is_array( $data ) && !empty( $data ) ) {
                            $menu = array_merge( $menu, $data );
                        }
                    }
                    // 输出菜单
                    $menu = array_merge( $menu, [
                        // 其它选项
                        "{cc}{$request->t( 'shell.menu.other' )}{end}",
                        // 调试方法
                        $request->t( 'shell.menu.backMenu' ) => ['999', function()use ( $request ) {
                            return self::menu( $request );
                        }],
                    ]);
                    return Shell::menu( $request, $menu );
                };
            };
            // 输出菜单
            $menu = array_merge( $menu, [
                // 其它选项
                "{cc}{$request->t( 'shell.menu.other' )}{end}",
                // 调试方法
                $request->t( 'shell.menu.backMenu' ) => ['999', function()use ( $request ) {
                    return self::menu( $request );
                }],
            ]);
            return Shell::menu( $request, $menu );
        }
        public function model( Request $request ) {
            $menu = [
                'title' => $request->t( 'shell.menu.model' ),
                '{-}',
            ];
            // 获取所有模型
            $models = SetupService::allModel();
            foreach ( $models as $model ) {
                $menu["{$model['name']} ({$model['table']})"] = function()use ( $request, $model ) {
                    return Shell::menu( $request, [
                        'title' => "{$model['name']} ({$model['table']})",
                        // 重装模型
                        $request->t( 'shell.menu.upModel' ) => function()use ( $request, $model ) {
                            return $request->echo( $model['class']::up(), ['base.reset'] );
                        },
                        // 卸载模型
                        $request->t( 'shell.menu.downModel' ) => function()use ( $request, $model ) {
                            return $request->echo( $model['class']::down(), ['base.delete'] );
                        }
                    ]);
                };
            }
            // 输出菜单
            $menu = array_merge( $menu, [
                // 其它选项
                "{cc}{$request->t( 'shell.menu.other' )}{end}",
                // 调试方法
                $request->t( 'shell.menu.backMenu' ) => ['999', function()use ( $request ) {
                    return self::menu( $request );
                }],
            ]);
            return Shell::menu( $request, $menu );
            dd( SetupService::allModel() );
        }
    }