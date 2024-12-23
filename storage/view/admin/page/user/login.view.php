<?php
use application\model\users_login;
use support\middleware\view;

?>
<div id="userLogin">
    <div class="card hasTitle">
        <div class="title">
            <h4><i class="bi-box"></i>{{user_login.control}}</h4>
        </div>
        <button class="button r5" onclick="page.down_all()"><i class="bi-patch-exclamation"></i>{{user_login.down_all}}</button>
        <button class="button r3" onclick="page.down( 'bi-person', 'uid' )"><i class="bi-person"></i>{{user_login.down_uid}}</button>
        <button class="button r3" onclick="page.down( 'bi-menu-button-wide-fill', 'login_ip' )"><i class="bi-menu-button-wide-fill"></i>{{user_login.down_login_ip}}</button>
        <button class="button r3" onclick="page.down( 'bi-phone', 'login_id' )"><i class="bi-phone"></i>{{user_login.down_login_id}}</button>
    </div>
    <?=view::show( 'admin/unit/table', [
        'id' => 'users_login',
        'check' => 'table.check',
        'delete' => 'table.delete',
        'edit' => 'table.edit',
        'words' => [
            'uid' => [ 'width' => '90px' ],
            'type' => [ 'width' => '110px' ],
            'auth' => [ 'width' => '90px' ],
            'enable' => [ 'width' => '90px' ],
            'expired' => [ 'width' => '180px' ],
            'expired_time' => [ 'width' => '100px' ],
            'login_id' => [ 'width' => '160px' ],
            'login_ip' => [ 'width' => '130px' ],
            'updated_at' => [ 'width' => '180px' ]
        ],
        'search' => [ 'uid', 'type', 'auth', 'enable', 'expired', 'expired_time', 'login_id', 'login_ip', 'updated_at' ],
        'sort' =>  [ 'uid', 'type', 'auth', 'enable', 'expired', 'updated_at' ]
    ])?>
</div>
<div class="down uid hidden">
    <?=view::show( 'system/form', [
        'input' => [
            'uid' => [ 'type' => 'number', 'title' => users_login::comment( 'uid' ), 'name' => 'uid', 'rule' => [ 'must' => true, 'min' => 0 ] ]
        ]
    ])?>
</div>
<div class="down login_ip hidden">
    <?=view::show( 'system/form', [
        'input' => [
            'login_ip' => [ 'type' => 'text', 'title' => users_login::comment( 'login_ip' ), 'name' => 'login_ip', 'rule' => [ 'must' => true ] ]
        ]
    ])?>
</div>
<div class="down login_id hidden">
    <?=view::show( 'system/form', [
        'input' => [
            'login_id' => [ 'type' => 'text', 'title' => users_login::comment( 'login_id' ), 'name' => 'login_id', 'rule' => [ 'must' => true ] ]
        ]
    ])?>
</div>
<script>
    page = {
        // 下线所有用户
        down_all: () => {
            unit.popup([ 'bi-patch-exclamation', t('user_login.down_all') ], t('user_login.down_all_text'), {
                text: t('sure'),
                run: 'page.down_all_submit()'
            });
        },
        down_all_submit: () => {
            c.viewLoad( `div#popup div.content` );
            c.send({
                link: '/api/admin/user/down_all',
                post: true,
                check: true,
                run: function() {
                    c.viewLoad( `div#popup div.content`, true );
                    unit.toast(['true',{type:t('operate')}]);
                    table['users_login'].draw();
                    unit.popup(false );
                },
                error: function() { c.viewLoad( `div#popup div.content`, true ); }
            });
        },
        // 指定下线
        down: ( icon, type ) => {
            unit.popup([ icon, t(`user_login.down_${type}`) ], `
                <form id='${type}'>
                    ${$( `div.down.${type}` ).html()}
                </form>
            `, {
                text: t('sure'),
                run: `page.down_submit( '${type}' )`
            });
        },
        down_submit: ( type ) => {
            c.form( `form#${type}`, ( res ) => {
                c.viewLoad( `div#popup div.content` );
                c.send({
                    link: '/api/admin/user/down',
                    post: { type: type, ...res },
                    check: true,
                    run: function() {
                        c.viewLoad( `div#popup div.content`, true );
                        unit.toast(['true',{type:t('operate')}]);
                        table['users_login'].draw();
                        unit.popup(false );
                    },
                    error: function() { c.viewLoad( `div#popup div.content`, true ); }
                });
            });
        }
    };
</script>