<html lang="<?=config( 'app.lang' )?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="Cache-Control" content="no-siteapp">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-touch-fullscreen" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="full-screen" content="yes">
        <meta name="browsermode" content="application">
        <meta name="x5-fullscreen" content="true">
        <meta name="x5-page-mode" content="app">
        <title>Error: <?=$code?> | Unable to access this content.</title>
        <style>
            * {
                padding: 0px;
                margin: 0px;
            }
            body {
                display: flex;
                width: 100vw;
                height: 100vh;
                background: rgb( 40, 44, 52 );
                font-size: 15px;
                color: rgb( 171, 178, 191 );
                align-items: center;
                justify-content: center;
                -webkit-display: flex;
                -webkit-align-items: center;
                -webkit-justify-content: center;
            }
            p { margin-bottom: 8px; }
            body main {
                width: 100%;
                max-width: 800px;
                margin:  0px auto;
                margin-bottom: 180px;
                padding: 20px;
                text-align: center;
                box-sizing: border-box;
            }
            main h1 {
                margin-bottom: 16px;
                font-size: 42px;
            }
            main hr {
                display: block;
                width: 100%;
                height: 0px;
                padding: 0px;
                margin: 20px 0px;
                border-bottom: 1px dashed rgb( 171, 178, 191 );
            }
            main a[button] {
                display: inline-block;
                padding: 4px 16px;
                background: rgb( 171, 178, 191, 0.75 );
                color: rgb( 40, 44, 52 );
                text-decoration: none;
                border-radius: 4px;
            }
            main a[button]:hover { background: rgb( 171, 178, 191 ); }
            main p.msg {
                max-width: 400px;
                margin: 0px auto;
                margin-bottom: 160px;
            }
        </style>
    </head>
    <body>
        <main>
            <h1>Error | <?=$code?></h1>
            <p class="msg"><?=$msg?></p>
            <a href="" button><?=__( 'base.refresh' )?> Refresh</a>
            <a href="/" button><?=__( 'base.back' )?> Back</a>
            <hr />
            <p class="copyright">Copyright © <?=date( 'Y' )?> <?=config( 'app.title' )?></p>
        </main>
    </body>
</html>