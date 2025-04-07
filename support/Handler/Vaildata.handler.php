<?php

namespace Support\Handler;

use Bootstrap;
use Support\Handler\Request;

/**
 * 数据验证器
 * ---
 * 使用示例:
 * $vaildata = $request->vaildata( [
 *    'name' => 'required|type:string|length:min:2|max:20',
 *    'age' => 'required|type:number|size:min:18|max:100',
 *    'email' => 'required|type:email|length:max:100',
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
                $name = explode( '|', $key );
                $keyName = $name[0];
                $name = $name[1] ?? $name[0];
                $value = $data[$keyName] ?? null;
                // 遍历规则
                foreach( explode( '|', $item ) as $rule ) {
                    $ruleData = explode( ':', $rule );
                    // 必填
                    if ( $ruleData[0] === 'required' && empty( $value ) ) { Bootstrap::toError( $this->request->echo( 2, ["vaildata.required", ['name' => $name]] ) ); }
                    // 类型验证
                    if ( $ruleData[0] === 'type' ) { $value = $this->checkType( $ruleData[1], $value, $data, $name ); }
                    // 长度大小检查
                    $this->checkLength( $ruleData, $value, $name );
                    // 安全检查
                    $this->checkSafe( $item, $ruleData, $value, $name );
                }
                // 验证完成
                $res[$keyName] = $value;
            }
            // 返回数据
            return $res;
        }
        /**
         * 类型验证
         */
        private function checkType( $type, $value, $data, $name ) {
            // 用户填写了内容
            $has = function()use ( $value ) { return !empty( $value ) || $value === 0 || $value === '0'; };
            switch ( $type ) {
                case 'string':
                    if ( $has() && !is_string( $value ) ) { Bootstrap::toError( $this->request->echo( 2, ["vaildata.type.string", ['name' => $name]] ) ); }
                    break;
                case 'number':
                    if ( $has() && !is_numeric( $value ) ) { Bootstrap::toError( $this->request->echo( 2, ["vaildata.type.number", ['name' => $name]] ) ); }
                    if ( $has() ) {  $value = (float)$value; }
                    break;
                case 'boolean':
                    $value = empty( $value ) || $value === 'off' || $value === 'false' ? false : true;
                    break;
                case 'json':
                    if ( $has() && !is_json( $value ) ) { Bootstrap::toError( $this->request->echo( 2, ["vaildata.type.json", ['name' => $name]] ) ); }
                    if ( $has() ) { $value = json_decode( $value, true ); }
                    break;
                case 'email':
                    if ( $has() && !filter_var( $value, FILTER_VALIDATE_EMAIL ) ) { Bootstrap::toError( $this->request->echo( 2, ["vaildata.type.email", ['name' => $name]] ) ); }
                    break;
                case 'uuid':
                    if ( $has() && !is_uuid( $value ) ) { Bootstrap::toError( $this->request->echo( 2, ["vaildata.type.uuid", ['name' => $name]] ) ); }
                    break;
                case 'phone':
                    if ( preg_match( '/^\+\d{1,3}(\s\d+)+$/', $value ) !== 1 ) { Bootstrap::toError( $this->request->echo( 2, ["vaildata.type.phone", ['name' => $name]] ) ); }
                    break;
                case 'alnum':
                    if ( $has() && !ctype_alnum( $value ) ) { Bootstrap::toError( $this->request->echo( 2, ["vaildata.type.alnum", ['name' => $name]] ) ); }
                    break;
                case 'datetime':
                    if ( $has() && preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value ) !== 1 ) { Bootstrap::toError( $this->request->echo( 2, ["vaildata.type.datetime", ['name' => $name]] ) ); }
                    break;
                case 'date':
                    if ( $has() && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) !== 1 ) { Bootstrap::toError( $this->request->echo( 2, ["vaildata.type.date", ['name' => $name]] ) ); }
                    break;
                case 'time':
                    if ( $has() && preg_match( '/^\d{2}:\d{2}:\d{2}$/', $value ) !== 1 ) { Bootstrap::toError( $this->request->echo( 2, ["vaildata.type.time", ['name' => $name]] ) ); }
                    break;

                default: break;
            }
            return $value;
        }
        /**
         * 长度大小检查
         */
        private function checkLength( $ruleData, $value, $name ) {
            $has = function()use ( $value ) { return !empty( $value ) || $value === 0 || $value === '0'; };
            // 长度验证
            if ( $has() && $ruleData[0] === 'length' ) {
                $length = strlen( $value );
                $ruleData[2] = (int)$ruleData[2];
                if ( $ruleData[1] === 'min' && $length < $ruleData[2] ) { Bootstrap::toError( $this->request->echo( 2, ["vaildata.length.min", ['name' => $name, 'length' => $ruleData[2]]] ) ); }
                if ( $ruleData[1] === 'max' && $length > $ruleData[2] ) { Bootstrap::toError( $this->request->echo( 2, ["vaildata.length.max", ['name' => $name, 'length' => $ruleData[2]]] ) ); }
            }
            // 大小验证
            if ( $has() && $ruleData[0] === 'size' ) {
                $ruleData[2] = (float)$ruleData[2];
                if ( $ruleData[1] === 'min' && (float)$value < $ruleData[2] ) { Bootstrap::toError( $this->request->echo( 2, ["vaildata.size.min", ['name' => $name, 'size' => $ruleData[2]]] ) ); }
                if ( $ruleData[1] === 'max' && (float)$value > $ruleData[2] ) { Bootstrap::toError( $this->request->echo( 2, ["vaildata.size.max", ['name' => $name, 'size' => $ruleData[2]]] ) ); }
            }
        }
        /**
         * 安全检查
         */
        private function checkSafe( $item, $ruleData, $value, $name ) {
            // 正则验证
            if ( $ruleData[0] === 'regex' ) {
                if ( !preg_match( $ruleData[1], $value ) ) { Bootstrap::toError( $this->request->echo( 2, ["vaildata.regex", ['name' => $name]] ) ); }
            }
            // 安全检查
            if ( strpos( $item, 'safe:false' ) === false ) {
                $value = htmlspecialchars( $value );
                $value = addslashes( $value );
                // 禁止包含 php 语法
                if ( strpos( $value, '<?' ) !== false || strpos( $value, '?>' ) !== false ) { Bootstrap::toError( $this->request->echo( 2, ["vaildata.safe", ['name' => $name]] ) ); }
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
                if ( preg_match( $dangerousPatterns, $value ) ) { Bootstrap::toError( $this->request->echo( 2, ["vaildata.safe", ['name' => $name]] ) ); }
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
                if ( empty( $name ) ) { continue; }
                $title = !empty( $item['title'] ) ? $item['title'] : $name;
                if ( !empty( $item['must'] ) ) { $rule = "required"; }
                if ( !empty( $item['type'] ) ) {
                    $map = [
                        'string' => [ 'text', 'password', 'longtext', 'markdown' ],
                        'alnum' => [ 'alnum' ],
                        'number' => [ 'number' ],
                        'phone' => [ 'phone' ],
                        'email' => [ 'email' ],
                        'datetime' => [ 'datetime' ],
                        'date' => [ 'date' ],
                        'time' => [ 'time' ],
                        'json' => [ 'json' ],
                        'uuid' => [ 'uuid' ],
                        'boolean' => [ 'switch', 'check' ]
                    ];
                    foreach( $map as $type => $inputs ) {
                        if ( in_array( $item['type'], $inputs ) ) {
                            $rule = !empty( $rule ) ? "{$rule}|type:{$type}" : "type:{$type}";
                        }
                    }
                }
                if ( !empty( $item['min'] ) ) { $rule = !empty( $rule ) ? "{$rule}|min:{$item['min']}" : "min:{$item['min']}"; }
                if ( !empty( $item['max'] ) ) { $rule = !empty( $rule ) ? "{$rule}|max:{$item['max']}" : "max:{$item['max']}"; }
                $rules["{$name}|{$title}"] = $rule;
            }
            return $rules;
        }
    }