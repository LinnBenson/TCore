<?php
use application\server\serviceServer;
use support\middleware\view;

    $seivice = serviceServer::getAllService();
?>
<style>
    div#service ul.list {
        --minWidth: 300px;
        --gap: 8px;
        padding: 0;
        list-style: none;
    }
    div#service ul.list li {
        padding: 12px 16px;
        background: rgb( var( --r6 ) );
        border-radius: 8px;
        overflow: hidden;
        position: relative;
    }
    div#service ul.list li p {
        font-size: 12px;
    }
    div#service ul.list li p span {
        opacity: 0.65;
    }
    div#service ul.list li div.state {
        width: 180px;
        padding: 4px 0px;
        font-size: 14px;
        text-align: center;
        transform: rotate( 45deg );
        position: absolute;
        top: 25px;
        right: -45px;
    }
</style>
<div id="service">
    <div class="card hasTitle">
        <div class="title">
            <h4><i class="bi-tools"></i>{{service.control}}</h4>
        </div>
        <button class="button r3" onclick="page.add( 'all' )"><i class="bi-clipboard-plus"></i>{{service.add}}</button>
        <button class="button r3" onclick="page.open( 'all' )"><i class="bi-plugin"></i>{{service.open_all}}</button>
        <button class="button r3" onclick="page.close( 'all' )"><i class="bi-escape"></i>{{service.close_all}}</button>
        <button class="button r3" onclick="page.restart( 'all' )"><i class="bi-repeat"></i>{{service.restart_all}}</button>
    </div>
    <ul class="list grid">
        <?php foreach( $seivice as $name => $config ): ?>
            <li class="<?=$name?>">
                <h5><?=$config['name']?></h5>
                <hr style="--margin: 12px"/>
                <?php if( $config['state'] ): ?>
                <p>{{service.time}} : <span><?= $config['state'] * 2 ?> s</span></p>
                <?php endif; ?>
                <p>{{service.protocol}} : <span><?=$config['protocol']?></span></p>
                <p>{{service.port}} : <span><?=$config['port']?></span></p>
                <p>{{service.run}} : <span><?=$config['run']?></span></p>
                <p>{{service.thread}} : <span><?=$config['thread']?></span></p>
                <p>{{service.public}} : <span><?=!empty( $config['public'] ) ? $config['public'] : 'NULL'?></span></p>
                <hr style="--margin: 12px"/>
                <?php if( $config['state'] ): ?>
                    <button class="button r3" onclick="page.close( '<?=$name?>' )">{{close}}</button>
                <?php else: ?>
                    <button class="button r3" onclick="page.open( '<?=$name?>' )">{{open}}</button>
                <?php endif; ?>
                <button class="button r3" onclick="page.edit( '<?=$name?>' )">{{edit}}</button>
                <button class="button r4" onclick="page.restart( '<?=$name?>' )">{{restart}}</button>
                <?php if( $name !== 'async' && $name !== 'chat' ): ?>
                    <button class="button r5" onclick="page.delete( '<?=$name?>' )">{{delete}}</button>
                <?php endif; ?>
                <div class="state <?=!empty( $config['state'] ) ? 'r4' : 'r5'?>"><?=!empty( $config['state'] ) ? 'Running' : 'Stopping'?></div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<div class="serviceForm hidden">
    <?=view::show( 'system/form', [
        'input' => [
            'key' => [ 'type' => 'username', 'name' => 'key', 'title' => '{{service.key}}', 'rule' => [ 'must' => 'true' ] ],
            'name' => [ 'type' => 'text', 'name' => 'name', 'title' => '{{service.name}}', 'rule' => [ 'must' => 'true' ] ],
            'protocol' => [ 'type' => 'select', 'name' => 'protocol', 'title' => '{{service.protocol}}', 'value' => 'websocket', 'data' => [ 'websocket' => 'Websocket', 'http' => 'HTTP', 'text' => 'TEXT' ], 'rule' => [ 'must' => 'true' ] ],
            'port' => [ 'type' => 'number', 'name' => 'port', 'title' => '{{service.port}}', 'rule' => [ 'must' => 'true', 'min' => 10000 ] ],
            'run' => [ 'type' => 'select', 'name' => 'run', 'title' => '{{service.run}}', 'value' => 'root', 'data' => [ 'root' => 'ROOT', 'www' => 'WWW' ], 'rule' => [ 'must' => 'true' ] ],
            'thread' => [ 'type' => 'number', 'name' => 'thread', 'title' => '{{service.thread}}', 'rule' => [ 'must' => 'true', 'min' => 1 ] ],
            'public' => [ 'type' => 'text', 'name' => 'public', 'title' => '{{service.public}}', 'rule' => [] ],
        ]
    ])?>
