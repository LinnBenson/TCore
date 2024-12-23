<?php
use support\middleware\view;

    echo view::show( 'system/body', [
        'title' => "{$w( $val['code'] )} Error"
    ]);
?>
@start['body']
        <style>
            body main {
                height: var( --vh );
            }
            body main div.content {
                font-size: 22px;
                font-weight: bold;
            }
        </style>
        <main class="center">
            <div class="content"><?=$w( $val['code'] )?> | ERROR</div>
        </main>
        <script>
            unit.toast( '<?=$w( $val['content'] )?>', true );
        </script>
@end['body']