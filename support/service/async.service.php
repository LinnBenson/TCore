<?php
    /**
     * 运行核心
     */
    require_once 'loader/core.loader.php';
    new core(function() {
        import( 'loader/service.loader.php' );
        return task::start( 'async' );
    });
?>