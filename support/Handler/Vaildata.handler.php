<?php
    /**
     * 数据验证器
     */
    namespace Support\Handler;

    use Support\Bootstrap;
    use Support\Helper\Tool;

    class Vaildata {
        public $rule = [], $data = [], $result = [];
        /**
         * 构造验证器
         * - $rule: 验证规则[array]
         * - $data: 验证数据[array]
         * - return void
         */
        public function __construct( $rule, $data = [] ) {
            // 检查规则
            if ( !is_array( $rule ) || empty( $rule ) ) { $rule = []; }
            // 检查数据
            if ( !is_array( $data ) || empty( $data ) ) { $data = []; }
            // 生成验证器
            foreach( $rule as $key => $value ) { if ( is_string( $value ) ) { $rule[$key] = Tool::toArray( $value ); } }
            $this->rule = $rule;
            $this->data = $data;
        }
        /**
         * 执行验证
         * - $request: 请求对象[Request]
         * - return array 返回验证结果
         */
        public function check( Request $request ) {
            // 检查规则
            foreach( $this->rule as $key => $rule ) {
                $name = $rule['name'] ?? $key; // 当前字段名称
                $type = $rule['type'] ?? 'string'; // 当前字段类型
                $value = $this->data[$key] ?? ''; // 当前字段值
                $has = !empty( $value ) || $value === 0 || $value === '0'; // 当前字段是否存在
                $default = $rule['default'] ?? null; // 当前字段默认值
                // 检查必填
                if ( isset( $rule['required'] ) && $rule['required'] && empty( $has ) ) {
                    return Bootstrap::error( $request->echo( 2, ["vaildata.required", ['name' => $name]] ) );
                }
                if ( !$has ) { $this->result[$key] = $default; continue; } // 如果不存在则跳过验证
                // 检查类型
                $value = $this->checkType( $request, $name, $value, $type, $rule );
                // 检查长度
                $this->checkLength( $request, $name, $value, $type, $rule );
                // 安全检查
                $this->checkSafe( $request, $name, $value, $rule );
                // 验证完成
                $this->result[$key] = $value;
            }
            // 输出规则
            return $this->result;
        }
        /**
         * 检查类型
         * - $request: 请求对象[Request]
         * - $name: 字段名称[string]
         * - $value: 字段值[mixed]
         * - $type: 字段类型[string]
         * - $rule: 验证规则[array]
         * - return mixed 返回转换后的值或错误信息
         */
        private function checkType( $request, $name, $value, $type, $rule ) {
            switch ( $type ) {
                case 'string':
                    if ( !is_string( $value ) ) {
                        return Bootstrap::error( $request->echo( 2, ["vaildata.type.string", ['name' => $name]] ) );
                    }
                    break;
                case 'number':
                    if ( !is_numeric( $value ) ) {
                        return Bootstrap::error( $request->echo( 2, ["vaildata.type.number", ['name' => $name]] ) );
                    }
                    $value = (float)$value; // 转换为浮点数
                    break;
                case 'boolean':
                    $value = !empty( $value ); // 转换为布尔值
                    break;
                case 'json':
                    if ( !is_json( $value ) ) {
                        return Bootstrap::error( $request->echo( 2, ["vaildata.type.json", ['name' => $name]] ) );
                    }
                    break;
                case 'email':
                    if ( !filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
                        return Bootstrap::error( $request->echo( 2, ["vaildata.type.email", ['name' => $name]] ) );
                    }
                    break;
                case 'phone':
                    if ( preg_match( '/^\+\d{1,3}(\s\d+)+$/', $value ) !== 1 ) {
                        return Bootstrap::error( $request->echo( 2, ["vaildata.type.phone", ['name' => $name]] ) );
                    }
                    break;
                case 'uuid':
                    if ( !is_uuid( $value ) ) {
                        return Bootstrap::error( $request->echo( 2, ["vaildata.type.uuid", ['name' => $name]] ) );
                    }
                    break;
                case 'alnum':
                    if ( !ctype_alnum( $value ) ) {
                        return Bootstrap::error( $request->echo( 2, ["vaildata.type.alnum", ['name' => $name]] ) );
                    }
                    break;
                case 'datetime':
                    if ( preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value ) !== 1 ) {
                        return Bootstrap::error( $request->echo( 2, ["vaildata.type.datetime", ['name' => $name]] ) );
                    }
                    break;
                case 'date':
                    if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) !== 1 ) {
                        return Bootstrap::error( $request->echo( 2, ["vaildata.type.date", ['name' => $name]] ) );
                    }
                    break;
                case 'time':
                    if ( preg_match( '/^\d{2}:\d{2}:\d{2}$/', $value ) !== 1 ) {
                        return Bootstrap::error( $request->echo( 2, ["vaildata.type.time", ['name' => $name]] ) );
                    }
                    break;
                case 'upload':
                    $files = is_json( $value ) ? json_decode( $value, true ) : []; // 转换为数组
                    $value = [];
                    $allow = null; // 允许的文件类型
                    if ( !empty( $rule['allow'] ) && $rule['allow'] !== '*' ) {
                        $allow = explode( ',', $rule['allow'] );
                    }
                    foreach( $files as $file ) {
                        $file = new File( $file );
                        if (
                            $file->state &&
                            ( $allow === null || in_array( $file->ext, $allow ) )
                        ) { $value[] = $file; }
                    }
                    break;

                default: break;
            }
            return $value; // 返回转换后的值
        }
        /**
         * 检查长度
         * - $request: 请求对象[Request]
         * - $name: 字段名称[string]
         * - $value: 字段值[mixed]
         * - $type: 字段类型[string]
         * - $rule: 验证规则[array]
         * - return void
         */
        private function checkLength( $request, $name, $value, $type, $rule ) {
            if ( isset( $rule['min'] ) ) {
                switch ( $type ) {
                    case 'number':
                        if ( (float)$value < (float)$rule['min'] ) { Bootstrap::error( $request->echo( 2, ["vaildata.size.min", ['name' => $name, 'size' => $rule['min']]] ) ); }
                        break;
                    case 'upload':
                        $quantity = is_array( $value ) ? count( $value ) : 0;
                        if ( $quantity < (float)$rule['min'] ) { Bootstrap::error( $request->echo( 2, ["vaildata.quantity.min", ['name' => $name, 'quantity' => $rule['min']]] ) ); }
                        break;

                    default:
                        $length = mb_strlen( $value, 'UTF-8' );
                        if ( $length < (float)$rule['min'] ) { Bootstrap::error( $request->echo( 2, ["vaildata.length.min", ['name' => $name, 'length' => $rule['min']]] ) ); }
                        break;
                }
            }
            if ( isset( $rule['max'] ) ) {
                switch ( $type ) {
                    case 'number':
                        if ( (float)$value > (float)$rule['max'] ) { Bootstrap::error( $request->echo( 2, ["vaildata.size.max", ['name' => $name, 'size' => $rule['max']]] ) ); }
                        break;
                    case 'upload':
                        $quantity = is_array( $value ) ? count( $value ) : 0;
                        if ( $quantity > (float)$rule['max'] ) { Bootstrap::error( $request->echo( 2, ["vaildata.quantity.max", ['name' => $name, 'quantity' => $rule['max']]] ) ); }
                        break;

                    default:
                        $length = mb_strlen( $value, 'UTF-8' );
                        if ( $length > (float)$rule['max'] ) { Bootstrap::error( $request->echo( 2, ["vaildata.length.max", ['name' => $name, 'length' => $rule['max']]] ) ); }
                        break;
                }
            }
        }
        /**
         * 安全检查
         * - $request: 请求对象[Request]
         * - $name: 字段名称[string]
         * - $value: 字段值[mixed]
         * - $rule: 验证规则[array]
         * - return void
         */
        private function checkSafe( Request $request, $name, $value, $rule ) {
            // 正则验证
            if ( !empty( $rule['regex'] ) ) {
                if ( !preg_match( $rule['regex'], $value ) ) { return Bootstrap::error( $request->echo( 2, ["vaildata.regex", ['name' => $name]] ) ); }
            }
            // 安全检查
            if ( $rule['safe'] !== false && is_string( $value ) ) {
                $value = htmlspecialchars( $value );
                $value = addslashes( $value );
                // 禁止包含 php 语法
                if ( strpos( $value, '<?' ) !== false || strpos( $value, '?>' ) !== false ) { return Bootstrap::error( $request->echo( 2, ["vaildata.safe", ['name' => $name]] ) ); }
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
                if ( preg_match( $dangerousPatterns, $value ) ) { return Bootstrap::error( $request->echo( 2, ["vaildata.safe", ['name' => $name]] ) ); }
            }
        }
    }