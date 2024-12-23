<?php
    router::add( 'onWorkerStart' )->controller( 'service/base', 'onWorkerStart' )->save();
    router::add( 'onConnect' )->controller( 'service/base', 'onConnect' )->save();
    router::add( 'onMessage' )->controller( 'service/base', 'onMessage' )->save();
    router::add( 'onClose' )->controller( 'service/base', 'onClose' )->save();
    // 用于测试
    router::add( 'system_print/*' )->controller( 'service/print' )->save();
    // 推送消息
    router::add( 'system_send' )->controller( 'service/base', 'send' )->save();
    // 获取当前用户信息
    router::add( 'user' )->controller( 'service/base', 'user' )->save();