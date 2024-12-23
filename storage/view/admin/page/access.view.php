<?php
use application\model\router_record;
use support\middleware\view;
use Illuminate\Support\Carbon;

    $today = router_record::whereDate( 'updated_at', Carbon::today() )->count();
    $today_id = router_record::whereDate( 'updated_at', Carbon::today() )->distinct( 'access_id' )->count();
    $total_id = router_record::distinct( 'access_id' )->count();
?>
<style>
    div#access div.info {
        --minWidth: 90px;
        --gap: 0px;
        grid-template-columns: repeat( auto-fit, minmax( var( --minWidth ), 1fr ) );
    }
    div#access div.info div.children {
        text-align: center;
        border-right: 1px dashed rgb( var( --r1 ), 0.5 );
    }
    div#access div.info div.children:last-of-type { border: none; }
    div#access div.info div.children p.number {
        margin-top: 8px;
        font-size: 18px;
        font-weight: bold;
        color: rgb( var( --r4 ) );
    }
    ul.black_list {
        padding: 0;
        list-style: none;
    }
    ul.black_list li {
        display: block;
        padding: 3px 8px;
        margin-top: 8px;
        font-size: 14px;
        border-radius: 3px;
        cursor: pointer;
        box-sizing: border-box;
    }
</style>
<div id="access">
    <?php if ( !config( 'app.record' ) ): ?>
        <div class="card r5" style="font-size: 14px;">
            <i class="bi-exclamation-diamond-fill" style="margin-right: 8px"></i>{{access.warn}}
        </div>
    <?php endif; ?>
    <div class="info card grid">
        <div class="children">
            <p>{{access.today}}</p>
            <p class="number"><?=$today?></p>
        </div>
        <div class="children">
            <p>{{access.today_id}}</p>
            <p class="number"><?=$today_id?></p>
        </div>
        <div class="children">
            <p>{{access.total_id}}</p>
            <p class="number"><?=$total_id?></p>
        </div>
    </div>
    <div class="card hasTitle">
        <div class="title">
            <h4><i class="bi-diagram-3"></i>{{access.edit}}</h4>
        </div>
        <button class="button r3" onclick="page.edit_config()"><i class="bi-box"></i>{{access.config}}</button>
        <button class="button r3" onclick="page.black()"><i class="bi-person-x"></i>{{access.black}}</button>
        <button class="button r3" onclick="page.add_black()"><i class="bi-journal-plus"></i>{{access.add_black}}</button>
    </div>
    <?=view::show( 'admin/unit/table', [
        'id' => 'router_record',
        'check' => 'table.check',
        'delete' => 'table.delete',
        'words' => [
            'id' => [ 'width' => '90px' ],
            'router' => [ 'width' => '130px' ],
            'type' => [ 'width' => '130px' ],
            'result' => [ 'width' => '130px' ],
            'target' => [ 'width' => '200px' ],
            'uid' => [ 'width' => '90px' ],
            'access_id' => [ 'width' => '160px' ],
            'access_ip' => [ 'width' => '130px' ],
            'created_at' => [ 'width' => '180px' ]
        ],
        'search' => [ 'id', 'router', 'type', 'result', 'target', 'uid', 'access_id', 'access_ip', 'created_at' ],
        'sort' => [ 'id', 'router', 'type', 'result', 'uid', 'access_id', 'access_ip', 'created_at' ]
    ])?>
