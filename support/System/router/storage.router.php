<?php

use Support\Handler\Router;

    Router::add( '/upload{{storage?}}', 'POST' )->controller( 'BaseController@upload' )->save();
    Router::add( '/{{storage}}/{{file}}' )->controller( 'BaseController@file' )->save();