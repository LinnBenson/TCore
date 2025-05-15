<?php

namespace Support\Provider;

use Bootstrap;
use Support\Handler\Request;
use Support\Handler\Router;

    /**
     * Shell 服务端提供者
     */
    class Shell {
        /**
         * Shell 服务端入口
         */
        public static function start( $argv ) {
            $target = ''; $get = []; $post = [];
            array_shift( $argv ); foreach( $argv as $value ) {
                if( strpos( $value, ':' ) ) {
                    $value = explode( ':', $value );
                    $post[$value[0]] = $value[1];
                }else if( strpos( $value, '=' ) ) {
                    $value = explode( '=', $value );
                    $get[$value[0]] = $value[1];
                }else {
                    $target .= "/{$value}";
                }
            };
            if ( empty( $target ) ) { $target = '/'; }
            $request = ( new Request( 'admin' ) )->edit([
                'router' => 'shell',
                'target' => $target,
                'get' => $get,
                'post' => $post,
                'share' => [
                    'echo' => function( $res ) {
                    if ( !is_array( $res ) ) { return $res; }
                    $content = toString( $res['data'] );
                    $text = [
                        "{-}{-}",
                        "State: {$res['state']}",
                        "Code: {$res['code']}",
                        $content,
                        "{-}{-}"
                    ];
                    return self::format( $text ).PHP_EOL;
                }
                ]
            ]);
            $request = Bootstrap::processRun( 'ConstructingRequest', $request );
            return Router::init( $request );
        }
        /**
         * 格式化参数
         * - 用于处理个性化文字
         * - @param string $content 内容
         * - @return string 格式化后的内容
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
         * - 用于要求用户输入
         * - @param string $content 提示内容
         * - @return string 返回输入内容
         */
        public static function input( $content ) {
            echo self::format( "\n{$content} => " );
            return trim( fgets( STDIN ) );
        }
        /**
         * 输出菜单内容
         * - 用于输出一个选项菜单内容
         * - @param Request $request 请求对象
         * - @param array $data 菜单数据
         * - @param string $inputText 输入提示内容
         * - @param string $run 运行方法
         * - @return mixed 返回运行结果
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
            if ( $inputText === false ) { $inputText = $request->t( 'cmd.input' ); }
            $input = self::input( $inputText );
            // 用户选择错误
            if ( empty( $methods["method_{$input}"] ) || !is_callable( $methods["method_{$input}"] ) ) {
                return $request->echo( 2, ['cmd.error.input'] );
            }
            return $methods["method_{$input}"]();
        }
        /**
         * 确认执行
         * - 用于确认执行操作
         * - @param Request $request 请求对象
         * - @param string $content 提示内容
         * - @param callable $method 执行方法
         * - @return mixed 返回执行结果
         */
        public static function confirm( Request $request, $content, $method ) {
            $input = self::input( "{$content}\n[Y/N] ".$request->t( 'cmd.confirm' ) );
            $input = strtoupper( $input );
            switch ( $input ) {
                case 'Y':
                    return $method();
                case 'N':
                    return $request->echo( 1, ['base.cancel:base.true'] );
                default:
                    return $request->echo( 2, ['cmd.error.select'] );
            }
        }
        /**
         * 循环输出
         * - 用于循环输出内容
         * - @param string $text 输出内容
         * - @return string 返回格式化后的内容
         */
        public static function loop( $text ) { return "\r".self::format( $text ); }
        /**
         * 进度条
         * - 用于输出进度条
         * - @param int $current 当前进度
         * - @param int $total 总进度
         * - @return string 返回格式化后的进度条
         */
        public static function schedule( $current, $total = 100 ) {
            $current = intval( $current ); $total = intval( $total );
            $percentage = $current / $total;
            $progress = round( $percentage * 30 );
            $schedule = "{cg}".str_repeat( '>', $progress )."{end}".str_repeat( '-', 30 - $progress );
            return "\r[ ".self::format( $schedule )." ] ".round( $percentage * 100 )."%";
        }
    }