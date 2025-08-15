<?php
    /**
     * Shell 服务提供者
     */
    namespace Support\Provider;

    use Support\Handler\Request;
    use Support\Handler\Router;

    class Shell {
        /**
         * 初始化 HTTP 服务
         * - return string 返回初始化结果
         */
        public static function init( $argv ) {
            // 解析命令行参数
            array_shift( $argv ); $target = $get = $post = [];
            foreach ( $argv as $value ) {
                if ( strpos( $value, '--' ) === 0 ) {
                    $get[] = substr( $value, 2 );
                    continue;
                }
                if ( strpos( $value, ':' ) !== false ) {
                    $value = explode( ':', $value );
                    if ( count( $value ) !== 2 ) { continue; }
                    $post[$value[0]] = $value[1];
                    continue;
                }
                $target[] = $value;
            }
            // 初始化请求数据
            $request = new Request([
                'type' => 'Cli',
                'router' => 'shell',
                'method' => 'GET',
                'target' => '/'.implode( '/', $target ),
                'source' => config( 'app.host' ),
                'header' => [],
                'get' => $get,
                'post' => $post,
                'cookie' => [],
                'file' => [],
                'session' => [],
                'ip' => gethostbyname( gethostname() ),
                'share' => self::setShare()
            ]);
            // 加载路由
            $filter = explode( '/', $request->target );
            Router::load( $request->router, isset( $filter[1] ) ? "/{$filter[1]}" : "/" );
            return Router::search( $request, $request->router, $request->target, $request->method );
        }
        /**
         * 设置共享数据
         * - return array 返回共享数据
         */
        private static function setShare() {
            return [
                'FormattingReturnedData' => function( Request $request, $res ) {
                    $state = $res['state'] === 'success' ? "{cg}" : "{cr}";
                    $res['state'] = strtoupper( $res['state'] );
                    $result = [
                        "{b}{$state}=> {$res['state']}|{$res['code']}{end}",
                        "{-}",
                        toString( $res['data'] )
                    ];
                    return self::format( $result ).PHP_EOL;
                }
            ];
        }
        /**
         * 格式化参数
         * - $content: 需要格式化的内容[string|array]
         * - return string 返回格式化后的内容
         */
        public static function format( $content ) {
            if ( is_array( $content ) ) { $content = toString( $content ); }
            $symbol = array(
                'cr' => "\033[31m", // 红色文字
                'cg' => "\033[32m", // 绿色文字
                'cy' => "\033[33m", // 黄色文字
                'cb' => "\033[34m", // 蓝色文字
                'cp' => "\033[35m", // 紫色文字
                'cc' => "\033[36m", // 青色文字
                'cw' => "\033[37m", // 白色文字
                'br' => "\033[41m", // 红色背景
                'bg' => "\033[42m", // 绿色背景
                'by' => "\033[43m", // 黄色背景
                'bb' => "\033[44m", // 蓝色背景
                'bp' => "\033[45m", // 紫色背景
                'bc' => "\033[46m", // 青色背景
                'bw' => "\033[47m", // 白色背景
                'b' => "\033[1m", // 加粗
                'i' => "\033[3m", // 斜体
                'u' => "\033[4m", // 下划线
                'end' => "\033[0m", // 重置样式
                'time' => date( 'Y-m-d H:i:s' ), // 当前时间
                '-' => "------------" // 分割线
            );
            foreach( $symbol as $k => $v ) {
                $content = preg_replace( '/\{'.$k.'\}/', $v, $content );
            };
            return $content;
        }
        /**
         * 要求输入
         * - $content: 提示内容[string]
         * - return string 返回用户输入的内容
         */
        public static function input( $content ) {
            echo self::format( "\n{$content} => " );
            return trim( fgets( STDIN ) );
        }
        /**
         * 输出菜单内容
         * - $request: 请求对象[Request]
         * - $data: 菜单数据[array]
         * - $inputText: 输入提示文本[string|false]
         * - $run: 直接运行的菜单项[int|false]
         * - return mixed 返回菜单项执行结果或错误信息
         */
        public static function menu( Request $request, $data, $inputText = false, $run = false ) {
            $text = []; $id = 1; $methods = [];
            foreach( $data as $k => $v ) {
                if ( $k === 'title' ) {
                    $text[] = "{b}{cb}{$v}{end}";
                }else if ( is_string( $v ) ) {
                    $text[] = $v;
                }else if ( is_callable( $v ) ) {
                    $text[] = "{$id}. {$k}";
                    $methods["method_{$id}"] = $v;
                    $id++;
                }else if ( is_array( $v ) ) {
                    $text[] = "{$v[0]}. {$k}";
                    $methods["method_{$v[0]}"] = $v[1];
                }
            }
            // 如果包含选择内容则直接返回
            if ( !empty( $run ) && is_callable( $methods["method_{$run}"] ) ) {
                return $methods["method_{$run}"]();
            }
            $text[] = "";
            echo PHP_EOL.self::format( $text );
            if ( $inputText === false ) { $inputText = $request->t( 'shell.input' ); }
            $input = self::input( $inputText );
            // 用户选择错误
            if ( empty( $methods["method_{$input}"] ) || !is_callable( $methods["method_{$input}"] ) ) {
                return $request->echo( 2, ['shell.error.input'] );
            }
            return $methods["method_{$input}"]();
        }
        /**
         * 确认执行
         * - $request: 请求对象[Request]
         * - $content: 确认内容[string]
         * - $method: 执行方法[callable]
         * - return mixed 返回执行结果或错误信息
         */
        public static function confirm( Request $request, $content, $method ) {
            $input = self::input( "{$content}\n[Y/N] ".$request->t( 'shell.confirm' ) );
            $input = strtoupper( $input );
            switch ( $input ) {
                case 'Y':
                    return $method();
                case 'N':
                    return $request->echo( 0, ['base.cancel:base.true'] );
                default:
                    return $request->echo( 2, ['shell.error.select'] );
            }
        }
        /**
         * 循环输出
         * - $text: 需要循环输出的内容[string]
         * - return string 返回格式化后的内容
         */
        public static function loop( $text ) { return "\r".self::format( $text ); }
        /**
         * 进度条
         * - $current: 当前进度[int]
         * - $total: 总进度[int]
         * - return string 返回格式化后的进度条
         */
        public static function schedule( $current, $total = 100 ) {
            $current = intval( $current ); $total = intval( $total );
            $percentage = $current / $total;
            $progress = round( $percentage * 30 );
            $schedule = "{cg}".str_repeat( '>', $progress )."{end}".str_repeat( '-', 30 - $progress );
            return "\r[ ".self::format( $schedule )." ] ".round( $percentage * 100 )."%";
        }
    }