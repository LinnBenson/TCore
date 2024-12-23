<?php
use support\middleware\view;

    $config = config( 'push' );
?>
<style>
    h4.formTitle {
        margin-bottom: 10px;
        padding-left: 8px;
        box-sizing: border-box;
    }
</style>
<div id="push">
    <div class="card hasTitle">
        <div class="title">
            <h4><i class="bi-box"></i>{{push.control}}</h4>
        </div>
        <button class="button r3" onclick="page.add()"><i class="bi-plus-circle-dotted"></i>{{push.add}}</button>
        <button class="button r3" onclick="page.edit_config()"><i class="bi-boxes"></i>{{push.config}}</button>
    </div>
    <?=view::show( 'admin/unit/table', [
        'id' => 'push_record',
        'check' => 'table.check',
        'delete' => 'table.delete',
        'words' => [
            'id' => [ 'width' => '90px' ],
            'uid' => [ 'width' => '90px' ],
            'type' => [ 'width' => '130px' ],
            'to' => [ 'width' => '200px' ],
            'title' => [ 'width' => '200px' ],
            'content' => [ 'width' => '240px' ],
            'source' => [ 'width' => '180px' ],
            'send_id' => [ 'width' => '160px' ],
            'send_ip' => [ 'width' => '130px' ],
            'created_at' => [ 'width' => '180px' ]
        ],
        'search' => [ 'id', 'uid', 'type', 'to', 'title', 'content', 'source', 'send_id', 'send_ip', 'created_at' ],
        'sort' => [ 'id', 'uid', 'type', 'to', 'source', 'send_id', 'send_ip', 'created_at' ]
    ])?>
</div>
<div class="add_push hidden">
    <?=view::show( 'system/form', [
        'input' => [
            [ 'type' => 'boolean', 'name' => 'async', 'title' => '{{push.async}}', 'value' => true, 'rule' => [] ],
            [ 'type' => 'select', 'name' => 'type', 'title' => '{{push.type}}', 'data' => [ 'email' => 'E-mail', 'bark' => 'Bark', 'telegram' => 'Telegram' ], 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'to', 'title' => '{{push.to}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'title', 'title' => '{{push.title}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'longtext', 'name' => 'content', 'title' => '{{push.content}}', 'rule' => [ 'must' => true ] ],
        ]
    ])?>
</div>
<div class="edit_config hidden">
    <h4 class="formTitle">E-mail</h4>
    <?=view::show( 'system/form', [
        'input' => [
            [ 'type' => 'text', 'name' => 'email_default', 'title' => '{{push.default}}', 'value' => $config['email']['default'], 'rule' => [] ],
            [ 'type' => 'text', 'name' => 'email_host', 'title' => '{{push.host}}', 'value' => $config['email']['host'], 'rule' => [] ],
            [ 'type' => 'number', 'name' => 'email_port', 'title' => '{{push.port}}', 'value' => $config['email']['port'], 'rule' => [] ],
            [ 'type' => 'email', 'name' => 'email_username', 'title' => '{{push.username}}', 'value' => $config['email']['username'], 'rule' => [] ],
            [ 'type' => 'password', 'name' => 'email_password', 'title' => '{{push.password}}', 'value' => $config['email']['password'], 'rule' => [] ],
            [ 'type' => 'email', 'name' => 'email_from', 'title' => '{{push.from}}', 'value' => $config['email']['from'], 'rule' => [] ],
            [ 'type' => 'text', 'name' => 'email_encrypt', 'title' => '{{push.encrypt}}', 'value' => $config['email']['encrypt'], 'rule' => [] ]
        ]
    ])?>
    <hr style="--margin: 16px" />
    <h4 class="formTitle">Bark</h4>
    <?=view::show( 'system/form', [
        'input' => [
            [ 'type' => 'text', 'name' => 'bark_default', 'title' => '{{push.default}}', 'value' => $config['bark']['default'], 'rule' => [] ],
            [ 'type' => 'text', 'name' => 'bark_host', 'title' => '{{push.host}}', 'value' => $config['bark']['host'], 'rule' => [] ]
        ]
    ])?>
    <hr style="--margin: 16px" />
    <h4 class="formTitle">Telegram</h4>
    <?=view::show( 'system/form', [
        'input' => [
            [ 'type' => 'text', 'name' => 'telegram_default', 'title' => '{{push.default}}', 'value' => $config['telegram']['default'], 'rule' => [] ],
            [ 'type' => 'text', 'name' => 'telegram_host', 'title' => '{{push.host}}', 'value' => $config['telegram']['host'], 'rule' => [] ]
        ]
    ])?>
</div>
<script>
    page = {
        add: () => {
            unit.popup(['bi-plus-circle-dotted', '{{push.add}}'], `
                <form id="add_push">
                    ${$( 'div.add_push' ).html()}
                </form>
            `, { text: '{{send}}', run: 'page.add_submit()' })
        },
        add_submit: () => {
            c.form( 'form#add_push', ( res ) => {
                c.viewLoad( 'div#popup.action div.content' );
                c.send({
                    link: '/api/admin/push/add_push',
                    post: res,
                    check: true,
                    run: () => {
                        c.viewLoad( 'div#popup.action div.content', true );
                        unit.popup( false );
                        table['push_record'].draw();
                        unit.toast(['true',{type:t('send')}]);
                    },
                    error: () => { c.viewLoad( 'div#popup.action div.content', true ); }
                });
            });
        },
        edit_config: () => {
            unit.popup(['bi-plus-circle-dotted', '{{push.config}}'], `
                <form id="edit_config">
                    ${$( 'div.edit_config' ).html()}
                </form>
            `, { text: '{{save}}', run: 'page.edit_config_submit()' })
        },
        edit_config_submit: () => {
            c.form( 'form#edit_config', ( res ) => {
                c.viewLoad( 'div#popup.action div.content' );
                c.send({
                    link: '/api/admin/push/edit_config',
                    post: res,
                    check: true,
                    run: () => {
                        c.viewLoad( 'div#popup.action div.content', true );
                        unit.popup( false );
                        unit.toast(['true',{type:t('save')}]);
                        index.router();
                    },
                    error: () => { c.viewLoad( 'div#popup.action div.content', true ); }
                });
            });
        }
    };
</script>