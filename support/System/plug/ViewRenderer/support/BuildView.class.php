<?php
    namespace Plug\ViewRenderer;

    use Support\Bootstrap;

    /**
     * 构造视图
     */
    class BuildView {
        public $id, $request, $view, $share, $child, $plugPath, $code, $config, $library, $resource, $cache;
        public $superior = null;
        /**
         * 构造函数
         */
        public function __construct( $data ) {
            // 整理参数
            $this->id = uuid(); // 唯一标识
            $this->request = is_object( $data['share']['request'] ) ? $data['share']['request'] : null; // 请求对象
            $this->view = $data['view']; // 视图名称
            $this->share = $data['share']; // 共享变量
            $this->plugPath = $data['plugPath']; // 插件路径
            $this->config = $data['config']; // 视图配置
            $this->library = $data['library']; // 库配置
            $this->resource = $this->config['path']; // 资源路径
            $this->cache = $data['cache']; // 是否缓存
            // 检查是否有上级视图
            if ( is_object( $this->share['view'] ) ) {
                $this->superior = $this->share['view'];
                $this->view = $this->superior->view;
                $this->request = $this->superior->request;
                $this->share = array_merge( $this->superior->share, $this->share );
                unset( $this->share['view'] );
            }else {
                $this->addSystemShare();
            }
            // 加载原始视图文件
            $check = str_replace( '/', '.', $data['view'] );
            if ( !empty( $this->library[$check] ) ) {
                $codeFile = $this->library[$check];
            }else {
                $codeFile = __file( "{$this->resource}{$data['view']}.view.html" );
            }
            if ( file_exists( $codeFile ) ) {
                // 原始的 HTML 视图需要进行渲染
                $process = function()use( $codeFile ) {
                    // 读取原始视图文件
                    $code = file_get_contents( $codeFile );
                    // 安全检查
                    if ( $this->cache !== false ) { $code = $this->safe( $code ); }
                    // 解析模块
                    $code = $this->module( $code );
                    // 解析视图语法
                    if ( $this->cache !== false ) { $code = $this->htmlToPhp( $code ); }
                    return $code;
                };
                if ( $this->cache !== false ) {
                    $this->code = Bootstrap::cache( 'file', "{$data['view']}.html", $process );
                }else {
                    $this->code = $process();
                }
            }else {
                // PHP 视图直接加载
                $codeFile = __file( "{$this->resource}{$this->view}.view.php" );
                $this->code = file_exists( $codeFile ) ? file_get_contents( $codeFile ) : '';
            }
        }
        /**
         * 渲染视图
         */
        public function render( $viewFileOriginalCode, $share = [] ) {
            try {
                ob_start();
                    extract( $share  );
                    eval( '?>'.$viewFileOriginalCode );
                $viewFileOriginalCode = ob_get_clean();
                return $viewFileOriginalCode;
            }catch ( \Throwable $e ) {
                Bootstrap::log( "View Error: {$this->view}", $e );
                return null;
            }
        }
        public function show() { return $this->render( $this->code, $this->share ); }
        /**
         * 安全保护
         */
        public function safe( $code ) {
            $code = str_replace( '<?php', '', $code );
            $code = str_replace( '<?=', '', $code );
            $code = str_replace( '?>', '', $code );
            return $code;
        }
        /**
         * 解析模块
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
                $moduleView = view( $moduleName, $parameter, false );
                $moduleView = $this->render( $moduleView, $parameter );
                return $moduleView;
            }, $code );
        }
        /**
         * 将 HTML 解析为 PHP
         */
        public function htmlToPhp( $html ) {
            $code = preg_replace( '/\{\{\/\*(.+?)\*\/\}\}/s', '', $html );
            $code = preg_replace( '/\{\{!!(.+?)!!\}\}/s', '<?php $1 ?>', $code );
            $code = preg_replace( "/{{\s*View\(\s*'(.*?)'\s*,\s*\[/", "{{ View( '$1', [ 'view' => \$this,", $code );
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
         */
        public function addSystemShare() {
            // 视图主名称
            $name = explode( '/', $this->view )[0];
            // 语言包
            $t = function( $key, $replace = [] )use ( $name ) {
                $key = str_replace( "&", "{$name}.", $key );
                return is_object( $this->request ) ? $this->request->t( $key, $replace ) : __( $key, $replace );
            };
            // 当前语言
            $lang = config( 'app.lang' );
            // 主题名称
            $themeName = null;
            if ( is_object( $this->request ) ) {
                $lang = $this->request->lang;
                $themeName = $this->request->header['theme'] ?? $this->request->session['theme'] ?? $this->request->header['cookie'];
            }
            if ( empty( $themeName ) || empty( $this->config['theme'][$themeName] ) ) { $themeName = 'Default'; }
            // 文件版本
            $v = config( 'app.debug' ) ? "version=".config( 'app.version' ).".".time() : "version=".config( 'app.version' );
            // 添加到共享变量
            $this->share = array_merge( $this->share, [
                'ViewName' => $name,
                't' => $t,
                'theme' => $this->config['theme'][$themeName],
                'themeName' => $themeName,
                'v' => $v,
                'version' => config( 'app.version' ),
                'lang' => $lang,
                'assets' => function( $file, $version = false )use ( $name, $v ) {
                    $version = $version ? "?{$v}" : '';
                    return "/assets/{$name}/{$file}{$version}";
                },
                'request' => $this->request,
                'config' => is_array( $this->config ) ? $this->config : null,
            ]);
            return true;
        }
    }