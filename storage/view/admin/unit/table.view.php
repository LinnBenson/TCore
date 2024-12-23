<?php
    $class = "\application\model\\{$val['id']}";
?>
<style>
    <?php if( !empty( $val['check'] ) || !empty( $val['edit'] ) || !empty( $val['delete'] ) ): ?>
        div.tc_table.<?=$val['id']?> table thead th:nth-of-type( 1 ),
        div.tc_table.<?=$val['id']?> table tbody td:nth-of-type( 1 ){
            min-width: 100px !important;
            max-width: calc( 100px + 1px ) !important;
            text-align: right;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }
    <?php endif; ?>
    <?php $num = !empty( $val['check'] ) || !empty( $val['edit'] ) || !empty( $val['delete'] ) ? 2 : 1; ?>
    <?php foreach( $val['words'] as $key => $item ): ?>
        <?=!empty( $item['width'] ) ? "
            div.tc_table.{$val['id']} table thead th:nth-of-type( {$num} ),
            div.tc_table.{$val['id']} table tbody td:nth-of-type( {$num} ){
                min-width: {$item['width']} !important;
                max-width: calc( {$item['width']} + 1px ) !important;
                text-align: right;
                text-overflow: ellipsis;
                white-space: nowrap;
                overflow: hidden;
            }" : ''?>
        <?php $num++; ?>
    <?php endforeach; ?>
</style>
<div class="tc_table <?=$w( $val['id'] )?>">
    <table id="<?=$w( $val['id'] )?>" class="stripe" style="width: 100%">
        <thead>
            <tr>
                <?php if( !empty( $val['check'] ) || !empty( $val['edit'] ) || !empty( $val['delete'] ) ): ?>
                    <th>{{operate}}</th>
                <?php endif; ?>
                <?php foreach( $val['words'] as $key => $item ): ?>
                    <th><?=$class::comment( $key )?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
    </table>
</div>
<script>
    table.<?=$w( $val['id'] )?> = new DataTable( 'table#<?=$w( $val['id'] )?>', {
        ajax: {
            url: '/api/admin/table',
            type: 'POST',
            beforeSend: function( xhr ) {
                const header = c.header();
                for ( const key in header ) { xhr.setRequestHeader( key, header[key] ); }
            },
            data: function ( post ) {
                post.table = '<?=$w( $val['id'] )?>';
                post.search_item = $( 'div.tc_table.<?=$w( $val['id'] )?> select.searchItem' ).val();
            }
        },
        columns: [
            <?php if( !empty( $val['check'] ) || !empty( $val['edit'] ) || !empty( $val['delete'] ) ): ?>
                {
                    data: null,
                    render: function( row ) {
                        return `
                            <?=!empty( $val['check'] ) ? "<i class='bi-eye r4 operate block' title='{{check}}' onClick=\"{$val['check']}( '{$val['id']}', '`+row.id+`' )\"></i>" : ''?>
                            <?=!empty( $val['edit'] ) ? "<i class='bi-highlighter r3 operate block' title='{{edit}}' onClick=\"{$val['edit']}( '{$val['id']}', '`+row.id+`' )\"></i>" : ''?>
                            <?=!empty( $val['delete'] ) ? "<i class='bi-trash3 r5 operate block' title='{{delete}}' onClick=\"{$val['delete']}( '{$val['id']}', '`+row.id+`' )\"></i>" : ''?>
                        `;
                    },
                    orderable: false,
                    searchable: false,
                },
            <?php endif; ?>
            <?php foreach( $val['words'] as $key => $item ): ?>
                {
                    data: '<?=$w( $key )?>',
                    orderable: <?=in_array( $key, $val['sort'] ) ? "true" : 'false'?>,
                    <?=!empty( $item['render'] ) ? "render: {$item['render']}," : ''?>
                },
            <?php endforeach; ?>
        ],
        processing: true,
        serverSide: true,
        pageLength: 50,
        lengthMenu: [ 50, 100, 200, 400 ],
        scrollX: true,
        scrollY: <?=!empty( $val['height'] ) ? $val['height'] : '530'?>
    });
    // 渲染搜索
    $( `div.tc_table.<?=$w( $val['id'] )?> div.dt-container .dt-search input` ).before(`
        <i class='bi-search searchIcon block'></i>
        <select class="searchItem">
            <?php foreach( $val['words'] as $key => $item ): ?>
                <?php if( in_array( $key, $val['search'] ) ): ?>
                    <option value="<?=$w( $key )?>"><?=$class::comment( $key )?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
    `);
</script>