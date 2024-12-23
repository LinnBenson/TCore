<?php

use application\model\push_record;
use support\method\push;

    class pushController {
        public function email( $conn, $res ) {
            $state = push::email( $this->tidy( $res ) );
            $res['type'] = 'email';
            if ( $state ) { return $this->record( $res ); }
            task::log( "[ Email ] The content titled {$res['title']} failed to be pushed to {$res['to']}." );
        }
        public function bark( $conn, $res ) {
            $state = push::bark( $this->tidy( $res ) );
            $res['type'] = 'bark';
            if ( $state ) { return $this->record( $res ); }
            task::log( "[ Bark ] The content titled {$res['title']} failed to be pushed to {$res['to']}." );
        }
        public function telegram( $conn, $res ) {
            $state = push::telegram( $this->tidy( $res ) );
            $res['type'] = 'telegram';
            if ( $state ) { return $this->record( $res ); }
            task::log( "[ Telegram ] The content titled {$res['title']} failed to be pushed to {$res['to']}." );
        }
        // 数据整理
        private function tidy( $res ) {
            $data = [];
            if ( isset( $res['title'] ) ) { $data['title'] = $res['title']; }
            if ( isset( $res['to'] ) ) { $data['to'] = $res['to']; }
            if ( isset( $res['content'] ) ) { $data['content'] = $res['content']; }
            return $data;
        }
        // 写入记录
        private function record( $res ) {
            if ( !empty( $res['uid'] ) || !empty( $res['text'] ) || !empty( $res['source'] ) || !empty( $res['send_id'] ) || !empty( $res['send_ip'] ) || !empty( $res['remark'] ) ) {
                $update = [
                    'uid' => isset( $res['uid'] ) ? $res['uid'] : null,
                    'type' => $res['type'],
                    'to' => $res['to'],
                    'title' => $res['title'],
                    'content' => !empty( $res['text'] ) ? $res['text'] : $res['content'],
                    'source' => isset( $res['source'] ) ? $res['source'] : 'Async Service',
                    'send_id' => isset( $res['send_id'] ) ? $res['send_id'] : null,
                    'send_ip' => isset( $res['send_ip'] ) ? $res['send_ip'] : null,
                    'remark' => isset( $res['remark'] ) ? $res['remark'] : null,
                ];
                push_record::create( $update );
            }
        }
    }