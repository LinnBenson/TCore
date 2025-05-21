<?php

use Support\Handler\Router;

    // 上传文件
    Router::add( '/upload{{storage?}}', 'POST' )->controller( 'BaseController@upload' )->save();
    // 访问验证码
    Router::add( '/verify/{{name}}' )->controller( 'BaseController@verify' )->save();
    // 访问存储器
    Router::add( '/{{storage}}/{{file}}' )->controller( 'BaseController@file' )->save();