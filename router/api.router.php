<?php
    // 管理员面板
    router::add( '/admin' )->auth(function(){ return task::$user->authLevel([ 600, 1000 ]); })->children(function( $e ) {
        router::add( '/user/*', 'POST', $e )->controller( 'admin/user' )->save();
        router::add( '/table/*', 'POST', $e )->controller( 'admin/table' )->save();
        router::add( '/push/*', 'POST', $e )->controller( 'admin/push' )->save();
        router::add( '/article/*', 'POST', $e )->controller( 'admin/article' )->save();
        router::add( '/setting/*', 'any', $e )->controller( 'admin/setting' )->save();
        router::add( '/storage/*', 'any', $e )->controller( 'admin/storage' )->start( 4 )->save();
        router::add( '/service/*', 'POST', $e )->controller( 'admin/service' )->auth(function(){ return task::$user->authLevel([ 1000, 1000 ]); })->save();
    });
    // 基础信息
    router::add( '/base', 'any' )->controller( 'api/base', 'index' )->children(function( $e ) {
        router::add( '/lang/*', 'GET', $e )->controller( 'api/base', 'lang' )->start( 3 )->save();
    });
    // 账户管理
    router::add( '/account', 'any' )->children(function( $e ) {
        router::add( '/login', 'POST', $e )->controller( 'api/account', 'login' )->save();
        router::add( '/register', 'POST', $e )->controller( 'api/account', 'register' )->save();
        router::add( '/verify', 'POST', $e )->controller( 'api/account', 'verify' )->save();
        router::add( '/retrieve_verify', 'POST', $e )->controller( 'api/account', 'retrieve_verify' )->save();
        router::add( '/edit', 'POST', $e )->controller( 'api/account', 'edit' )->save();
        router::add( '/bind', 'POST', $e )->controller( 'api/account', 'bind' )->save();
        router::add( '/safety', 'POST', $e )->controller( 'api/account', 'safety' )->save();
        router::add( '/retrieve', 'POST', $e )->controller( 'api/account', 'retrieve' )->save();
    });
    // 存储管理
    router::add( '/storage', 'any' )->children(function( $e ) {
        router::add( '/upload/*', 'POST', $e )->controller( 'api/storage', 'upload' )->start( 3 )->save();
        router::add( '/cache/*', 'GET', $e )->controller( 'api/storage', 'cache' )->start( 3 )->save();
        router::add( '/media/*', 'GET', $e )->controller( 'api/storage', 'media' )->start( 3 )->save();
        router::add( '/avatar/*', 'GET', $e )->controller( 'api/storage', 'avatar' )->start( 3 )->save();
    });