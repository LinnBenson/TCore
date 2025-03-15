<?php

namespace Support\Handler;

use Support\Helper\Tool;

    /**
     * 日志处理
     */
    class Log {
        private $config, $logFile;
        /**
         * 构造函数
         * new Log( $file|false:string(日志位置) )
         */
        public function __construct( $config ) {
            if ( is_string( $config ) ) { $config = config( "log.{$config}" ); }
            if ( !is_array( $config ) || empty( $config['path'] ) || empty( $config['file'] ) ) {
                $config = config( 'Bootstrap' );
            }
            $this->logFile = $config['path'].$config['file'];
            Tool::inFolder( $this->logFile );
            $this->config = $config;
        }
        /**
         * 写入日志
         * - $Log->write( $text:string|array|object(日志内容) )
         * - return boolean(写入结果)
         */
        private function record( $text, $type = 'INFO' ) {
            $title = '';
            $file = str_replace( '{{date}}', date( 'Ymd' ), $this->logFile );;
            // 传入为两个值数组时认为是带标题的日志
            if ( is_array( $text ) && count( $text ) === 2 && is_string( $text[0] ) ) {
                $title = $text[0]; $text = $text[1];
            }
            // 传入为对象时认为是异常对象
            if ( is_object( $text ) && method_exists( $text, 'getMessage' ) ) {
                $text = $text->getMessage();
            }
            // 再次检查是否为字符串
            if ( !is_string( $text ) ) { $text = print_r( $text, true ); }
            // 准备写入内容
            $title = $title ? "[{$title}] " : '';
            $text = date('Y-m-d H:i:s')." {$type} | {$title}{$text}\n";
            // 写入日志
            if ( file_exists( $file ) ) {
                if ( filesize( $file ) < $this->config['maxSize'] ) {
                    return file_put_contents( $file, $text, FILE_APPEND|LOCK_EX );
                }
            }
            // 其它情况直接覆盖或创建文件
            return file_put_contents( $file, $text );
        }
        /**
         * 便捷写入日志
         * - $Log->info( $text:string(日志内容) )
         * - $Log->debug( $text:string(日志内容) )
         * - $Log->error( $text:string(日志内容) )
         * - $Log->warning( $text:string(日志内容) )
         * - $Log->notice( $text:string(日志内容) )
         * - return boolean(写入结果)
         */
        public function info( $text ) { return $this->record( $text, 'INFO' ); }
        public function debug( $text ) { return $this->record( $text, 'DEBUG' ); }
        public function error( $text ) { return $this->record( $text, 'ERROR' ); }
        public function warning( $text ) { return $this->record( $text, 'WARNING' ); }
        public function notice( $text ) { return $this->record( $text, 'NOTICE' ); }
        /**
         * 便捷写入日志
         * - Log::to( $config:string(配置名) )
         * - return boolean(写入结果)
         */
        public static function to( $config ) { return new Log( $config ); }
    }