<?php
use support\middleware\view;

    $avatar = "/storage/media/avatar/{$user->uid}.png";
    if ( !file_exists( "storage/media/avatar/{$user->uid}.png" ) ) { $avatar = null; }
?>
<div id="user">
    <div class="card hasTitle">
        <div class="title">
            <h4><i class="bi-ui-checks-grid"></i>{{user.info}}</h4>
        </div>
        <div class="words">
            <div><p>UID</p><p><?=$user->uid?></p></div>
            <div><p>{{user.status}}</p><p><?=$user->info['status']?> ( <?=$user->level?> )</p></div>
            <div><p>{{user.id}}</p><p><span name="id"><?=$user->id?></span></p></div>
            <div><p>{{user.ip}}</p><p><?=$user->ip?></p></div>
            <div><p>{{user.ua}}</p><p><?=$user->ua?></p></div>
            <div><p>{{user.time}}</p><p>UTC<?=$user->time >= 0 ? "+{$user->time}" : $user->time?></p></div>
            <div><p>{{user.lang}}</p><p><?=$user->lang?></p></div>
        </div>
        <button class="button r5" style="margin-top: 16px" onclick="page.refresh_id()"><i class="bi-arrow-counterclockwise"></i>{{user.refresh_id}}</button>
        <button class="button r5" style="margin-top: 16px" onclick="page.refresh_set()"><i class="bi-plus-slash-minus"></i>{{user.refresh_set}}</button>
    </div>
    <div class="card edit hasTitle">
        <div class="title">
            <h4><i class="bi-ui-checks-grid"></i>{{user.edit}}</h4>
            <button class="button r3" onclick="page.edit()"><i class="bi-check-circle"></i>{{save}}</button>
        </div>
        <form id="edit">
            <?=view::show( 'system/form', [
                'left' => false,
                'input' => [
                    'avatar' => [ 'type' => 'upload', 'upload' => '/api/storage/upload/avatar', 'value' => $avatar, 'name' => 'avatar', 'title' => '{{user.avatar}}', 'rule' => [ 'allow' => [ 'jpg', 'png', 'jpeg' ] ]],
                    'username' => [ 'type' => 'username', 'value' => $user->info['username'], 'name' => 'username', 'title' => '{{user.username}}', 'rule' => [ 'must' => true, 'min' => 4, 'max' => 12 ] ],
                    'email' => [ 'type' => 'email', 'value' => $user->info['email'], 'name' => 'email', 'title' => '{{user.email}}', 'rule' => [ 'must' => true ] ],
                    'phone' => [ 'type' => 'phone', 'value' => $user->info['phone'], 'name' => 'phone', 'title' => '{{user.phone}}', 'rule' => [] ],
                    'nickname' => [ 'type' => 'text', 'value' => $user->info['nickname'], 'name' => 'nickname', 'title' => '{{user.nickname}}', 'rule' => [ 'must' => true ] ],
                    'password' => [ 'type' => 'password', 'name' => 'password', 'title' => '{{user.password}}', 'rule' => [] ]
                ]
            ])?>
        </form>
    </div>
    <div class="card edit hasTitle">
        <div class="title">
            <h4><i class="bi-plug"></i>{{user.set}}</h4>
            <button class="button r3" onclick="page.user_set()"><i class="bi-check-circle"></i>{{save}}</button>
        </div>
        <form id="user_set">
            <?=view::show( 'system/form', [
                'left' => false,
                'input' => [
                    'theme' => [ 'type' => 'select', 'name' => 'theme', 'title' => '{{user.theme}}', 'data' => view::getTheme(), 'rule' => [ 'must' => true ]],
                    'timezone' => [ 'type' => 'select', 'name' => 'timezone', 'title' => '{{user.time}}', 'data' => view::getTimezone(),  'rule' => [ 'must' => true ] ],
                    'lang' => [ 'type' => 'select', 'name' => 'lang', 'title' => '{{user.lang}}', 'data' => view::getLang( view::$project ),  'rule' => [ 'must' => true ] ]
                ]
            ])?>
        </form>
    </div>
</div>
<script>
    page = {
        edit: () => {
            c.form( 'form#edit', ( res ) => {
                c.viewLoad( 'div.card.edit' );
                c.send({
                    link: '/api/admin/setting/user',
                    post: res,
                    check: true,
                    run: () => {
                        c.viewLoad( 'div.card.edit', true );
                        unit.toast(['true',{type:t('edit')}]);
                        index.router();
                    },
                    error: () => {
                        c.viewLoad( 'div.card.edit', true );
                    }
                });
            });
        },
        refresh_id: () => {
            const id = uuid();
            c.setCache( 'identifier', id );
            c.text( 'id', id );
        },
        user_set: () => {
            const theme = $( 'form#user_set [name="theme"]' ).val();
            const timezone = $( 'form#user_set [name="timezone"]' ).val();
            const lang = $( 'form#user_set [name="lang"]' ).val();
            if ( !empty( theme ) ) { c.setCache( 'theme', theme ); }
            if ( !empty( timezone ) ) { c.setCache( 'timezone', timezone ); }
            if ( !empty( lang ) ) { c.setCache( 'lang', lang ); }
            unit.toast(['true',{type:t('save')}]);
            index.router();
        },
        refresh_set: () => {
            c.setCache( 'theme' );
            c.setCache( 'timezone' );
            c.setCache( 'lang' );
            unit.toast(['true',{type:t('reset')}]);
            index.router();
        }
    };
    // 初始化
    $( 'form#user_set [name="theme"]' ).val( get( 'theme' ) ? get( 'theme' ) : 'Default' );
    $( 'form#user_set [name="timezone"]' ).val( get( 'timezone' ) ? get( 'timezone' ) : '<?=task::$user->time?>' );
    $( 'form#user_set [name="lang"]' ).val( get( 'lang' ) ? get( 'lang' ) : c.lang );
</script>