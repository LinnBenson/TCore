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
    let result = text;
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
 * 核心工具
 */
window['c'] = {
    complete: false, // 页面加载状态
    id: '', // 用户识别码
    info: {}, // 服务器信息
    themeConfig: {}, // 主题信息
    user: false, // 用户信息
    clipboard: false, // 点击复制实体
    service: {}, // 服务项
    size: { width: 0, height: 0 }, // 屏幕尺寸
    /**
     * 参数同步
     * 主要用于 view / api 请求时服务器同步响应
     */
    update: function() {
        // 同步 identifier
        let identifier = $.cookie( 'identifier' );
        if ( empty( identifier ) || !is_uuid( identifier ) ) {
            identifier = uuid();
        }
        this.id = identifier;
        this.setCache( 'identifier', identifier );
        // 设置 Theme
        let theme = get( 'theme' );
        if ( !empty( theme ) ) {
            this.setCache( 'theme', theme );
        }else { this.setCache( 'theme' ); }
        // 设置 Lang
        let lang = get( 'lang' );
        if ( !empty( lang ) ) {
            this.setCache( 'lang', lang );
        }else { this.setCache( 'lang' ); }
        // 设置 Token
        let token = get( 'token' );
        if ( !empty( token ) ) {
            this.setCache( 'token', token );
        }else { this.setCache( 'token' ); }
        // 添加配置信息
        Object.assign( this, sendConfig );
        document.documentElement.setAttribute( 'lang', this.lang );
        this.size.width = window.innerWidth;
        this.size.height = window.innerHeight;
        document.documentElement.style.setProperty( '--vw', `${window.innerWidth}px` );
        document.documentElement.style.setProperty( '--vh', `${window.innerHeight}px` );
        window.addEventListener( 'resize', () => {
            this.size.width = window.innerWidth;
            this.size.height = window.innerHeight;
            document.documentElement.style.setProperty( '--vw', `${window.innerWidth}px` );
            document.documentElement.style.setProperty( '--vh', `${window.innerHeight}px` );
        });
        // 配置基础信息
        const user = get( 'user' );
        if ( !empty( user ) ) { this.user = user };
        const info = get( 'info' );
        if ( !empty( info ) ) { this.info = info };
        // 从服务器刷新数据
        this.serverInfo();
        // 页面加载完成
        window.onload = function() {
            // 注册组件
            unit.set();
            // 注册点击复制
            c.clipboard = new Clipboard( '.copy' );
            c.clipboard.on( 'success', function() {
                unit.toast( ['true', { type: t( 'copy' ) }] );
            });
            c.clipboard.on( 'error', function() {
                unit.toast( ['false', { type: t( 'copy' ) }], true );
            });
            $( document ).on( 'submit', 'form', function( e ) {
                e.preventDefault();
            });
            // 注册代码输入框
            $( '.code' ).on( 'keydown', function( e ) {
                if ( e.key === 'Tab' ) {
                    e.preventDefault();
                    var cursorPos = this.selectionStart;
                    var text = $( this ).val();
                    var newText = text.substring( 0, cursorPos ) + "    " + text.substring( this.selectionEnd );
                    $( this ).val( newText );
                    this.setSelectionRange( cursorPos + 4, cursorPos + 4 );
                }
            });
        };
    },
    /**
     * 发起一个网络请求
     * @param {array} data 请求数据
     * @returns 请求实体
     */
    send: async function( data, load = false ) {
        if ( load ) { unit.load( true ); }
        let type = 'GET'; let parameter = '';
        if ( !empty( data.post ) ) {
            type = 'POST';
            parameter = is_json( data.post ) ? 'application/json; charset=utf-8' : 'application/x-www-form-urlencoded; charset=utf-8';
        }
        data.other = !empty( data.other ) ? data.other : {};
        return $.ajax({
            url: data.link,
            type: type,
            contentType: parameter,
            data: data.post ? data.post : false,
            headers: this.header(),
            async: data.async === false ? false : true,
            success: function( res ) {
                if ( load ) { unit.load( false ); }
                if ( !empty( data.check ) ) { return c.res( data, res ); }
                if ( typeof data.run === 'function' ) { data.run( res ); }
                return res;
            },
            error: function( res ) {
                if ( load ) { unit.load( false ); }
                // 需要注销登录
                if ( res.status === 403 ) { return c.logout(); }
                // 可能存在的请求正常
                if ( typeof res.responseJSON === 'object' && !empty( res.responseJSON.d ) ) {
                    if ( typeof data.error === 'function' ) { data.error( res.responseJSON ); }
                    if ( typeof res.responseJSON.d === 'string' ) {
                        unit.toast( res.responseJSON.d, true );
                    }
                    return res;
                }
                // 其它异常
                if ( typeof data.error === 'function' ) { data.error( res.responseJSON ); }
                unit.toast( ['error.network'], true ); return res;
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
        if ( typeof res !== 'object' || empty( res.s ) ) {
            if ( typeof data.error === 'function' ) { data.error( res.d ); }
            unit.toast( ['error.unknown'], true );
            return res;
        }
        switch ( res.s ) {
            case 'success':
                if ( typeof data.run === 'function' ) { data.run( res.d ); }
                break;
            case 'fail':
                if ( typeof data.error === 'function' ) { data.error( res.d ); }
                if ( typeof res.d === 'string' ) { unit.toast( res.d ); }
                break;
            case 'error':
                if ( typeof data.error === 'function' ) { data.error( res.d ); }
                if ( typeof res.d === 'string' ) { unit.toast( res.d, true ); }
                break;
            case 'warn':
                if ( typeof data.error === 'function' ) { data.error( res.d ); }
                if ( typeof res.d === 'string' ) { unit.toast( res.d, true ); }

            default:
                if ( typeof data.error === 'function' ) { data.error( res.d ); }
                unit.toast( ['error.unknown'], true ); break;
        }
        return res;
    },
    /**
     * 表单获取
     * @param {form} e 表单指向
     * @param {string|function} run 函数或链接
     * @returns false
     */
    form: function( e, run = false ) {
        // 获取表单数据
        let data;
        if ( e instanceof jQuery ) {
            data = {
                [e.attr( 'name' )]: e.val()
            };
        }else {
            const $form = $( e );
            let inputs = $form.serializeArray();
            data = {};
            for ( const input of inputs ) { data[input['name']] = input['value']; }
        }
        // 标记错误
        let showError = function( id ) {
            $( `.${id}` ).addClass( 'error' );
            setTimeout(() => { $( `.${id}` ).removeClass( 'error' ); }, 1000 );
        }
        // 验证逻辑
        for ( const name in data ) {
            if ( !empty( rule[name] ) && is_array( rule[name] ) ) {
                if ( rule[name].type === 'md' ) {
                    data[name] = val[`markdown_${name}`].value();
                }
                const itemRule = rule[name]; const value = data[name];
                // 必填验证
                if ( !empty( itemRule.must ) && empty( value ) && value !== 0 && value !== '0' ) {
                    showError( itemRule.id );
                    unit.toast( ['form.must',{name:itemRule.title}], true );
                    return false;
                }
                // 类型验证
                if ( !empty( itemRule.type ) ) {
                    switch ( itemRule.type ) {
                        case 'number':
                            if ( !empty( value ) ) {
                                if ( !is_number( value ) ) {
                                    showError( itemRule.id );
                                    unit.toast( ['form.number',{name:itemRule.title}], true );
                                    return false
                                }
                                data[name] = empty( value ) ? 0 : parseFloat( value );
                            }
                            break;
                        case 'json':
                            if ( !empty( value ) ) {
                                if ( !is_json( value ) ) {
                                    showError( itemRule.id );
                                    unit.toast( ['form.json',{name:itemRule.title}], true );
                                    return false
                                }
                            }
                            break;
                        case 'boolean':
                            data[name] = empty( value ) || value === 'off' ? false : true;
                            break;
                        case 'email':
                            if ( !empty( value ) ) {
                                if ( !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test( value ) ) {
                                    showError( itemRule.id );
                                    unit.toast( ['form.email',{name:itemRule.title}], true );
                                    return false
                                }
                            }
                            break;
                        case 'date':
                            if ( !empty( value ) ) {
                                if ( !is_date( value ) ) {
                                    showError( itemRule.id );
                                    unit.toast( ['form.date',{name:itemRule.title}], true );
                                    return false
                                }
                            }
                            break;
                        case 'time':
                            if ( !empty( value ) ) {
                                let check = is_time( value );
                                if ( check === false ) {
                                    showError( itemRule.id );
                                    unit.toast( ['form.time',{name:itemRule.title}], true );
                                    return false
                                }
                                data[name] = check;
                            }
                            break;

                        default: break;
                    }
                }
                // 大小验证
                if ( itemRule.type !== 'upload' ) {
                    if ( !empty( itemRule.min ) || itemRule.min === 0 ) {
                        if ( itemRule.type === 'number' ) {
                            if ( data[name] < itemRule.min && ( !empty( value ) || value === 0 || value === '0' ) ) {
                                showError( itemRule.id );
                                unit.toast( ['form.minNumber',{name:itemRule.title,set:itemRule.min}], true );
                                return false
                            }
                        }else {
                            if ( data[name].length < itemRule.min && ( !empty( value ) || value === 0 || value === '0' ) ) {
                                showError( itemRule.id );
                                unit.toast( ['form.minString',{name:itemRule.title,set:itemRule.min}], true );
                                return false
                            }
                        }
                    }
                    if ( !empty( itemRule.max ) || itemRule.min === 0 ) {
                        if ( itemRule.type === 'number' ) {
                            if ( data[name] > itemRule.max && ( !empty( value ) || value === 0 || value === '0' ) ) {
                                showError( itemRule.id );
                                unit.toast( ['form.maxNumber',{name:itemRule.title,set:itemRule.max}], true );
                                return false
                            }
                        }else {
                            if ( data[name].length > itemRule.max && ( !empty( value ) || value === 0 || value === '0' ) ) {
                                showError( itemRule.id );
                                unit.toast( ['form.maxString',{name:itemRule.title,set:itemRule.max}], true );
                                return false
                            }
                        }
                    }
                }
            }
            // 可能存在的 datetime
            if ( name.includes( '-date' ) ) {
                let newName = name.replace( "-date", "" );
                let date = data[name]; let time = is_time( data[`${newName}-time`] );
                let newValue = `${date} ${time}`;
                if ( !empty( rule[newName] ) && is_array( rule[newName] ) ) {
                    const itemRule = rule[newName];
                    // 必填验证
                    if ( !empty( itemRule.must ) && empty( newValue ) ) {
                        showError( itemRule.id );
                        unit.toast( ['form.must',{name:itemRule.title}], true );
                        return false;
                    }
                    // 有效验证
                    if ( !empty( newValue ) && !is_datetime( newValue ) ) {
                        showError( itemRule.id );
                        unit.toast( ['form.datetime',{name:itemRule.title}], true );
                        return false;
                    }
                }
                data[newName] = newValue;
            }
            // 手机号验证
            if ( name === 'qv' || name === 'phone' ) {
                const itemRule = rule['phone'];
                if ( !empty( itemRule.must ) && !empty( itemRule.must ) && empty( data[name] ) ) {
                    showError( itemRule.id );
                    unit.toast( ['form.must',{name:t(`form.${name}`)}], true );
                    return false;
                }
                if ( !empty( itemRule ) && !empty( data[name] ) && !is_number( data[name] ) ) {
                    showError( itemRule.id );
                    unit.toast( ['form.number',{name:t(`form.${name}`)}], true );
                    return false;
                }
            }
        }
        // 可能存在的 datetime
        for ( const key in data ) {
            if ( data.hasOwnProperty( key ) ) {
                if ( key.endsWith( "-date" ) || key.endsWith( "-time"  ) ) { delete data[key]; }
            }
        }
        // 执行方法
        if ( typeof run === 'function' ) { run( data ); }
        // 返回数据
        return data;
    },
    /**
     * 刷新信息
     * 每次页面加载时请求一次，用于实时更新服务器信息和用户数据
     */
    serverInfo: function() {
        this.send({
            link: '/api/base',
            check: true,
            run: function( res ) {
                // 挂载服务器信息
                if ( !empty( res.info ) ) {
                    c.info = res.info; set( 'info', res.info );
                }else {
                    c.info = {}; del( 'info' );
                }
                // 挂载用户
                if ( !empty( res.userinfo ) ) {
                    c.user = res.userinfo; set( 'user', res.userinfo );
                }else {
                    c.logout( false, false );
                }
                // 挂载主题
                if ( !empty( res.theme ) ) {
                    c.themeConfig = res.theme; set( 'themeConfig', res.theme );
                }else {
                    c.themeConfig = {}; del( 'themeConfig' );
                }
                // 挂载服务器
                if ( !empty( res.service ) ) {
                    for( const item of res.service ) {
                        c.service[item.name] = new wsStructure({
                            host: item.link,
                            restart: true,
                            start: function( e ) {
                                $res = c.header();
                                e.send({
                                    action: 'login',
                                    res: $res
                                });
                            },
                            end: function() {
                                unit.toast( ['error.service',{name:item.name}], true );
                            }
                        } );
                    }
                }
            }
        });
    },
    /**
     * 视图渲染
     * @param {array} vals 替换内容
     * @param {html} code 源码
     * @param {string} div 填充位置
     * @returns boolean|html
     */
    view: function( vals, code, div = false ) {
        // 根据路径解析值
        function resolveValue( obj, path ) {
            const keys = path.split( '.' );
            let result = obj;
            for ( const key of keys ) {
                if ( result && typeof result === 'object' && key in result ) {
                    result = result[key];
                } else {
                    return undefined;
                }
            }
            return typeof result !== 'object' ? result : undefined;
        }
        // 整理模板
        function processTemplate( code, context ) {
            return code.replace( /\{(.*?)\}/g, ( match, key ) => {
                const value = resolveValue( context, key );
                return value !== undefined ? value : match;
            });
        }
        let html = '';
        if ( !Array.isArray( vals ) ) {
            // 单个对象处理
            html = processTemplate( code, vals );
        } else {
            // 数组处理
            for ( const val of vals ) {
                html += processTemplate(code, val);
            }
        }
        return div ? $( div ).html( html ) : html;
    },
    /**
     * 填充内容
     * @param {string} name 选中元素
     * @param {string} text 内容
     */
    text: function( name, text ) {
        $( `[name='${name}']:not(input):not(select):not(textarea)` ).html( text );
    },
    /**
     * 视图加载
     * @param {string} div 选中元素
     * @param {number} timeout 超时时间
     */
    viewLoad: function( div, timeout = false ) {
        if ( timeout === true ) {
            $( div ).css( 'position', '' );
            $( div ).css( 'overflow', '' );
            $( div ).css( 'min-height', '' );
            $( div ).find( 'div.viewLoad' ).remove();
            return true;
        }else {
            $( div ).css( 'position', 'relative' );
            $( div ).css( 'overflow', 'hidden' );
            $( div ).css( 'min-height', '90px' );
            $( div ).append(`
                <div class="viewLoad center">
                    <div>
                        <div class='icon'></div>
                        <div class='text'>Loading</div>
                    </div>
                </div>
            `);
            if ( is_number( timeout ) ) {
                setTimeout(() => {
                    this.viewLoad( div, true );
                }, timeout );
            }
        }
    },
    /**
     * 附加请求头
     * @returns array 请求头数据
     */
    header: function() {
        let data = {
            identifier: get( 'identifier' )
        };
        if ( !empty( get( 'timezone' ) ) ) { data['timezone'] = get( 'timezone' ); }
        if ( !empty( get( 'lang' ) ) ) { data['lang'] = get( 'lang' ); }
        if ( !empty( get( 'token' ) ) ) { data['token'] = get( 'token' ); }
        return data
    },
    /**
     * 设置缓存
     * @param {string} type 缓存类型
     * @param {string} v 缓存值
     * @returns boolean
     */
    setCache: function( type, v = false ) {
        const allow = [ 'identifier', 'lang', 'timezone', 'theme', 'token', 'userinfo' ];
        if ( !allow.includes( type ) ) { return false; }
        if ( empty( v ) ) {
            $.cookie( type, null, { expires: -1, path: '/' } );
            del( type );
            return  true;
        }
        $.cookie( type, v, { expires: 365, path: '/' } );
        set( type, v );
        return true;
    },
    /**
     * 登录到用户
     * @param {array} res 登录数据
     * @param {string} to 跳转地址
     */
    login: function( res, to = false ) {
        if ( !is_array( res ) || empty( res.token ) || empty( res.userinfo ) ) {
            return unit.toast( ['account.loginFalse'], true );
        }
        this.setCache( 'token', res.token );
        set( 'user', res.userinfo );
        unit.toast( ['true',{type:t('account.login')}] );
        if ( to ) {
            return location.href = to;
        }
        window.location.reload();
    },
    /**
     * 注销登录状态
     * @param {boolean} reload 是否刷新页面
     * @param {boolean} toast 是否显示通知
     */
    logout: function( reload = true, toast = true ) {
        if ( toast ) { unit.toast( ['error.403'], true ); }
        this.user = false; del( 'user' ); c.setCache( 'token' );
        if ( reload ) { window.location.reload(); }
    },
    /**
     * 切换密码显示
     * @param {string} div 父级元素
     */
    showPassword: function( div ) {
        const e = $( `${div} input` );
        const state = e.attr( 'type' );
        if ( state === 'password' ) {
            e.attr( 'type', 'text' );
            $( `${div} i.action` ).removeClass( 'bi-eye' );
            $( `${div} i.action` ).addClass( 'bi-eye-slash' );
        }else {
            e.attr( 'type', 'password' );
            $( `${div} i.action` ).removeClass( 'bi-eye-slash' );
            $( `${div} i.action` ).addClass( 'bi-eye' );
        }
    },
    /**
     * 链接地址哈希解析
     * @returns object 哈希信息
     */
    hashParams: function() {
        const hash = window.location.hash.slice( 1 );
        const params = {};
        if ( hash ) {
            hash.split( '&' ).forEach( pair => {
                const [key, value] = pair.split( '=' );
                params[decodeURIComponent( key )] = decodeURIComponent( value || '' );
            });
        }
        return params;
    },
    /**
     * 删除上传文件
     * @param {div} id 父级上传盒子的类名
     */
    deleteUpload: function( id ) {
        $( `div.${id}` ).find( 'input' ).val( '' );
        $(  `div.${id}` ).find( 'div.label div' ).removeClass( 'action' );
        $( `div.${id}` ).find( `div.label div.upload` ).addClass( 'action' );
    },
    babel: function( name = false ) {
        // ReactDOM.createRoot( document.getElementById( 'react' ) ).render( <App /> );
        name = name ? `script[name="${name}"][type="text/babel"]` : `script[type="text/babel"]`;
        $( name ).each(function () {
            const scriptContent = $( this ).html();
            const transformedCode = Babel.transform( scriptContent, { presets: ['react'] } ).code;
            eval( transformedCode );
        });
    }
};
/**
 * 组件
 */
window['unit'] = {
    // 计时器
    loadTime: false,
    toastTime: false,
    loadStartTime: false,
    toasStarttTime: false,
    /**
     * 加载组件
     */
    set: function() {
        html = `
            <link href="/library/style/unit.css?${c.version}" rel="stylesheet" />
            <div id='load' style="opacity: 0;">
                <div class='content center'>
                    <div>
                        <div class='icon'></div>
                        <div class='text'>Loading</div>
                    </div>
                </div>
            </div>
            <div id="toast" style="opacity: 0;">
                <div class='content'>
                    <i class=''></i>
                    <span class='more'></span>
                </div>
            </div>
            <div id="popup" class="center" style="opacity: 0;">
                <div class='content'>
                    <div class="popupTitle r3">
                        <div></div><div></div><div></div>
                        <span></span>
                        <i class=""></i>
                    </div>
                    <div class="contentBody scroll"></div>
                    <div class="button">
                        <button class="button set"></button>
                        <button class="button" onClick="unit.popup( false )"><i class='bi-x-square'></i>${t('close')}</button>
                    </div>
                </div>
            </div>
            <div id="checkBig" class="center" style="opacity: 0;">
                <i class="bi-x-lg block r5 close" onClick="unit.checkBig( false )"></i>
                <img src="" />
            </div>
        `;
        $( 'body' ).prepend( html );
        c.complete = true;
    },
    /**
     * 加载动画组件
     * @param {boolean} state 设置状态
     * @param {number} timeout 关闭时间
     */
    load: function( state, timeout = false ) {
        if ( !c.complete ) {
            clearInterval( this.loadStartTime );
            this.loadStartTime = setInterval(() => {
                this.load( state, timeout );
            }, 100 );
            return false;
        }
        clearInterval( this.toasStarttTime );
        if ( state ) {
            $( 'div#load' ).addClass( 'action' );
            clearTimeout( this.loadTime );
            if ( is_number( timeout ) ) {
                this.loadTime = setTimeout(() => {
                    this.load( false );
                }, timeout );
            }
        }else {
            $( 'div#load' ).removeClass( 'action' );
        }
    },
    /**
     * Toast 通知组件
     * @param {string} text 通知内容
     * @param {boolean} error 是否为错误通知
     * @param {number} timeout 关闭时间
     */
    toast: function( text, error = false, timeout = 5000 ) {
        if ( !c.complete ) {
            clearInterval( this.toasStarttTime );
            this.toasStarttTime = setInterval(() => {
                this.toast( text, error, timeout );
            }, 100 );
            return false;
        }
        clearInterval( this.toasStarttTime );
        if ( Array.isArray( text ) && text.length <= 2 ) { text = t( text[0], text[1] ); }
        const icon = error ? 'bi-question-circle-fill' : 'bi-info-circle-fill';
        $( 'div#toast div.content i' ).attr( 'class', icon );
        $( 'div#toast div.content span' ).text( text );
        if ( error ) {
            $( 'div#toast' ).addClass( 'error' );
        }else {
            $( 'div#toast' ).removeClass( 'error' );
        }
        $( 'div#toast' ).addClass( 'action' );
        unit.action( 'div#toast', 'toastOpenAction' );
        clearTimeout( this.toastTime );
        if ( !is_number( timeout )  ) { timeout = 5000; }
        this.toastTime = setTimeout(() => {
            $( 'div#toast' ).removeClass( 'action' );
        }, timeout );
    },
    popup: function( title, content = '', button = false, width = '480px' ) {
        if ( title === false ) { return $( 'div#popup' ).removeClass( 'action' ); }
        const $popup = $( 'div#popup' );
        let titleIcon = 'bi-info-circle-fill';
        if ( is_array( title ) ) {
            titleIcon = title[0];
            title = title[1];
        }
        $popup.find( 'div.popupTitle i' ).attr( 'class', titleIcon );
        $popup.find( 'div.popupTitle span' ).text( title );
        $popup.find( 'div.contentBody' ).html( content );
        if ( is_array( button ) ) {
            let buttonIcon = !empty( button['icon'] ) ? button['icon'] : 'bi-check2-square';
            let buttonText = !empty( button['text'] ) ? button['text'] : '';
            let buttonRun = !empty( button['run'] ) ? button['run'] : '';
            $popup.find( 'div.button' ).addClass( 'action' );
            $popup.find( 'div.button button.set' ).html( `<i class='${buttonIcon}'></i>${buttonText}` );
            $popup.find( 'div.button button.set' ).attr( 'onClick', buttonRun );
        }else {
            $popup.find( 'div.button' ).removeClass( 'action' );
        }
        $( 'div#popup' ).attr( 'style', `--width: ${width}` );
        $( 'div#popup' ).addClass( 'action' );
    },
    checkBig: function( link ) {
        $checkBig = $( 'div#checkBig' );
        $checkBig.find( 'img' ).attr( 'src', '' );
        if ( link === false ) { return $checkBig.removeClass( 'action' ); }
        link = link.split('?')[0];
        $checkBig.find( 'img' ).attr( 'src', link );
        $checkBig.addClass( 'action' );
    },
    /**
     * 为组件执行动画
     * @param {div} div 需要执行动画的元素
     * @param {string} name 动画名称
     * @param {number} time 动画时间
     */
    action: function( div, name, time = 180 ) {
        $( div ).addClass( name );
        setTimeout(() => {
            $( div ).removeClass( name );
        }, time );
    },
    /**
     * 渲染上传图片
     * @param {*} id 上传图片的 ID
     * @param {*} file 链接
     * @returns 渲染结果
     */
    uploadPreview: function( id, file ) {
        const $img = $( `div.previewImg[name="${id}"]` );
        file = file.split('?')[0];
        if ( /\.(jpg|png|gif|jpeg)$/i.test( file ) ) {
            $img.attr( 'onclick', `event.preventDefault(); unit.checkBig( '${file}' );` );
            return $img.find( 'img' ).attr( 'src', file );
        }
        $img.attr( 'onclick', `event.preventDefault();` );
        return $img.find( 'img' ).attr( 'src', "/library/icon/file.png" );
    }
};

/**
 * 共享变量
 */
window['val'] = {};
/**
 * 表单规则
 */
window['rule'] = {};
/* 上传监听 */
$( document ).on( 'change', 'input.upload_file', function( e ) {
    // 获取上传文件
    const file = this.files[0]; if ( !file ) { return; }
    const fileType = file.type.split( '/' )[1];
    const fileSize = file.size;
    // 获取基本元素并开启加载
    const id = $( this ).attr( 'id' );
    const name = $( this ).attr( 'name' );
    const upload = $( this ).attr( 'upload' );
    const box = `div.formInput div.input.${id}`;
    c.viewLoad( box );
    // 获取上传规则
    const fileRule = rule[name] ? rule[name] : {};
    // 重置上传内容
    const reset = () => {
        const fileVal = $( box ).find( `input[name="${name}"][type="text"]` ).val();
        $( box ).find( 'div.label div' ).removeClass( 'action' );
        if ( !empty( fileVal ) ) {
            $( box ).find( `div.label div.hasFile` ).addClass( 'action' );
            return $( box ).find( `div.label div.hasFile span.fileName` ).text( fileVal );
        }
        $( box ).find( `div.label div.upload` ).addClass( 'action' );
        $( this ).val( '' );
    };
    // 文件检查
    const error = ( err ) => {
        c.viewLoad( box, true );
        $( this ).val( '' );
        unit.toast( err, true );
        reset();
    };
    if ( is_array( fileRule.allow ) && !fileRule.allow.includes( fileType ) ) {
        return error( ['form.fileType',{name:fileRule.title}] );
    }
    if ( is_number( fileRule.min ) && fileSize < fileRule.min ) {
        return error( ['form.fileMin',{name:fileRule.title,set:fileRule.min}] );
    }
    if ( is_number( fileRule.max ) && fileSize < fileRule.max ) {
        return error( ['form.fileMax',{name:fileRule.title,set:fileRule.max}] );
    }
    // 验证通过
    const data = new FormData();
    data.append( name, file );
    c.send({
        link: upload,
        post: data,
        check: true,
        run: function( res ) {
            c.viewLoad( box, true );
            unit.toast( ['true',{type:t('upload')}] );
            $( box ).find( `input[name="${name}"][type="text"]` ).val( res );
            $( box ).find( `input.upload_file` ).val( '' );
            reset();
            unit.uploadPreview( id, res );
        },
        error: function() {
            c.viewLoad( box, true );
            error( ['false',{type:t('upload')}] );
        },
        other: {
            processData: false,
            contentType: false
        }
    });
});