<?php
use application\model\router_record;
use application\server\serviceServer;
use Illuminate\Support\Carbon;
use support\middleware\view;

    $totalAccess = router_record::count();
    $todayAccess = router_record::whereDate( 'updated_at', Carbon::today() )->count();
    $totalSuccessAccess = router_record::where( 'result', 'success' )->count();
    $totalFailAccess = router_record::where( 'result', 'fail' )->count();
    $todayFailAccess = router_record::where( 'result', 'fail' )->whereDate( 'updated_at', Carbon::today() )->count();

    $is_function_disabled = function( $function_name ) {
        $disabled_functions = explode(',', ini_get('disable_functions'));
        $disabled_functions = array_map('trim', $disabled_functions);
        return in_array($function_name, $disabled_functions);
    };
    $expand = [ 'fileinfo', 'redis', 'imagick', 'imap', 'exif', 'ssh2' ];
    $functions = [
        'stream_socket_server', 'stream_socket_client', 'pcntl_signal_dispatch', 'pcntl_signal', 'pcntl_alarm', 'pcntl_fork',
        'posix_getuid', 'posix_getpwuid', 'posix_kill', 'posix_setsid', 'posix_getpid', 'posix_getpwnam', 'posix_getgrnam',
        'posix_getgid', 'posix_setgid', 'posix_initgroups', 'posix_setuid', 'posix_isatty', 'proc_open', 'proc_get_status',
        'proc_close', 'shell_exec', 'exec', 'putenv', 'getenv'
    ];
    $service = serviceServer::getAllService();
?>
<style>
    div#home div.environment p {
        margin-bottom: 8px;
    }
    div#home div.environment p span.name {
        font-weight: bold;
    }
    div#home div.environment p span.pass { color: rgb( var( --r4 ) ); }
    div#home div.environment p span.failed { color: rgb( var( --r5 ) ); }
    div#home div.environment p span.item {
        margin-right: 4px;
        margin-bottom: 4px;
    }
    div#home div.accessingData div.pie {
        float: left;
        width: 320px;
        height: 200px;
        text-align: center;
    }
    div#home div.accessingData div.info {
        width: calc( 100% - 320px );
        height: 200px;
        margin-left: 320px;
        box-sizing: border-box;
    }
    div#home div.accessingData div.info div {
        width: 100%;
        text-align: left;
    }
    div#home div.accessingData div.info h5 {
        margin-bottom: 8px;
    }
    div#home div.accessingData div.info p {
        font-size: 14px;
    }
    div#home div.accessingData div.info button {
        padding: 3px 8px;
        margin-top: 8px;
        font-size: 14px;
    }
    @media ( max-width: 600px ) {
        div#home div.accessingData div.pie {
            float: none;
            width: 100%;
        }
        div#home div.accessingData div.info {
            width: 100%;
            height: auto;
            margin: 0;
            margin-top: 16px;
        }
        div#home div.accessingData div.info div {
            text-align: center;
        }
    }
    div#home div.welcome {
        margin-bottom: 160px;
    }
</style>
<script language="javascript" type="text/javascript" src="/library/javascript/flot/jquery.flot.js"></script>
<script language="javascript" type="text/javascript" src="/library/javascript/flot/jquery.flot.pie.js"></script>
<div id="home">
    <div class="welcome">
        <h3>Hello, <?=$user->info['nickname']?>!</h3>
        <p>{{home.welcome1}} {{_app.name}} {{home.welcome2}}</p>
        <hr />
        <a class="button r3" href="#view=/user"><i class="bi-person-square"></i>{{home.user}}</a>
        <button class="button r5" onclick="c.logout()"><i class="bi-box-arrow-left"></i>{{page.logout}}</button>
    </div>
    <div class="card environment hasTitle">
        <div class="title">
            <h4><i class="bi-display"></i>{{home.environment}}</h4>
        </div>
        <p><span class="name">{{home.version}} : </span><?=PHP_VERSION?> ( Min: 7.4, <?=version_compare( PHP_VERSION, '7.4', '>' ) ? '<span class="pass">Pass</span>' : '<span class="failed">Failed</span>'?> )</p>
        <p>
            <span class="name">{{home.expand}} : </span>
            <?php foreach( $expand as $name ): ?>
                <?php if( extension_loaded( $name ) ): ?>
                    <span class="item pass"><?=$name?></span>
                <?php else: ?>
                    <span class="item failed"><?=$name?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </p>
        <p>
            <span class="name">{{home.functions}} : </span>
            <?php foreach( $functions as $name ): ?>
                <?php if( !$is_function_disabled( $name ) ): ?>
                    <span class="item pass"><?=$name?></span>
                <?php else: ?>
                    <span class="item failed"><?=$name?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </p>
        <p><span class="name">Async Service : </span><?=$service['async']['state'] ? '<span class="pass">Running</span>' : '<span class="failed">Stopping</span>'?></p>
    </div>
    <div class="card accessingData hasTitle">
        <div class="title">
            <h4><i class="bi-circle-square"></i>{{home.accessingData}}</h4>
        </div>
        <div class="pie"></div>
        <div class="info center">
            <div>
                <h5>{{home.accessingTitle}}</h5>
                <p>{{home.totalAccess}} : <?=$totalAccess?></p>
                <p>{{home.todayAccess}} : <?=$todayAccess?></p>
                <p>{{home.totalFailAccess}} : <?=$totalFailAccess?></p>
                <p>{{home.todayFailAccess}} : <?=$todayFailAccess?></p>
                <a href="#view=/access"><button class="button r3">{{home.accessin}}</button></a>
            </div>
        </div>
    </div>
</div>
<script>
    page = {
        // 初始化
        start: () => {
            page.accessingData();
        },
        labelFormatter: (label, series) => {
            return `<div style='font-size:8pt; text-align:center; padding:2px; color:white;'>${label}<br/>${Math.round( series.percent )}%</div>`;
        },
        // 渲染访问数据
        accessingData: () => {
            $.plot( 'div.accessingData div.pie', [
                { label: "Success",  data: <?=$totalSuccessAccess?> },
                { label: "Fail",  data: <?=$totalFailAccess?> }
            ], {
                series: {
                    pie: {
                        show: true,
                        radius: 1,
                        label: {
                            show: true,
                            radius: 3/4,
                            formatter: page.labelFormatter,
                            background: {
                                opacity: 0.5
                            }
                        }
                    }
                },
                legend: { show: false }
            });
        },
    };
    page.start();
</script>