<style>
    div#devApi textarea, div#devApi input {
        border: 1px solid rgb( var( --r3 ), 0.25 );
        border-radius: 4px;
        box-sizing: border-box;
    }
    div#devApi input::placeholder {
        color: rgb( var( --r1 ), 0.25 );
    }
    div#devApi div.result, div#devApi div.card {
        margin-bottom: 16px;
    }
    div#devApi div.result textarea {
        width: 100% !important;
        min-height: 320px;
        padding: 16px;
    }
    div#devApi div.testType {
        display: none;
    }
    div#devApi div.testType.action {
        display: block;
    }
    div#devApi div.testType div.item {
        --left: 100px;
        margin-bottom: 16px;
    }
    div#devApi div.testType div.item::after {
        content: "";
        display: table;
        clear: both;
    }
    div#devApi div.testType div.item div.left {
        float: left;
        width: var( --left );
    }
    div#devApi div.testType div.item div.left h4, div#devApi div.testType div.item div.left p {
        line-height: 40px;
    }
    div#devApi div.testType div.item div.right {
        float: right;
        width: calc( 100% - var( --left ) );
    }
    div#devApi div.testType div.item div.right input {
        width: 100%;
        height: 40px;
        padding: 0px 16px;
        background: rgb( var( --r0 ) );
    }
    div#devApi div.testType div.item div.right textarea {
        width: 100% !important;
        min-height: 180px;
        padding: 16px;
    }
    div#devApi div.testType div.item div.right div.two {
        margin-bottom: 8px;
    }
    div#devApi div.testType div.item div.right div.two input {
        width: calc( 65% - 4px );
    }
    div#devApi div.testType div.item div.right div.two input:first-of-type {
        width: calc( 35% - 4px );
        margin-right: 8px;
    }
    @media ( max-width: 800px ) {
        div#devApi div.testType div.item div.left {
            width: 100%;
        }
        div#devApi div.testType div.item div.right {
            width: 100%;
        }
        div#devApi div.testType div.item div.left h4, div#devApi div.testType div.item div.left p {
            line-height: 30px;
        }
    }
