<?php
use support\middleware\view;

    echo view::show( view::$project.'/frame', [
        'title' => "{{retrieve.title}}",
        'image' => "/{{project}}/retrieve.jpg"
    ]);
?>
@start['content']

<link href="/{{project}}/retrieve.css?{{v}}" rel="stylesheet" />
<div id="retrieve">
    <form class="retrieve" onsubmit="view.submit( this )">
        <div class="step step1 action">
            <i class="bi-1-square stepIcon"></i>
            <span class="stepTitle">{{retrieve.step1}}</span>
            <?=view::show( 'system/form', [ 'input' => [
                [ 'type' => 'text', 'name' => 'user', 'title' => '{{retrieve.user}}', 'rule' => [ 'must' => true ] ]
            ]])?>
            <button class="button r3" type="button" onclick="view.verify()"><i class="bi-send"></i>{{retrieve.send}}</button>
        </div>
        <hr />
        <div class="step step2">
            <i class="bi-2-square stepIcon"></i>
            <span class="stepTitle">{{retrieve.step2}}</span>
            <?=view::show( 'system/form', [ 'input' => [
                [ 'type' => 'number', 'name' => 'verify', 'title' => '{{item.verify}}', 'rule' => [ 'must' => true ] ],
                [ 'type' => 'password', 'name' => 'password', 'title' => '{{retrieve.password_new}}', 'rule' => [ 'must' => true ] ]
            ]])?>
        </div>
        <hr />
        <div class="step step3">
            <i class="bi-3-square stepIcon"></i>
            <span class="stepTitle">{{retrieve.step3}}</span>
            <div>
                <button class="button r3" type="submit"><i class="bi-check2-circle"></i>{{retrieve.button}}</button>
            </div>
        </div>
    </form>
    <div class="other r0">
        <ul class="control">
            <a href="/{{project}}/login<?=!empty( $_SERVER['QUERY_STRING'] ) ? "?{$_SERVER['QUERY_STRING']}" : $_SERVER['QUERY_STRING']?>">
                <li><i class="bi-person block icon"></i>{{login.button}}<i class="bi-chevron-right block right"></i></li>
            </a>
            <a href="/{{project}}/register<?=!empty( $_SERVER['QUERY_STRING'] ) ? "?{$_SERVER['QUERY_STRING']}" : $_SERVER['QUERY_STRING']?>">
                <li><i class="bi-people block icon"></i>{{register.button}}<i class="bi-chevron-right block right"></i></li>
            </a>
            <a href="/">
                <li class="noBorder"><i class="bi-house block icon"></i>{{item.backHome}}<i class="bi-chevron-right block right"></i></li>
            </a>
        </ul>
    </div>
</div>
<script>
    var view = {
        logout: `<?=$w( $_GET['logout'] )?>`,
        start: () => {
            if ( !empty( view.logout ) ) { c.logout( false, false ); }
            $( document ).on( 'input', 'div#retrieve div.step2 input[type="password"]', function() {
                if ( $( this ).val() ) {
                    $( 'div#retrieve div.step.step3' ).addClass( 'action' );
                }else {
                    $( 'div#retrieve div.step.step3' ).removeClass( 'action' );
                }
            });
        },
        /* 发送验证码 */
        verifyTimeout: 0,
        verifyTime: false,
        verify: () => {
            if ( view.verifyTimeout > 0 ) { return unit.toast( ['register.verifyWait',{s:view.verifyTimeout}], true ); }
            const user = $( 'div#retrieve div.step input[name="user"]' ).val();
            if ( empty( user ) ) { return unit.toast( ['retrieve.noUser'], true ); }
            c.viewLoad( 'div#retrieve div.step1' );
            c.send({
                link: '/api/account/retrieve_verify',
                post: { user: user },
                check: true,
                run: ( res ) => {
                    c.viewLoad( 'div#retrieve div.step1', true );
                    $( 'div#retrieve div.step.step2' ).addClass( 'action' );
                    unit.toast( ['true',{type:t('send')}] );
                    view.verifyTimeout = 60;
                    view.verifyTime = setInterval(() => {
                        view.verifyTimeout = view.verifyTimeout - 1;
                        if ( view.verifyTimeout === 0 ) { clearInterval( view.verifyTime ); }
                    }, 1000 );
                },
                error: () => { c.viewLoad( 'div#retrieve div.step1', true ); }
            });
        },
        /* 提交表单 */
        submit: ( e ) => {
            c.form( e, ( res ) => {
                c.viewLoad( 'div#retrieve div.step' );
                c.send({
                    link: '/api/account/retrieve',
                    post: res,
                    check: true,
                    run: ( res ) => {
                        c.viewLoad( 'div#retrieve div.step', true );
                        unit.toast( ['true',{type:t('save')}] );
                        location.href = "/{{project}}/login<?=!empty( $_SERVER['QUERY_STRING'] ) ? "?{$_SERVER['QUERY_STRING']}" : $_SERVER['QUERY_STRING']?>";
                    },
                    error: () => { c.viewLoad( 'div#retrieve div.step', true ); }
                });
            });
        }
    }
    view.start();
</script>

@end['content']