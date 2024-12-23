<?php
use application\model\users;
use application\model\users_login;
use Illuminate\Support\Carbon;
use support\middleware\view;

    $total = users::count();
    $userTotal = users::whereIn( 'status', ['vip', 'approve', 'user'] )->count();
    $userToday = users::whereIn( 'status', ['vip', 'approve', 'user'] )->whereDate( 'created_at', Carbon::today() )->count();
    $toadyAction = users_login::whereDate( 'updated_at', Carbon::today() )->distinct( 'uid' )->count();
?>
<style>
    div#userList div.info {
        --minWidth: 90px;
        --gap: 0px;
        grid-template-columns: repeat( auto-fit, minmax( var( --minWidth ), 1fr ) );
    }
    div#userList div.info div.children {
        text-align: center;
        border-right: 1px dashed rgb( var( --r1 ), 0.5 );
    }
    div#userList div.info div.children:last-of-type { border: none; }
    div#userList div.info div.children p.number {
        margin-top: 8px;
        font-size: 18px;
        font-weight: bold;
        color: rgb( var( --r4 ) );
    }
    @media ( max-width: 720px ) {
        div#userList div.info div.children:first-of-type { display: none; }
    }
</style>
<div id="userList">
    <div class="info card grid">
        <div class="children">
            <p>{{user_list.total}}</p>
            <p class="number"><?=$total?></p>
        </div>
        <div class="children">
            <p>{{user_list.userTotal}}</p>
            <p class="number"><?=$userTotal?></p>
        </div>
        <div class="children">
            <p>{{user_list.userToday}}</p>
            <p class="number"><?=$userToday?></p>
        </div>
        <div class="children">
            <p>{{user_list.toadyAction}}</p>
            <p class="number"><?=$toadyAction?></p>
        </div>
    </div>
    <div class="card hasTitle">
        <div class="title">
            <h4><i class="bi-box"></i>{{user_list.operate}}</h4>
        </div>
        <button class="button r3" onclick="page.add_virtual()"><i class="bi-person-plus"></i>{{user_list.virtual}}</button>
        <button class="button r3" onclick="page.edit_config()"><i class="bi-command"></i>{{user_list.edit_config}}</button>
    </div>
    <?=view::show( 'admin/unit/table', [
        'id' => 'users',
        'check' => 'table.check',
        'delete' => 'table.delete',
        'edit' => 'table.edit',
        'words' => [
            'id' => [ 'width' => '90px' ],
            'username' => [ 'width' => '130px' ],
            'email' => [ 'width' => '200px' ],
            'phone' => [ 'width' => '200px' ],
            'nickname' => [ 'width' => '170px' ],
            'status' => [ 'width' => '100px' ],
            'enable' => [ 'width' => '90px' ],
            'invite' => [ 'width' => '130px' ],
            'agent' => [ 'width' => '130px' ],
            'created_at' => [ 'width' => '180px' ]
        ],
        'search' => [ 'id', 'username', 'email', 'phone', 'status', 'enable', 'invite', 'agent', 'created_at' ],
        'sort' => [ 'id', 'status', 'enable', 'agent', 'created_at' ]
    ])?>
    <div class="add_virtual hidden">
        <?=view::show( 'system/form', [
            'input' => [
                'username' => [ 'type' => 'username', 'name' => 'username', 'title' => users::comment( 'username' ), 'rule' => [ 'must' => true, 'min' => 4, 'max' => 12 ] ],
                'email' => [ 'type' => 'email', 'name' => 'email', 'title' => users::comment( 'email' ), 'rule' => [ 'must' => true ] ],
                'phone' => [ 'type' => 'phone', 'name' => 'phone', 'title' => users::comment( 'phone' ), 'rule' => [ ] ],
                'password' => [ 'type' => 'password', 'name' => 'password', 'title' => users::comment( 'password' ), 'rule' => [ 'must' => true ] ],
                'agent' => [ 'type' => 'number', 'name' => 'agent', 'title' => users::comment( 'agent' ), 'rule' => [] ],
            ]
        ])?>
    </div>
    <div class="edit_config hidden">
        <?=view::show( 'system/form', [
            'input' => [
                'allow_login' => [ 'type' => 'boolean', 'name' => 'allow_login', 'title' => '{{user_list.allow_login}}', 'value' => config( 'user.allow.login' ), 'rule' => [] ],
                'allow_register' => [ 'type' => 'boolean', 'name' => 'allow_register', 'title' => '{{user_list.allow_register}}', 'value' => config( 'user.allow.register' ), 'rule' => [] ],
                'verify_email' => [ 'type' => 'boolean', 'name' => 'verify_email', 'title' => '{{user_list.verify_email}}', 'value' => config( 'user.verify.email' ), 'rule' => [] ],
                'verify_phone' => [ 'type' => 'boolean', 'name' => 'verify_phone', 'title' => '{{user_list.verify_phone}}', 'value' => config( 'user.verify.phone' ), 'rule' => [] ],
                'invite' => [ 'type' => 'boolean', 'name' => 'invite', 'title' => '{{user_list.invite}}', 'value' => config( 'user.invite' ), 'rule' => [] ],
                'expired' => [ 'type' => 'number', 'name' => 'expired', 'title' => '{{user_list.expired}}', 'value' => config( 'user.expired' ), 'rule' => [ 'must' => true ] ],
            ]
        ])?>
    </div>
</div>
<script>
    page = {
        add_virtual: function() {
            unit.popup( ['bi-person-plus', t('user_list.virtual')], `
                <form id='add_virtual'>
                    ${$( 'div.add_virtual' ).html()}
                </form>
            `, {
                text: t('save'),
                run: 'page.add_virtual_submit()'
            });
        },
        add_virtual_submit: function() {
            c.form( 'form#add_virtual', ( res ) => {
                c.viewLoad( `div#popup div.content` );
                c.send({
                    link: '/api/admin/user/add_virtual',
                    post: res,
                    check: true,
                    run: function() {
                        c.viewLoad( `div#popup div.content`, true );
                        unit.toast(['true',{type:t('create')}]);
                        table['users'].draw();
                        unit.popup(false );
                    },
                    error: function() { c.viewLoad( `div#popup div.content`, true ); }
                });
            });
        },
        edit_config: function() {
            unit.popup( ['bi-command', t('user_list.edit_config')], `
                <form id='edit_config'>
                    ${$( 'div.edit_config' ).html()}
                </form>
            `, {
                text: t('save'),
                run: 'page.edit_config_submit()'
            });
        },
        edit_config_submit: function() {
            c.form( 'form#edit_config', ( res ) => {
                c.viewLoad( `div#popup div.content` );
                c.send({
                    link: '/api/admin/user/edit_config',
                    post: res,
                    check: true,
                    run: function() {
                        c.viewLoad( `div#popup div.content`, true );
                        unit.toast(['true',{type:t('edit')}]);
                        unit.popup(false );
                        index.router();
                    },
                    error: function() { c.viewLoad( `div#popup div.content`, true ); }
                });
            });
        },
    };
</script>