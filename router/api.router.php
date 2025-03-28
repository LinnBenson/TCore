<?php

use App\Controller\Test;
use Support\Handler\Router;

    Router::add( '/test' )->group(function(){
        Router::add( '/' )->auth(function(){ return null; })->controller( 'Test@index' )->save();
        Router::add( '/request' )->controller([ Test::class, 'request' ])->save();
        Router::add( '/parameter/{{P1}}{{P2?}}' )->controller( 'Test@parameter' )->save();
    });
