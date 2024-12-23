<!doctype html>
<html>
    <head>
        <meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
		<meta http-equiv="Cache-Control" content="no-siteapp">
        <!-- Define a web icon -->
		<link rel="icon" href="{{fav}}?{{v}}" type="image/png">
		<link rel="apple-touch-icon" sizes="180x180" href="{{fav}}?{{v}}">
		<link rel="apple-touch-icon-precomposed" href="{{fav}}?{{v}}" />
		<meta name="msapplication-TileImage" content="{{fav}}?{{v}}" />
		<link rel="shortcut icon" href="{{fav}}?{{v}}" />
		<!-- Force use of application mode -->
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-touch-fullscreen" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="default">
		<meta name="full-screen" content="yes">
		<meta name="browsermode" content="application">
		<meta name="x5-fullscreen" content="true">
		<meta name="x5-page-mode" content="app">
        <meta name="theme-color" content="rgb( {{--r0}} )" />
        <!-- Quote common script file -->
        <script src="/library/javascript/jquery-3.7.1.js"></script>
        <script src="/library/javascript/cookie_1.4.1.js"></script>
        <script src="/library/javascript/clipboard_1.6.1.js"></script>
        <script src="/library/javascript/react/react.production.min.js"></script>
        <script src="/library/javascript/react/react-dom.production.min.js"></script>
        <script src="/library/javascript/react/babel.min.js"></script>
        <script src="/library/javascript/simplemde/simplemde.min.js"></script>
        <script src="/library/javascript/marked.min.js"></script>
        <script src="/library/javascript/markdown.js"></script>
        <script src="/library/javascript/core.js?{{v}}"></script>
        <script src="/library/javascript/websocket.js?{{v}}"></script>
        <!-- Generate variable code -->
        <style>
            :root{--r0:{{--r0}};--r1:{{--r1}};--r2:{{--r2}};--r2c:{{--r2c}};--r3:{{--r3}};--r3c:{{--r3c}};--r4:{{--r4}};--r4c:{{--r4c}};--r5:{{--r5}};--r5c:{{--r5c}};--r6:{{--r6}};}
        </style>
        <script>
            window['sendConfig'] = { version: '{{v}}', project: '{{project}}', theme: '{{_theme}}', lang: '{{_lang}}' };
            c.send({ link: '/api/base/lang/{{project}}?{{v}}', async: false, run: ( res ) => { window['text'] = res; } });
            c.update();
        </script>
        <!-- Reference a common style file -->
		<link href="/library/icon/bootstrap/bootstrap-icons.min.css" rel="stylesheet" />
        <link href="/library/javascript/simplemde/simplemde.min.css" rel="stylesheet" />
		<link href="/library/style/core.css?{{v}}" rel="stylesheet" />
		<link href="/library/style/markdown.css?{{v}}" rel="stylesheet" />
        <!-- END -->
        <title><?=$val['title']?> - {{_app.name}}</title>
<?php if ( !empty( $val['seo_key'] ) ): ?>
        <meta name="keywords" content="<?=$val['seo_key']?>" id="metakeywords">
<?php endif; ?>
<?php if ( !empty( $val['seo_des'] ) ): ?>
        <meta name="description" content="<?=$val['seo_des']?>" id="metadesc">
        <meta property="og:description" content="<?=$val['seo_des']?>" />
<?php endif; ?>
        <meta property="og:url" content="<?=!empty( $val['seo_url'] ) ? $val['seo_url'] : '{{_app.host}}'.parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH )?>" />
        <meta property="og:image" content="<?=!empty( $val['seo_img'] ) ? $val['seo_img'] : '{{fav}}?{{v}}'?>" />
        <meta property="og:title" content="<?=!empty( $val['seo_title'] ) ? $val['seo_title'] : "{$val['title']} - {{_app.name}}"?>" />
    </head>
    <body>
        @val['body']
    </body>
</html>