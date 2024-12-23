<?php
use application\model\media;
use Illuminate\Support\Carbon;
use support\middleware\view;

    $total = media::count();
    $totalToday = media::whereDate( 'created_at', Carbon::today() )->count();
?>
<div id="mediaUser">
    <div class="card hasTitle">
        <div class="title">
            <h5><i class="bi-box"></i>{{media_user.state}}</h5>
        </div>
        <p style="margin-bottom: 8px">{{media_user.total}} : <?=$total?></p>
        <p>{{media_user.total_today}} : <?=$total?></p>
    </div>
    <?=view::show( 'admin/unit/table', [
        'id' => 'media',
        'check' => 'table.check',
        'delete' => 'table.delete',
        'words' => [
            'id' => [ 'width' => '90px' ],
            'uid' => [ 'width' => '90px' ],
            'storage' => [ 'width' => '130px' ],
            'file' => [ 'width' => '200px' ],
            'public' => [ 'width' => '100px' ],
            'application' => [ 'width' => '200px' ],
            'created_at' => [ 'width' => '180px' ]
        ],
        'search' => [ 'id', 'uid', 'storage', 'file', 'public', 'application', 'created_at' ],
        'sort' => [ 'id', 'uid', 'storage', 'public', 'created_at' ]
    ])?>
</div>