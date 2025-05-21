<?php

use Support\Handler\Router;

    // 系统基础信息
    Router::add( '/base/index', 'POST' )->controller( 'BaseController@index' )->save();