var view = {
    /* 修改用户资料 */
    edit: () => {
        unit.popup( ['bi-pencil-square', t('user.edit')], `
            <form id="edit">
                ${$( 'div#user div.edit' ).html()}
            </form>
        `, {text: t('save'), run: 'view.edit_submit()'} );
    },
    edit_submit: () => {
        c.form( 'form#edit', ( res ) => {
            c.viewLoad( 'div#popup div.content' );
            c.send({
                link: '/api/account/edit',
                post: res,
                check: true,
                run: () => {
                    unit.toast( ['true',{type:t('edit')}] );
                    location.reload();
                },
                error: () => {
                    c.viewLoad( 'div#popup div.content', true );
                }
            });
        });
    },
    /* 修改绑定关系 */
    bind: () => {
        unit.popup( ['bi-link-45deg', t('user.bind')], `
            <form id="bind">
                ${$( 'div#user div.bind' ).html()}
            </form>
        `, {text: t('save'), run: 'view.bind_submit()'} );
    },
    bind_submit: () => {
        c.form( 'form#bind', ( res ) => {
            c.viewLoad( 'div#popup div.content' );
            c.send({
                link: '/api/account/bind',
                post: res,
                check: true,
                run: () => {
                    unit.toast( ['true',{type:t('edit')}] );
                    location.reload();
                },
                error: () => {
                    c.viewLoad( 'div#popup div.content', true );
                }
            });
        });
    },
    /* 安全项修改 */
    safety: () => {
        unit.popup( ['bi-shield-check', t('user.safety')], `
            <form id="safety">
                ${$( 'div#user div.safety' ).html()}
            </form>
        `, {text: t('save'), run: 'view.safety_submit()'} );
    },
    safety_submit: () => {
        c.form( 'form#safety', ( res ) => {
            c.viewLoad( 'div#popup div.content' );
            c.send({
                link: '/api/account/safety',
                post: res,
                check: true,
                run: () => {
                    unit.toast( ['true',{type:t('edit')}] );
                    location.reload();
                },
                error: () => {
                    c.viewLoad( 'div#popup div.content', true );
                }
            });
        });
    },
    /* 发送验证码 */
    verifyTimeout: 0,
    verifyTime: false,
    verify: () => {
        if ( view.verifyTimeout > 0 ) { return unit.toast( ['register.verifyWait',{s:view.verifyTimeout}], true ); }
        const email = $( 'form#bind input[name="email"]' ).val();
        if ( empty( email ) ) { return unit.toast( ['register.noEmail'], true ); }
        c.viewLoad( 'div#popup div.content' );
        c.send({
            link: '/api/account/verify',
            post: { email: email },
            check: true,
            run: ( res ) => {
                c.viewLoad( 'div#popup div.content', true );
                unit.toast( ['true',{type:t('send')}] );
                view.verifyTimeout = 60;
                view.verifyTime = setInterval(() => {
                    view.verifyTimeout = view.verifyTimeout - 1;
                    if ( view.verifyTimeout === 0 ) { clearInterval( view.verifyTime ); }
                }, 1000 );
            },
            error: () => { c.viewLoad( 'div#popup div.content', true ); }
        });
    },
};