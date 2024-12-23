window['table'] = {
    ref: {},
    // 查看表格数据
    check: ( name, id ) => {
        c.viewLoad( `div.tc_table.${name}` );
        c.send({
            link: '/api/admin/table/check',
            post: { table: name, id: id },
            check: true,
            run: function( res ) {
                c.viewLoad( `div.tc_table.${name}`, true );
                const row = res.row;
                const comment = res.comment;
                let keyName; let html = '';
                for ( const key in row ) {
                    keyName = comment[key] ? comment[key] : key;
                    html += `<div><p>${keyName}</p><p>${row[key] !== null ? row[key] : ''}</p></div>`;
                }
                unit.popup( ['bi-eye', `${t('check')} ID: ${id}`], `
                    <div class="words">${html}</div>
                ` );
            },
            error: function() { c.viewLoad( `div.tc_table.${name}`, true ); }
        });
    },
    // 删除数据
    delete: ( name, id ) => {
        unit.popup( ['bi-trash3', `${t('delete')} ID: ${id}`], t('index.deleteSure',{id: id}), {
            text: t( 'sure' ),
            run: `table.deletePass( '${name}', '${id}' )`
        });
    },
    deletePass: ( name, id ) => {
        c.viewLoad( `div#popup div.content` );
        c.send({
            link: '/api/admin/table/delete',
            post: { table: name, id: id },
            check: true,
            run: function() {
                table[name].draw();
                c.viewLoad( `div#popup div.content`, true );
                unit.toast(['true',{type:t('delete')}]);
                unit.popup( false );
            },
            error: function() { c.viewLoad( `div#popup div.content`, true ); }
        });
    },
    // 修改数据
    edit: ( name, id ) => {
        c.viewLoad( `div.tc_table.${name}` );
        c.send({
            link: '/api/admin/table/edit_view',
            post: { table: name, id: id },
            run: function( res ) {
                c.viewLoad( `div.tc_table.${name}`, true );
                unit.popup( ['bi-highlighter', `${t('edit')} ID: ${id}`], `
                    <form id="edit_${name}">
                        ${res}
                    </form>
                `, {
                    text: t( 'save' ),
                    run: `table.editSubmit( '${name}', '${id}' )`
                });
            },
            error: function() {
                unit.toast( ['edit',{type:t('edit')}] );
                c.viewLoad( `div.tc_table.${name}`, true );
            }
        });
    },
    editSubmit: ( name, id ) => {
        c.form( `form#edit_${name}`, ( res ) => {
            c.viewLoad( `div#popup div.content` );
            c.send({
                link: '/api/admin/table/edit',
                post: {
                    table: name, id: id,
                    ...res
                },
                check: true,
                run: function() {
                    table[name].draw();
                    c.viewLoad( `div#popup div.content`, true );
                    unit.toast(['true',{type:t('edit')}]);
                    unit.popup( false );
                },
                error: function() { c.viewLoad( `div#popup div.content`, true ); }
            });
        });
    }
};