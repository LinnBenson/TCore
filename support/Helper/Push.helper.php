<?php

namespace Support\Helper;

use App\Model\PushRecord;
use Bootstrap;
use Longman\TelegramBot\Telegram;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

    /**
     * 推送工具
     * - $data: {
     *   uid: 推送用户（ 为空时为 NULL ）
     *   receive: 推送用户（ 为空时为默认接收用户 ）
     *   title: 推送用户（ 为空时为 app.title ）
     *   content: 推送内容（ 必填 ）
     *   source: 来源（ 为空时为 NULL ）
     *   remark: 备注（ 为空时为 NULL ）
     * }
     */
    class Push {
        /**
         * 推送到 Email
         * - @param array $data 请求参数
         * - @param boolean $async 异步方式请求
         * - @return boolean 推送结果
         */
        public static function email( $data, $async = false ) {
            if ( $async ) {

            }
            $data = self::init( 'telegram', $data );
            if (
                empty( $data ) ||
                empty( $data['config']['host'] ) ||
                empty( $data['config']['port'] ) ||
                empty( $data['config']['username'] ) ||
                empty( $data['config']['password'] )
            ) { return false; }
            try {
                // 推送工作开始
                $sendMailServer = new PHPMailer( true );
                $sendMailServer -> SMTPDebug = SMTP::DEBUG_SERVER;
                $sendMailServer -> CharSet = 'UTF-8';
                $sendMailServer -> isSMTP();
                $sendMailServer -> Host = $data['config']['host'];
                $sendMailServer -> SMTPAuth = true;
                $sendMailServer -> Username = $data['config']['username'];
                $sendMailServer -> Password = $data['config']['password'];
                $sendMailServer -> SMTPAutoTLS = false;
                $sendMailServer -> SMTPSecure = !empty( $data['config']['encrypt'] ) ? PHPMailer::ENCRYPTION_STARTTLS : false;
                $sendMailServer -> Port = $data['config']['port'];
                $sendMailServer -> SMTPDebug = false;
                $sendMailServer -> setFrom( $data['config']['username'], config( 'app.title' ) );
                $sendMailServer -> addAddress( $data['receive'] );
                $sendMailServer -> isHTML( true );
                $sendMailServer -> Subject = '=?utf-8?B?'.base64_encode( $data['title'] ).'?=';
                $sendMailServer -> Body = $data['content'];
                $result = $sendMailServer -> send();
                // 返回结果
                if ( empty( $result ) ) { return false; }
                self::record( $data );
                return true;
            }catch ( \Throwable $e ) {
                Bootstrap::log( "Push to Email", $e );
                return false;
            }
        }
        /**
         * 推送到 Telegram
         * - @param array $data 请求参数
         * - @param boolean $async 异步方式请求
         * - @return boolean 推送结果
         */
        public static function telegram( $data, $async = false ) {
            if ( $async ) {

            }
            $data = self::init( 'telegram', $data );
            if ( empty( $data ) || empty( $data['config']['key'] ) ) { return false; }
            try {
                new telegram( $data['config']['key'], '' );
                $content = !empty( $data['title'] ) ? "*{$data['title']}*\n{$data['content']}" : $data['content'];
                $result = \Longman\TelegramBot\Request::sendMessage([
                    'chat_id' => $data['receive'],
                    'text' => $content,
                    'parse_mode' => 'markdown',
                    'disable_web_page_preview' => true
                ]);
                $state = $result->isOk();
                if ( !empty( $state ) ) {
                    self::record( $data );
                    return true;
                }
                return false;
            }catch ( \Throwable $e ) {
                Bootstrap::log( "Push to Telegram", $e );
                return false;
            }
        }
        /**
         * 推送到 Bark
         * - @param array $data 请求参数
         * - @param boolean $async 异步方式请求
         * - @return boolean 推送结果
         */
        public static function bark( $data, $async = false ) {
            if ( $async ) {

            }
            $data = self::init( 'bark', $data );
            if ( empty( $data ) || empty( $data['config']['host'] ) ) { return false; }
            try {
                $post = array(
                    'title' => $data['title'],
                    'body' => $data['content'],
                    'isArchive' => 1,
                    'icon' => config( 'app.host' )."/library/favicon.png"
                );
                $state = Web::post( "{$data['config']['host']}/{$data['receive']}", json_encode( $post ) );
                if ( is_json( $state ) ) {
                    $state = json_decode( $state, true );
                    if ( $state['code'] === 200 ) {
                        self::record( $data );
                        return true;
                    }
                }
                return false;
            }catch ( \Throwable $e ) {
                Bootstrap::log( "Push to Bark", $e );
                return false;
            }
        }
        /**
         * 记录推送数据
         * - 用于记录推送完成信息
         * - @param array $data 请求参数
         * - @return boolean 记录结果
         */
        private static function record( $data ) {
            $set = PushRecord::create([
                'uid' => $data['uid'],
                'type' => $data['type'],
                'receive' => $data['receive'],
                'title' => $data['title'],
                'content' => $data['content'],
                'source' => $data['source'],
                'remark' => $data['remark']
            ]);
            return $set && $set->id ? true : false;
        }
        /**
         * 初始化参数
         * - 用于准备推送使用的数据
         * - @param string $type 推送类型
         * - @param array $data 请求参数
         * - @return null|array 推送数据
         */
        private static function init( $type, $data ) {
            $config = config( "api.{$type}" );
            if ( empty( $config ) || !is_array( $config ) ) { return null; }
            // 整理推送数据
            $title = isset( $data['title'] ) ? $data['title'] : config( 'app.title' );
            $receive = isset( $data['receive'] ) ? $data['receive'] : $config['receive'];
            $content = isset( $data['content'] ) ? $data['content'] : null;
            $uid = isset( $data['uid'] ) ? $data['uid'] : null;
            $source = isset( $data['source'] ) ? $data['source'] : null;
            $remark = isset( $data['remark'] ) ? $data['remark'] : null;
            if ( empty( $receive ) || $content === null ) { return null; }
            if ( is_array( $title ) ) { $title = toString( $title ); }
            if ( is_array( $content ) ) { $content = toString( $content ); }
            // 返回数据
            return [
                'config' => $config,
                'type' => $type,
                'title' => $title,
                'receive' => $receive,
                'content' => $content,
                'source' => $source,
                'remark' => $remark
            ];
        }
    }