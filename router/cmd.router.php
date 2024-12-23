<?php
    router::add( '/' )->controller( 'cmd/base' )->save();
    router::add( '/service/*' )->controller( 'cmd/service' )->start( 3 )->save();
    router::add( '/manage/*' )->controller( 'cmd/manage' )->start( 3 )->save();