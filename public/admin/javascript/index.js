// PC 端默认展开侧边栏
if ( c.size.width > 600 ) {
    $( 'div#index' ).addClass( 'sidebarAction' );
    $( 'i.sidebar' ).removeClass( 'bi-text-indent-left' );
    $( 'i.sidebar' ).addClass( 'bi-text-indent-right' );
}
// 动态监听地址信息
window.addEventListener( 'hashchange', () => {
    index.href = c.hashParams();
    index.router();
});
// 主内容滚动监听
$( 'div#index div#body main' ).on( 'scroll', function() {
    if ( $( 'div#index div#body main' ).scrollTop() === 0 ) {
        return $( 'div#index div#body main i#toUp' ).removeClass( 'action' );
    }
    return $( 'div#index div#body main i#toUp' ).addClass( 'action' );
});
var page = null;
// 主类
var index = {
    // 链接哈希信息
    href: c.hashParams(),
    // 视图信息
    view: c.hashParams().view,
    // 路由功能实现
    router: () => {
        $( 'div#index div#body main' ).animate({ scrollTop: 0 }, 50 );
        let view = index.href.view;
        if ( empty( index.view ) && empty( view ) ) { view = '/home'; }
        if ( !empty( view ) ) {
            index.view = view;
            const href = `/${c.project}/page${view}`;
            c.viewLoad( 'div#index div#body main' );
            c.send({
                link: href,
                run: ( res ) => {
                    c.viewLoad( 'div#index div#body main', true );
                    document.title = `${t(`page.${index.view}`)} - ${t('index.title')} - ${c.info.name}`;
                    $( `ul#menu li` ).removeClass( 'action' );
                    $( `li[name="${index.view}"]` ).addClass( 'action' );
                    c.text( 'titleName', t(`page.${index.view}`) );
                    page = null;
                    if ( /<html[\s\S]*?>[\s\S]*?<\/html>/i.test( res ) ) {
                        return index.routerError( ['error.404'] );
                    }
                    $( 'div#index div#body main div#content' ).html( res );
                },
                error: () => {
                    page = null;
                    index.routerError( ['error.network'] );
                    c.viewLoad( 'div#index div#body main', true );
                }
            });
        }
    },
    // 路由错误事件
    routerError: ( res ) => {
        unit.toast( res, true );
        document.title = `${t(`error.title`)} - ${t('index.title')} - ${c.info.name}`;
        c.view({ error: t( ...res ) }, `
            <div id='error' class='center'>
                <div>
                    <i class='bi-x-circle'></i>
                    <p>{error}</p>
                </div>
            </div>
        `, 'div#index div#body main div#content' );
    },
    // 侧边栏控制
    sidebar: ( close = false ) => {
        const state = $( 'div#index' ).hasClass( 'sidebarAction' );
        if ( state || close ) {
            $( 'i.sidebar' ).removeClass( 'bi-text-indent-right' );
            $( 'i.sidebar' ).addClass( 'bi-text-indent-left' );
            return $( 'div#index' ).removeClass( 'sidebarAction' );
        }
        $( 'i.sidebar' ).removeClass( 'bi-text-indent-left' );
        $( 'i.sidebar' ).addClass( 'bi-text-indent-right' );
        return $( 'div#index' ).addClass( 'sidebarAction' );
    },
    autoSidebar: () => {
        if ( c.size.width <= 600 ) {
            index.sidebar( true );
        }
    },
    // 侧边栏子菜单展开
    menuList: ( name ) => {
        const $children = $( `ul#menu ul[name="${name}"]` );
        if ( $children.hasClass( 'action' ) ) {
            $children.removeClass( 'action' );
            $( `li[name="${name}"]` ).find( 'i.right' ).removeClass( 'bi-chevron-down' );
            $( `li[name="${name}"]` ).find( 'i.right' ).addClass( 'bi-chevron-right' );
            return;
        }
        $children.addClass( 'action' );
        $( `li[name="${name}"]` ).find( 'i.right' ).removeClass( 'bi-chevron-right' );
        $( `li[name="${name}"]` ).find( 'i.right' ).addClass( 'bi-chevron-down' );
    },
    // 将主内容滚动到顶部
    mainToUp: () => {
        $( 'div#index div#body main' ).animate({ scrollTop: 0 }, 240 );
    }
};
index.router();