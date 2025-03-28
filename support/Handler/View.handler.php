<?php

namespace Support\Handler;

use Support\Helper\Tool;

    class View {
        private $id = ''; // 视图 ID
        private $name= null; // 视图名称
        private $view = ''; // 视图内容
        private $share = []; // 共享数据
        private $superior = null; // 上级视图
        private $request = null; // 请求对象
        private $render = true; // 是否渲染
        /**
         * 构造函数
         * - $name string 视图文件
         * - $share array 共享数据
         * ---
         * return null
         */
        public function __construct( $name, $share = [], $request = null, $render = true ) {
            // 生成视图 ID
            $renderState = $render ? 'true' : 'false';
            $this->id = md5( $name.$renderState );
            // 捕获变量
            $this->name = $name;
            $this->request = $request;
            $this->share = $share;
            $this->render = $render;
            // 检查是否有上级视图
            if ( is_object( $share['view'] ) ) {
                $this->superior = $share['view'];
                $this->name = $this->superior->name;
                $this->request = $this->superior->request;
                unset( $share['view'] );
            }
            // 视图文件
            $view = __file( config( 'view.path' )."{$name}.view.html" );
            $cache = config( 'view.cache' )."{$this->id}.html";
            /**
             * 检查缓存并加载视图内容
             */
            $hasCache = file_exists( $cache ) && !config( 'app.debug' ) && $this->render ? true : false;
            $this->view = $hasCache ? file_get_contents( $cache ) : file_get_contents( $view );
            /**
             * 无需渲染的内容直接返回
             */
            if ( !$this->render ) { return true; }
            if ( !$hasCache ) {
                /**
                 * 安全检查
                 */
                if ( empty( $this->superior ) && $this->render ) { $this->view = $this->safe( $this->view ); }
                /**
                 * 解析模块
                 */
                $this->view = $this->module( $this->view );
                /**
                 * 解析视图语法
                 */
                if ( $this->render ) {
                    $this->view = $this->htmlToPhp( $this->view );
                    if ( !config( 'app.debug' ) && $this->render ) {
                        if ( !is_dir( 'storage/cache/view' ) ) { Tool::inFolder( $cache ); }
                        file_put_contents( $cache, $this->view );
                    }
                }
            }
            // return false;
            $this->view = $this->render( $this->view, $this->addSystemShare( $this->share ) );
            return true;
        }
        /**
         * 安全保护
         * - $code string 视图内容
         * ---
         * return string PHP 代码
         */
        public function safe( $code ) {
            $code = str_replace( '<?php', '', $code );
            $code = str_replace( '<?=', '', $code );
            $code = str_replace( '?>', '', $code );
            return $code;
        }
        /**
         * 解析模块
         * - $code string 视图内容
         * ---
         * return string PHP 代码
         */
        public function module( $code ) {
            return preg_replace_callback( '/<x-([\w\.]+)(?:\s+([^>]*))?>(.*?)<\/x-\1>/s', function( $module ) {
                // 模块名称
                $moduleName = startsWith( $module[1], '_' ) ? substr( $module[1], 1 ) : "module.{$module[1]}";
                // 传递参数
                $parameter = [];
                preg_match_all('/(\w+)="(.*?)"/', $module[2], $attrMatches, PREG_SET_ORDER);
                foreach ( $attrMatches as $attr ) {
                    $parameter[$attr[1]] = $attr[2];
                }
                // 输出主内容
                if ( preg_match( '/<x-([\w\.]+)(?:\s+([^>]*))?>(.*?)<\/x-\1>/s', $module[3] ) ) {
                    $module[3] = $this->module( $module[3] );
                }
                $parameter['children'] = $module[3];
                // 加载模块内容
                $moduleView = view( $moduleName, $parameter, $this->request, false );
                $moduleView = $this->render( $moduleView, $parameter );
                return $moduleView;
            }, $code );
        }
        /**
         * 渲染视图
         * - $code string PHP 代码
         * ---
         * return string 视图内容
         */
        public function render( $viewFileOriginalCode, $share = [] ) {
            // 共享变量
            try {
                // 开始解析
                ob_start();
                    extract( $share );
                    eval( '?>'.$viewFileOriginalCode );
                $viewFileOriginalCode = ob_get_clean();
                return $viewFileOriginalCode;
            }catch ( \Throwable $e ) {
                Log::to( 'Router' )->error(["View: {$this->name}", $e]);
                return config( 'app.debug' ) ? "[{$this->name}] Rendering Error: {$e->getMessage()}" : '';
            }
        }
        /**
         * 将 HTML 解析为 PHP
         * ---
         * return string PHP 代码
         */
        public function htmlToPhp( $html ) {
            $code = preg_replace( '/\{\{\/\*(.+?)\*\/\}\}/s', '', $html );
            $code = preg_replace( '/\{\{!!(.+?)!!\}\}/s', '<?php $1 ?>', $code );
            $code = preg_replace( "/{{\s*view\(\s*'(.*?)'\s*,\s*\[/", "{{ view( '$1', [ 'view' => \$this,", $code );
            $code = preg_replace( '/\{\{(.+?)\}\}/s', '<?php echo $1; ?>', $code );
            $code = preg_replace( '/@if\s*\((.*?)\)\s*:/s', '<?php if ($1): ?>', $code );
            $code = preg_replace( '/@elseif\s*\((.*?)\)\s*:/s', '<?php elseif ($1): ?>', $code );
            $code = preg_replace( '/@else\s*:/s', '<?php else: ?>', $code );
            $code = preg_replace( '/@endif/', '<?php endif; ?>', $code );
            $code = preg_replace( '/@foreach\s*\((.*?)\)\s*:/s', '<?php foreach ($1): ?>', $code );
            $code = preg_replace( '/@endforeach/', '<?php endforeach; ?>', $code );
            $code = preg_replace( '/@for\s*\((.*?)\)\s*:/s', '<?php for ($1): ?>', $code );
            $code = preg_replace( '/@endfor/', '<?php endfor; ?>', $code );
            $code = preg_replace( '/@while\s*\((.*?)\)\s*:/s', '<?php while ($1): ?>', $code );
            $code = preg_replace( '/@endwhile/', '<?php endwhile; ?>', $code );
            return $code;
        }
        /**
         * 添加共享变量
         * - $share array 共享数据
         * ---
         * return array 共享数据
         */
        public function addSystemShare( $share ) {
            $name = explode( '/', $this->name )[0];
            $share['t'] = function( $key, $replace = [] )use ( $name ) {
                $key = str_replace( "&", "{$name}.", $key );
                return is_object( $this->request ) ? $this->request->t( $key, $replace ) : __( $key, $replace );
            };
            if ( is_object( $this->request ) ) { $share['themeName'] = $this->request->header['theme'] ?? $this->request->session['theme'] ?? $this->request->header['cookie']; }
            if ( empty( $share['themeName'] ) || empty( config( 'view.theme' )[$share['themeName']] ) ) { $share['themeName'] = 'Default'; }
            $v = config( 'app.debug' ) ? "version=".config( 'update.version' ).".".time() : "version=".config( 'update.version' );
            $share = array_merge( $share, [
                'ViewName' => $name,
                'theme' => config( 'view.theme' )[$share['themeName']],
                'v' => $v,
                'version' => config( 'update.version' ),
                'lang' => $this->request->lang,
                'assets' => function( $file )use ( $name, $v ) { return "/assets/{$name}/{$file}?{$v}"; },
                'request' => $this->request,
            ]);
            return $share;
        }
        /**
         * 显示视图
         * ---
         * return string 视图内容
         */
        public function show() { return $this->view; }
    }