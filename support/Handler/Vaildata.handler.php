<?php

namespace Support\Handler;

use Bootstrap;
use Support\Handler\Request;
use Support\Helper\Tool;

/**
 * 数据验证器
 * ---
 * 使用示例:
 * $vaildata = $request->vaildata( [
 *    'name' => 'must|type:string|length:min:2|max:20',
 *    'age' => 'must|type:number|size:min:18|max:100',
 *    'email' => 'must|type:email|length:max:100',
 *    'file' => 'must|type:file|num:1',
 * ], $request->post );
 */
    class Vaildata{
        private $request = null;
        private $rule = [];
        private $data = [];
        /**
         * 构造函数
         * - $request object 请求对象
         * - $rule array 验证规则
         * - $data array 验证数据
         * ---
         * return null
         */
        public function __construct( Request $request, $rule, $data ) {
            $this->request = $request;
            if ( is_array( $rule ) && !empty( $rule['data'] ) ) { $rule = $this->getFormRule( $rule ); }
            $this->rule = is_array( $rule ) ? $rule : [];
            $this->data = is_array( $data ) ? $data : [];
        }
        /**
         * 验证规则
         * ---
         * return array 数据结果
         */
        public function check() {
            $data = $this->data;
            $res = [];
            // 遍历参数
            foreach( $this->rule as $key => $item ) {
                $keys = explode( '|', $key );
                $name = $keys[0];
                $title = $keys[1] ?? $keys[0];
                $value = $data[$name] ?? null;
                $rule = Tool::toArray( $item );
                // 必填验证
                if ( !empty( $rule['must'] ) && empty( $value ) ) { return Bootstrap::error( $this->request->echo( 2, ["vaildata.required", ['name' => $title]] ) ); }
                // 类型验证
                if ( !empty( $rule['type'] ) ) { $value = $this->checkType( $rule['type'], $value, $title ); }
                // 长度大小检查
                $this->checkLength( $rule, $value, $title );
                // 安全检查
                $this->checkSafe( $rule, $value, $title );
                // 验证完成
                $res[$name] = $value;
            }
            // 返回数据
            return $res;
        }
        /**
         * 类型验证
         */
        private function checkType( $type, $value, $name ) {
            // 用户填写了内容
            $has = function()use ( $value ) { return !empty( $value ) || $value === 0 || $value === '0'; };
            switch ( $type ) {
                case 'string':
                    if ( $has() && !is_string( $value ) ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.type.string", ['name' => $name]] ) ); }
                    break;
                case 'number':
                    if ( $has() && !is_numeric( $value ) ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.type.number", ['name' => $name]] ) ); }
                    if ( $has() ) {  $value = (float)$value; }
                    break;
                case 'boolean':
                    $value = empty( $value ) || $value === 'off' || $value === 'false' ? false : true;
                    break;
                case 'json':
                    if ( $has() && !is_json( $value ) ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.type.json", ['name' => $name]] ) ); }
                    if ( $has() ) { $value = json_decode( $value, true ); }
                    break;
                case 'email':
                    if ( $has() && !filter_var( $value, FILTER_VALIDATE_EMAIL ) ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.type.email", ['name' => $name]] ) ); }
                    break;
                case 'uuid':
                    if ( $has() && !is_uuid( $value ) ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.type.uuid", ['name' => $name]] ) ); }
                    break;
                case 'phone':
                    if ( $has() && preg_match( '/^\+\d{1,3}(\s\d+)+$/', $value ) !== 1 ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.type.phone", ['name' => $name]] ) ); }
                    break;
                case 'alnum':
                    if ( $has() && !ctype_alnum( $value ) ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.type.alnum", ['name' => $name]] ) ); }
                    break;
                case 'datetime':
                    if ( $has() && preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value ) !== 1 ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.type.datetime", ['name' => $name]] ) ); }
                    break;
                case 'date':
                    if ( $has() && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) !== 1 ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.type.date", ['name' => $name]] ) ); }
                    break;
                case 'time':
                    if ( $has() && preg_match( '/^\d{2}:\d{2}:\d{2}$/', $value ) !== 1 ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.type.time", ['name' => $name]] ) ); }
                    break;
                case 'verify':
                    if ( $has() && strtoupper( $value ) !== Redis::getCache( "VerifyImg_{$name}_{$this->request->id}" ) ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.type.verify"] ) ); }
                    break;
                case 'file':
                    if ( $has() && !is_json( $value ) ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.type.upload", ['name' => $name]] ) ); }
                    if ( $has() ) {
                        $value = json_decode( $value, true );
                        foreach( $value as $key => $item ) {
                            $item = new File( $item );
                            $value[$key] = is_object( $item ) && $item->id ? $item : null;
                        }
                    }
                    break;

                default: break;
            }
            return $value;
        }
        /**
         * 长度大小检查
         */
        private function checkLength( $rule, $value, $name ) {
            $has = function()use ( $value ) { return !empty( $value ) || $value === 0 || $value === '0'; };
            // 长度验证
            if ( $has() ) {
                if ( $rule['type'] === 'number' ) {
                    if ( is_numeric( $rule['min'] ) && (float)$value < (float)$rule['min'] ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.size.min", ['name' => $name, 'size' => $rule['min']]] ) ); }
                    if ( is_numeric( $rule['max'] ) && (float)$value > (float)$rule['max'] ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.size.max", ['name' => $name, 'size' => $rule['max']]] ) ); }
                }else if ( $rule['type'] === 'file' ) {
                    if ( is_numeric( $rule['num'] ) && count( $value ) > (float)$rule['num'] ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.num", ['name' => $name, 'num' => $rule['num']]] ) ); }
                }else {
                    $length = mb_strlen( $value, 'UTF-8' );
                    if ( is_numeric( $rule['min'] ) && $length < (float)$rule['min'] ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.length.min", ['name' => $name, 'length' => $rule['min']]] ) ); }
                    if ( is_numeric( $rule['max'] ) && $length > (float)$rule['max'] ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.length.max", ['name' => $name, 'length' => $rule['max']]] ) ); }
                }
            }
        }
        /**
         * 安全检查
         */
        private function checkSafe( $rule, $value, $name ) {
            // 正则验证
            if ( !empty( $rule['regex'] ) ) {
                if ( !preg_match( $rule['regex'], $value ) ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.regex", ['name' => $name]] ) ); }
            }
            // 安全检查
            if ( $rule['safe'] !== false && is_string( $value ) ) {
                $value = htmlspecialchars( $value );
                $value = addslashes( $value );
                // 禁止包含 php 语法
                if ( strpos( $value, '<?' ) !== false || strpos( $value, '?>' ) !== false ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.safe", ['name' => $name]] ) ); }
                // 禁止包含危险字符
                $dangerousPatterns = '/
                    \?php                    | # PHP 开头标签
                    --                       | # SQL 注释
                    DROP\s+TABLE             | # SQL DROP 语句
                    SELECT\s+\*              | # SQL SELECT 语句
                    INSERT\s+INTO            | # SQL INSERT 语句
                    DELETE\s+FROM            | # SQL DELETE 语句
                    \'|\"                    | # 单引号或双引号
                    on\w+=                   | # HTML 事件属性 (e.g., onload, onclick)
                    \|\|                     | # Shell 逻辑操作符 ||
                    &&                       | # Shell 逻辑操作符 &&
                    eval                     | # PHP eval 函数
                    base64_decode            | # PHP base64 解码
                    \{\{.*?\}\}              | # 语法糖
                    @if                      | # 语法糖
                    @elseif                  | # 语法糖
                    @else                    | # 语法糖
                    @foreach                 | # 语法糖
                    @for                     | # 语法糖
                    @while                   | # 语法糖
                    @end                       # 语法糖
                /ix';
                if ( preg_match( $dangerousPatterns, $value ) ) { Bootstrap::error( $this->request->echo( 2, ["vaildata.safe", ['name' => $name]] ) ); }
            }
        }
        /**
         * 获取表单规则
         */
        private function getFormRule( $form ) {
            $rules = [];
            if ( is_array( $form ) && !empty( $form['data'] ) ) { $form = $form['data']; }
            foreach( $form as $item ) {
                $rule = '';
                $name = $item['name'];
                $itemType = '';
                if ( empty( $name ) ) { continue; }
                $title = !empty( $item['title'] ) ? $item['title'] : $name;
                if ( !empty( $item['must'] ) ) { $rule = "must"; }
                if ( !empty( $item['type'] ) ) {
                    $map = [
                        'string' => [ 'string', 'password', 'longtext', 'markdown' ],
                        'alnum' => [ 'alnum' ],
                        'number' => [ 'number' ],
                        'phone' => [ 'phone' ],
                        'email' => [ 'email' ],
                        'datetime' => [ 'datetime' ],
                        'date' => [ 'date' ],
                        'time' => [ 'time' ],
                        'json' => [ 'json' ],
                        'uuid' => [ 'uuid' ],
                        'boolean' => [ 'switch', 'check' ],
                        'file' => [ 'upload' ],
                    ];
                    foreach( $map as $type => $inputs ) {
                        if ( in_array( $item['type'], $inputs ) ) {
                            $rule = !empty( $rule ) ? "{$rule}|type:{$type}" : "type:{$type}";
                        }
                    }
                }
                if ( !empty( $item['min'] ) ) {
                    $rule = !empty( $rule ) ? "{$rule}|min:{$item['min']}" : "min:{$item['min']}";
                }
                if ( !empty( $item['max'] ) ) {
                    $rule = !empty( $rule ) ? "{$rule}|max:{$item['max']}" : "max:{$item['max']}";
                }
                if ( !empty( $item['num'] ) ) { $rule = !empty( $rule ) ? "{$rule}|num:{$item['num']}" : "num:{$item['num']}"; }
                $rules["{$name}|{$title}"] = $rule;
            }
            return $rules;
        }
    }