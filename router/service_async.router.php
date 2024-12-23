<?php
    router::add( 'onWorkerStart' )->controller( 'service/base', 'onWorkerStart' )->save();
    router::add( 'onWorkerStart_link' )->controller( 'service/async/task' )->save();
    router::add( 'onConnect' )->controller( 'service/base', 'onConnect' )->save();
    router::add( 'onMessage' )->controller( 'service/base', 'onMessage' )->save();
    router::add( 'onClose' )->controller( 'service/base', 'onClose' )->save();
    // 用于测试
    router::add( 'system_print/*' )->controller( 'service/print' )->save();
    // 推送消息
    router::add( 'system_send' )->controller( 'service/base', 'send' )->save();
    // 接收来自 CORE 的命令
    router::add( 'send_channel' )->controller( 'service/base', 'send_channel' )->save();
    router::add( 'system_update' )->controller( 'service/async/task', 'update' )->save();
    // 推送命令
    router::add( 'system_push_email' )->controller( 'service/async/push', 'email' )->save();
    router::add( 'system_push_bark' )->controller( 'service/async/push', 'bark' )->save();
    router::add( 'system_push_telegram' )->controller( 'service/async/push', 'telegram' )->save();