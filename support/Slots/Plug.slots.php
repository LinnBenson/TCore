<?php
    /**
     * 插件渲染器
     */
    namespace Support\Slots;

    use Support\Bootstrap;

    class Plug {
        /**
         * 插件名称
         */
        public $name = 'Unknown';
        /**
         * 插件版本
         */
        public $version = '1.0.0';
        /**
         * 插件描述
         */
        public $description = 'There is no description for this plugin.';
        /**
         * 插件作者
         */
        public $author = 'Unknown author';
        /**
         * 插件配置
         */
        public $configCache = null;
        /**
         * 插件运行目录
         */
        public $path = '';
        /**
         * 权限申请
         * [ 权限名 => 权限方法 ] 此项由函数管理，请勿直接修改
         */
        public $permissions = [];
        /**
         * 插件依赖
         * [ 'PlugName1', 'PlugName2' ] 表示依赖 PlugName1 和 PlugName2 插件
         * 为空时表示不限制依赖
         */
        public $rely = [];
        /**
         * 适用版本
         * [ '1.0.0', '1.0.0' ] 表示适用于 1.0.0 版本
         * [ '1.0.0', '2.0.0' ] 表示适用于 1.0.0 到 2.0.0 版本
         * 为空时表示不限制版本
         */
        public $versionSuitable = [];
        /**
         * 注册权限介入
         * - $name: 权限名[string]
         * - $method: 权限方法[string]|function
         * - return bool 是否成功注册权限
         */
        public function intervene( $name, $method ) {
            if (
                is_string( $method ) &&
                method_exists( $this, $method ) &&
                !(new \ReflectionMethod( $this, $method ))->isPrivate()
            ) {
                $method = function( $data = null ) use ( $method ) {
                    return $this->$method( $data );
                };
            }
            // 注册权限
            if (
                is_callable( $method ) &&
                isset( config( 'permissions' )[$name] ) &&
                !isset( $this->permissions[$name] )
            ) {
                $this->permissions[$name] = $method;
                return true;
            }
            return false;
        }
        /**
         * 获取插件配置
         * - $key: 配置项名称[string]
         * - $default: 默认值[mixed]|null
         * - return mixed 返回配置项的值
         */
        public function config( $key, $default = null ) {
            // 初始化配置
            if ( $this->configCache === null ) {
                $config = [];
                $configFile = $this->path."config.php";
                if ( file_exists( $configFile ) ) {
                    $configData = import( $configFile );
                    if ( is_array( $configData ) ) { $config = array_merge( $config, $configData ); }
                }
                $configFile = "config/plug/{$this->name}.config.php";
                if ( file_exists( $configFile ) ) {
                    $configData = import( $configFile );
                    if ( is_array( $configData ) ) { $config = array_merge( $config, $configData ); }
                }
                $this->configCache = $config;
            }
            // 键拆分
            $keys = explode( '.', $key );
            // 获取配置
            $value = $this->configCache[$keys[0]];
            // 获取配置值
            if ( count( $keys ) === 1 ) { return $value; }
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
         * 自动加载
         * - $name: 类名[string]
         * - $file: 类文件[string]
         * - return bool 加载结果
         */
        public function autoload( $name, $file ) {
            return Bootstrap::autoload([
                "Plug\\{$this->name}\\{$name}" => $this->path.$file
            ]);
        }
        /**
         * 引用文件
         * - $file: 文件路径[string]
         * - return mixed 引用结果
         */
        public function import( $file ) {
            return require $this->path.$file;
        }
    }