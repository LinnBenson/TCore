<?php

use Support\Handler\Router;

    Router::add( '/' )->controller( 'ShellController@menu' )->save();