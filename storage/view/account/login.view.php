<?php
use support\middleware\view;

    echo view::show( view::$project.'/frame', [
        'title' => "{{login.title}}",
        'image' => "/{{project}}/login.jpg"
    ]);
?>
@start['content']

<link href="/{{project}}/login.css?{{v}}" rel="stylesheet" />
<div id="login">
    <div class="slogan">
        <h4>{{login.slogan}}</h4>
    </div>
    <div class="type">
        <div class="box center">
            <div class="iconBox" name='email' onclick="view.loginType( 'email' )"><i class="bi-envelope block"></i></div>
            <div class="iconBox" name="phone" onclick="view.loginType( 'phone' )"><i class="bi-phone block"></i></div>
        </div>
    </div>
    <form class="login" onsubmit="view.submit( this )">
        <div class="loginType" name='email'>
            <?=view::show( 'system/form', [ 'input' => [ [ 'type' => 'text', 'name' => 'user', 'title' => '{{item.username_email}}', 'rule' => [] ] ] ])?>
        </div>
        <div class="loginType" name='phone'>
            <?=view::show( 'system/form', [ 'input' => [ [ 'type' => 'phone', 'name' => 'phone', 'title' => '{{item.phone}}', 'rule' => [] ] ] ])?>
        </div>
        <?=view::show( 'system/form', [ 'input' => [ [ 'type' => 'password', 'name' => 'password', 'title' => '{{item.password}}', 'rule' => [ 'must' => true ] ] ] ])?>
        <hr />
        <?=view::show( 'system/form', [  'input' => [ [ 'type' => 'boolean', 'name' => 'remember', 'title' => '{{login.remember}}', 'rule' => [ 'must' => true ] ] ] ])?>
        <hr />
        <button type="submit" class="button r3"><i class="bi-check2-circle"></i>{{login.button}}</button>
    </form>
    <div class="other r0">
        <ul class="control">
            <a href="/{{project}}/register<?=!empty( $_SERVER['QUERY_STRING'] ) ? "?{$_SERVER['QUERY_STRING']}" : $_SERVER['QUERY_STRING']?>">
                <li><i class="bi-people block icon"></i>{{register.button}}<i class="bi-chevron-right block right"></i></li>
            </a>
            <a href="/{{project}}/retrieve<?=!empty( $_SERVER['QUERY_STRING'] ) ? "?{$_SERVER['QUERY_STRING']}" : $_SERVER['QUERY_STRING']?>">
                <li><i class="bi-patch-question block icon"></i>{{retrieve.title}}<i class="bi-chevron-right block right"></i></li>
            </a>
            <a href="/">
                <li class="noBorder"><i class="bi-house block icon"></i>{{item.backHome}}<i class="bi-chevron-right block right"></i></li>
            </a>
        </ul>
    </div>
</div>
<script>
    var view = {
        pass: `<?=$w( $_GET['pass'] )?>`,
        logout: `<?=$w( $_GET['logout'] )?>`,
        start: () => {
            if ( !empty( view.logout ) ) { c.logout( false, false ); }
            view.loginType( 'email' );
        },
        loginType: ( name ) => {
            $( `div.type div.iconBox` ).removeClass( 'action' );
            $( `form.login div.loginType` ).removeClass( 'action' );
            $( `div.type div.iconBox[name="${name}"]` ).addClass( 'action' );
            $( `form.login div.loginType[name="${name}"]` ).addClass( 'action' );
            $( `form.login input[name="user"]` ).val( '' );
            $( `form.login input[name="phone"]` ).val( '' );
        },
        submit: ( e ) => {
            c.form( e, ( res ) => {
                c.viewLoad( 'form.login' );
                c.send({
                    link: '/api/account/login',
                    post: res,
                    check: true,
                    run: ( res ) => {
                        c.viewLoad( 'form.login', true );
                        unit.toast( ['login.success'] );
                        c.login( res, !empty( view.pass ) ? view.pass : '/{{project}}/user' );
                    },
                    error: () => { c.viewLoad( 'form.login', true ); }
                });
            });
        }
    };
    view.start();
</script>

@end['content']