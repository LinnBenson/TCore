<?php
use support\middleware\view;

?>
<style>
    div#devIcon div.title {
        margin-bottom: 16px;
    }
    div#devIcon div.title h4 {
        margin-bottom: 16px;
    }
    div#devIcon div.title div.search div {
        margin: 0;
    }
    div#devIcon ul {
        --minWidth: 90px;
        --gap: 8px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    div#devIcon ul li {
        padding: 8px;
        padding-top: 0px;
        background: rgb( var( --r6 ) );
        text-align: center;
        border-radius: 8px;
        box-sizing: border-box;
    }
    div#devIcon ul li i {
        --size: 50px;
        font-size: 18px;
    }
    div#devIcon ul li p {
        font-size: 12px;
    }
</style>
<div id="devIcon">
    <div class="card title">
        <h4><a href="https://icons.getbootstrap.com" target="_blank">Bootstrap icons v1.11.3 ( <span name="count"></span> )</a></h4>
        <div class="search">
            <?=view::show( 'system/form', [
                'input' => [
                    [ 'type' => 'text', 'name' => 'search', 'icon' => false, 'right' => [ 'icon' => 'bi-search', 'action' => 'page.search' ] ]
                ]
            ])?>
        </div>
    </div>
    <ul class="grid"></ul>
</div>
<script>
    page = {
        icons: [],
        getIcons: () => {
            const biClasses = new Set();
            for ( const stylesheet of document.styleSheets ) {
                try {
                    for ( const rule of stylesheet.cssRules || [] ) {
                        if ( rule.selectorText ) {
                            const matches = rule.selectorText.match(/\.bi-[\w-]+/g);
                            if ( matches ) {
                                matches.forEach( cls => biClasses.add( cls.substring( 1 ) ) );
                            }
                        }
                    }
                }catch( error ) {
                    unit.toast( ['error.unknown'], true );
                }
            }
            const allIcon = Array.from( biClasses );
            if ( is_array( allIcon ) ) { this.icons = allIcon; }
            return this.icons;
        },
        render: ( icons = false ) => {
            if ( icons === false ) { icons = page.getIcons(); }
            let html = '';
            for ( const icon of icons ) {
                html += `
                    <li class="copy" data-clipboard-text='${icon}'>
                        <i class='block ${icon}'></i>
                        <p class="more">${icon.replace( /^bi-/, '' )}</p>
                    </li>
                `;
            }
            $( 'div#devIcon ul' ).html( html );
            c.text( 'count', icons.length );
        },
        search: () => {
            const val = $( 'input[name="search"]' ).val();
            if ( empty( val ) ) {
                page.render();
            }else {
                const icons = this.icons.filter(item => item.includes( val ));
                page.render( icons );
            }
        }
    };
    page.render();
</script>