</style>
<div id="devApi">
    <div class="result">
        <textarea class="code"></textarea>
    </div>
    <div class="card">
        {{dev_api.type}}:&nbsp;&nbsp;
        <button class="button r3" onclick="page.testType( 'test_link' )"><i class="bi-link-45deg"></i>Link</button>
        <button class="button r3" onclick="page.testType( 'test_ws' )"><i class="bi-hdd-network"></i>Websocket</button>
    </div>
    <div class="card testType test_link hasTitle">
        <div class="title">
            <h4>{{dev_api.test_link}}</h4>
            <button class="button r3 send" onclick="page.send_link()"><i class="bi-check2-circle"></i>{{dev_api.send}}</button>
        </div>
        <div class="item">
            <div class="left">
                <h4>{{dev_api.link}}</h4>
            </div>
            <div class="right">
                <input type="text" name='link' autocomplete="off" />
            </div>
        </div>
        <div class="item">
            <div class="left">
                <p>{{dev_api.post}}</p>
            </div>
            <div class="right">
                <div class="two">
                    <input type="text" name='post_key_1' placeholder='key' autocomplete="off" /><input type="text" name='post_value_1' placeholder='value' autocomplete="off" />
                </div>
                <div class="two">
                    <input type="text" name='post_key_2' placeholder='key' autocomplete="off" /><input type="text" name='post_value_2' placeholder='value' autocomplete="off" />
                </div>
                <div class="two">
                    <input type="text" name='post_key_3' placeholder='key' autocomplete="off" /><input type="text" name='post_value_3' placeholder='value' autocomplete="off" />
                </div>
                <div class="two">
                    <input type="text" name='post_key_4' placeholder='key' autocomplete="off" /><input type="text" name='post_value_4' placeholder='value' autocomplete="off" />
                </div>
                <div class="two">
                    <input type="text" name='post_key_5' placeholder='key' autocomplete="off" /><input type="text" name='post_value_5' placeholder='value' autocomplete="off" />
                </div>
                <div class="two">
                    <input type="text" name='post_key_6' placeholder='key' autocomplete="off" /><input type="text" name='post_value_6' placeholder='value' autocomplete="off" />
                </div>
                <div class="two">
                    <input type="text" name='post_key_7' placeholder='key' autocomplete="off" /><input type="text" name='post_value_7' placeholder='value' autocomplete="off" />
                </div>
                <textarea class="code" name="post" style="margin-bottom: 16px"></textarea>
            </div>
        </div>
        <div class="item">
            <div class="left">
                <p>{{dev_api.header}}</p>
            </div>
            <div class="right">
                <div class="two">
                    <input type="text" name='header_key_1' placeholder='key' autocomplete="off" /><input type="text" name='header_value_1' placeholder='value' autocomplete="off" />
                </div>
                <div class="two">
                    <input type="text" name='header_key_2' placeholder='key' autocomplete="off" /><input type="text" name='header_value_2' placeholder='value' autocomplete="off" />
                </div>
                <div class="two">
                    <input type="text" name='header_key_3' placeholder='key' autocomplete="off" /><input type="text" name='header_value_3' placeholder='value' autocomplete="off" />
                </div>
                <div class="two">
                    <input type="text" name='header_key_4' placeholder='key' autocomplete="off" /><input type="text" name='header_value_4' placeholder='value' autocomplete="off" />
                </div>
                <div class="two">
                    <input type="text" name='header_key_5' placeholder='key' autocomplete="off" /><input type="text" name='header_value_5' placeholder='value' autocomplete="off" />
                </div>
            </div>
        </div>
    </div>
    <div class="card testType test_ws hasTitle">
        <div class="title">
            <h4>{{dev_api.test_ws}}</h4>
            <button class="button r3 send" onclick="page.send_ws()"><i class="bi-check2-circle"></i>{{dev_api.send}}</button>
        </div>
        <div class="item">
            <div class="left">
                <h4>{{dev_api.address}}</h4>
            </div>
            <div class="right">
                <input type="text" name='link' autocomplete="off" />
            </div>
        </div>
        <div class="item">
            <div class="left">
                <p>{{dev_api.json}}</p>
            </div>
            <div class="right">
                <textarea class="code" name="post" style="margin-bottom: 16px"></textarea>
                <div class="action">
                    <button class="button r4" onclick="page.openService()">开始连接</button>
                    <button class="button r3" onclick="page.heartbeat()">发送心跳</button>
                    <button class="button r3" onclick="page.login()">登录信息</button>
                    <button class="button r5" onclick="page.closeService()">断开连接</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    page = {
        type: '',
        ws: null,
        testType: ( name ) => {
            page.type = name;
            $( 'div#devApi div.testType' ).removeClass( 'action' );
            $( `div#devApi div.testType.${name}` ).addClass( 'action' );
        },
        send_link: () => {
            const link = $( 'div.test_link input[name="link"]' ).val();
            const postJson = $( 'div.test_link textarea[name="post"]' ).val();
            if ( empty( link ) ) { return unit.toast( ['error.null'], true ); }
            const header = page.getData( 'header' );
            let post = page.getData( 'post' );
            c.viewLoad( 'div#devApi div.result' );
            const parameter = is_json( postJson ) ? 'application/json; charset=utf-8' : 'application/x-www-form-urlencoded; charset=utf-8';
            post = !empty( post ) ? post : false;
            if ( is_json( postJson ) ) { post = postJson; }
            $.ajax({
                url: link,
                type: !empty( post ) ? 'POST' : 'GET',
                contentType: parameter,
                data: post,
                headers: !empty( header ) ? header : {},
                xhrFields: {
                    withCredentials: false
                },
                success: function( res ) {
                    c.viewLoad( 'div#devApi div.result', true );
                    page.result( res );
                },
                error: function( res ) {
                    c.viewLoad( 'div#devApi div.result', true );
                    page.result( res.responseJSON );
                }
            });
        },
        send_ws: ( res = false ) => {
            const link = $( 'div.test_ws input[name="link"]' ).val();
            if ( empty( link ) ) { return unit.toast( ['error.null'], true ); }
            if ( empty( page.ws ) || page.ws.state === false ) { return unit.toast( ['dev_api.ws_null'], true ); }
            if ( empty( res ) ) {
                res = $( 'div.test_ws textarea[name="post"]' ).val();
            }
            if ( empty( res ) ) { return unit.toast( ['dev_api.send_null'], true ); }
            page.ws.send( res );
            page.wsResult( `Send message:\n${res}` );
        },
        openService: () => {
            const link = $( 'div.test_ws input[name="link"]' ).val();
            if ( empty( link ) ) { return unit.toast( ['error.null'], true ); }
            page.ws = new wsStructure({
                host: link,
                restart: false,
                start: function() {
                    unit.toast( ['dev_api.ws_start'] );
                    page.wsResult( `System Notifications:\nConnection start!` );
                },
                check: function( res ) {
                    if ( is_array ) {
                        res = JSON.stringify( res, null, 4 );
                    }
                    page.wsResult( `Receive message:\n${res}` );
                },
                end: function() {
                    page.wsResult( `System Notifications:\nConnection close!` );
                }
            }, true );
        },
        closeService: () => {
            if ( empty( page.ws ) || page.ws.state === false ) { return unit.toast( ['dev_api.ws_null'], true ); }
            page.ws.close();
            unit.toast( ['dev_api.ws_close'] );
        },
        heartbeat: () => {
            return page.send_ws( 'ping' );
        },
        login: () => {
            return page.send_ws(JSON.stringify( {
                action: 'login',
                res: c.header()
            }, null, 4 ));
        },
        getData: ( name ) => {
            const data = {};
            for ( let i = 1; i < 10; i++ ) {
                let $key = $( `input[name="${name}_key_${i}"]` );
                if ( empty( $key ) ) { break; }
                let $value = $( `input[name="${name}_value_${i}"]` );
                $key = $key.val(); $value = $value.val();
                if ( !empty( $key ) ) {
                    data[$key] = $value;
                }
            }
            return data;
        },
        result: ( res ) => {
            if ( is_array( res ) ) { res = JSON.stringify( res, null, 4 ); }
            if ( empty( res ) && res !== 0 && res !== '0' ) { res = 'null'; }
            $( 'div#devApi div.result textarea' ).val( res );
        },
        wsResult: ( res ) => {
            let content = $( 'div#devApi div.result textarea' ).val();
            $( 'div#devApi div.result textarea' ).val(
                content +
                `${res}\n--------------------\n`
            );
            $('div#devApi div.result textarea').animate(
                { scrollTop: $('div#devApi div.result textarea')[0].scrollHeight },
                240
            );
        }
    };
    page.testType( 'test_link' );
</script>