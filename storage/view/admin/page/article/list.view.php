<?php
use application\model\article;
use support\middleware\view;
use Illuminate\Support\Carbon;

    $total = article::count();
    $total_today = article::whereDate( 'updated_at', Carbon::today() )->count();
?>
<style>
    div#articleList div.info {
        --minWidth: 90px;
        --gap: 0px;
        grid-template-columns: repeat( auto-fit, minmax( var( --minWidth ), 1fr ) );
    }
    div#articleList div.info div.children {
        text-align: center;
        border-right: 1px dashed rgb( var( --r1 ), 0.5 );
    }
    div#articleList div.info div.children:last-of-type { border: none; }
    div#articleList div.info div.children p.number {
        margin-top: 8px;
        font-size: 18px;
        font-weight: bold;
        color: rgb( var( --r4 ) );
    }
    div.article_info {
        padding: 8px;
        margin-bottom: 16px;
        background: rgb( var( --r3 ), 0.1 );
        box-sizing: border-box;
        border-radius: 4px;
    }
    div.article_info p {
        font-size: 14px;
    }
</style>
<div id="articleList">
    <div class="info card grid">
        <div class="children">
            <p>{{article_list.total}}</p>
            <p class="number"><?=$total?></p>
        </div>
        <div class="children">
            <p>{{article_list.total_today}}</p>
            <p class="number"><?=$total_today?></p>
        </div>
    </div>
    <div class="card hasTitle">
        <div class="title">
            <h4><i class="bi-gear"></i>{{article_list.other}}</h4>
        </div>
        <a href="#view=/article/new" class="button r3"><i class="bi-patch-plus"></i>{{article_list.add}}</a>
    </div>
    <?=view::show( 'admin/unit/table', [
        'id' => 'article',
        'check' => 'page.check',
        'delete' => 'table.delete',
        'edit' => 'page.edit',
        'words' => [
            'id' => [ 'width' => '90px' ],
            'type' => [ 'width' => '140px' ],
            'uid' => [ 'width' => '90px' ],
            'title' => [ 'width' => '200px' ],
            'sort' => [ 'width' => '90px' ],
            'public' => [ 'width' => '100px' ],
            'release' => [ 'width' => '100px' ],
            'synopsis' => [ 'width' => '200px' ],
            'tag' => [ 'width' => '200px' ],
            'updated_at' => [ 'width' => '180px' ],
            'created_at' => [ 'width' => '180px' ]
        ],
        'search' => [ 'id', 'type', 'uid', 'title', 'sort', 'public', 'synopsis', 'tag', 'updated_at', 'created_at' ],
        'sort' => [ 'id', 'type', 'uid', 'sort', 'public', 'updated_at', 'created_at' ]
    ])?>
</div>
<script>
    page = {
        check: ( table, id ) => {
            c.viewLoad( 'main' );
            c.send({
                link: '/api/admin/article/check',
                post: { id: id },
                check: true,
                run: function( res ) {
                    c.viewLoad( `main`, true );
                    unit.popup(['bi-eye', res.title], `
                        <div class="article_info">
                            <p><?=article::comment( 'uid' )?> : ${res.uid}</p>
                            <p><?=article::comment( 'sort' )?> : ${res.sort_name}</p>
                            <p><?=article::comment( 'tag' )?> : ${res.tag}</p>
                            <p><?=article::comment( 'public' )?> : ${res.public ? 'true' : 'false'}</p>
                            <p><?=article::comment( 'created_at' )?> : ${res.created_at}</p>
                        </div>
                        <div class="markdown">
                            ${md.to( res.content )}
                        </div>
                    `, false, '600px');
                },
                error: function() { c.viewLoad( `main`, true ); }
            });
        },
        edit: ( table, id ) => {
            location.href = `#view=/article/edit&id=${id}`;
        }
    };
</script>