</div>
<div class="serviceEditForm hidden">
    <?=view::show( 'system/form', [
        'input' => [
            'name' => [ 'type' => 'text', 'name' => 'name', 'title' => '{{service.name}}', 'rule' => [ 'must' => 'true' ] ],
            'protocol' => [ 'type' => 'select', 'name' => 'protocol', 'title' => '{{service.protocol}}', 'value' => 'websocket', 'data' => [ 'websocket' => 'Websocket', 'http' => 'HTTP', 'text' => 'TEXT' ], 'rule' => [ 'must' => 'true' ] ],
            'port' => [ 'type' => 'number', 'name' => 'port', 'title' => '{{service.port}}', 'rule' => [ 'must' => 'true', 'min' => 10000 ] ],
            'run' => [ 'type' => 'select', 'name' => 'run', 'title' => '{{service.run}}', 'value' => 'root', 'data' => [ 'root' => 'ROOT', 'www' => 'WWW' ], 'rule' => [ 'must' => 'true' ] ],
            'thread' => [ 'type' => 'number', 'name' => 'thread', 'title' => '{{service.thread}}', 'rule' => [ 'must' => 'true', 'min' => 1 ] ],
            'public' => [ 'type' => 'text', 'name' => 'public', 'title' => '{{service.public}}', 'rule' => [] ],
        ]
    ])?>
</div>
<script>
    page = {
        info: JSON.parse( '<?=json_encode( $seivice )?>' ),
        open: function( name = 'all' ) {
            if ( name === 'all' ) {
                c.viewLoad( 'div#service ul.list li' );
            }else {
                c.viewLoad( `div#service ul.list li.${name}` );
            }
            c.send({
                link: '/api/admin/service/open',
                post: { name: name },
                check: true,
                run: () => {
                    c.viewLoad( 'div#service ul.list li', true );
                    unit.toast(['service.true']);
                },
                error: () => {
                    c.viewLoad( 'div#service ul.list li', true );
                }
            });
        },
        close: function( name = 'all' ) {
            if ( name === 'all' ) {
                c.viewLoad( 'div#service ul.list li' );
            }else {
                c.viewLoad( `div#service ul.list li.${name}` );
            }
            c.send({
                link: '/api/admin/service/close',
                post: { name: name },
                check: true,
                run: () => {
                    c.viewLoad( 'div#service ul.list li', true );
                    unit.toast(['service.true']);
                },
                error: () => {
                    c.viewLoad( 'div#service ul.list li', true );
                }
            });
        },
        restart: function( name = 'all' ) {
            if ( name === 'all' ) {
                c.viewLoad( 'div#service ul.list li' );
            }else {
                c.viewLoad( `div#service ul.list li.${name}` );
            }
            c.send({
                link: '/api/admin/service/restart',
                post: { name: name },
                check: true,
                run: () => {
                    c.viewLoad( 'div#service ul.list li', true );
                    unit.toast(['service.true']);
                },
                error: () => {
                    c.viewLoad( 'div#service ul.list li', true );
                }
            });
        },
        delete: function( name ) {
            c.viewLoad( `div#service ul.list li.${name}` );
            c.send({
                link: '/api/admin/service/delete',
                post: { name: name },
                check: true,
                run: () => {
                    c.viewLoad( `div#service ul.list li.${name}`, true );
                    unit.toast(['true',{type:t('delete')}]);
                    index.router();
                },
                error: () => {
                    c.viewLoad( `div#service ul.list li.${name}`, true );
                }
            });
        },
        add: function() {
            unit.popup(['bi-clipboard-plus','{{service.add}}'], `
                <form id="serviceForm">
                    ${$( 'div.serviceForm' ).html()}
                </form>
            `, {
                text: '{{sure}}',
                run: 'page.add_submit()'
            });
        },
        add_submit: () => {
            c.form( 'form#serviceForm', ( res ) => {
                c.viewLoad( `div#popup div.content` );
                c.send({
                    link: '/api/admin/service/create',
                    post: res,
                    check: true,
                    run: () => {
                        c.viewLoad( `div#popup div.content`, true );
                        unit.toast(['true',{type:t('create')}]);
                        unit.popup( false );
                        index.router();
                    },
                    error: () => {
                        c.viewLoad( `div#popup div.content`, true );
                    }
                });
            });
        },
        edit: ( name ) => {
            unit.popup(['bi-pencil-square',`{{edit}} : ${name}`], `
                <form id="serviceForm">
                    ${$( 'div.serviceEditForm' ).html()}
                </form>
            `, {
                text: '{{sure}}',
                run: `page.edit_submit( '${name}' )`
            });
            // 重写默认值
            $form = $( 'form#serviceForm' );
            for ( const key in page.info[name] ) {
                $( 'form#serviceForm' ).find( `[name="${key}"]` ).val( page.info[name][key] );
            }
        },
        edit_submit: ( name ) => {
            c.form( 'form#serviceForm', ( res ) => {
                c.viewLoad( `div#popup div.content` );
                c.send({
                    link: '/api/admin/service/edit',
                    post: { key: name, ...res },
                    check: true,
                    run: () => {
                        c.viewLoad( `div#popup div.content`, true );
                        unit.toast(['true',{type:t('edit')}]);
                        unit.popup( false );
                        index.router();
                    },
                    error: () => {
                        c.viewLoad( `div#popup div.content`, true );
                    }
                });
            });
        },
    };
</script>