<?php

use Support\Handler\Router;

    Router::add( '/base/index', 'POST' )->controller( 'BaseController@index' )->save();