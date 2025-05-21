<?php

namespace Support\Handler;

use Bootstrap;
use Support\Slots\RequestBuild;

    /**
     * 请求构建器
     */
    class Request extends RequestBuild{
        /**
         * 构造请求
         * - $type 传入数组可自定义构造
         * - @param string $type 请求类型
         * - @return void
         */
        public function __construct( $type = null ) {
            // 初始化请求
            $this->init( $type );
            // 自动构建
            $this->autoBuild();
            // 验证请求
            $this->verifyRequest();
        }
        /**
         * 初始化请求
         * - 用于初始化一个请求
         * - @param string $type 请求类型
         * - @return void
         */
        public function init( $type = null ) {
            $this->type = $type; // 请求类型
            $this->router = null; // 路由类型
            $this->code = 200; // 响应状态码
            $this->share = []; // 共享数据
            $this->id = null; // 请求 ID
            $this->ua = null; // 用户 UA
            $this->ip = null; // 用户 IP
            $this->method = null; // 请求方法
            $this->target = null; // 请求目标
            $this->source = null; // 请求来源
            $this->header = []; // 请求头
            $this->get = []; // GET 请求参数
            $this->post = []; // POST 请求参数
            $this->file = []; // 上传文件
            $this->cookie = []; // Cookie 数据
            $this->session = []; // Session 数据
            $this->user = null; // 用户信息
            $this->lang = config( 'app.lang' ); // 语言设置
        }
        /**
         * 验证请求
         * - 用于验证请求参数是否正常
         * - @return bool 验证结果
         */
        public function verifyRequest() {
            // 请求 ID 生成
            if ( !is_uuid( $this->id ) ) {
                $this->id = $this->header['id'] ?? $this->session['id'] ?? $this->cookie['id'] ?? uuid();
            }
            // 检查语言
            $this->lang = $this->header['lang'] ?? $this->session['lang'] ?? $this->cookie['lang'] ?? config( 'app.lang' );
            if (
                empty( $this->lang ) ||
                ( !is_dir( "resource/lang/{$this->lang}" ) && !is_dir( "support/System/resource/lang/{$this->lang}" ) )
            ) { $this->lang = config( 'app.lang' ); }
            // 检查请求参数是否正常
            $mustArray = [ 'header', 'get', 'post', 'file', 'cookie', 'session', 'share' ];
            foreach ( $mustArray as $key ) {
                if ( !is_array( $this->$key ) ) { $this->$key = []; }
            }
            if ( !is_string( $this->type ) ) { $this->type = null; }
            if ( !is_uuid( $this->id ) ) { $this->id = uuid(); }
            if ( !is_numeric( $this->code ) ) { $this->code = 200; }
            if ( is_callable( $this->share['saveId'] ) ) { $this->share['saveId']( $this->id ); }
            if ( empty( $this->target ) ) { $this->target = '/'; }
            $this->target = rtrim( $this->target, '/' );
            if ( substr( $this->target, 0, 1 ) !== '/' ) { $this->target = "/{$this->target}"; }
            if ( empty( $this->source ) ) { $this->source = '/'; }
            $this->user = new Account( $this );
            return true;
        }
        /**
         * 数据验证
         * - 用于验证请求参数
         * - @param array $rules 验证规则
         * - @param array $data 验证数据
         * - @return array 验证结果
         */
        public function vaildata( $rules, $data = null ) {
            $data = is_array( $data ) ? $data : $this->post;
            $vaildata = new Vaildata( $this, $rules, $data );
            return $vaildata->check();
        }
        /**
         * 使用语言包
         * - 用于针对用户语言使用语言包
         * - @param string $key 语言包键
         * - @param array $replace 替换内容
         * - @return string 语言包内容
         */
        public function t( $key, $replace = [] ) { return __( $key, $replace, $this->lang ); }
        /**
         * 回调数据结果
         * - $state 支持 Boolean / Array / Int
         * - @param int|array|bool $state 返回状态
         * - @param mixed $data 返回数据
         * - @param int $code 返回状态码
         * - @param array $header 返回头部
         * - @return string 返回结果
         */
        public function echo( $state, $data, $code = null, $header = [ 'Content-Type' => 'application/json' ] ) {
            // 检查包含的返回方法
            $method = null;
            if ( is_array( $state ) && count( $state ) === 2 ) {
                $method = $state[1];
                $state = $state[0];
            }
            // 返回状态
            $stateMap = [ 0 => 'success', 1 => 'fail', 2 => 'error', 3 => 'warning', ];
            if ( is_bool( $state ) ) {
                if ( is_array( $data ) && count( $data ) === 1 ) {
                    $addState = $state ? 'base.true' : 'base.false';
                    $data[0] = "{$data[0]}:{$addState}";
                }
                $state = $state ? 0 : 1;
            }
            $stateName = $stateMap[$state] ?? 'info';
            // 语言处理
            if ( is_array( $data ) && count( $data ) <= 2 && is_string( $data[0] ) ) {
                $msg = $this->t( $data[0], $data[1] ?? [] );
                if ( $msg !== $data[0] ) { $data = $msg; }
            }
            // 处理返回
            $res = [
                'state' => $stateName,
                'code' => is_numeric( $code ) ? $code : $this->code,
                'time' => time(),
                'data' => $data,
            ];
            if ( !empty( $method ) ) { $res['method'] = $method; }
            // 系统流程干预
            $res = Bootstrap::processRun( 'ResultCallback', $res );
            // 返回数据
            if ( is_callable( $this->share['echo'] ) ) { $res = $this->share['echo']( $res, $header ); }
            return is_array( $res ) ? json_encode( $res, JSON_UNESCAPED_UNICODE ) : $res;
        }
    }