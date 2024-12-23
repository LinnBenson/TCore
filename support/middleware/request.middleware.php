<?php
namespace support\middleware;

use core;
use task;

    class request {
        /**
         * 获取请求数据
         * - $rule array 验证规则
         * - $source POST|GET|array 来源
         * ---
         * return array 通过的数据
         */
        public static function get( $rule, $source = 'POST' ) {
            // 捕获来源
            if ( !is_array( $source ) && $source = 'POST' ) { $source = $_POST; }
            if ( !is_array( $source ) && $source = 'GET' ) { $source = $_GET; }
            if ( !is_array( $source ) || !is_array( $rule ) || empty( $rule ) ) { return []; }
            // 整理规则
            $newRule = [];
            foreach( $rule as $key => $info ) {
                $newRule[$key] = [];
                if ( empty( $info ) ) { continue; }
                $infos = explode( ',', $info );
                foreach( $infos as $item ) {
                    $items = explode( ':', $item );
                    switch ( $items[0] ) {
                        case 'min':
                        case 'max':
                            $items[1] = floatval( $items[1] );
                            break;
                        case 'must':
                            $items[1] = $items[1] === 'true' ? true : false;
                            break;

                        default: break;
                    }
                    $newRule[$key][$items[0]] = $items[1];
                }
                $newRule[$key]['safe'] = $newRule[$key]['safe'] === 'false' ? false : true;
                $newRule[$key]['name'] = !empty( $newRule[$key]['name'] ) ? $newRule[$key]['name'] : $key;
            } $rule = $newRule;
            // 扫描规则
            $res = []; // 待返回的数据
            foreach( $rule as $key => $info ) {
                // 提前获取可能存在的 value
                $value = ''; $value = $source[$key];
                // 必填检查
                if ( $info['must'] && ( empty( $value ) && $value !== 0 && $value !== '0' ) ) {
                    core::error( task::echo( 2, ['form.must',['name'=>$info['name']]] ) );
                }
                // 类型检查
                switch ( $info['type'] ) {
                    case 'number':
                        if ( !empty( $value ) || $value === 0 || $value === '0' ) {
                            if ( !is_numeric( $value ) ) {
                                core::error( task::echo( 2, ['form.number',['name'=>$info['name']]] ) );
                            }
                            $value = floatval( $value );
                            if ( $value == intval( $value ) ) { $value = intval($value); }
                        }
                        break;
                    case 'email':
                        if ( !empty( $value ) && !filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
                            core::error( task::echo( 2, ['form.email',['name'=>$info['name']]] ) );
                        }
                        break;
                    case 'json':
                        if ( !empty( $value ) && !is_json( $value ) ) {
                            core::error( task::echo( 2, ['form.json',['name'=>$info['name']]] ) );
                        }
                        break;
                    case 'boolean':
                        $value = !empty( $value ) || $value === 'on' ? true : false;
                        break;
                    case 'username':
                        if ( !empty( $value ) && !preg_match( '/^[a-zA-Z0-9]+$/', $value ) ) {
                            core::error( task::echo( 2, ['form.username',['name'=>$info['name']]] ) );
                        }
                        break;
                    case 'date':
                        if ( !empty( $value ) && !!preg_match( '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $value ) ) {
                            core::error( task::echo( 2, ['form.date',['name'=>$info['name']]] ) );
                        }
                        break;
                    case 'time':
                        if ( !empty( $value ) && !preg_match( '/^(2[0-3]|[01]\d):([0-5]\d):([0-5]\d)$/', $value ) ) {
                            core::error( task::echo( 2, ['form.time',['name'=>$info['name']]] ) );
                        }
                        break;
                    case 'datetime':
                        if ( !empty( $value ) && !preg_match( '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]) (2[0-3]|[01]\d):([0-5]\d):([0-5]\d)$/', $value ) ) {
                            core::error( task::echo( 2, ['form.datetime',['name'=>$info['name']]] ) );
                        }
                        break;
                    case 'qv':
                        if ( !empty( $value ) && !is_numeric( $value ) ) {
                            core::error( task::echo( 2, ['form.number',['name'=>t('form.qv')]] ) );
                        }
                        break;
                    case 'phone':
                        if ( !empty( $value ) && !is_numeric( $value ) ) {
                            core::error( task::echo( 2, ['form.number',['name'=>t('form.phone')]] ) );
                        }
                        if ( !empty( $value ) && !empty( $_POST['qv'] ) && is_numeric( $_POST['qv'] ) ) {
                            $value = task::$user->setPhone( $_POST['qv'], $value );
                        }
                        break;
                    case 'md':
                        $value = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
                        $info['safe'] = false;
                        $dangerousPatterns = '/
                            <\?php                   | # PHP 开头标签
                            DROP\s+TABLE             | # SQL DROP 语句
                            SELECT\s+\*              | # SQL SELECT 语句
                            INSERT\s+INTO            | # SQL INSERT 语句
                            DELETE\s+FROM            | # SQL DELETE 语句
                            @start\[                 | # 语法糖
                            @end\[                   | # 语法糖
                            @val\[                   # 语法糖
                        /ix';
                        if ( preg_match( $dangerousPatterns, $value, $matches ) ) {
                            core::error( task::echo( 2, ['form.illegal',['name'=>$info['name']]] ) );
                        }
                        break;

                    default: break;
                }
                // 最大值和最小值验证
                if ( isset( $info['min'] ) ) {
                    if ( $info['type'] === 'number' ) {
                        if ( $info['min'] > $value ) {
                            core::error( task::echo( 2, ['form.minNumber',['name'=>$info['name'],'set'=>$info['min']]] ) );
                        }
                    }else {
                        if ( $info['min'] > strlen( $value ) ) {
                            core::error( task::echo( 2, ['form.minString',['name'=>$info['name'],'set'=>$info['min']]] ) );
                        }
                    }
                }
                if ( isset( $info['max'] ) ) {
                    if ( $info['type'] === 'number' ) {
                        if ( $info['max'] < $value ) {
                            core::error( task::echo( 2, ['form.maxNumber',['name'=>$info['name'],'set'=>$info['max']]] ) );
                        }
                    }else {
                        if ( $info['max'] < strlen( $value ) ) {
                            core::error( task::echo( 2, ['form.maxString',['name'=>$info['name'],'set'=>$info['max']]] ) );
                        }
                    }
                }
                // 安全性检查
                if ( $info['safe'] && !empty( $value ) ) {
                    if ( !self::safe( $value ) ) {
                        core::error( task::echo( 2, ['form.illegal',['name'=>$info['name']]] ) );
                    }
                }
                // 验证通过
                $res[$key] = $value;
            }
            // 返回数据
            return $res;
        }
        /**
         * 安全性检查
         * - $value string 检查的值
         * ---
         * return boolean 检查结果
         */
        public static function safe( $value ) {
            if ( !is_string( $value ) ) { return true; }
            // 开始检查
            $dangerousPatterns = '/
                <\?php                   | # PHP 开头标签
                <script                  | # script 标签
                --                       | # SQL 注释
                ;                        | # 分号（可能用于 SQL 语句分隔）
                DROP\s+TABLE             | # SQL DROP 语句
                SELECT\s+\*              | # SQL SELECT 语句
                INSERT\s+INTO            | # SQL INSERT 语句
                DELETE\s+FROM            | # SQL DELETE 语句
                <|>                      | # HTML 标签
                \'|\"                    | # 单引号或双引号
                \{\{.*?\}\}              | # Blade 模板语法
                on\w+=                   | # HTML 事件属性 (e.g., onload, onclick)
                <iframe                  | # iframe 注入
                <embed                   | # embed 标签
                <object                  | # object 标签
                <img.*?src=.*?javascript:| # 图片中的 JS 注入
                <style.*?                | # 样式标签
                \|\|                     | # Shell 逻辑操作符 ||
                &&                       | # Shell 逻辑操作符 &&
                \$\(                     | # Shell 命令替换 $()
                `.*?`                    | # Shell 命令替换反引号
                eval                     | # PHP eval 函数
                base64_decode            | # PHP base64 解码
                curl                     | # 命令行 curl
                wget                     | # 命令行 wget
                @start\[                 | # 语法糖
                @end\[                   | # 语法糖
                @val\[                   # 语法糖
            /ix';
            if ( preg_match( $dangerousPatterns, $value, $matches ) ) { return false; }
            return true;
        }
    }