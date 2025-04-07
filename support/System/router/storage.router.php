<?php

use Support\Handler\Router;

    Router::add( '/upload{{storage?}}', 'GET' )->controller( 'StorageSupport@upload' )->save();