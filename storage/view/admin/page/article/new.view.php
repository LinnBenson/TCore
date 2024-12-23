<?php
use application\model\article;
use application\model\article_sort;
use support\middleware\view;

    $sortData = article_sort::all();
    $sort = [];
    foreach( $sortData as $item ) {
        $sort[$item->id] = $item->name;
    }
?>
<style>
    div#articleNew div.button {
        margin-top: 32px;
    }
    div#articleNew div.button button { margin: 0; }
    @media ( min-width: 700px ) {
        div#articleNew div.button {
            padding-left: 148px;
            box-sizing: border-box;
        }
    }
</style>
<div id="articleNew">
    <div class="card">
        <form onsubmit="page.submit( this )">
            <?=view::show( 'system/form', [
                'left' => false,
                'input' => [
                    [ 'type' => 'number', 'name' => 'uid', 'title' => article::comment( 'uid' ), 'value' => task::$user->uid, 'rule' => [ 'must' => true ] ],
                    [ 'type' => 'text', 'name' => 'title', 'title' => article::comment( 'title' ), 'rule' => [ 'must' => true ] ],
                    [ 'type' => 'md', 'name' => 'content', 'title' => article::comment( 'content' ), 'rule' => [] ],
                    [ 'type' => 'select', 'name' => 'sort', 'title' => article::comment( 'sort' ), 'data' => $sort, 'rule' => [ 'must' => true ] ],
                    [ 'type' => 'boolean', 'name' => 'public', 'title' => article::comment( 'public' ), 'rule' => [] ],
                    [ 'type' => 'boolean', 'name' => 'release', 'title' => article::comment( 'release' ), 'value' => true, 'rule' => [] ],
                    [ 'type' => 'text', 'name' => 'tag', 'title' => article::comment( 'tag' ), 'rule' => [] ],
                    [ 'type' => 'datetime', 'name' => 'created_at', 'title' => article::comment( 'created_at' ), 'value' => getTime(), 'rule' => [] ],
                ]
            ])?>
            <div class="button">
                <button class="button r3"><i class="bi-patch-plus"></i>{{article_list.add}}</button>
            </div>
        </form>
    </div>
</div>
<script>
    page = {
        submit: ( e ) => {
            c.form( e, ( res ) => {
                c.viewLoad( `main` );
                c.send({
                    link: '/api/admin/article/submit',
                    post: res,
                    check: true,
                    run: function() {
                        c.viewLoad( `main`, true );
                        unit.toast(['true',{type:t('release')}]);
                        location.href = '#view=/article/list';
                    },
                    error: function() { c.viewLoad( `main`, true ); }
                });
            });
        }
    };
</script>