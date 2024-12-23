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
    div#articleEdit div.button {
        margin-top: 32px;
    }
    div#articleEdit div.button button { margin: 0; }
    @media ( min-width: 700px ) {
        div#articleEdit div.button {
            padding-left: 148px;
            box-sizing: border-box;
        }
    }
</style>
<div id="articleEdit">
    <div class="card">
        <form id="article" onsubmit="page.submit( this )">
            <?=view::show( 'system/form', [
                'left' => false,
                'input' => [
                    [ 'type' => 'number', 'name' => 'uid', 'title' => article::comment( 'uid' ), 'value' => task::$user->uid, 'rule' => [ 'must' => true ] ],
                    [ 'type' => 'text', 'name' => 'title', 'title' => article::comment( 'title' ), 'rule' => [ 'must' => true ] ],
                    [ 'type' => 'md', 'name' => 'content', 'title' => article::comment( 'content' ), 'rule' => [] ],
                    [ 'type' => 'select', 'name' => 'sort', 'title' => article::comment( 'sort' ), 'data' => $sort, 'rule' => [ 'must' => true ] ],
                    [ 'type' => 'boolean', 'name' => 'public', 'title' => article::comment( 'public' ), 'rule' => [] ],
                    [ 'type' => 'boolean', 'name' => 'release', 'title' => article::comment( 'release' ), 'rule' => [] ],
                    [ 'type' => 'text', 'name' => 'tag', 'title' => article::comment( 'tag' ), 'rule' => [] ]
                ]
            ])?>
            <div class="button">
                <button class="button r3"><i class="bi-pen"></i>{{article_list.edit}}</button>
            </div>
        </form>
    </div>
</div>
<script>
    page = {
        id: '',
        start: () => {
            const hash = c.hashParams();
            if ( empty( hash.id ) ) { return unit.toast(['error.404']); }
            c.viewLoad( 'main' );
            c.send({
                link: '/api/admin/article/check',
                post: { id: hash.id },
                check: true,
                run: function( res ) {
                    c.viewLoad( `main`, true );
                    page.id = res.id;
                    $form = $( 'form#article' );
                    $form.find( '[name="uid"]' ).val( res.uid );
                    $form.find( '[name="title"]' ).val( res.title );
                    $form.find( '[name="sort"]' ).val( res.sort );
                    val["markdown_content"].value( md.edit( res.content ) );
                    $form.find( '[name="public"]' ).prop( 'checked', !empty( res.public ) ? true : false );
                    $form.find( '[name="tag"]' ).val( res.tag );
                    $form.find( '[name="release"]' ).prop( 'checked', !empty( res.release ) ? true : false );
                },
                error: function() { c.viewLoad( `main`, true ); }
            });
        },
        submit: ( e ) => {
            c.form( e, ( res ) => {
                c.viewLoad( `main` );
                c.send({
                    link: '/api/admin/article/edit',
                    post: { id: page.id, ...res },
                    check: true,
                    run: function() {
                        c.viewLoad( `main`, true );
                        unit.toast(['true',{type:t('edit')}]);
                        location.href = '#view=/article/list';
                    },
                    error: function() { c.viewLoad( `main`, true ); }
                });
            });
        }
    };
    page.start();
</script>