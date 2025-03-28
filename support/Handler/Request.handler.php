<?php

namespace Support\Handler;

use App\Bootstrap\MainProcess;
use Support\Slots\RequestBuild;

    class Request extends RequestBuild {
        /**
         * 构造函数
         * - new Request( $type:string(请求类型) );
         */
        public function __construct( $type ) {
            $this->type = $type;
            switch ( $type ) {
                case 'http': $this->http(); break;

                default: $this->reset(); break;
            }
            // 请求 ID 生成
            $this->id = $this->header['id'] ?? $this->session['id'] ?? $this->cookie['id'] ?? uuid();
            // 检查语言
            $this->lang = $this->header['lang'] ?? $this->session['lang'] ?? $this->cookie['lang'] ?? config( 'app.lang' );
            // 参数保护
            $this->protect();
        }
        /**
         * 请求内容参数保护
         */
        private function protect() {
            if ( !is_uuid( $this->id ) ) { $this->id = uuid(); }
            $mustArray = [ 'header', 'get', 'post', 'cookie', 'session', 'share' ];
            foreach ( $mustArray as $key ) {
                if ( !is_array( $this->$key ) ) { $this->$key = []; }
            }
            if ( empty( $this->lang ) || !is_dir( "resource/lang/{$this->lang}" ) ) { $this->lang = config( 'app.lang' ); }
            if ( is_callable( $this->share['saveId'] ) ) { $this->share['saveId']( $this->id ); }
            return true;
        }
        /**
         * 数据验证
         * - $request->vaildata( $rules:array(验证规则), $data|$request->post:array(验证内容) );
         * - return array(验证后数据)
         */
        public function vaildata( $rules, $data = null ) {
            $data = is_array( $data ) ? $data : $this->post;
            $vaildata = new Vaildata( $this, $rules, $data );
            return $vaildata->check();
        }
        /**
         * 语言包调用
         * - $request->t( $text:string(语言包键名), $replace|[]:array(替换内容) );
         * - return string(语言包内容)
         */
        public function t( $text, $replace = [] ) { return __( $text, $replace, $this->lang ); }
        /**
         * 数据回调
         * - $request->echo( $state:int(状态), $data:any(数据), $code:int(状态码), $header:array(头部信息) );
         * - return string(数据回调)
         */
        public function echo( $state, $data, $code = 200, $header = [ 'Content-Type' => 'application/json' ] ) {
            // 检查包含的返回方法
            $method = null;
            if ( is_array( $state ) && count( $state ) === 2 ) {
                $method = $state[1];
                $state = $state[0];
            }
            // 语言处理
            if ( is_array( $data ) && count( $data ) <= 2 && is_string( $data[0] ) ) {
                $msg = $this->t( $data[0], $data[1] ?? [] );
                if ( $msg !== $data[0] ) { $data = $msg; }
            }
            // 整理数据
            $res = array();
            $stateConfig = [ '0' => 'success', '1' => 'fail', '2' => 'error', '3' => 'warning' ];
            $res['state'] = $stateConfig[$state] ?? 'unknown';
            $res['code'] = $code ?? $this->code;
            $res['time'] = time();
            $res['data'] = $data;
            if ( !empty( $method ) ) { $res['method'] = $method; }
            // 用户指定回调格式
            $res = MainProcess::EchoResult( $this, $res );
            // 返回数据
            if ( is_callable( $this->share['echo'] ) ) { $res = $this->share['echo']( $res, $header ); }
            return is_array( $res ) ? json_encode( $res, JSON_UNESCAPED_UNICODE ) : $res;
        }
        /**
         * 重置请求信息
         * - $request->reset();
         * - return true
         */
        public function reset() {
            $this->router = null;
            $this->id = null;
            $this->ua = null;
            $this->ip = null;
            $this->code = 200;
            $this->method = null;
            $this->target = null;
            $this->source = null;
            $this->header = [];
            $this->get = [];
            $this->post = [];
            $this->cookie = [];
            $this->session = [];
            $this->uid = null;
            $this->user = null;
            $this->lang = null;
            $this->share = [ 'saveId' => null, 'echo' => null ];
            return true;
        }
    }