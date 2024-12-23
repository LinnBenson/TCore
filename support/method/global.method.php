<?php
use Blocktrail\CryptoJSAES\CryptoJSAES;

    /**
     * 查询 .env 配置
     * - $key string 键名
     * - $default string 默认值
     * ---
     * return string|null 查询结果
     */
    function env( $key, $default = null ) {
        $result = isset( $_ENV[$key] ) && $_ENV[$key] !== '' ? $_ENV[$key] : $default;
        if ( $result === 'true' ) { return true; }
        if ( $result === 'false' ) { return false; }
        if ( $result === 'null' ) { return null; }
        return $result;
    }
    /**
     * 查询配置信息
     * - $key string 键名
     * - $default any 默认值
     * ---
     * return any|null 查询结果
     */
    function config( $key, $default = null ) {
        $keys = explode( '.', $key );
        if ( isset( core::$cache['config'][$keys[0]] ) ) {
            // 从缓存加载配置文件
            $config = core::$cache['config'][$keys[0]];
        }else {
            // 从系统读取配置文件
            $configFile = "config/{$keys[0]}.config.php";
            if ( !file_exists( $configFile ) ) { return $default; }
            $config = core::$cache['config'][$keys[0]] = import( $configFile );
            // 查询覆盖缓存
            $cache = config( 'cache', [] );
            if ( !empty( $cache[$keys[0]] ) && $keys[0] !== 'cache' ) {
                $config = core::$cache['config'][$keys[0]] = array_replace_recursive( $config, $cache[$keys[0]] );
            }
        }
        // 查询配置
        if ( count( $keys ) === 1 ) { return $config; }
        array_shift( $keys );
        foreach ( $keys as $k ) {
            if ( isset( $config[$k] ) ) {
                $config = $config[$k];
            }else {
                return $default;
            }
        }
        return $config;
    }
    /**
     * 引用全部文件
     * - $file string|array 引用文件
     * ---
     * return boolean 引用结果
     */
    function import( $file ) {
        if ( is_string( $file ) ) {
            return require_once $file;
        }else if ( is_array( $file ) ) {
            for ( $i = 0; $i < 999; $i++ ) {
                if ( empty( $file[$i] ) ) { break; }
                require_once $file[$i];
            }
            return true;
        }
        return false;
    }
    /**
     * 语言包调用
     * - $word string 语言包键值
     * - $replace array 替换数据
     * - $textData array 调用语言包
     * ---
     * return string 调用结果
     */
    function t( $word, $replace = [], $text = false ) {
        if ( !is_string( $word ) ) { return ''; }
        if ( $text === false || !is_array( $text ) ) { $text = core::$text; }
        $words = explode( '.', $word );
        foreach ( $words as $k ) {
            if ( isset( $text[$k] ) ) {
                $text = $text[$k];
            }else {
                return $word;
            }
        }
        if ( !is_string( $text ) ) { return $word; }
        if ( empty( $replace ) || !is_array( $replace ) ) { return $text; }
        foreach ( $replace as $k => $v ) {
            $v = (string) $v;
            $k = '{{'.$k.'}}';
            if ( strpos( $text, $k ) !== false) {
                $check = t( $v, [], $text );
                if ( $check !== $v ) { $v = $check; }
                $text = str_replace( $k, $v, $text );
            }
        }
        return $text;
    };
    /**
     * 检查动作是否为私有方法
     * - $class string 类名
     * - $method string 方法名
     * ---
     * return boolean 判断结果
     */
    function isPrivate( $class, $method ) {
        try {
            $reflection = new ReflectionMethod( $class, $method );
            return $reflection->isPrivate();
        }catch ( ReflectionException $e ) {
            return false;
        }
    }
    /**
     * 时间转换
     * - $time string 待转换的时间
     * - $add string 时区转换
     * ---
     * return string 转换后的时间
     */
    function toTime( $time, $add = false ) {
        $date = null;
        if ( is_string( $time ) && strtotime( $time ) !== false ) {
            $date = \Carbon\Carbon::parse( $time )->setTimezone( config( 'app.timezone' ) )->format( 'Y-m-d H:i:s' );
        }
        if ( is_object( $time ) && method_exists( $time, 'toDateTimeString' ) ) {
            $date = $time->toDateTimeString();
        }
        if ( $add === false && is_numeric( task::$user->time ) ) { $add = task::$user->time; }
        if ( !empty( $date ) && is_numeric( $add ) ) {
            $fromTimezone = new DateTimeZone( config( 'app.timezone' ) );
            $toTimezone = new DateTimeZone( 'UTC' );
            $dateTime = new DateTime( $date, $fromTimezone );
            $dateTime->setTimezone( $toTimezone );
            $dateTime->modify( "{$add} hours" );
            $date = $dateTime->format( 'Y-m-d H:i:s' );
        }
        return $date;
    }
    /**
     * 获取格式化时间
     * - $time string 待转换的时间
     * ---
     * return string 格式化时间
     */
    function getTime( $time = false ) {
        if ( !empty( $time ) && is_numeric( $time ) ) { return date( 'Y-m-d H:i:s', $time ); }
        return date( 'Y-m-d H:i:s' );
    };
    /**
     * 判断变量是否为 json 格式
     * - $val any 需要检查的内容
     * ---
     * return boolean 判断结果
     */
    function is_json( $val ) {
        // 非字符串类型或者为空字符串直接返回 false
        if ( !is_string( $val ) || $val === '' ) { return false; }
        // 其它情况
        $jsonData = json_decode( $val );
        if ( json_last_error() === JSON_ERROR_NONE && ( is_object( $jsonData ) || is_array( $jsonData ) ) ) {
            return true;
        }
        return false;
    }
    /**
     * 强行将变量转为可视字符串
     * - $val any 数据
     * ---
     * return string 转换结果
     */
    function toString( $val ) {
        if ( !isset( $val ) ) { return 'The incoming debugging content is empty.'; }
        if ( is_bool( $val ) ) { return $val ? 'Boolean(true)' : 'Boolean(false)'; }
        if ( is_numeric( $val ) ) { return is_string( $val ) ? "String('{$val}')" : "Number({$val})"; }
        if ( is_json( $val ) ) { return "Json Data:\n".json_encode( json_decode( $val ), JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES ); }
        if ( is_array( $val ) || is_object( $val ) ) { return print_r( $val, true ); }
        return $val;
    }
    /**
     * 生成 UUID
     * ---
     * return string UUID
     */
    function UUID() {
        $data = random_bytes( 16 );
        $data[6] = chr( ord($data[6]) & 0x0f | 0x40 );
        $data[8] = chr( ord($data[8]) & 0x3f | 0x80 );
        return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );
    }
    /**
     * 判断字符串是否为 UUID
     * - $string string 变量
     * ---
     * return boolean 判断结果
     */
    function is_uuid( $string ) {
        $pattern = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/';
        return preg_match( $pattern, $string ) === 1;
    }
    /**
     * 拼接长文本
     * - $text array 待拼接的内容
     * ---
     * return string 拼接后的内容
     */
    function blog( $text ) {
        if ( !is_array( $text ) && !is_string( $text ) ) { return ''; }
        if ( is_string( $text ) ) { return $text; }
        return implode( "\n", $text );
    }
    /**
     * 加密内容
     * - $content string 内容
     * - $key string 密钥
     * ---
     * return string 加密后的内容
     */
    function encrypt( $content, $key = false ) {
        return CryptoJsAes::encrypt( $content, $key === false ? env( 'APP_KEY', 'TCore' ) : $key );
    }
    /**
     * 解密内容
     * - $content string 内容
     * - $key string 密钥
     * ---
     * return string 解密后的内容
     */
    function decrypt( $content, $key = false ) {
        return CryptoJsAes::decrypt( $content, $key === false ? env( 'APP_KEY', 'TCore' ) : $key );
    }