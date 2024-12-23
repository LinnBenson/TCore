<?php
use support\middleware\view;

?>
<style>
    div#settingTheme ul.list {
        --minWidth: 285px;
        --gap: 8px;
        padding: 0;
        list-style: none;
    }
    div#settingTheme ul.list li {
        padding: 12px;
        background: rgb( var( --r0 ) );
        border-radius: 8px;
    }
    div#settingTheme ul.list li div.color div {
        display: inline-block;
        padding: 4px 10px;
        margin-top: 4px;
        font-size: 12px;
        border-radius: 3px;
    }
    div#settingTheme ul.list li p {
        margin-top: 4px;
        font-size: 14px;
    }
</style>
<div id="settingTheme">
    <div class="card hasTitle">
        <div class="title">
            <h4><i class="bi-box"></i>{{setting_theme.list}}</h4>
            <button class="button r3" onclick="page.add_theme()"><i class="bi-patch-plus"></i>{{create}}</button>
        </div>
        <ul class="list grid">
            <?php foreach( config( 'theme', [] ) as $name => $config ): ?>
                <li>
                    <h5><?=$name?></h5>
                    <hr style="--margin: 8px" />
                    <div class="color">
                        {{setting_theme.color}} :<br />
                        <div style="background: rgb( <?=$config['--r0']?> ); color: rgb(  <?=$config['--r1']?> )">R0</div>
                        <div style="background: rgb( <?=$config['--r1']?> ); color: rgb(  <?=$config['--r0']?> )">R1</div>
                        <div style="background: rgb( <?=$config['--r2']?> ); color: rgb(  <?=$config['--r2c']?> )">R2</div>
                        <div style="background: rgb( <?=$config['--r3']?> ); color: rgb(  <?=$config['--r3c']?> )">R3</div>
                        <div style="background: rgb( <?=$config['--r4']?> ); color: rgb(  <?=$config['--r4c']?> )">R4</div>
                        <div style="background: rgb( <?=$config['--r5']?> ); color: rgb(  <?=$config['--r5c']?> )">R5</div>
                        <div style="background: rgb( <?=$config['--r6']?> ); color: rgb(  <?=$config['--r1']?> )">R6</div>
                    </div>
                    <p>Favicon : <?=$config['fav']?></p>
                    <p>LOGO : <?=$config['logo']?></p>
                    <hr style="--margin: 8px" />
                    <button class="button r3" onclick="page.edit_theme( '<?=$name?>' )"><i class="bi-pen"></i>{{edit}}</button>
                    <button class="button r5" onclick="page.del_theme( '<?=$name?>' )"><i class="bi-trash"></i>{{delete}}</button>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<div class="edit_theme hidden">
    <?=view::show( 'system/form', [
        'input' => [
            [ 'type' => 'text', 'name' => 'logo', 'title' => 'LOGO', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'fav', 'title' => 'Favicon', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r0', 'title' => '{{setting_theme.r0}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r1', 'title' => '{{setting_theme.r1}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r2', 'title' => '{{setting_theme.r2}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r2c', 'title' => '{{setting_theme.r2c}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r3', 'title' => '{{setting_theme.r3}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r3c', 'title' => '{{setting_theme.r3c}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r4', 'title' => '{{setting_theme.r4}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r4c', 'title' => '{{setting_theme.r4c}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r5', 'title' => '{{setting_theme.r5}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r5c', 'title' => '{{setting_theme.r5c}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r6', 'title' => '{{setting_theme.r6}}', 'rule' => [ 'must' => true ] ],
        ]
    ])?>
</div>
<div class="add_theme hidden">
    <?=view::show( 'system/form', [
        'input' => [
            [ 'type' => 'username', 'name' => 'name', 'title' => '{{setting_theme.name}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'logo', 'title' => 'LOGO', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'fav', 'title' => 'Favicon', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r0', 'title' => '{{setting_theme.r0}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r1', 'title' => '{{setting_theme.r1}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r2', 'title' => '{{setting_theme.r2}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r2c', 'title' => '{{setting_theme.r2c}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r3', 'title' => '{{setting_theme.r3}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r3c', 'title' => '{{setting_theme.r3c}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r4', 'title' => '{{setting_theme.r4}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r4c', 'title' => '{{setting_theme.r4c}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r5', 'title' => '{{setting_theme.r5}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r5c', 'title' => '{{setting_theme.r5c}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'text', 'name' => 'r6', 'title' => '{{setting_theme.r6}}', 'rule' => [ 'must' => true ] ],
        ]
    ])?>
</div>
<script>
    page = {
        edit_theme: ( name ) => {
            if ( empty( c.themeConfig[name] ) ) { return unit.toast([ 'error.null' ]); }
            const theme = c.themeConfig[name];
            unit.popup(['bi-pen', '{{edit}}'], `
                <form id="edit_theme">
                    ${$( 'div.edit_theme' ).html()}
                </form>
            `, {
                text: '{{save}}',
                run: `page.edit_theme_submit( '${name}' )`
            });
            // 填充默认值
            for ( let key in theme ) {
                const value = theme[key];
                key = key.replace( '--', '' );
                $( `form#edit_theme input[name="${key}"]` ).val( value );
            }
        },
        edit_theme_submit: ( name ) => {
            c.form( 'form#edit_theme', ( res ) => {
                c.viewLoad( 'div#popup div.content' );
                c.send({
                    link: '/api/admin/setting/edit_theme',
                    post: { name: name, ...res },
                    check: true,
                    run: () => {
                        c.viewLoad( 'div#popup div.content', true );
                        unit.toast(['true',{type:t('save')}]);
                        unit.popup( false );
                        index.router();
                    },
                    error: () => { c.viewLoad( 'div#popup div.content', true ); }
                });
            });
        },
        add_theme: () => {
            unit.popup(['bi-pen', '{{edit}}'], `
                <form id="add_theme">
                    ${$( 'div.add_theme' ).html()}
                </form>
            `, {
                text: '{{save}}',
                run: `page.add_theme_submit( '${name}' )`
            });
        },
        add_theme_submit: () => {
            c.form( 'form#add_theme', ( res ) => {
                c.viewLoad( 'div#popup div.content' );
                c.send({
                    link: '/api/admin/setting/edit_theme',
                    post: res,
                    check: true,
                    run: () => {
                        c.viewLoad( 'div#popup div.content', true );
                        unit.toast(['true',{type:t('save')}]);
                        unit.popup( false );
                        index.router();
                    },
                    error: () => { c.viewLoad( 'div#popup div.content', true ); }
                });
            });
        },
        del_theme: ( name ) => {
            c.viewLoad( 'div#index div#body main' );
            c.send({
                link: '/api/admin/setting/del_theme',
                post: { name: name },
                check: true,
                run: () => {
                    c.viewLoad( 'div#index div#body main', true );
                    unit.toast(['true',{type:t('delete')}]);
                    index.router();
                },
                error: () => { c.viewLoad( 'div#index div#body main', true ); }
            });
        }
    };
</script>