</div>
<div class="edit_config hidden">
    <?=view::show( 'system/form', [
        'input' => [
            [ 'type' => 'number', 'name' => 'max_record', 'title' => '{{access.max_record}}', 'value' => config( 'access.maxRecord' ), 'rule' => [ 'must' => true, 'min' => 1 ] ],
            [ 'type' => 'number', 'name' => 'max_access_id', 'title' => '{{access.max_access_id}}', 'value' => config( 'access.maxAccess_id' ), 'rule' => [ 'must' => true, 'min' => 1 ] ],
            [ 'type' => 'number', 'name' => 'max_access_ip', 'title' => '{{access.max_access_ip}}', 'value' => config( 'access.maxAccess_ip' ), 'rule' => [ 'must' => true, 'min' => 1 ] ],
            [ 'type' => 'number', 'name' => 'max_access_uid', 'title' => '{{access.max_access_uid}}', 'value' => config( 'access.maxAccess_uid' ), 'rule' => [ 'must' => true, 'min' => 1 ] ],
            [ 'type' => 'number', 'name' => 'black_time', 'title' => '{{access.black_time}} (s)', 'value' => config( 'access.blackTime' ), 'rule' => [ 'must' => true, 'min' => 1 ] ]
        ]
    ])?>
</div>
<div class="add_black hidden">
    <?=view::show( 'system/form', [
        'input' => [
            [ 'type' => 'select', 'name' => 'type', 'title' => '{{access.target}}', 'data' => [ 'id' => router_record::comment( 'access_id' ), 'ip' => router_record::comment( 'access_ip' ), 'uid' => router_record::comment( 'uid' ), ], 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'target', 'title' => '{{access.target}}', 'rule' => [ 'must' => true] ]
        ]
    ])?>
</div>
<script>
    page = {
        edit_config: () => {
            unit.popup(['bi-box','{{access.config}}'], `
                <form id="edit_config">
                    ${$( 'div.edit_config' ).html()}
                </form>
            `,{ text: '{{save}}', run: 'page.edit_config_submit()' });
        },
        edit_config_submit: () => {
            c.form( 'form#edit_config', ( res ) => {
                c.viewLoad( 'div#popup div.content' );
                c.send({
                    link: '/api/admin/setting/edit_access_config',
                    post: res,
                    check: true,
                    run: () => {
                        c.viewLoad( 'div#popup div.content', true );
                        unit.toast(['true',{type:t('save')}]);
                        unit.popup( false );
                        index.router();
                    },
                    error: () => {
                        c.viewLoad( 'div#popup div.content', true );
                    }
                });
            });
        },
        black: () => {
            c.viewLoad( 'main' );
            c.send({
                link: '/api/admin/setting/black_list',
                check: true,
                run: ( res ) => {
                    c.viewLoad( 'main', true );
                    let html = ''; let name = '';
                    for ( const key in res ) {
                        name = key.split( "_" );
                        html += `<li class="r3" onClick="page.del_black( '${name[3]}', '${name[4]}' )">${name[3]}: ${name[4]} (${res[key]})</li>`;
                    }
                    unit.popup(['bi-box','{{access.config}}'], `
                        <ul class="black_list">
                            <p>${t('access.delete')}</p>
                            ${html}
                        </ul>
                    `);
                },
                error: () => {
                    c.viewLoad( 'main', true );
                }
            });
        },
        del_black: ( type, target ) => {
            c.viewLoad( 'div#popup div.content' );
            c.send({
                link: '/api/admin/setting/del_black',
                post: { type: type, target: target },
                check: true,
                run: () => {
                    c.viewLoad( 'div#popup div.content', true );
                    unit.toast(['true',{type:t('delete')}]);
                    page.black();
                },
                error: () => {
                    c.viewLoad( 'div#popup div.content', true );
                }
            });
        },
        add_black: () => {
            unit.popup(['bi-box','{{access.add_black}}'], `
                <form id="add_black">
                    ${$( 'div.add_black' ).html()}
                </form>
            `,{ text: '{{save}}', run: 'page.add_black_submit()' });
        },
        add_black_submit: () => {
            c.form( 'form#add_black', ( res ) => {
                c.viewLoad( 'div#popup div.content' );
                c.send({
                    link: '/api/admin/setting/add_black',
                    post: res,
                    check: true,
                    run: () => {
                        c.viewLoad( 'div#popup div.content', true );
                        unit.toast(['true',{type:t('add')}]);
                        unit.popup( false );
                        index.router();
                    },
                    error: () => {
                        c.viewLoad( 'div#popup div.content', true );
                    }
                });
            });
        }
    };
</script>