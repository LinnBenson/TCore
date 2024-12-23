<?php
    $logs = config( 'log' );
?>
<style>
    div#devLog div.show {
        position: relative;
    }
    div#devLog div.show textarea {
        width: 100% !important;
        height: 540px;
        padding: 16px;
        border-radius: 8px;
        box-sizing: border-box;
    }
</style>
<div id="devLog">
    <div class="card hasTitle">
        <div class="title">
            <h4><i class="bi-file-earmark"></i>{{dev_log.title}}</h4>
        </div>
        <?php foreach( $logs as $name => $con ): ?>
            <button class="button r3" onclick="page.select( '<?=$name?>' )"><?=$name?></button>
        <?php endforeach; ?>
    </div>
    <div class="card">
        {{dev_log.current}} : <span name="current">NULL</span>
        <hr style="--margin: 16px"/>
        <button class="button r5" onclick="page.clear()"><i class="bi-trash3"></i>{{dev_log.clear}}</button>
    </div>
    <div class="show">
        <textarea class="code"></textarea>
    </div>
</div>
<script>
    page = {
        name: '',
        select: ( name ) => {
            page.name = name;
            c.text( 'current', name );
            c.viewLoad( 'div#devLog div.show' );
            c.send({
                link: '/api/admin/setting/log',
                post: { name: name },
                check: true,
                run: ( res ) => {
                    c.viewLoad( 'div#devLog div.show', true );
                    $textarea = $( 'div#devLog div.show textarea' );
                    $textarea.val( res );
                    $textarea.animate({ scrollTop: $textarea[0].scrollHeight }, 200);
                },
                error: () => { c.viewLoad( 'div#devLog div.show', true ); }
            });
        },
        clear: () => {
            c.viewLoad( 'div#devLog div.show' );
            c.send({
                link: '/api/admin/setting/log_clear',
                post: { name: page.name },
                check: true,
                run: ( res ) => {
                    c.viewLoad( 'div#devLog div.show', true );
                    $textarea = $( 'div#devLog div.show textarea' );
                    $textarea.val( '' );
                    $textarea.animate({ scrollTop: $textarea[0].scrollHeight }, 200);
                },
                error: () => { c.viewLoad( 'div#devLog div.show', true ); }
            });
        }
    };
</script>