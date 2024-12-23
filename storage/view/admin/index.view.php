<?php
use application\model\admin_menu;
use support\middleware\view;

    echo view::show( 'system/body', [
        'title' => "{{index.title}}"
    ]);
    $menu = viewController::menu( admin_menu::where( 'menu', 'main' )->where( 'enable', 1 )->orderBy( 'sequence', 'asc' )->get() );
?>
@start['body']
<!-- DataTables -->
<link href="/library/javascript/DataTables/adjust.css?{{v}}" rel="stylesheet" />
<link href="/library/javascript/DataTables/dataTables.dataTables.css" rel="stylesheet" />
<script src="/library/javascript/DataTables/adjust.js?{{v}}"></script>
<script src="/library/javascript/DataTables/dataTables.js"></script>
<link href="/{{project}}/style/index.css?{{v}}" rel="stylesheet" />
<div id="index" class="scroll">
    <div id="sidebar" class="r3">
        <div class="logo center" style="
            background: url( /{{project}}/image/title.jpg?{{v}} );
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
        ">
            <div>
                <img src='{{logo}}' alt="logo" />
                <h5>{{_app.name}} {{_app.version}}</h5>
            </div>
        </div>
        <ul id="menu" class="scroll">
            <?php foreach ( $menu as $item ): ?>
                <?php if( $item['type'] === 'link' ): ?>
                    <a href="#view=<?=$item['target']?>">
                <?php endif; ?>
                <li name="<?=$item['name'] ? $item['name'] : $item['target']?>" onclick="<?=$item['type'] === 'list' ? "index.menuList( '{$item['name']}' );" : 'index.autoSidebar();'?><?=$item['type'] === 'click' ? $item['target'] : ''?>">
                    <i class="itemIcon block <?=!empty( $item['icon'] ) ? $item['icon'] : 'bi-star'?>"></i>
                    <?=$item['title']?>
                    <?php if( $item['type'] === 'list' ): ?>
                        <i class="right block bi-chevron-right"></i>
                    <?php endif; ?>
                </li>
                <?php if( $item['type'] === 'link' ): ?>
                    </a>
                <?php elseif ( $item['type'] === 'list' ): ?>
                    <ul name="<?=$item['name'] ? $item['name'] : $item['target']?>">
                        <?php foreach ( $item['target'] as $listItem ): ?>
                            <?php if( $listItem['type'] === 'link' ): ?>
                                <a href="#view=<?=$listItem['target']?>">
                            <?php endif; ?>
                            <li name="<?=$listItem['name'] ? $listItem['name'] : $listItem['target']?>" onclick="index.autoSidebar();<?=$item['type'] === 'click' ? $item['target'] : ''?>">
                                <i class="itemIcon block <?=!empty( $listItem['icon'] ) ? $listItem['icon'] : 'bi-star'?>"></i>
                                <?=$listItem['title']?>
                            </li>
                            <?php if( $listItem['type'] === 'link' ): ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
    <div id="body">
        <header class="r3">
            <i class="bi-text-indent-left block sidebar" onclick="index.sidebar()"></i>
            <div class="right">
                <i class="bi-arrow-repeat block" onclick="location.reload()"></i>
                <a href="#view=/user">
                    <img src="/storage/avatar/<?=$user->uid?>" alt="avatar" title="<?=$user->info['nickname']?>" />
                </a>
            </div>
        </header>
        <main class="scroll">
            <div class="titleName r3"><i class="bi-bookmark-fill" style="margin-right: 12px"></i><span name="titleName"></span></div>
            <div id="content"></div>
            <i id="toUp" class="bi-arrow-bar-up block r3" onclick="index.mainToUp()"></i>
        </main>
    </div>
</div>
<script src="/{{project}}/javascript/index.js?{{v}}"></script>
@end['body']