<?php
use support\middleware\view;

?>
<style>
    div#settingCommon form.editConfig {
        padding: 16px 0px;
    }
    div#settingCommon form.editConfig div.button {
        margin-top: 32px;
        padding-left: calc( 140px + 8px );
        box-sizing: border-box;
    }
    @media ( max-width: 700px ) {
        div#settingCommon form.editConfig div.button {
            padding-left: 0;
        }
    }
</style>
<div id="settingCommon">
    <div class="card del_cache hasTitle">
        <div class="title">
            <h4><i class="bi-gear-wide-connected"></i>{{setting_common.fast}}</h4>
        </div>
        <button class="button r5" onclick="page.del_cache()"><i class="bi-inboxes"></i>{{setting_common.delCache}}</button>
        <p style="margin-top: 8px; font-size: 14px;">{{setting_common.delCacheWarn}}</p>
    </div>
    <div class="card edit_config hasTitle">
        <div class="title">
            <h4><i class="bi-option"></i>{{setting_common.config}}</h4>
        </div>
        <form class="editConfig" onsubmit="page.edit_config( this )">
            <?=view::show( 'system/form', [
                'left' => false,
                'input' => [
                    'record' => [ 'type' => 'boolean', 'name' => 'record', 'title' => '{{setting_common.record}}', 'value' => config( 'app.record' ) ],
                    'debug' => [ 'type' => 'boolean', 'name' => 'debug', 'title' => '{{setting_common.debug}}', 'value' => config( 'app.debug' ) ],
                    'name' => [ 'type' => 'text', 'name' => 'name', 'title' => '{{setting_common.name}}', 'value' => config( 'app.name' ), 'rule' => [ 'must' => true ] ],
                    'version' => [ 'type' => 'text', 'name' => 'version', 'title' => '{{setting_common.version}}', 'value' => config( 'app.version' ), 'rule' => [ 'must' => true ] ],
                    'host' => [ 'type' => 'text', 'name' => 'host', 'title' => '{{setting_common.host}}', 'value' => config( 'app.host' ), 'rule' => [ 'must' => true ] ],
                    'lang' => [ 'type' => 'text', 'name' => 'lang', 'title' => '{{setting_common.lang}}', 'value' => config( 'app.lang' ), 'rule' => [ 'must' => true ] ],
                    'timezone' => [
                        'type' => 'select', 'name' => 'timezone', 'title' => '{{setting_common.timezone}}', 'value' => config( 'app.timezone' ),
                        'rule' => [ 'must' => true ],
                        'data' => [
                            'Asia/Shanghai' => 'Asia/Shanghai',   // 中国
                            'Asia/Seoul' => 'Asia/Seoul',         // 韩国
                            'Asia/Tokyo' => 'Asia/Tokyo',         // 日本
                            'America/New_York' => 'America/New_York', // 美国（纽约，东部时区）
                            'Europe/London' => 'Europe/London',   // 英国
                        ]
                    ],
                    'fav' => [ 'type' => 'upload', 'upload' => '/api/storage/upload/avatar', 'name' => 'fav', 'title' => '{{setting_common.fav}}', 'value' => '/favicon.png?{{v}}', 'rule' => [ 'must' => true, 'allow' => [ 'png', 'jpg', 'jpeg' ] ] ],
                    'logo_b' => [ 'type' => 'upload', 'upload' => '/api/storage/upload/avatar', 'name' => 'logo_b', 'title' => '{{setting_common.logo_b}}', 'value' => '/library/icon/logo_b.png?{{v}}', 'rule' => [ 'must' => true, 'allow' => [ 'png', 'jpg', 'jpeg' ] ] ],
                    'logo_w' => [ 'type' => 'upload', 'upload' => '/api/storage/upload/avatar', 'name' => 'logo_w', 'title' => '{{setting_common.logo_w}}', 'value' => '/library/icon/logo_w.png?{{v}}', 'rule' => [ 'must' => true, 'allow' => [ 'png', 'jpg', 'jpeg' ] ] ],
                ]
            ]);?>
            <div class="button">
                <button class="button r3"><i class="bi-check-circle"></i>{{save}}</button>
            </div>
        </form>
    </div>
</div>
<script>
    page = {
        del_cache: () => {
            unit.popup(['bi-inboxes', '{{setting_common.delCache}}'], '{{setting_common.delCacheHint}}',{
                text: '{{sure}}',
                run: 'page.del_cache_submit()'
            });
        },
        del_cache_submit: () => {
            c.viewLoad( 'div#popup div.content' );
            c.send({
                link: '/api/admin/setting/del_cache',
                check: true,
                run: () => {
                    c.viewLoad( 'div#popup div.content', true );
                    unit.popup( false );
                    unit.toast(['true',{type:t('clear')}]);
                    index.router();
                },
                error: () => {
                    c.viewLoad( 'div#popup div.content', true );
                }
            });
        },
        edit_config: ( $form ) => {
            c.form( $form, ( res ) => {
                c.viewLoad( 'div#settingCommon div.card.edit_config' );
                c.send({
                    link: '/api/admin/setting/edit_config',
                    post: res,
                    check: true,
                    run: () => {
                        c.viewLoad( 'div#settingCommon div.card.edit_config', true );
                        unit.toast(['true',{type:t('edit')}]);
                        index.router();
                    },
                    error: () => {
                        c.viewLoad( 'div#settingCommon div.card.edit_config', true );
                    }
                });
            });
        }
    };
</script>