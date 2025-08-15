<?php
    /**
     * 管理员面板
     */
    namespace Plug\AdminPanel;

    use App\Model\AdminMenu;
    use Support\Bootstrap;
    use Support\Handler\Request;
    use Support\Slots\Plug;
    use Support\Handler\Router;

    class Index extends Plug {
        /**
         * 插件名称
         */
        public $name = 'AdminPanel';
        /**
         * 插件版本
         */
        public $version = '1.0.0';
        /**
         * 插件描述
         */
        public $description = 'System default administrator panel';
        /**
         * 插件作者
         */
        public $author = 'TCore';
        /**
         * 插件依赖
         */
        public $rely = [ 'ViewRenderer' ];
        /**
         * 初始化插件
         */
        public function init() {
            // 注册权限
            $this->intervene( 'PLUGIN_COMMAND_MENU', function( Request $request ) {
                return [
                    $request->t( 'admin.init' ) => function()use ( $request ) {
                        AdminMenu::up();
                        return $request->echo( 0, ['base.init:base.true'] );
                    }
                ];
            });
            $this->intervene( 'SYSTEM_STARTUP', function(){
                Bootstrap::autoload([
                    'App\Model\AdminMenu' => $this->path.'support/model/AdminMenu.model.php'
                ]);
            });
            $this->intervene( 'REGISTERING_ROUTES', 'router' );
            $this->intervene( 'QUERY_LANGUAGE_PACKAGE', 'lang' );
        }
        /**
         * 注册路由
         */
        public function router( $data ) {
            if ( !in_array( $data['filter'], [ '/adminpanel', "/{$this->config( 'entrance' )}" ] ) ) { return null; }
            switch ( $data['name'] ) {
                case 'view':
                    // 绑定静态资源
                    Router::add( '/adminpanel/assets' )->to(function( Request $request ) {
                        return $this->assets( $request );
                    })->save();
                    // 绑定视图
                    plug( 'ViewRenderer' )->add( require_once $this->path.'support/view.config.php' );
                    // 主入口
                    Router::add( "/{$this->config( 'entrance' )}" )->auth(function( Request $request ){
                        if ( $request->user() && $request->user()->level <= config( 'account.status.ADMIN' ) ) {
                            return view( 'admin.index', [ 'request' => $request ] );
                        }
                        return true;
                    })->view( 'admin.login' )->save();
                    // 视图入口
                    Router::add( '/adminpanel/view' )->auth(function( Request $request ){
                        if ( !$request->user() || $request->user()->level > config( 'account.status.ADMIN' ) ) {
                            return false;
                        }
                        return true;
                    })->to(function( Request $request ) {
                        $res = $request->vaildata([
                            'view' => 'required|type:string'
                        ], $request->get );
                        return view( $res['view'], [ 'request' => $request ] );
                    })->save();
                    break;

                default: break;
            }
        }
        /**
         * 注册语言包
         */
        public function lang( $data ) {
            if ( $data['target'] === 'admin' ) {
                $package = $this->path."resource/lang/{$data['target']}.php";
                if ( !file_exists( $package) ) {
                    $package = $this->path."resource/lang/".config( 'app.lang' ).".php";
                }
                return file_exists( $package ) ? require_once $package : [];
            }
            return null;
        }
        /**
         * 绑定静态资源
         */
        public function assets( Request $request ) {
            $file = $request->get['file'] ?? '';
            return Router::ToFile( $this->path.'resource/assets/'.$file );
        }
        /**
         * 渲染菜单
         */
        public function menu( Request $request ) {
            $menu = AdminMenu::where( 'enable', 1 )->where( function ( $query ) {
                $query->whereNull( 'parent' )->orWhere( 'parent', '' );
            })->orderBy( 'serial', 'asc' )->get()->toArray();
            $html = '';
            foreach( $menu as $item ) {
                $item['hasMenu'] = false;
                $parent = '';
                $parentData = AdminMenu::where( 'enable', 1 )->where( 'parent', $item['name'] )->orderBy( 'serial', 'asc' )->get();
                if ( $parentData->count() > 0 ) {
                    $item['hasMenu'] = true;
                    $parent = '<ul class="parentMenu menu_'.h( $item['name'] ).'">';
                    foreach( $parentData->toArray() as $pItem ) {
                        $parent .= view( 'admin.menu.item', [ 'request' => $request, 'item' => $pItem, 'parent' => '' ] );
                    }
                    $parent .= '</ul>';
                }
                $html .= view( 'admin.menu.item', [ 'request' => $request, 'item' => $item, 'parent' => $parent ] );
                $parentData = null;
            }
            return $html;
        }
    }
    return new Index();