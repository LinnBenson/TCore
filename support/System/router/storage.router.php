<?php
    use Support\Handler\Router;

    /**
     * 上传文件
     */
    Router::add( '/upload/{{name}}' )->controller( 'StorageController@upload' )->save();
    /**
     * 获取文件
     */
    Router::add( '/file/{{storage}}/{{file}}' )->controller( 'StorageController@file' )->save();
    /**
     * 获取用户头像
     */
    Router::add( '/avatar/{{uid}}' )->controller( 'StorageController@avatar' )->save();