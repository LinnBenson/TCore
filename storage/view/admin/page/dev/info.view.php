<?php
    $extensions = get_loaded_extensions();
    $functions = get_defined_functions();
    $classes = get_declared_classes();
?>
<style>
    div#devInfo div.config {
        --minWidth: 300px;
        --gap: 16px;
        display: grid;
        margin-bottom: 16px;
        grid-template-columns: repeat( auto-fit, minmax( var( --minWidth ), 1fr ) );
        grid-gap: var( --gap );
    }
    div#devInfo div.config div.card { margin: 0; }
    div#devInfo div.info p {
        margin-bottom: 4px;
        font-size: 14px;
        color: rgb( var( --r1 ), 0.75 );
    }
    div#devInfo div.info p b {
        margin-right: 8px;
        color: rgb( var( --r1 ) );
    }
    div#devInfo ul.itemList {
        padding: 0;
        list-style: none;
    }
    div#devInfo ul.itemList li {
        display: inline-block;
        padding: 2px 6px;
        margin-bottom: 4px;
        font-size: 12px;
        border-radius: 4px;
    }
</style>
<div id="devInfo">
    <div class="config">
        <div class="card hasTitle info">
            <div class="title">
                <h4>{{dev_info.php}}</h4>
            </div>
            <p><b>System:</b><?=php_uname()?></p>
            <p><b>Version:</b><?=phpversion()?></p>
            <p><b>Timezone:</b><?=date_default_timezone_get()?></p>
            <p><b>Memory Limit:</b><?=ini_get('memory_limit')?></p>
            <p><b>Max Execution Time:</b><?=ini_get('max_execution_time')?></p>
            <p><b>Config:</b><?=php_ini_loaded_file()?></p>
        </div>
        <div class="card hasTitle info">
            <div class="title">
                <h4>{{dev_info.config}}</h4>
            </div>
            <p><b>Name:</b><?=config( 'app.name' )?></p>
            <p><b>Host:</b><?=config( 'app.host' )?></p>
            <p><b>Debug:</b><?=config( 'app.debug' ) ? 'true' : 'false'?></p>
            <p><b>Timezone:</b><?=config( 'app.timezone' )?></p>
            <p><b>Lang:</b><?=config( 'app.lang' )?></p>
            <p><b>Version:</b><?=config( 'app.version' )?></p>
            <p><b>Channel:</b><?=config( 'app.channel' )?></p>
        </div>
    </div>
    <div class="card hasTitle">
        <div class="title">
            <h4>{{dev_info.extensions}}</h4>
        </div>
        <ul class="itemList">
            <?php foreach( $extensions as $name ): ?>
                <li class="r3"><?=$name?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="card hasTitle">
        <div class="title">
            <h4>{{dev_info.functions}}</h4>
        </div>
        <ul class="itemList">
            <?php foreach( $functions['user'] as $name ): ?>
                <li class="r3"><?=$name?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="card hasTitle">
        <div class="title">
            <h4>{{dev_info.classes}}</h4>
        </div>
        <ul class="itemList">
            <?php foreach( $classes as $name ): ?>
                <li class="r3"><?=$name?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>