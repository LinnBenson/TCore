<?php
namespace support\method;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;

    class push {
        /**
         * 从邮箱推送
         * - $data array 推送数据
         * ---
         * return boolean 推送结果
         */
        public static function email( $data ) {
            $res = self::tidy( 'email', $data ); $data = null;
            if ( $res === false ) { return false; }
            $config = $res['config']; unset( $res['config'] );
            try {
                // 推送工作开始
                $sendMailServer = new PHPMailer( true );
                $sendMailServer -> SMTPDebug = SMTP::DEBUG_SERVER;
                $sendMailServer -> CharSet = 'UTF-8';
                $sendMailServer -> isSMTP();
                $sendMailServer -> Host = $config['host'];
                $sendMailServer -> SMTPAuth = true;
                $sendMailServer -> Username = $config['username'];
                $sendMailServer -> Password = $config['password'];
                $sendMailServer -> SMTPAutoTLS = false;
                $sendMailServer -> SMTPSecure = !empty( $config['encrypt'] ) ? PHPMailer::ENCRYPTION_STARTTLS : false;
                $sendMailServer -> Port = $config['port'];
                $sendMailServer -> SMTPDebug = false;
                $sendMailServer -> setFrom( $config['from'], config( 'app.name' ) );
                $sendMailServer -> addAddress( $res['to'] );
                $sendMailServer -> isHTML( true );
                $sendMailServer -> Subject = '=?utf-8?B?'.base64_encode( $res['title'] ).'?=';
                $sendMailServer -> Body = $res['content'];
                $result = $sendMailServer -> send();
                // 返回结果
                if ( empty( $result ) ) { return false; }
                return true;
            }catch ( Exception $e ) {
                core::log( $e, 'core' );
                return false;
            }
        }
        /**
         * 从 Bark 推送
         * - $data array 推送数据
         * ---
         * return boolean 推送结果
         */
        public static function bark( $data ) {
            $res = self::tidy( 'bark', $data ); $data = null;
            if ( $res === false ) { return false; }
            $config = $res['config']; unset( $res['config'] );
            try {
                $post = array(
                    'title' => $res['title'],
                    'body' => $res['content'],
                    'isArchive' => 1,
                    'icon' => config( 'app.host' )."/favicon.png"
                );
                $state = web::post( "{$config['host']}/{$res['to']}", json_encode( $post ) );
                if ( is_json( $state ) ) {
                    $state = json_decode( $state, true );
                    if ( $state['code'] === 200 ) { return true; }
                }
                return false;
            }catch ( Exception $e ) {
                core::log( $e, 'core' );
                return false;
            }
        }
        /**
         * 从 Telegram 推送
         * - $data array 推送数据
         * ---
         * return boolean 推送结果
         */
        public static function telegram( $data ) {
            $res = self::tidy( 'telegram', $data ); $data = null;
            if ( $res === false ) { return false; }
            $config = $res['config']; unset( $res['config'] );
            try {
                new Telegram( $config['host'], '' );
                $content = !empty( $res['title'] ) ? "*{$res['title']}*\n{$res['content']}" : $res['content'];
                $result = Request::sendMessage([
                    'chat_id' => $res['to'],
                    'text' => $content,
                    'parse_mode' => 'markdown',
                    'disable_web_page_preview' => true
                ]);
                return $result->isOk();
            }catch ( Exception $e ) {
                core::log( $e, 'core' );
                return false;
            }
        }
        /**
         * 数据整理
         */
        private static function tidy( $type, $data ) {
            // 查询推送配置
            $config = config( "push.{$type}" );
            if ( empty( $config ) || !is_array( $config ) || empty( $config['host'] ) ) { return false; }
            // 整理推送数据
            $title = isset( $data['title'] ) ? $data['title'] : config( 'app.name' );
            $to = isset( $data['to'] ) ? $data['to'] : $config['default'];
            $content = isset( $data['content'] ) ? $data['content'] : false;
            if ( empty( $to ) || $content === false ) { return false; }
            if ( is_array( $title ) ) { return false; }
            if ( is_array( $to ) ) { $to = blog( $to ); }
            if ( is_array( $content ) ) { $content = blog( $content ); }
            // 返回数据
            return [
                'config' => $config,
                'title' => $title,
                'to' => $to,
                'content' => $content,
                'time' => toTime( getTime() )
            ];
        }
    }