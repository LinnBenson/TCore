<?php

use Support\Handler\Router;

    Router::add( '/' )->url( '/'.Plug( 'TCoreAccount' )->entrance )->save();