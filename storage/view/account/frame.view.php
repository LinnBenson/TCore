<?php
use support\middleware\view;

    echo view::show( 'system/body', [
        'title' => $val['title']
    ]);
?>
@start['body']

<link href="/{{project}}/frame.css?{{v}}" rel="stylesheet" />
<div id="body" style="
    background: url( '<?=$val['image']?>?{{v}}' );
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
">
    <div id="bodyBackground" class="scroll">
        <div id="bodyContent">
            <header style="
                background: url( '<?=$val['image']?>?{{v}}' );
                background-size: cover;
                background-repeat: no-repeat;
                background-position: center;
            ">
                <a href="/">
                    <img class="logo" src="{{logo}}?{{v}}" title="{{_app.name}}" alt="logo" />
                </a>
                <div class="title">
                    <i class="bi-info-circle-fill"></i><?=$val['title']?>
                </div>
            </header>
            <main>
                @val['content']
            </main>
            <footer class="center">
                <div class="footerContent">
                    © <?=date( 'Y' )?> {{_app.name}}. All Rights Reserved.
                </div>
            </footer>
        </div>
    </div>
</div>

@end['body']