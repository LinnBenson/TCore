<?php
namespace support\middleware;

use application\model\router_record;
use core;
use router;
use task;
use support\method\RE;

    class access {
        // 白名单
        public static $whitelist = [
            'ip' => [
                "unknown",
                "0.0.0.0"
            ],
            'id' => [
                "00000000-0000-0000-0000-000000000000"
            ],
            'uid' => [
                "unknown"
            ]
        ];
        // 记录忽略路由
        public static $neglect = [ "onWorkerStart", "onWorkerStart_link", "onConnect", "onMessage", "onClose", "onClose_link", "all" ];
        /**
         * 检查访问来源
         * - $set array 覆写用户信息
         * ---
         * return boolean 校验是否通过
         */
        public static function check( $set ) {
            $info = self::info( $set );
            if ( $info === true ) { return true; }
            foreach( $info as $key => $value ) {
                $check = RE::getCache( "black_{$key}_{$value}" );
                if ( !empty( $check ) ) { return false; }
            }
            return true;
        }
        /**
         * 记录访问
         * - $target string 路由目标
         * - $set array 覆写用户信息
         * - $result boolean 校验结果
         * ---
         * return boolean 记录结果
         */
        public static function record( $target, $set = [], $result = false ) {
            if ( in_array( $target, self::$neglect ) ) { return true; }
            $info = self::info( $set );
            if ( $info === true ) { return true; }
            // 整理数据
            $type = router::$type === 'api' || router::$type === 'app' || router::$type === 'view' ? $_SERVER['REQUEST_METHOD'] : 'ANY';
            $time = getTime();
            $data = [
                'router' => router::$type,
                'type' => $type,
                'target' => $target,
                'result' => $result ? 'success' : 'fail',
                'uid' => $info['uid'],
                'access_id' => $info['id'],
                'access_ip' => $info['ip'],
                'access_ua' => isset( $set['ua'] ) ? $set['ua'] : task::$user->ua,
                'remark' => null,
                'created_at' => $time,
                'updated_at' => $time,
            ];
            try {
                RE::push( 1, 'router_record', $data );
                RE::expire( 1, 'router_record', 90 );
                return true;
            }catch ( Exception $e ) {
                core::log( ["[ {$target} ] Routing record error.", $e], 'core' );
                return false;
            }
        }
        /**
         * 批量写入数据
         * ---
         * return null
         */
        public static function insert() {
            try {
                $count = [ 'id' => [], 'ip' => [], 'uid' => [] ]; // 计数
                $insert = []; // 等待写入的数据
                // 从 Redis 取出记录的数据
                $data = RE::show( 1, 'router_record' );
                RE::delete( 1, 'router_record' );
                for ( $i=0; $i < config( 'access.maxRecord' ); $i++ ) {
                    // 开始写入数据
                    if ( empty( $data[$i] ) ) { break; }
                    $item = json_decode( $data[$i], true );
                    if ( is_array( $item ) ) { $insert[] = $item; }
                    if ( count( $insert ) >= 500 ) {
                        router_record::insert( $item );
                        $insert = [];
                    }
                    // 更新计数信息
                    $count['id'][$item['access_id']] = intval( $count['id'][$item['access_id']] ) + 1;
                    $count['ip'][$item['access_ip']] = intval( $count['ip'][$item['access_ip']] ) + 1;
                    $count['uid'][$item['uid']] = intval( $count['uid'][$item['uid']] ) + 1;
                }
                // 写入剩余数据
                if ( !empty( $insert ) ) { router_record::insert( $insert ); }
                // 检查是否有访问触发黑名单
                foreach( $count as $type => $data ) {
                    foreach( $data as $key => $num ) {
                        if ( $num < config( "access.maxAccess_{$type}" ) || empty( $key ) || in_array( $key, self::$whitelist[$type] ) ) { continue; }
                        RE::setCache( "black_{$type}_{$key}", $num, config( 'access.blackTime' ) );
                    }
                }
            }catch ( Exception $e ) {
                task::log( ["Routing record task error.", $e] );
                return false;
            }
        }
        /**
         * 整理信息
         * - $set array 覆写用户信息
         * ---
         * return 用户信息
         */
        private static function info( $set = [] ) {
            $id = is_array( $set ) && isset( $set['id'] ) ? $set['id'] : task::$user->id;
            $ip = is_array( $set ) && isset( $set['ip'] ) ? $set['ip'] : task::$user->ip;
            $uid = is_array( $set ) && isset( $set['uid'] ) ? $set['uid'] : task::$user->uid;
            if ( in_array( $id, self::$whitelist['id'] ) ) { return true; }
            if ( in_array( $ip, self::$whitelist['ip'] ) && $ip !== 'unknown' ) { return true; }
            if ( in_array( $uid, self::$whitelist['uid'] ) ) { return true; }
            return [ 'id' => $id, 'ip' => $ip, 'uid' => $uid ];
        }
    }