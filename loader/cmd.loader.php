<?php
use loader\user;

    class task {
        // 当前用户
        public static $user = null;
        /**
         * 启动
         */
        public static function start( $argv ) {
            array_shift( $argv );
            $argv = "/".implode( '/', $argv );
            router::$type = 'cmd';
            self::$user = new user( 'cmd' );
            return router::start( $argv );
        }
        /**
         * 格式化参数
         * - $content string 参数
         * ---
         * return string 格式化后的内容
         */
        public static function format( $content ) {
            if ( is_array( $content ) ) { $content = blog( $content ); }
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
         * - $content string 提示文字
         * ---
         * return string
         */
        public static function input( $content ) {
            echo self::format( "\n{$content} => " );
            return trim( fgets( STDIN ) );
        }
        /**
         * 确认执行
         * - $content string 提示文字
         * - $method function 执行方法
         * ---
         * return string
         */
        public static function confirm( $content, $method ) {
            $input = self::input( "{$content}\n[Y/N] ".t( 'input' ) );
            $input = strtoupper( $input );
            switch ( $input ) {
                case 'Y':
                    return $method();
                case 'N':
                    return self::echo( 1, ['true', ['type' => 'cancel']] );
                default:
                    return self::echo( 2, ['error.input'] );
            }
        }
        /**
         * 输出菜单内容
         * - $data array 菜单数据
         * - $inputText string 输入提示
         * - $run string 直接运行参数
         * ---
         * return string
         */
        public static function menu( $data, $inputText = false, $run = false ) {
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
            if ( $inputText === false ) { $inputText = t( 'input' ); }
            $input = self::input( $inputText );
            // 用户选择错误
            if ( !is_callable( $methods["method_{$input}"] ) ) {
                return self::echo( 2, ['error.input'] );
            }
            return $methods["method_{$input}"]();
        }
        /**
         * 输出结果
         * - $type string 输出类型
         * - $state any 修改结果
         * ---
         * return string 结果
         */
        public static function result( $type, $state ) {
            return task::echo(
                !empty( $state ) || $state === '0' || $state === 0 ? 0 : 2,
                [!empty( $state ) ? 'true' : 'false',['type' => $type]]
            );
        }
        /**
         * 输出函数
         * - state number 回调状态
         * - $content string 回调内容
         * - $code number 响应代码
         * ---
         * return json
         */
        public static function echo( $state, $content, $code = 200 ) {
            $check = false;
            if ( is_array( $content ) && count( $content ) <= 2 ) { $check = t( $content[0], $content[1] ); }
            if ( !empty( $check ) ) { $content = $check; }
            switch ( $state ) {
                case 0: $state = '{cg}{b}Success{end}'; break;
                case 1: $state = '{cy}{b}Fail{end}'; break;
                case 2: $state = '{cr}{b}Error{end}'; break;
                case 3: $state = '{cr}{b}Warn{end}'; break;
                default: $state = '{cc}{b}Unknown{end}'; break;
            }
            $code = !empty( $code ) && is_numeric( $code ) ? $code : 200;
            if ( is_numeric( self::$user->code ) ) { $code = self::$user->code; self::$user->code = false; }
            $content = !empty( $content ) || $content === '0' || $content === 0 || $content === false ? toString( $content ) : 'Null';
            $text = [
                "{-}{-}",
                "State: {$state}",
                "Code: {$code}",
                $content,
                "{-}{-}"
            ];
            return self::format( $text ).PHP_EOL;
        }
    }