<?php
    /**
     * 视图渲染器
     */
    namespace Plug\ViewRenderer;

    use Support\Handler\Request;
    use Support\Slots\Plug;
    use Support\Handler\Router;

    class Index extends Plug {
        /**
         * 插件名称
         */
        public $name = 'ViewRenderer';
        /**
         * 插件版本
         */
        public $version = '1.0.0';
        /**
         * 插件描述
         */
        public $description = 'HTML view for rendering the system';
        /**
         * 插件作者
         */
        public $author = 'TCore';
        /**
         * 初始化插件
         */
        public function init() {
            // 注册权限
            $this->intervene( 'REGISTERING_ROUTES', 'router' );
            // 注册动态加载
            $this->autoload( 'BuildView', 'support/BuildView.class.php' );
            // 初始化资源地址
            $this->libraryConfig = $this->import( 'support/Library.config.php' );
        }
        /**
         * 展示视图
         */
        public function show( $view, $share = [], $cache = true ) {
            $this->config( 'load' );
            return (new BuildView([
                'view' => $view,
                'share' => is_array( $share ) ? $share : [],
                'cache' => !empty( $cache ) ? true : false,
                'plugPath' => $this->path,
                'config' => $this->configCache,
                'library' => $this->libraryConfig
            ]))->show();
        }
        /**
         * 注册路由
         */
        public function router( $data ) {
            // 注册资源
            if ( $data['name'] === 'view' && $data['filter'] === '/viewrenderer' ) {
                Router::add( '/viewrenderer/assets' )->to(function( Request $request ) {
                    return $this->assets( $request );
                })->save();
            }
        }
        /**
         * 绑定静态资源
         */
        public function assets( Request $request ) {
            $file = $request->get['file'] ?? '';
            return Router::ToFile( $this->path.'resource/library/assets/'.$file );
        }
        public $libraryConfig = [];
        /**
         * 绑定资源地址
         */
        public function add( $data ) {
            $this->libraryConfig = array_merge( $this->libraryConfig, $data );
            return true;
        }
    }
    return new Index();