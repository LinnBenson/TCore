<div class="about">
    <div class="card hasTitle">
        <div class="title">
            <h4><i class="bi-star"></i>Reame.md</h4>
        </div>
        <div class="markdown"></div>
    </div>
</div>
<script>
    page = {
        start: () => {
            c.viewLoad( 'main' );
            c.send({
                link: '/api/admin/setting/readme',
                check: true,
                run: ( res ) => {
                    c.viewLoad( 'main', true );
                    $( 'div.markdown' ).html( md.to( res ) );
                },
                error: () => { c.viewLoad( 'main', true ); }
            });
        }
    };
    page.start();
</script>