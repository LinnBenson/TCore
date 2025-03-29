window['tc'] = {
    complete: false, // 页面加载状态
    text: get( 'text' ) ?? {}, // 语言包
    server: get( 'server' ) ?? {}, // 服务器信息
    user: get( 'user' ) ?? null, // 用户信息
    size: { width: 0, height: 0 }, // 屏幕尺寸
    clipboard: false, // 复制对象
    /**
     * 默认加载
     */
    SystemDefaultLoading: function() {
        // 设置屏幕尺寸
        document.documentElement.style.setProperty( '--vw', `${window.innerWidth}px` );
        document.documentElement.style.setProperty( '--vh', `${window.innerHeight}px` );
        window.addEventListener( 'resize', () => {
            this.size.width = window.innerWidth;
            this.size.height = window.innerHeight;
            document.documentElement.style.setProperty( '--vw', `${window.innerWidth}px` );
            document.documentElement.style.setProperty( '--vh', `${window.innerHeight}px` );
        });
        // 数据同步
        const cache = [ 'id','lang', 'themeName' ];
        for( const item of cache ) { tc.cache( item, system[item] ); }
        tc.refresh( 'all' );
        // 页面加载完成
        window.onload = function() {
            if ( typeof system !== 'object' ) { window['system'] = {}; }
            // 注册点击复制
            tc.clipboard = new Clipboard( '.copy' );
            tc.clipboard.on( 'success', function() { tc.unit.toast( ['base.copy:base.true'] ); });
            tc.clipboard.on( 'error', function() { tc.unit.toast( ['base.copy:base.false'], true ); });
            // 插入功能代码
            tc.unit.html();
            // 页面加载完成
            tc.complete = true;
        };
    },
    refresh: function( type ) {
        // 允许的类型
        const types = [ 'text', 'server', 'all' ];
        if ( !types.includes( type ) ) { return false; }
        tc.send({
            url: '/api/base/index',
            data: { type: type },
            check: true,
            run: ( res ) => {
                if ( type === 'all' ) {
                    tc.text = res.text; set( 'text', res.text );
                    tc.server = res.server; set( 'server', res.server );
                }else {
                    tc[type] = res; set( type, res );
                }
                // 刷新用户
                if ( is_array( tc.server.user ) ) {
                    tc.user = tc.server.user; set( 'user', tc.user );
                }else {
                    tc.user = null; del( 'user' );
                }
            }
        });
    },
    /**
     * 缓存操作
     * @param {string} name 缓存名称
     * @param {any} value 缓存值
     * @returns boolean 结果
     */
    cache: function( name, value = false ) {
        // 允许的缓存
        const allow = [ 'id', 'lang', 'themeName', 'theme' ];
        if ( !allow.includes( name ) ) { return false; }
        // 删除缓存
        if ( value === null || value === undefined ) {
            $.cookie( name, null, { expires: -1, path: '/' } ); del( name );
            return true;
        }
        // 获取缓存
        if ( value === false ) { return get( name ); }
        // 设置缓存
        if ( !empty( value ) ) {
            if ( is_array( value ) ) { value = JSON.stringify( value ); }
            $.cookie( name, value, { expires: 365, path: '/' } ); set( name, value );
            return true;
        }
        return false;
    },
    /**
     * 发起一个网络请求
     * @param {array} data 请求数据
     * @param {function} load 加载方法
     * @returns 请求实体
     */
    send: async function( data, load = false ) {
        if ( typeof load === 'function' ) { load( true ); }
        let parameter = '';
        if ( !empty( data.data ) ) {
            data.type = !empty( data.type ) ? data.type : 'POST';
            parameter = is_json( data.data ) ? 'application/json; charset=utf-8' : 'application/x-www-form-urlencoded; charset=utf-8';
        }
        data.other = !empty( data.other ) ? data.other : {};
        return $.ajax({
            url: data.url,
            type: !empty( data.type ) ? data.type : 'GET',
            contentType: parameter,
            data: data.data ? data.data : false,
            headers: tc.header(),
            async: data.async === false ? false : true,
            success: function( res ) {
                if ( typeof load === 'function' ) { load( false ); }
                if ( !empty( data.check ) ) { return tc.res( data, res ); }
                if ( typeof data.run === 'function' ) { data.run( res ); }
                return res;
            },
            error: function( res ) {
                if ( typeof load === 'function' ) { load( false ); }
                // 可能存在的请求正常
                if ( typeof res.responseJSON === 'object' && !empty( res.responseJSON.data ) ) {
                    if ( typeof data.error === 'function' ) { data.error( res.responseJSON ); }
                    if ( typeof res.responseJSON.data === 'string' ) {
                        tc.unit.toast( res.responseJSON.data, true );
                    }
                    return res;
                }
                // 其它异常
                if ( typeof data.error === 'function' ) { data.error( res ); }
                tc.unit.toast( ['base.error.network'], true ); return res;
            },
            ...data.other
        });
    },
    /**
     * 验证 API 数据
     * @param {array} data 请求原始数据
     * @param {any} res 请求结果
     * ---
     * @returns 请求结果
     */
    res: function( data, res ) {
        if ( typeof res !== 'object' || empty( res.state ) ) {
            if ( typeof data.error === 'function' ) { data.error( res.data ); }
            tc.unit.toast( ['base.error.unknown'], true );
            return res;
        }
        switch ( res.state ) {
            case 'success':
                if ( typeof data.run === 'function' ) { data.run( res.data ); }
                break;
            case 'fail':
                if ( typeof data.error === 'function' ) { data.error( res.data ); }
                if ( typeof res.data === 'string' ) { tc.unit.toast( res.data, true ); }
                break;
            case 'error':
                if ( typeof data.error === 'function' ) { data.error( res.data ); }
                if ( typeof res.data === 'string' ) { tc.unit.toast( res.data, true ); }
                break;
            case 'warn':
                if ( typeof data.error === 'function' ) { data.error( res.data ); }
                if ( typeof res.data === 'string' ) { tc.unit.toast( res.data, true ); }

            default:
                if ( typeof data.error === 'function' ) { data.error( res.data ); }
                tc.unit.toast( ['base.error.unknown'], true ); break;
        }
        return res;
    },
    /**
     * 附加请求头
     * @returns array 请求头数据
     */
    header: function() {
        let data = {};
        if ( !empty( get( 'token' ) ) ) { data['token'] = get( 'token' ); }
        if ( !empty( get( 'id' ) ) ) { data['id'] = get( 'id' ); }
        if ( !empty( get( 'lang' ) ) ) { data['lang'] = get( 'lang' ); }
        return data
    },
    /**
    /**
     * 视图操作
     */
    view: function( name ) {
        let $element = typeof name === 'string' ? /[.#\s\[\]]/.test( name ) ? $( name ) : $( `[${name}]` ) : name;
        return {
            // 添加代码
            html: function( code = null ) { if ( code === null ) { return $element.html(); } $element.html( code ); return tc.view( $element ); },
            // 检查元素是否存在
            has: function() { return $element.length > 0 ? tc.view( $element ) : false; },
            // 检查类名是否存在
            hasClass: function( name ) { $element.hasClass( name ); return tc.view( $element ); },
            // 激活元素
            action: function( state = null ) {
                if ( typeof state === 'boolean' ) {
                    return state ? $element.addClass( 'action' ) : $element.removeClass( 'action' );
                }
                if ( $element.hasClass( 'action' ) ) {
                    $element.removeClass( 'action' );
                    return true;
                }else {
                    $element.addClass( 'action' );
                    return false;
                }
            },
            // 类名交换
            replace: function( params1, params2 ) {
                if ( $element.hasClass( params2 ) ) {
                    $element.removeClass( params2 );
                    $element.addClass( params1 );
                }else {
                    $element.removeClass( params1 );
                    $element.addClass( params2 );
                }
                return tc.view( $element );
            },
            // 添加类名
            addClass: function( name ) { $element.addClass( name ); return tc.view( $element ); },
            // 移除类名
            removeClass: function( name ) { $element.removeClass( name ); return tc.view( $element ); },
            // 设置类名
            setClass: function( value ) { $element.attr( 'class', value ); return tc.view( $element ); },
            // 设置样式
            style: function( data ) { $element.css( data ); return tc.view( $element ); },
            // 设置属性
            attr: function( name, value = null ) { if ( value === null ) { return $element.attr( name ); } $element.attr( name, value ); return tc.view( $element ); },
            // 移除元素
            remove: function() { return $element.remove(); },
            // 查询子元素
            find: function( name ) { return tc.view( $element.find( /[.#\s\[\]]/.test( name ) ? name : `[${name}]` ) ); },
            // 为元素执行动画
            animation: function( name, time = 180 ) {
                $element.addClass( name );
                setTimeout(() => {
                    $element.removeClass( name );
                }, time );
                return tc.view( $element );
            },
            // 为元素添加加载动画
            load: function( state, timeout = 20000 ) {
                // 延时加载
                if ( !tc.complete ) {
                    clearInterval( tc.unit.loadTime );
                    tc.unit.loadTime = setInterval(() => {
                        if ( tc.complete ) { tc.unit.load( state, timeout ); clearInterval( tc.unit.loadTime ); return true; }
                    }, 100 );
                    return false;
                }
                clearInterval( tc.unit.loadTime ); clearTimeout( tc.unit.loadTime );
                // 逻辑代码
                $element.css( state ? {
                    'position': 'relative', 'overflow': 'hidden', 'min-height': '80px'
                }:{
                    'position': '', 'overflow': '', 'min-height': ''
                });
                if ( state ) {
                    $element.append(`
                        <div class="unit-view-load">
                            ${tc.view( 'div#TCoreUnit div#loading' ).html()}
                        </div>
                    `);
                    tc.unit.loadTime = setTimeout(() => { tc.view( $element ).load( false ); }, timeout );
                    return true;
                }
                $element.find( 'div.unit-view-load' ).remove();
                return true;
            },
            // 渲染列表
            list: function( list, code ) {
                let html = '';
                for ( const item of list ) {
                    html += typeof item === 'string' ? code.replaceAll( '{item}', item ) : code.replaceAll( /\{(\w+)\}/g, ( match, key ) => item[key] || '' );
                }
                $element.html( html );
            },
            // 组件滚动
            scroll: function( type, offset = null, timeout = 200 ) {
                let properties = {};
                switch ( type ) {
                    case 'left':
                        properties.scrollLeft = offset !== null ? offset : 0;
                        break;
                    case 'right':
                        properties.scrollLeft = offset !== null ? offset : $element[0].scrollWidth;
                        break;
                    case 'top':
                        properties.scrollTop = offset !== null ? offset : 0;
                        break;
                    case 'bottom':
                        properties.scrollTop = offset !== null ? offset : $element[0].scrollHeight;
                        break;
                    default: return;
                }
                return $element.animate( properties, timeout );
            },
            // 事件注册
            on: function( type, method ) {
                return $element.on( type, method );
            }
        };
    },
    /**
     * 操作组件
     */
    unit: {
        // 添加组件代码
        html: function() {
            $( 'body' ).prepend(`
                <div id="TCoreUnit">
                    <div id="popup" class="center">
                        <div class="unit-popup-content" popup>
                            <div class="unit-popup-header r3"><p></p></div>
                            <div class="unit-popup-body">
                                <div class="unit-popup-body-content scroll"></div>
                            </div>
                            <div class="unit-popup-footer">
                                <div class="unit-popup-button-method" onClick=""></div>
                                <div class="unit-popup-button-close" onClick="tc.unit.popup( false )"><i class="bi-x-circle right8"></i>${t( 'base.close' )}</div>
                            </div>
                        </div>
                    </div>
                    <div id="loading" class="center"><div class="unit-loader"></div></div>
                    <div id="toast">
                        <div class="content">
                            <i class="icon bi-star block"></i>
                            <span class="text more"></span>
                        </div>
                    </div>
                </div>
            `);
        },
        /**
         * Toast
         * @param {string} text 内容
         * @param {boolean} error 状态状态
         * @param {number} timeout 超时时间
         * @returns boolean
         */
        toastTime: false,
        toast: function( text, error = false, timeout = 3000 ) {
            // 延时加载
            if ( !tc.complete ) {
                clearInterval( tc.unit.toastTime );
                tc.unit.toastTime = setInterval(() => {
                    if ( tc.complete ) { tc.unit.toast( text, error, timeout ); clearInterval( tc.unit.toastTime ); return true; }
                }, 100 );
                return false;
            }
            clearInterval( tc.unit.toastTime ); clearTimeout( tc.unit.toastTime );
            // 语言包调用
            if ( Array.isArray( text ) && text.length <= 2 ) { text = t( ...text ); }
            // 逻辑代码
            const $toast = tc.view( 'div#TCoreUnit div#toast' );
            $toast.find( 'div.content i' ).setClass( error ? 'icon bi-question-circle-fill block' : 'icon bi-bell-fill block' );
            $toast.find( 'div.content span' ).html( text );
            $toast.setClass( error ? 'action error' : 'action pass' ).animation( 'toastOpenAction', 200 );
            tc.unit.toastTime = setTimeout(() => {
                tc.view( 'div#TCoreUnit div#toast' ).action( false );
            }, timeout );
            return true;
        },
        /**
         * Load
         * @param {boolean} state 加载状态
         * @param {number} timeout 超时时间
         * @returns boolean
         */
        loadTime: false,
        load: function( state, timeout = 60000 ) {
            // 延时加载
            if ( !tc.complete ) {
                clearInterval( tc.unit.loadTime );
                tc.unit.loadTime = setInterval(() => {
                    if ( tc.complete ) { tc.unit.load( state, timeout ); clearInterval( tc.unit.loadTime ); return true; }
                }, 100 );
                return false;
            }
            clearInterval( tc.unit.loadTime ); clearTimeout( tc.unit.loadTime );
            // 逻辑代码
            $load = tc.view( 'div#TCoreUnit div#loading' );
            if (  state ) {
                $load.addClass( 'action' );
                tc.unit.loadTime = setTimeout(() => { tc.unit.load( false ); }, timeout );
                return true;
            }
            $load.removeClass( 'action' );
            return true;
        },
        /**
         * Popup
         * @param {string|boolean} title 标题
         * @param {boolean} body 主体内容
         * @param {object} option 可选参数
         * @returns boolean
         */
        popupTime: false,
        popupMethod: {},
        popup: function( title = false, body = '', option = {} ) {
            // 延时加载
            if ( !tc.complete ) {
                clearInterval( tc.unit.popupTime );
                tc.unit.popupTime = setInterval(() => {
                    if ( tc.complete ) { tc.unit.popup( title, body, option ); clearInterval( tc.unit.popupTime ); return true; }
                }, 100 );
                return false;
            }
            clearInterval( tc.unit.popupTime ); clearTimeout( tc.unit.popupTime );
            // 逻辑代码
            const $popup = tc.view( 'div#TCoreUnit div#popup' );
            if ( title === false ) { $popup.action( false ); return true; }
            if ( !/<\/?[a-z][\s\S]*>/i.test( title ) ) { title = `<i class="bi-bounding-box right8"></i>${title}`; }
            $popup.find( 'div.unit-popup-header p' ).html( title );
            if ( typeof body === 'object' ) { body = tc.view( body ).html(); }
            $popup.find( 'div.unit-popup-body-content' ).html( body );
            // 检查 option
            $popup.style({ '--boxWidth': option.width ? option.width : '' });
            if ( !empty( option.text ) && !empty( option.run ) ) {
                $popup.find( 'div.unit-popup-footer' ).addClass( 'hasMethod' );
                const icon = `<i class="${option.icon ? option.icon : 'bi-check-circle'} right8"></i>`;
                $popup.find( 'div.unit-popup-button-method' ).html( icon+option.text );
                const autoClose = !empty( option.close ) ? 'tc.unit.popup();' : '';
                if ( typeof option.run === 'function' ) {
                    $rid = uuid();
                    tc.unit.popupMethod[$rid] = option['run'];
                    option['run'] = `tc.unit.popupMethod['${$rid}']();`;
                }
                $popup.find( 'div.unit-popup-button-method' ).attr( 'onClick', `${autoClose}${option['run']}` );
            }else {
                $popup.find( 'div.unit-popup-footer' ).removeClass( 'hasMethod' );
            }
            // 激活
            $popup.action( true );
            return true;
        },
    },
    form: {
        cache: {},
        /**
         * 切换密码显示
         * @param {string} rid 输入框 rid
         */
        showPassword: function( rid ) {
            tc.view( `div[rid='${rid}']` ).find( 'i.itemInputMethod' ).replace( 'bi-eye', 'bi-eye-slash' );
            const $input = tc.view( `div[rid='${rid}']` ).find( 'input.itemInput' );
            if ( $input.attr( 'type' ) === 'password' ) {
                return $input.attr( 'type', 'text' );
            }
            return $input.attr( 'type', 'password' );
        },
        /**
         * 注册输入框数据
         * @param {string} rid 输入框 rid
         * @param {json} data 输入框数据
         * @returns boolean 注册结果
         */
        register: function( rid, data ) {
            tc.form.cache[rid] = typeof data === 'string' ? JSON.parse( data ) : data;
            for( const id in tc.form.cache ) {
                if( !tc.view( `div[rid='${id}']` ).has ) {
                    delete tc.form.cache[id];
                }
            }
            return true;
        }
    }
};
tc.SystemDefaultLoading();
/**
 * 判断变量是否存在
 * @param {variable} v 传入一个变量
 * @returns boolean
 */
function empty( v ) {
    switch( typeof v ) {
        case 'undefined':
            return true;
        case 'string':
            if ( v.replace( /(^[ \t\n\r]*)|([ \t\n\r]*$)/g, '' ).length === 0 ) return true;
            break;
        case 'boolean':
            if ( !v ) return true;
            break;
        case 'number':
            if ( 0 === v || isNaN( v ) ) return true;
            break;
        case 'object':
            if ( null === v || v.length === 0 ) return true;
            for ( var i in v ) { return false; }
            return true;
        default: break;
    }
    return false;
}
/**
 * 判断变量是否为 JSON
 * @param {variable} v 传入一个变量
 * @returns boolean
 */
function is_json( v ) {
    if ( v === '' || v === null ) { return false; }
    try {
        const check = JSON.parse( v );
        if ( is_array( check ) ) {
            return true;
        } else {
            return false;
        }
    } catch ( error ) {
        return false;
    }
}
/**
 * 判断变量是否为数组或者对象
 * @param {any} v 判断对象
 * @returns boolean
 */
function is_array( v ) {
    if ( v === '' || v === null ) { return false; }
    if ( Array.isArray( v ) || typeof v === 'object' ) {
        return true;
    }
    return false;
}
/**
 * 查询本地存储
 * @param {string} key 键名
 * @returns 查询结果
 */
function get( key ) {
    let data = localStorage.getItem( key );
    if ( is_json( data ) ) { data = JSON.parse( data ); }
    return data;
}
/**
 * 设置本地存储
 * @param {string} key 键名
 * @param {string} value 数据
 * @returns boolean
 */
function set( key, value ) {
    if ( is_array( value ) ) {
        value = JSON.stringify( value );
    }
    return localStorage.setItem( key, value );
}
/**
 * 删除本地存储
 * @param {string} key 键名
 * @returns boolean
 */
function del( key ) {
    return localStorage.removeItem( key );
}
/**
 * 判断变量是否为数字
 * @param {any} v 判断对象
 * @returns boolean
 */
function is_number( v ) {
    if ( typeof v === 'number' ) { return true; }
    if ( typeof v === 'string' ) { return /^-?\d*\.?\d+$/.test( v ); }
    return false;
}
/**
 * 生成 UUID
 * @param {string|boolean} check 检查 UUID
 * @returns UUID
 */
function uuid( check = false ) {
    const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[4][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;
    if ( check ) { return uuidRegex.test( check ); }
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace( /[xy]/g, function( c ) {
        const r = Math.random() * 16 | 0;
        const v = c === 'x' ? r : ( ( r & 0x3 ) | 0x8 );
        return v.toString( 16 );
    });
}
/**
 * 判断变量是否为 UUID
 * @param {any} v 判断对象
 * @returns boolean
 */
function is_uuid( v ) {
    if ( typeof v === 'string' ) { return /^[0-9a-f]{8}-[0-9a-f]{4}-[4][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test( v ); }
    return false;
}
/**
 * 判断变量是否为日期
 * @param {any} str 判断对象
 * @returns boolean
 */
function is_date( dateString ) {
    const regex = /^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/;
    // 检查格式
    if ( !regex.test( dateString ) ) { return false; }
    // 检查日期是否合法
    const [year, month, day] = dateString.split('-').map( Number );
    const date = new Date( year, month - 1, day );
    return date.getFullYear() === year && date.getMonth() + 1 === month && date.getDate() === day;
}
/**
 * 判断变量是否为时间
 * @param {any} str 判断对象
 * @returns boolean
 */
function is_time( input ) {
    const regexFull = /^([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/;
    const regexPartial = /^([01]\d|2[0-3]):([0-5]\d)$/;
    if ( regexFull.test( input ) ) {
        return input;
    }else if ( regexPartial.test( input ) ) {
        return input+":00";
    } else {
        return false;
    }
}
/**
 * 判断变量是否为完整时间
 * @param {any} str 判断对象
 * @returns boolean
 */
function is_datetime( str ) {
    if ( typeof str !== 'string' ) { return false; }
    str = str.split( ' ' );
    if ( !is_date( str[0] ) || is_time( str[1] ) === false ) {
        return false;
    }
    return true;
}
/**
 * 使用语言包
 * @param {string} word 传入对应文本的键
 * @returns 传出语言
 */
function t( word, replace = {} ) {
    let words = word.split( ':' );
    if ( !empty( words[0] ) && !empty( words[1] ) ) {
        return t( words[0] ) + t( words[1] );
    }
    words = words[0].split( '.' );
    let result = tc.text;
    for ( const w of words ) {
        if ( result && typeof result === 'object' && w in result ) {
            result = result[w];
        }else {
            return word;
        }
    }
    if ( !empty( replace ) ) {
        for ( const key in replace ) {
            result = result.replace( `{{${key}}}`, replace[key] );
        }
    }
    return result;
}