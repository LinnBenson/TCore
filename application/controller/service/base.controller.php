<?php

use application\model\users;
use loader\user;
use support\method\RE;
use Channel\Client;

    class baseController {
        // 用户禁止请求的动作
        private $stopAction = [ 'onWorkerStart', 'onWorkerStart_link', 'onConnect', 'onConnect_link', 'onMessage', 'onClose', 'onClose_link', 'all' ];
        /**
         * 服务项启动
         */
        public function onWorkerStart( $ref ) {
            task::$thread = $ref->id;
            // 连接 Channel
            task::channelLink();
            if ( task::$config['thread'] < 100 ) {
                Client::on( task::$name, function( $data )use( $ref ) {
                    // 检查传递数据
                    if ( empty( $data ) || !is_array( $data ) || empty( $data['action'] ) ) { return false; }
                    // 检查线程要求
                    if ( is_numeric( $data['thread'] ) && $ref->id !== intval( $data['thread'] ) ) { return false; }
                    // 执行任务
                    $res = !empty( $data['res'] ) && is_array( $data['res'] ) ? $data['res'] : [];
                    router::search( $data['action'], [ $ref, $res ] );
                });
            }
            if ( $ref->id === 0 ) {
                // 清除心跳超时的连接
                task::addTimer( 'service_timeout', 15, function()use( $ref ){
                    foreach( $ref->connections as $conn ) {
                        if ( empty( $conn->timeout ) ) {
                            $conn->timeout = time(); continue;
                        }
                        if ( time() - $conn->timeout > 31 ) {
                            $conn->close( task::echoTo( 1, ['error.timeout'] ) );
                        }
                    }
                });
                // 服务项定时响应心跳
                $ref->service_state = 1;
                task::addTimer( 'service_state', 2, function()use( $ref ) {
                    RE::setCache( "service_state_".task::$name, $ref->service_state, 4 );
                    $ref->service_state++;
                });
            }
            // 更新在线信息
            task::addTimer( 'id_online_check', 30, function() {
                if ( !empty( task::$group['id'] ) && is_array( task::$group['id'] ) ) {
                    foreach( task::$group['id'] as $user => $data ) {
                        if ( empty( $data ) ) { continue; }
                        RE::setCache( "online_id_".$user, 1, 50 );
                    }
                }
            });
            task::addTimer( 'user_online_check', 6, function() {
                if ( !empty( task::$group['uid'] ) && is_array( task::$group['uid'] ) ) {
                    foreach( task::$group['uid'] as $user => $data ) {
                        if ( empty( $data ) ) { continue; }
                        RE::setCache( "online_uid_".$user, 1, 10 );
                    }
                }
            });
            // 检查 Channel 状态
            task::addTimer( 'channel_state', 5, function() {
                try {
                    Client::publish( 'heartbeat', 'ping' );
                } catch ( \Exception $e ) {
                    task::channelLink();
                    task::log( getTime()." Channel connection disconnected.\n" );
                }
            });
            // 用户自定义路由
            router::search( 'onWorkerStart_link', [ $ref ] );
        }
        /**
         * 服务项连接
         */
        public function onConnect( $conn ) {
            $conn->user = new user( "service_link" ); // 用户实体
            $conn->method = []; // 用户方法
            $conn->post = []; // 用户发送的数据
            $conn->get = []; // 用户发送的数据
            $conn->group = []; // 用户加入的群组
            // 用户自定义路由
            router::search( 'onConnect_link', [ $conn ] );
        }
        /**
         * 服务项收到消息
         */
        public function onMessage( $conn, $res ) {
            // 心跳响应
            $conn->timeout = time();
            // 用户回调方法
            $conn->method['echo'] = function( $result )use( $conn ) {
                if ( is_json( $result ) ) { $result = json_decode( $result, true ); }
                if ( is_object( $result ) && method_exists( $result, 'withBody' ) ) { return $conn->send( $result ); }
                if ( is_array( $result ) && ( is_numeric( $result[0] ) || is_array( $result[0] ) ) && count( $result ) <= 3 ) {
                    return $conn->send( task::echoTo( $result[0], $result[1], $result[2], $conn->user ) );
                }
                return false;
            };
            // 检测执行方法
            if ( task::$config['protocol'] === 'http' ) {
                $this->onMessage_http( $conn, $res );
            }else {
                $this->onMessage_ws( $conn, $res );
            }
        }
        /**
         * 服务项收到消息 HTTP
         */
        private function onMessage_http( $conn, $res ) {
            // 接收用户消息
            $rawBody = $res->rawBody();
            if ( !empty( $rawBody ) ) {
                if ( is_json( $rawBody ) ) { $type = 'json'; $rawBody = json_decode( $rawBody, true ); }else { $type = 'form'; parse_str( $rawBody, $rawBody ); }
            }
            if ( empty( $rawBody ) || !is_array( $rawBody ) ) { $rawBody = []; }
            $action = $rawBody['action'];
            if ( $type === 'json' ) {
                $response = !empty( $rawBody['res'] ) && is_array( $rawBody['res'] ) ? $rawBody['res'] : [];
                $conn->post = $resData = $response;
            }else {
                unset( $rawBody['action'] );
                $conn->post = $resData = $rawBody;
            }
            // 挂载到用户
            $ip = $res->header('x-real-ip') ?? $res->header('x-forwarded-for');
            $ip = $ip ?: $res->getRemoteIp();
            $conn->user->loadUser_service_link([
                'ip' => $ip,
                'ua' => $res->header( 'user-agent' ),
                'header' => $res->header(),
                'cookie' => $res->cookie()
            ]);
            $record = [
                'id' => $conn->user->id,
                'ip' => $conn->user->ip,
                'ua' => $conn->user->ua,
                'uid' => $conn->user->uid,
            ];
            // 检查用户动作意图
            if ( !empty( router::$config['all'] ) ) { $conn->method['echo']( router::search( 'all', [ $conn, $resData ], $record ) ); return $$conn->close(); }
            if ( empty( $action ) || in_array( $action, $this->stopAction ) || str_starts_with( $action, 'system_' ) ) { $conn->method['echo']([ 2, ['error.null'], 500 ]); return $conn->close(); }
            // 响应路由
            $conn->method['echo']( router::search( $action, [ $conn, $resData ], $record ) );
            return $conn->close();
        }
        /**
         * 服务项收到消息 WEBSOCKET
         */
        private function onMessage_ws( $conn, $res ) {
            // 响应心跳
            if ( $res === 'ping' ) { return true; }
            // 接收用户消息
            if ( is_json( $res ) ) { $rawBody = json_decode( $res, true ); }
            $response = !empty( $rawBody['res'] ) && is_array( $rawBody['res'] ) ? $rawBody['res'] : [];
            $conn->post = $resData = $response;
            // 检查用户动作意图
            $action = $rawBody['action'];
            if ( !empty( router::$config['all'] ) ) { $conn->method['echo']( router::search( 'all', [ $conn, $resData ], [
                'id' => $conn->user->id,
                'ip' => $conn->user->ip,
                'ua' => $conn->user->ua,
                'uid' => $conn->user->uid,
            ]));}
            if ( empty( $action ) || in_array( $action, $this->stopAction ) || str_starts_with( $action, 'system_' ) ) { $conn->method['echo']([ 2, ['error.null'], 500 ]); return false; }
            // 用户登录
            if ( $action === 'login' ) {
                task::quit( "id.{$conn->user->id}", $conn );
                task::quit( "uid.{$conn->user->uid}", $conn );
                $header = [];
                if ( !empty( $response['identifier'] ) ) { $header['identifier'] = $response['identifier']; }
                if ( !empty( $response['token'] ) ) { $header['token'] = $response['token']; }
                if ( !empty( $response['lang'] ) ) { $header['lang'] = $response['lang']; }
                if ( !empty( $response['timezone'] ) ) { $header['timezone'] = $response['timezone']; }
                $conn->user->loadUser_service_link([
                    'ip' => 'unknown',
                    'ua' => 'websocket',
                    'header' => $header
                ]);
                // 加入群组
                task::join( "id.{$conn->user->id}", $conn );
                RE::setCache( "online_id_".$conn->user->id, 1, 35 );
                if ( $conn->user->state ) {
                    task::join( "uid.{$conn->user->uid}", $conn );
                    RE::setCache( "online_uid_".$conn->user->uid, 1, 8 );
                }
                return $conn->method['echo']([ 0, [
                    'id' => $conn->user->id,
                    'ip' => $conn->user->ip,
                    'ua' => $conn->user->ua,
                    'lang' => $conn->user->lang,
                    'timezone' => $conn->user->time,
                    'uid' => $conn->user->uid
                ]]);
            }
            if ( task::$name !== 'async' && !is_uuid( $conn->user->id ) ) { return false; }
            $record = [
                'id' => $conn->user->id,
                'ip' => $conn->user->ip,
                'ua' => $conn->user->ua,
                'uid' => $conn->user->uid,
            ];
            // 注销登录
            if ( $action === 'logout' ) {
                if ( $conn->user->state ) { task::quit( "uid.{$conn->user->uid}", $conn ); }
                $conn->user->logout( false );
                return $conn->method['echo']([ 0, ['success'] ]);
            }
            // 响应路由
            $data = router::search( $action, [ $conn, $resData ], $record );
            $conn->method['echo']( $data );
        }
        /**
         * 服务项关闭连接
         */
        public function onClose( $conn ) {
            // 用户自定义路由
            router::search( 'onClose_link', [ $conn ] );
            // 退出所有加入的群组
            foreach( $conn->group as $name ) {
                task::quit( $name, $conn );
            }
        }
        /**
         * 发送数据到 Channel
         */
        public function send_channel( $conn, $res ) {
            if ( empty( $res['service'] ) || empty( $res['action'] ) ) { return [ 2, ['error.input'] ]; }
            $data = !empty( $res['res'] ) && is_array( $res['res'] ) ? $res['res'] : [];
            $thread = is_numeric( $res['thread'] ) ? $res['thread'] : false;
            task::channel( $res['service'], $res['action'], $data, $thread );
            return [ 0, ['success'] ];
        }
        /**
         * 获取当前用户信息
         */
        public function user( $conn ) {
            $user = $conn->user;
            $echo = [
                'id' => $user->id,
                'ip' => $user->ip,
                'ua' => $user->ua,
                'lang' => $user->lang,
                'time' => $user->time,
                'group' => $conn->group,
                'state' => $user->state
            ];
            if ( $user->state ) {
                $echo['uid'] = $user->uid;
            }
            return [ [ 0, 'user' ], $echo ];
        }
        /**
         * 向群组推送消息
         */
        public function send( $ref, $res ) {
            $name = $res['name'];
            $send = $res['res'];
            task::send( $name, $send );
        }
    }