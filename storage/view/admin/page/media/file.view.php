<?php
    $storageCon = config( 'storage' );
?>
<style>
    div#mediaFile ul.storage {
        --minWeidth: 320px;
        --gap: 8px;
        padding: 0;
        list-style: none;
    }
    div#mediaFile ul.storage li {
        display: block;
        padding: 16px;
        background: rgb( var( --r0 ) );
        box-sizing: border-box;
        border-radius: 8px;
        cursor: pointer;
    }
    div#mediaFile ul.storage li:hover { background: rgb( var( --r3 ), 0.25 ); }
    div#mediaFile ul.storage li.action { background: rgb( var( --r3 ) ); }
    div#mediaFile ul.storage li.action * { color: rgb( var( --r3c ) ); }
    div#mediaFile ul.storage li h5 {
        margin-bottom: 8px;
    }
    div#mediaFile ul.storage li p {
        font-size: 14px;
    }
    div#mediaFile ul.file {
        --minWidth: 160px;
        --gap: 8px;
        padding: 0;
        list-style: none;
    }
    div#mediaFile ul.file li {
        display: block;
        height: auto;
        text-align: center;
        cursor: pointer;
    }
    div#mediaFile ul.file li div {
        display: inline-block;
    }
    div#mediaFile ul.file li div:first-of-type {
        width: 40px;
        vertical-align: bottom;
    }
    div#mediaFile ul.file li div:first-of-type img {
        display: block;
        width: 40px;
        height: 40px;
        border-radius: 4px;
        object-fit: cover;
    }
    div#mediaFile ul.file li div:last-of-type {
        width: calc( 100% - 40px - 4px );
        padding-left: 4px;
        box-sizing: border-box;
    }
    div#mediaFile ul.file li div:last-of-type p {
        font-size: 12px;
        text-align: left;
    }
    div#mediaFile div.page {
        margin-top: 48px;
        text-align: center;
    }
    div#mediaFile div.page button.button {
        margin: 0px 8px;
    }
    form#upload {
        padding: 32px 0px;
    }
</style>
<div id="mediaFile">
    <div class="card hasTitle">
        <div class="title">
            <h4><i class="bi-file-ruled"></i>{{media_file.storage}}</h4>
        </div>
        <ul class="storage grid">
            <?php foreach( $storageCon as $name => $con ): ?>
                <li name="<?=$name?>" onclick="page.list( '<?=$name?>' )">
                    <h5><?=$name?></h5>
                    <p>{{media_file.public}} : <?=$con['public'] ? 'true' : 'false'?></p>
                    <p>{{media_file.dir}} : storage/media<?=$con['dir']?></p>
                    <p>{{media_file.size}} : <?=$con['size'][0]?>B - <?=$con['size'][1]?>B</p>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="card hasTitle">
        <div class="title">
            <h4><i class="bi-file-earmark-break"></i>{{media_file.file_list}} - <span name="storage"></span></h4>
            <button class="button r3" onclick="page.upload()"><i class="bi-upload"></i>{{upload}}</button>
        </div>
        <ul class="file grid"></ul>
        <div class="page">
            <button class="button r3" onclick="page.up()">{{media_file.up}}</button><span name="page"></span> / <span name="total"></span><button class="button r3" onclick="page.down()">{{media_file.down}}</button>
        </div>
    </div>
</div>
<div class="uploadForm hidden">
    <input type="file" name="file" accept="*/*" />
</div>
<script>
    page = {
        storage: '',
        page: 1,
        size: 20,
        total: 0,
        list: ( name ) => {
            if ( empty( name ) ) { name = page.storage; }
            page.page = 1;
            page.storage = name;
            c.text( 'storage', name );
            $( `ul.storage li` ).removeClass( 'action' );
            $( `ul.storage li[name="${name}"]` ).addClass( 'action' );
            page.refresh();
        },
        refresh: () => {
            c.viewLoad( 'ul.file', true );
            c.viewLoad( 'ul.file' );
            c.send({
                link: '/api/admin/storage/get',
                post: { storage: page.storage, page: page.page },
                check: true,
                run: ( res ) => {
                    page.total = Math.ceil( res.total / page.size );
                    c.text( 'page', page.page  ); c.text( 'total', page.total );
                    c.viewLoad( 'ul.file', true );
                    page.page = page.page + 1;
                    let listHtml = ''; let link = '';
                    for ( const item of res.data ) {
                        type = '';
                        if ( /\.(jpg|png|gif|jpeg)$/i.test( item.link.split('?')[0] ) ) { type = 'img'; }
                        icon = /\.(jpg|png|gif|jpeg)$/i.test( item.link.split('?')[0] ) ? item.link : "/library/icon/file.png";
                        listHtml += `
                            <li title="${item.name}">
                                <div class="left">
                                    <img src="${icon}" />
                                </div>
                                <div class="right">
                                    <p class="more" style="margin-bottom: 4px">${item.name}</p>
                                    <p>
                                        <a href="${item.link.split('?')[0]}" download style="margin-right: 6px">${t('download')}</a>
                                        ${ item.name !== 'default.png' ? `<a style="margin-right: 6px" onClick="page.deleteFile( '${item.link.split('?')[0]}' )">${t('delete')}</a>` : '' }
                                        ${type === 'img' ? `<a onClick="unit.checkBig( '${item.link}' )">${t('check')}</a>` : ''}
                                    </p>
                                </div>
                            </li>
                        `;
                    }
                    $( 'ul.file' ).html( listHtml );
                },
                error: () => { c.viewLoad( 'ul.file', true ); }
            });
        },
        upload: () => {
            unit.popup(['bi-upload',t('upload')],`
                <form id="upload">
                    ${$( 'div.uploadForm' ).html()}
                </form>
            `,{
                text: t('upload'),
                run: "page.upload_submit()"
            });
        },
        upload_submit: () => {
            const $fileInput = $( 'form#upload input' )[0];
            const file = $fileInput.files[0];
            if ( !file ) { return unit.toast( ['error.input'], true ); }
            const data = new FormData();
            data.append( 'file', file );
            data.append( 'storage', page.storage );
            c.viewLoad( `div#popup div.content` );
            c.send({
                link: '/api/admin/storage/upload',
                post: data,
                check: true,
                run: function( res ) {
                    c.viewLoad( `div#popup div.content`, true );
                    unit.toast(['true',{type:t('upload')}]);
                    unit.popup(false );
                    page.list( page.storage );
                },
                error: function() {
                    c.viewLoad( `div#popup div.content`, true );
                },
                other: {
                    processData: false,
                    contentType: false
                }
            });
        },
        deleteFile: ( file ) => {
            c.viewLoad( 'ul.file' );
            c.send({
                link: '/api/admin/storage/delete',
                post: {
                    storage: page.storage,
                    file: file
                },
                check: true,
                run: function() {
                    c.viewLoad( 'ul.file', true );
                    unit.toast(['true',{type:t('delete')}]);
                    page.page = page.page - 1;
                    page.refresh();
                },
                error: function() {
                    c.viewLoad( 'ul.file', true );
                }
            });
        },
        up: () => {
            if ( page.page - 2 <= 0 ) { return false; }
            page.page = page.page - 2;
            page.refresh();
        },
        down: () => {
            if ( page.page + 1 > page.total ) { return false; }
            page.refresh();
        }
    };
    page.list( '<?=array_key_first( $storageCon )?>' );
</script>