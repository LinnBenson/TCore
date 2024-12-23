<?php
use support\middleware\view;

    echo view::show( view::$project.'/frame', [
        'title' => "{{register.title}}",
        'image' => "/{{project}}/register.jpg"
    ]);
?>
@start['content']

<link href="/{{project}}/register.css?{{v}}" rel="stylesheet" />
<div id="register">
    <div class="icon">
        <img src="/{{project}}/register_icon.svg?{v}" alt="icon" />
        <h4>{{register.slogan}}</h4>
    </div>
    <hr />
    <form class="register" onsubmit="view.submit( this )">
        <?php
            $invite = config( 'user.invite' ) ? '*' : '';
            $form = [
                'username' => [ 'type' => 'username', 'name' => 'username', 'title' => '{{item.username}}', 'rule' => [ 'must' => true, 'min' => 4, 'max' => 12 ] ],
                'email' => [ 'type' => 'email', 'name' => 'email', 'title' => '{{item.email}}', 'rule' => [ 'must' => true ] ],
                'password' => [ 'type' => 'password', 'name' => 'password', 'title' => '{{item.password}}', 'rule' => [ 'must' => true ] ],
                'invite' => [ 'type' => 'text', 'name' => 'invite', 'title' => "{{item.invite}}{$invite}", 'value' => $_GET['invite'], 'rule' => [ 'must' => config( 'user.invite' ) ] ],
            ];
            // 是否启用邮箱验证
            if ( config( 'user.verify.email' ) ) {
                $new = [];
                foreach( $form as $key => $value ) {
                    $new[$key] = $value;
                    if ( $key === 'email' ) {
                        $new['verify'] = [ 'type' => 'number', 'name' => 'verify', 'title' => '{{item.verify}}', 'right' => [ 'icon' => 'bi-send', 'action' => 'view.verify' ], 'rule' => [ 'must' => true ] ];
                    }
                }
                $form = $new;
            }
            // 输出表单
            echo view::show( 'system/form', ['input' => $form ]);
        ?>
        <button type="submit" class="button r3"><i class="bi-check2-circle"></i>{{register.button}}</button>
    </form>
    <div class="other r0">
        <ul class="control">
            <a href="/{{project}}/login<?=!empty( $_SERVER['QUERY_STRING'] ) ? "?{$_SERVER['QUERY_STRING']}" : $_SERVER['QUERY_STRING']?>">
                <li><i class="bi-person block icon"></i>{{login.button}}<i class="bi-chevron-right block right"></i></li>
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
        logout: `<?=$w( $_GET['logout'] )?>`,
        start: () => {
            if ( !empty( view.logout ) ) { c.logout( false, false ); }
        },
        /* 发送验证码 */
        verifyTimeout: 0,
        verifyTime: false,
        verify: () => {
            if ( view.verifyTimeout > 0 ) { return unit.toast( ['register.verifyWait',{s:view.verifyTimeout}], true ); }
            const email = $( 'form.register input[name="email"]' ).val();
            if ( empty( email ) ) { return unit.toast( ['register.noEmail'], true ); }
            c.viewLoad( 'form.register' );
            c.send({
                link: '/api/account/verify',
                post: { email: email },
                check: true,
                run: ( res ) => {
                    c.viewLoad( 'form.register', true );
                    unit.toast( ['true',{type:t('send')}] );
                    view.verifyTimeout = 60;
                    view.verifyTime = setInterval(() => {
                        view.verifyTimeout = view.verifyTimeout - 1;
                        if ( view.verifyTimeout === 0 ) { clearInterval( view.verifyTime ); }
                    }, 1000 );
                },
                error: () => { c.viewLoad( 'form.register', true ); }
            });
        },
        submit: ( e ) => {
            c.form( e, ( res ) => {
                c.viewLoad( 'form.register' );
                c.send({
                    link: '/api/account/register',
                    post: res,
                    check: true,
                    run: ( res ) => {
                        c.viewLoad( 'form.register', true );
                        unit.toast( ['register.success'] );
                        location.href = "/{{project}}/login<?=!empty( $_SERVER['QUERY_STRING'] ) ? "?{$_SERVER['QUERY_STRING']}" : $_SERVER['QUERY_STRING']?>";
                    },
                    error: () => { c.viewLoad( 'form.register', true ); }
                });
            });
        }
    };
    view.start();
</script>

@end['content']