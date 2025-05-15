<html lang="">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="Cache-Control" content="no-siteapp">
        <title><?=config( 'app.title' )?> by TCore</title>
        <style>
            * { margin: 0; padding: 0; font-size: 15px; }
            a { color: rgb( 164, 171, 184 ); }
            p { margin-bottom: 4px; }
            hr {
                width: 100%;
                height: 1px;
                margin: 16px 0px;
                background: rgb( 164, 171, 184 );
                border: none;
            }
            table * { color: rgb( 164, 171, 184 ); }
            table tr td:first-child { width: 100px; padding-right: 8px; text-align: right; font-weight: bold; }
            div#content {
                display: flex;
                width: 100%;
                height: 100vh;
                align-items: center;
                justify-content: center;
                -webkit-display: flex;
                -webkit-align-items: center;
                -webkit-justify-content: center;
            }
            div.body {
                width: calc( 100vw - 16px );
                max-width: 800px;
                height: 75vh;
                max-height: 410px;
                padding: 24px;
                background: rgb( 40, 44, 52 );
                color: rgb( 164, 171, 184 );
                border-radius: 16px;
                box-sizing: border-box;
            }
            div.body h1 {
                margin: 0px;
                margin-top: 8px;
                font-size: 24px;
            }
            div.body div.card {
                padding: 16px;
                margin-bottom: 8px;
                background: rgb( 164, 171, 184, 0.24 );
                border-radius: 8px;
            }
        </style>
    </head>
    <body>
        <div id="content">
            <div class="body">
                <h1>TCore Version <?=config( 'app.version' )?></h1>
                <hr />
                <div class="card">
                    <p>Lightweight and portable PHP development framework</p>
                </div>
                <div class="card">
                    <table>
                        <tr>
                            <td>ID</td>
                            <td><?=$request->id?>/a></td>
                        </tr>
                        <tr>
                            <td>Host</td>
                            <td><a href="<?=config( 'app.host' )?>" target="_blank"><?=config( 'app.host' )?></a></td>
                        </tr>
                        <tr>
                            <td>Debug</td>
                            <td><?=config( 'app.debug' ) ? 'true' : 'false'?></td>
                        </tr>
                        <tr>
                            <td>Language</td>
                            <td><?=config( 'app.lang' )?></td>
                        </tr>
                        <tr>
                            <td>Timezone</td>
                            <td><?=config( 'app.timezone' )?></td>
                        </tr>
                        <tr>
                            <td>Github</td>
                            <td><a href="https://github.com/LinnBenson/TCore" target="_blank">https://github.com/LinnBenson/TCore</a></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>