<?php

use Support\Handler\Router;

    Router::add( '/test' )->controller( 'Test@index' )->save();