<?php
    use Illuminate\Support\Str;
    use Support\Bootstrap;

    /**
     * 获取系统文件路径
     * - 找不到传入文件时查询系统文件，如果有，则使用系统文件
     * - $dir: 传入的目录路径[string]
     * - return string 返回处理后的目录路径
     */
    function __file( $dir ) {
        return !file_exists( $dir ) && file_exists( "support/System/{$dir}" ) ? "support/System/{$dir}" : $dir;
    }
    /**
     * 批量引用文件
     * - $file: 需要引用的文件[string|array]
     * - return mixed 返回引用的结果
     */
    function import( $file ) {
        if ( is_string( $file ) ) { return require __file( $file ); }
        if ( is_array( $file ) ) {
            $result = [];
            for ( $i = 0; $i < 9999; $i++ ) {
                if ( empty( $file[$i] ) ) { break; }
                $quote = require_once __file( $file[$i] );
                if ( is_array( $quote ) ) { $result = array_merge( $result, $quote ); }
            }
            return $result;
        }
        return null;
    }
    /**
     * ENV 变量
     * - $key: 变量名称[string]
     * - $default: 默认值[mixed]|null
     * - return mixed 返回环境变量的值
     */
    function env( $key, $default = null ) {
        $value = isset( $_ENV[$key] ) && $_ENV[$key] !== '' ? $_ENV[$key] : $default;
        if ( $value === 'true' ) { return true; }
        if ( $value === 'false' ) { return false; }
        if ( $value === 'null' ) { return null; }
        return $value;
    }
    /**
     * 配置信息
     * - $key: 配置项名称[string]
     * - $default: 默认值[mixed]|null
     * - return mixed 返回配置项的值
     */
    function config( $key, $default = null ) {
        // 三方程序干预
        if ( Bootstrap::$init ) {
            $cover = Bootstrap::cache( 'thread', "config_cover:{$key}", function()use( $key ) {
                return Bootstrap::permissions( 'QUERY_CONFIGURATION_INFORMATION', $key );
            });
            if ( $cover !== $key && $cover !== null ) { return $cover; }
        }
        // 键拆分
        $keys = explode( '.', $key );
        // 获取配置
        $value = Bootstrap::cache( 'thread', "config:{$keys[0]}", function()use( $keys ) {
            $result = [];
            $systemFile = "support/System/config/{$keys[0]}.config.php";
            if ( file_exists( $systemFile ) ) { $result = require $systemFile; }
            $userFile = "config/{$keys[0]}.config.php";
            if ( file_exists( $userFile ) ) { $result = array_merge( $result, require $userFile ); }
            return $result;
        });
        // 获取配置值
        if ( count( $keys ) === 1 && !empty( $value ) ) { return $value; }
        if ( !is_array( $value ) || empty( $value ) ) { return $default; }
        if ( empty( $value ) ) { return $default;}
        array_shift( $keys ); foreach ( $keys as $k ) {
            if ( isset( $value[$k] ) ) {
                $value = $value[$k];
            }else {
                return $default;
            }
        }
        return $value;
    }
    /**
     * 使用语言包
     * - $key: 语言包键[string]
     * - $replace: 替换内容[array]|[]
     * - $locale: 语言包语言[string]|null
     * - return string 返回语言包内容
     */
    function __( $key, $replace = [], $locale = null ) {
        // 检查是否为双重语言调用
        $check = explode( ':', $key );
        if ( count( $check ) === 2 && empty( $replace ) ) { return __( $check[0], [], $locale ).__( $check[1], [], $locale ); }
        // 整理参数
        $keys = explode( '.', $key );
        if ( empty( $locale ) ) { $locale = config( 'app.lang' ); }
        // 加载使用的语言包
        $text = Bootstrap::cache( 'thread', "lang:{$locale}|{$keys[0]}", function()use( $locale, $keys ) {
            $result = Bootstrap::permissions( 'QUERY_LANGUAGE_PACKAGE', [ 'lang' => $locale, 'target' => $keys[0] ] );
            if ( !is_array( $result ) ) { $result = []; }
            $folder1 = "support/System/resource/lang/{$locale}/{$keys[0]}.lang.php";
            $folder2 = "resource/lang/{$locale}/{$keys[0]}.lang.php";
            if ( !file_exists( $folder1 ) && !file_exists( $folder2 ) ) {
                $locale = config( 'app.lang' );
                $folder1 = "support/System/resource/lang/{$locale}/{$keys[0]}.lang.php";
                $folder2 = "resource/lang/{$locale}/{$keys[0]}.lang.php";
            }
            if ( file_exists( $folder1 ) ) { $result = array_merge( $result, require $folder1 ); }
            if ( file_exists( $folder2 ) ) { $result = array_merge( $result, require $folder2 ); }
            return is_array( $result ) ? $result : [];
        });
        // 查询语言包
        array_shift( $keys ); foreach ( $keys as $k ) {
            if ( !isset( $text[$k] ) ) { return $key; }
            $text = $text[$k];
        }
        if ( !is_string( $text ) ) { return $key; }
        if ( empty( $replace ) ) { return $text; }
        // 替换占位符
        foreach ( $replace as $k => $v ) { $text = str_replace( "{{".$k."}}", $v, $text ); }
        return $text;
    }
    /**
     * 插件调用
     * - $name: 插件名称[string]
     * - return class|null 返回插件实例或 null
     */
    function plug( $name ) {
        return Bootstrap::cache( 'thread', "plug:{$name}", function()use( $name ) {
            $index = __file( "plug/{$name}/index.php" );
            if ( !file_exists( $index ) ) {
                Bootstrap::log( 'Plug', "{$name} not found." );
                return null;
            }
            $plugClass = import( $index );
            if ( !is_object( $plugClass ) ) {
                Bootstrap::log( 'Plug', "{$name} index file must return a class instance." );
                return null;
            }
            // 确保是 Plug 类的实例
            if ( !is_subclass_of( $plugClass, 'Support\Slots\Plug' ) ) {
                Bootstrap::log( 'Plug', "{$name} must extend Support\Slots\Plug." );
                return null;
            }
            // 检查依赖
            if ( is_array( $plugClass->rely ) && !empty( $plugClass->rely ) ) {
                foreach ( $plugClass->rely as $relyPlug ) {
                    $relyPlug = __file( "plug/{$relyPlug}/index.php" );
                    if ( !file_exists( $relyPlug ) ) {
                        Bootstrap::log( 'Plug', "{$name} rely {$relyPlug} not found." );
                        return null;
                    }
                }
            }
            // 检查版本适用性
            $suitable = $plugClass->versionSuitable;
            if (
                ( is_array( $suitable ) && count( $suitable ) === 2 ) &&
                ( version_compare( config( 'app.version' ), $suitable[0], '<' ) || version_compare( config( 'app.version' ), $suitable[1], '>' ) )
            ) {
                Bootstrap::log( 'Plug', "{$name} not suitable for current version." );
                return null;
            }
            // 设置插件运行目录
            $path = "plug/{$name}/";
            $plugClass->path = is_dir( $path ) ? $path : "support/System/plug/{$name}/";
            // 初始化插件
            if ( method_exists( $plugClass, 'init' ) && !(new \ReflectionMethod( $plugClass, 'init'))->isPrivate() ) {
                try {
                    $plugClass->init();
                }catch ( \Throwable $e ) {
                    Bootstrap::log( 'Plug', "{$name} init error: ".$e->getMessage() );
                    return null;
                }
            }
            // 导出插件
            return $plugClass;
        });
    }
    /**
     * 格式化时间
     * - $time: 传入的时间戳或时间对象[mixed]|false
     * - return string 返回格式化后的时间字符串
     */
    function toDate( $time = false ) {
        if ( !empty( $time ) && is_numeric( $time ) ) { return date( 'Y-m-d H:i:s', $time ); }
        if ( is_object( $time ) ) { return \Carbon\Carbon::parse( $time )->timezone( config('app.timezone') ); }
        return date( 'Y-m-d H:i:s' );
    };
    /**
     * 访问视图文件
     * - $view: 视图文件路径[string]
     * - $share: 共享参数[array]
     * - return string 返回视图内容
     */
    function view( $view, $parameter = [], $cache = true ) {
        $view = str_replace( '.', '/', $view );
        if ( is_string( config( 'app.renderer' ) ) ) {
            return Plug( config( 'app.renderer' ) )->show( $view, $parameter, $cache );
        }else {
            $file = __file( "resource/view/{$view}.view.php" );
            if ( file_exists( $file ) ) {
                ob_start();
                    extract( $parameter );
                    require $file;
                return ob_get_clean();
            }
            $file = __file( "resource/view/{$view}.view.html" );
            if ( file_exists( $file ) ) {
                return file_get_contents( $file );
            }
            return null;
        }
    }
    /**
     * 哈希一个参数
     * - $content: 传入的内容[string]
     * - return string|null 返回哈希后的字符串或 null
     */
    function h( $content ) {
        if ( !is_string( $content ) ) { return null; }
        return hash( 'sha256', $content.env( 'APP_KEY', 'DefaultKey' ) );
    }
    /**
     * 判断字符串是否以指定内容开始
     * - $string: 传入的字符串[string]
     * - $val: 指定的内容[string]
     * - return bool 返回是否以指定内容开始
     */
    function startsWith( $string, $val ) { return Str::startsWith( $string, $val ); }
    /**
     * 判断字符串是否以指定内容结尾
     * - $string: 传入的字符串[string]
     * - $val: 指定的内容[string]
     * - return bool 返回是否以指定内容结尾
     */
    function endsWith( $string, $val ) { return Str::endsWith( $string, $val ); }
    /**
     * 截断字符 [ Chinese ]
     * - $value: 传入的字符串[string]
     * - $length: 截断长度[int]
     * - $end: 截断后追加的内容[string]|''
     * - return string 返回截断后的字符串
     */
    function limitCn( $value, $length, $end = '' ) { return Str::limit( $value, $length, $end ); }
    /**
     * 截断字符 [ English ]
     * - $value: 传入的字符串[string]
     * - $length: 截断长度[int]
     * - $end: 截断后追加的内容[string]|''
     * - return string 返回截断后的字符串
     */
    function limitEn( $value, $length, $end = '' ) { return Str::words( $value, $length, $end ); }