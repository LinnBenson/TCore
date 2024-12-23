<?php
use application\model\article_sort;
use support\middleware\view;
?>

<div id="articleSort">
    <div class="card hasTitle">
        <div class="title">
            <h4><i class="bi-gear"></i>{{article_sort.other}}</h4>
        </div>
        <button class="button r3" onclick="page.add_sort()"><i class="bi-patch-plus"></i>{{article_sort.add}}</button>
    </div>
    <?=view::show( 'admin/unit/table', [
        'id' => 'article_sort',
        'check' => 'table.check',
        'delete' => 'table.delete',
        'edit' => 'table.edit',
        'words' => [
            'id' => [ 'width' => '90px' ],
            'name' => [ 'width' => '200px' ],
            'uid' => [ 'width' => '90px' ],
            'public' => [ 'width' => '100px' ],
            'posts' => [ 'width' => '120px' ],
            'created_at' => [ 'width' => '180px' ]
        ],
        'search' => [ 'id', 'uid', 'public', 'name', 'posts', 'created_at' ],
        'sort' => [ 'id', 'uid', 'public', 'posts', 'created_at' ]
    ])?>
</div>
<div class="add_sort hidden">
    <?=view::show( 'system/form', [
        'input' => [
            [ 'type' => 'number', 'name' => 'uid', 'title' => article_sort::comment( 'uid' ), 'rule' => [] ],
            [ 'type' => 'text', 'name' => 'name', 'title' => article_sort::comment( 'name' ), 'rule' => [ 'must' => true ] ],
            [ 'type' => 'boolean', 'name' => 'public', 'title' => article_sort::comment( 'public' ), 'rule' => [] ],
            [ 'type' => 'boolean', 'name' => 'posts', 'title' => article_sort::comment( 'posts' ), 'rule' => [] ]
        ]
    ])?>
</div>
<script>
    page = {
        add_sort: () => {
            unit.popup( ['bi-patch-plus', t('article_sort.add')], `
                <form id='add_sort'>
                    ${$( 'div.add_sort' ).html()}
                </form>
            `, {
                text: t('save'),
                run: 'page.add_sort_submit()'
            });
        },
        add_sort_submit: () => {
            c.form( 'form#add_sort', ( res ) => {
                c.viewLoad( `div#popup div.content` );
                c.send({
                    link: '/api/admin/article/add_sort',
                    post: res,
                    check: true,
                    run: function() {
                        c.viewLoad( `div#popup div.content`, true );
                        unit.toast(['true',{type:t('create')}]);
                        table['article_sort'].draw();
                        unit.popup(false );
                    },
                    error: function() { c.viewLoad( `div#popup div.content`, true ); }
                });
            });
        }
    };
</script>