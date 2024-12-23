<?php
namespace support\middleware;

use core;
use router;
use task;

    class view {
        // 视图存储路径
        public static $project = '';
        public static $view = '';
        private static $viewDir = 'storage/view';
        public static $val = []; // 语法糖
        /**
         * 显示视图
         * - $file string 视图路径
         * - $argv array 替换参数
         * - $val array 传递变量
         * ---
         * return string 视图
         */
        public static function show( $file, $val = [], $argv = [] ) {
            // 生成视图文件路径
            $fileDir = self::$viewDir."/{$file}.view.php";
            // 查询视图是否存在
            if ( !file_exists( $fileDir ) ) { return router::error( 404 ); }
            // 设置当前项目
            if ( empty( self::$project ) ) {
                $val['project'] = $argv['project'] = self::$project = explode( '/', $file )[0];
            }
            if ( empty( self::$view ) ) { self::$view = $file; }
            // 页面共享参数
            $user = task::$user;
            $w = function( $data ) {
                if ( !isset( $data ) || ( !is_string( $data ) && !is_numeric( $data ) ) ) { return ''; }
                return htmlspecialchars( $data, ENT_QUOTES, 'UTF-8' );
            };
            // 闭包方式获取视图
            $view = function( $dir )use( $val, $user, $w ) {
                try {
                    ob_start();
                        require $dir;
                    return ob_get_clean();
                }catch ( Exception $e ) {
                    core::log( [ "[ {$dir} ]".t('error.view'), $e ], 'core' );
                    return t( 'error.500' );
                }
            };
            $view = $view( $fileDir );
            // 输出结果
            if ( self::$view !== $file ) { return $view; }
            // 输出语法糖
            return self::loadArgv( $view, $argv );
        }
        /**
         * 生成附加参数
         * ---
         * return array 替换数组
         */
        public static function addArgv() {
            $result = [
                '_app' => [
                    'name' => config( 'app.name' ),
                    'host' => config( 'app.host' ),
                    'timezone' => config( 'app.timezone' ),
                    'lang' => config( 'app.lang' ),
                    'version' => config( 'app.version' )
                ]
            ];
            // 查询当前使用主题
            $c = config( 'theme.Default' );
            $result['_theme'] = 'Default';
            if ( isset( $_COOKIE['theme'] ) ) {
                $check = config( "theme.{$_COOKIE['theme']}" );
                if ( !empty( $check ) && is_array( $check ) ) {
                    $c = $check;
                    $result['_theme'] = $_COOKIE['theme'];
                }
            }
            $result = array_merge( $result, $c );
            // 文件版本
            $result['v'] = config( 'app.debug' ) ? config( 'app.version' ).'.'.time() : config( 'app.version' );
            // 加载语言包
            $lang = task::$user->lang;
            $langFile = function( $lang ) { return self::$viewDir."/".self::$project."/lang/{$lang}.lang.php"; };
            if ( !file_exists( $langFile( $lang ) ) ) { $lang = config( 'app.lang' ); }
            if ( file_exists( $langFile( $lang ) ) ) {
                $result = array_merge( $result, require $langFile( $lang ) );
            }
            $result['_lang'] = $lang;
            // 输出
            return $result;
        }
        private static function getVal( $code ) {
            // 查询语法糖
            if ( preg_match_all( "/@start\['(.*?)'\](.*?)@end\['\\1'\]/s", $code, $vals ) === 0 ) {
                return $code;
            }
            preg_match_all( "/@val\['([^']+)'\]/", $code, $useVal );
            // 写入语法变量
            foreach( $vals[1] as $key => $name ) {
                self::$val[$name] = $vals[2][$key];
                $code = str_replace( $vals[0][$key], '', $code );
            }
            // 开始查询使用
            $uniqueUseVal0 = array_unique( $useVal[0] );
            $uniqueUseVal1 = array_unique( $useVal[1] );
            foreach( $uniqueUseVal1 as $key => $name ) {
                $code = str_replace( $uniqueUseVal0[$key], is_string( self::$val[$name] ) ? self::$val[$name] : '', $code );
            }
            return $code;
        }
        /**
         * 语言包替换
         * - $code string 视图
         * - argv array 替换参数
         * ---
         * return string 视图
         */
        private static function loadArgv( $code, $add = [] ) {
            $code = self::getVal( $code );
            // 准备替换
            $text = array_merge( core::$text, self::addArgv() );
            $text = array_merge( $text, $add );
            // 准备替换
            preg_match_all( '/\{\{(.*?)\}\}/', $code, $matches );
            $getNestedValue = function( $text, $keys, $default = null ) {
                foreach ( $keys as $key ) {
                    if ( isset($text[$key] ) ) {
                        $text = $text[$key];
                    } else {
                        return $default;
                    }
                }
                return is_string( $text ) || is_numeric( $text ) ? $text : $default;
            };
            $replacements = [];
            // 开始替换
            foreach ( array_unique( $matches[1] ) as $item ) {
                $keys = explode( '.', $item );
                $itemText = $getNestedValue( $text, $keys, $item );
                $replacements['{{'.$item.'}}'] = $itemText;
            }
            // 替换完成
            return strtr( $code, $replacements );
        }
        /**
         * 调用跳转代码
         * - $url string 跳转地址
         * ---
         * return string 跳转代码
         */
        public static function link( $url ) {
            return "<script type=\"text/javascript\">window.location.href='{$url}';</script>";
        }
        /**
         * 获取语言列表
         * - $name string 项目名称
         * ---
         * return array 结果
         */
        public static function getLang( $name = false ) {
            $dir = "storage/view/{$name}/lang";
            if ( empty( $name ) ) { $dir = "storage/lang"; }
            if ( !file_exists( $dir ) ) { return []; }
            $files = array_diff( scandir( $dir ), ['.', '..'] );
            $result = [];
            foreach( $files as $file ) {
                if ( file_exists( "$dir/$file" ) ) {
                    $name = $key = explode( '.', $file )[0];
                    $langArr = require "$dir/$file";
                    if ( is_array( $langArr ) && !empty( $langArr['name'] ) ) {
                        $name = $langArr['name'];
                    }
                    $result[$key] = $name;
                }
            }
            return $result;
        }
        /**
         * 获取主题列表
         * ---
         * return array 结果
         */
        public static function getTheme() {
            $theme = config( 'theme' );
            $result = [];
            foreach( $theme as $key => $value ) {
                $result[$key] = $key;
            }
            return $result;
        }
        /**
         * 获取时区列表
         * ---
         * return array 结果
         */
        public static function getTimezone() {
            $timezone = [];
            for ( $i= -12; $i <= 14; $i++ ) {
                $key= "";
                if ( $i >= 0 ) {
                    $key = "UTC+{$i}";
                }else {
                    $key = "UTC{$i}";
                }
                $timezone[$i] = $key;
            }
            return $timezone;
        }
    }