window['tc'] = {
    init: false, // 初始化状态
    text: get( 'text' ) ?? {}, // 语言包
    size: { width: 0, height: 0 }, // 屏幕尺寸
    server: get( 'server' ) ?? {}, // 服务器信息
    user: get( 'user' ) ?? null, // 当前用户
    cache: { form: {} }, // 缓存
    clipboard: null, // 剪贴板对象
    /**
     * 默认加载
     */
    initialization: function() {
        // 设置屏幕尺寸
        document.documentElement.style.setProperty( '--vw', `${window.innerWidth}px` );
        document.documentElement.style.setProperty( '--vh', `${window.innerHeight}px` );
        tc.size.width = window.innerWidth;
        tc.size.height = window.innerHeight;
        window.addEventListener( 'resize', () => {
            tc.size.width = window.innerWidth;
            tc.size.height = window.innerHeight;
            document.documentElement.style.setProperty( '--vw', `${window.innerWidth}px` );
            document.documentElement.style.setProperty( '--vh', `${window.innerHeight}px` );
        });
        // 数据同步
        const cache = [ 'id','lang', 'themeName' ];
        for( const item of cache ) { tc.setCache( item, system[item] ); }
        tc.user = tc.server.user ?? null;
        tc.refresh( 'all' );
        // 加载完成事件
        window.onload = function() {
            if ( typeof system !== 'object' ) { window['system'] = {}; }
            // 注册点击复制
            tc.clipboard = new Clipboard( '.copy' );
            tc.clipboard.on( 'success', function() { tc.unit.toast( ['base.copy:base.true'] ); });
            tc.clipboard.on( 'error', function() { tc.unit.toast( ['base.copy:base.false'], true ); });
            // 页面加载完成
            tc.init = true;
        };
    },
    refresh: function( type ) {
        // 允许的类型
        const types = [ 'text', 'server', 'all' ];
        if ( !types.includes( type ) ) { return false; }
        tc.send({
            url: `/api/base/info${type === 'all' ? '' : `/${type}`}`,
            check: true,
            run: ( res ) => {
                switch ( type ) {
                    case 'all':
                        tc.text = res.text; set( 'text', res.text );
                        tc.server = res.server; set( 'server', res.server );
                        break;
                    case 'server':
                        tc.server = server; set( 'server', server );
                        break;
                    case 'text':
                        tc.text = text; set( 'text', text );
                        break;
                    default: break;
                }
                if ( is_array( tc.server.user ) ) {
                    set( 'user', tc.server.user );
                }else {
                    tc.user = null; tc.setCache( 'token', null ); del( 'user' );
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
    setCache: function( name, value = false ) {
        // 允许的缓存
        const allow = [ 'id', 'lang', 'token', 'themeName', 'theme' ];
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
        data.headers = is_array( data.headers ) ? data.headers : {};
        return $.ajax({
            url: data.url,
            type: !empty( data.type ) ? data.type : 'GET',
            contentType: data.contentType ? data.contentType : parameter,
            data: data.data ? data.data : false,
            headers: { ...tc.header(), ...data.headers },
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
                    if ( res.status === 401 && res.responseJSON.code === 401 ) {
                        tc.logout( true );
                    }
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
     * 登录用户
     * @param {any} res 登录数据
     * @returns {boolean} 是否登录成功
     */
    login: function( res ) {
        if (
            typeof res === 'object' &&
            !empty( res.token ) &&
            !empty( res.user )
        ) {
            tc.setCache( 'token', res.token );
            set( 'user', res.user );
            tc.user = res.user;
            return true;
        }
        return false;
    },
    /**
     * 登出用户
     * @returns {boolean} 是否登出成功
     */
    logout: function( toast = false ) {
        tc.setCache( 'token', null ); del( 'user' );
        tc.user = null;
        if ( toast ) { tc.unit.toast( ['base.error.401'], false ); }
        location.reload();
        return true;
    },
    router: function( method ) {
        function handle() {
            let hash = location.hash.substring( 1 );
            let params = {};
            hash.split( '&' ).forEach( pair => {
                let [ key, value ] = pair.split( '=' );
                if ( key ) params[key] = value;
            });
            method( params );
        }
        window.addEventListener( 'hashchange', handle );
        handle();
    },
    /**
     * 组件调用
     */
    unit: {
        /**
         * Toast 提示
         * @param {string|array} text 提示文本
         * @param {boolean} error 是否为错误提示
         * @param {number} timeout 超时时间
         * @returns {boolean} 返回 true
         */
        toast: function( text, error = false, timeout = 3000 ) {
            clearTimeout( tc.cache.toastTime );
            // 语言包调用
            if ( Array.isArray( text ) && text.length <= 2 ) { text = t( ...text ); }
            // 组件调用
            const $toast = $( `div[ViewUnit] div.unit[toast]` );
            $toast.find( 'div.content i' ).attr( 'class', error ? 'icon bi-question-circle-fill block' : 'icon bi-bell-fill block' );
            $toast.find( 'div.content span' ).html( text );
            error ? $toast.addClass( 'error' ).addClass( 'active' ) : $toast.removeClass( 'error' ).addClass( 'active' );
            this.animation( $toast, 'toastOpen', 150 );
            tc.cache.toastTime = setTimeout(() => {
                $toast.removeClass( 'active' );
            }, timeout );
            return true;
        },
        /**
         * 加载控件
         * @param {boolean} state 加载状态
         * @param {number} timeout 超时时间
         * @returns {boolean} 返回 true
         */
        load: function( state = false, timeout = false ) {
            if ( is_array( state ) ) { return tc.unit.boxLoad( state[0], state[1], timeout ); }
            clearTimeout( tc.cache.loadTime );
            // 组件调用
            const $load = $( `div[ViewUnit] div.unit[load]` );
            state ? $load.addClass( 'active' ) : $load.removeClass( 'active' );
            if ( timeout && typeof timeout === 'number' && timeout > 0 ) {
                tc.cache.loadTime = setTimeout(() => {
                    tc.unit.load( false );
                }, timeout );
            }
            return $load;
        },
        /**
         * 加载盒子
         * @param {string} div 元素选择器
         * @param {boolean} state 加载状态
         * @param {number} timeout 超时时间
         * @returns {boolean} 返回 true
         */
        boxLoad: function( div, state = false, timeout = false ) {
            // 组件调用
            const $load = $( `div[ViewUnit] div.unit[windowLoad]` );
            const $box = $( div );
            $box.css( state ? {
                'position': 'relative', 'overflow': 'hidden', 'min-height': '80px'
            }:{
                'position': '', 'overflow': '', 'min-height': ''
            });
            if ( state ) {
                $box.append( $load.html() );
                if ( timeout && typeof timeout === 'number' && timeout > 0 ) {
                    setTimeout(() => {
                        tc.unit.boxLoad( div, false );
                    }, timeout );
                }
                return true;
            }
            $box.find( 'div.windowLoaderBox' ).remove();
        },
        /**
         * 为元素执行动画
         * @param {jQurey} $element 元素
         * @param {string} name 动画名称
         * @param {number} time 超时时间
         * @returns {jQurey} 返回元素
         */
        animation: function( $element, name, time = 180 ) {
            $element.addClass( name );
            setTimeout(() => {
                $element.removeClass( name );
            }, time );
            return $element;
        }
    },
    /**
     * 表单操作
     */
    form: {
        /**
         * 显示或隐藏密码输入框
         * @param {string} rid 输入框的 rid 属性值
         * @returns {void}
         */
        showPassword: function( rid ) {
            const $input = $( `div[input][rid="${rid}"]` ).find( 'input[type="password"], input[type="text"]' );
            if ( $input.attr( 'type' ) === 'password' ) {
                $input.attr( 'type', 'text' );
                $( `div[input][rid="${rid}"]` ).find( 'i.inputMethod' ).removeClass( 'bi-eye' ).addClass( 'bi-eye-slash' );
            } else {
                $input.attr( 'type', 'password' );
                $( `div[input][rid="${rid}"]` ).find( 'i.inputMethod' ).removeClass( 'bi-eye-slash' ).addClass( 'bi-eye' );
            }
        },
        /**
         * 设置 UUID 输入框的值
         * @param {string} rid 输入框的 rid 属性值
         * @returns {void}
         */
        setUuid: function( rid ) {
            const $input = $( `div[input][rid="${rid}"]` ).find( 'input[type="text"]' );
            if ( $input.length > 0 ) {
                $input.val( uuid() );
            }
        },
        /**
         * 获取表单输入值
         * @param {string} div 输入框所在的元素选择器
         * 格式为 { name: value }
         * @return {object} 返回一个对象，包含所有输入框的值
         */
        value: function( div ) {
            const res = {}; const inputs = {};
            const $div = $( div );
            if ( $div.length === 0 ) { return {}; }
            $div.find( 'div[input]' ).each(function() {
                const rid = $( this ).attr( 'rid' );
                const type = $( this ).attr( 'type' );
                const name = $( this ).attr( 'name' );
                let title = $( this ).find( 'div.title p.title' );
                title = title.length > 0 ? title.text() : name;
                if ( empty( rid ) || empty( type ) || empty( name ) ) { return; }
                switch ( type ) {
                    case 'switch':
                        res[name] = $( this ).find( `input[name="${name}"]` ).is( ':checked' );
                        break;
                    case 'number':
                        const number = $( this ).find( `[name="${name}"]` ).val();
                        res[name] = is_number( number ) ? parseFloat( number ) : '';
                        break;
                    case 'phone':
                        const qv = $( this ).find( `[name="${name}_qv"]` ).val();
                        const phone = $( this ).find( `[name="${name}_number"]` ).val();
                        res[name] = !empty( qv ) && !empty( phone ) ? `+${qv} ${phone}` : '';
                        break;
                    case 'datetime':
                        const date = $( this ).find( `[name="${name}"]` ).val();
                        res[name] = completionDatetime( date );
                        break;
                    case 'date':
                        const dateValue = $( this ).find( `[name="${name}"]` ).val();
                        res[name] = dateValue ? dateValue : '';
                        break;
                    case 'time':
                        const time = $( this ).find( `[name="${name}"]` ).val();
                        res[name] = completionTime( time );
                        break;
                    case 'upload':
                        const files = $( this ).find( `[name="${name}"]` ).val();
                        res[name] = is_json( files ) ? files : '[]';
                        break;
                    default:
                        res[name] = $( this ).find( `[name="${name}"]` ).val();
                        break;
                }
                inputs[rid] = {
                    title: title,
                    value: res[name],
                    type: type,
                    name: name
                };
            });
            const setInputError = ( rid ) => {
                const $input = $( `div[input][rid="${rid}"]` );
                if ( $input.length > 0 ) {
                    $input.addClass( 'error' );
                    setTimeout(() => {
                        $input.removeClass( 'error' );
                    }, 5000 );
                }
                return false;
            };
            // 校验值
            for ( const key in inputs ) {
                const rule = tc.cache.form[key] ?? {};
                if ( empty( rule ) ) { continue; }
                const input = inputs[key];
                const type = rule.type ?? input.type;
                if ( rule.required === true && empty( input.value ) ) {
                    tc.unit.toast( t( 'vaildata.required', { name: input.title } ), true );
                    return setInputError( key );
                }
                if ( input.value === '' ) { continue; }
                switch ( type ) {
                    case 'email':
                        if ( !/^([a-zA-Z0-9._%+-]+)@([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})$/.test( input.value ) ) {
                            tc.unit.toast( t( 'vaildata.type.email', { name: input.title } ), true );
                            return setInputError( key );
                        }
                        break;
                    case 'phone':
                        if ( !/^(\+\d{1,3}\s)?\d{10}$/.test( input.value ) ) {
                            tc.unit.toast( t( 'vaildata.type.phone', { name: input.title } ), true );
                            return setInputError( key );
                        }
                        break;
                    case 'number':
                        if ( !is_number( input.value ) ) {
                            tc.unit.toast( t( 'vaildata.type.number', { name: input.title } ), true );
                            return setInputError( key );
                        }
                        break;
                    case 'uuid':
                        if ( !is_uuid( input.value ) ) {
                            tc.unit.toast( t( 'vaildata.type.uuid', { name: input.title } ), true );
                            return setInputError( key );
                        }
                        break;
                    case 'json':
                        if ( !is_json( input.value ) ) {
                            tc.unit.toast( t( 'vaildata.type.json', { name: input.title } ), true );
                            return setInputError( key );
                        }
                        break;
                    case 'alnum':
                        if ( !/^[a-zA-Z0-9]+$/.test( input.value ) ) {
                            tc.unit.toast( t( 'vaildata.type.alnum', { name: input.title } ), true );
                            return setInputError( key );
                        }
                        break;
                    case 'datetime':
                        if ( !/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test( input.value ) ) {
                            tc.unit.toast( t( 'vaildata.type.datetime', { name: input.title } ), true );
                            return setInputError( key );
                        }
                        break;
                    case 'date':
                        if ( !/^\d{4}-\d{2}-\d{2}$/.test( input.value ) ) {
                            tc.unit.toast( t( 'vaildata.type.date', { name: input.title } ), true );
                            return setInputError( key );
                        }
                        break;
                    case 'time':
                        if ( !/^\d{2}:\d{2}:\d{2}$/.test( input.value ) ) {
                            tc.unit.toast( t( 'vaildata.type.time', { name: input.title } ), true );
                            return setInputError( key );
                        }
                        break;

                    default: break;
                }
                if ( type === 'number' ) {
                    if ( is_number( rule.min ) && input.value < rule.min ) {
                        tc.unit.toast( t( 'vaildata.size.min', { name: input.title, size: rule.min } ), true );
                        return setInputError( key );
                    }
                    if ( is_number( rule.max ) && input.value > rule.max ) {
                        tc.unit.toast( t( 'vaildata.size.max', { name: input.title, size: rule.max } ), true );
                        return setInputError( key );
                    }
                }else if ( type === 'upload' ) {
                    let quantity = is_json( input.value ) ? JSON.parse( input.value ) : [];
                    quantity = quantity.length;
                    if ( is_number( rule.min ) && quantity < rule.min ) {
                        tc.unit.toast( t( 'vaildata.quantity.min', { name: input.title, quantity: rule.min } ), true );
                        return setInputError( key );
                    }
                    if ( is_number( rule.max ) && quantity > rule.max ) {
                        tc.unit.toast( t( 'vaildata.quantity.max', { name: input.title, quantity: rule.max } ), true );
                        return setInputError( key );
                    }
                }else {
                    if ( is_number( rule.min ) && input.value.length < rule.min ) {
                        tc.unit.toast( t( 'vaildata.length.min', { name: input.title, length: rule.min } ), true );
                        return setInputError( key );
                    }
                    if ( is_number( rule.max ) && input.value.length > rule.max ) {
                        tc.unit.toast( t( 'vaildata.length.max', { name: input.title, length: rule.max } ), true );
                        return setInputError( key );
                    }
                }
            }
            return res;
        },
        /** 提交表单
         * @param {string} rid 表单的 rid 属性值
         * @param {string} submit 提交回调函数
         * @return {boolean} 返回 true 如果提交成功，否则返回 false
         **/
        submit: function( rid, submit ) {
            event.preventDefault();
            const $form = $( `div[form][rid="${rid}"] form` );
            if ( $form.length === 0 ) { return false; }
            const data = tc.form.value( `div[form][rid="${rid}"] form` );
            if ( data === false ) { return false; }
            submit( data, rid );
            return true;
        },
        /**
         * 上传文件
         * @param {string} rid 输入框的 rid 属性值
         * @param {File} file 要上传的文件
         * @param {string} callback 上传回调地址
         * @returns {boolean} 返回 true 如果上传成功，否则返回 false
         */
        upload: function( rid, file, callback ) {
            const $input = $( `div[input][rid="${rid}"]` );
            const rule = tc.cache.form[rid] ?? {};
            const name = $( `div[input][rid="${rid}"] input[type="file"]` ).attr( 'to' );
            const $save = $( `div[input][rid="${rid}"] input[name="${name}"]` );
            let oldValue = $save.val(); oldValue = is_json( oldValue ) ? JSON.parse( oldValue ) : [];
            let title = $input.find( 'div.title p.title' );
            title = title.length > 0 ? title.text() : name;
            // 检查是否超过上传限制
            if ( is_number( rule.max ) && oldValue.length >= rule.max ) {
                tc.unit.toast( t( 'vaildata.quantity.max', { name: title, quantity: rule.max } ), true );
                return false;
            }
            // 类型要求
            let fileType = 'other';
            if ( !empty( rule.allow ) && rule.allow !== '*' ) {
                fileType = file.name.split( '.' ).pop().toUpperCase();
                const allow = rule.allow.split( ',' );
                if ( !allow.includes( fileType ) && !allow.includes( fileType.toLowerCase() ) ) {
                    tc.unit.toast( [ 'vaildata.type.upload', { name: title } ], true );
                    return false;
                }
            }
            // 生成上传预览
            const id = uuid();
            $input.find( 'ul.uploadFiles span.uploadFile' ).append(`
                <li rid="${id}" class="uploadFileItem uploading">
                    <img src="" alt="File" />
                    <div class="uploadDelete" onclick="tc.form.uploadDelete( '${rid}', '${id}' )">
                        <p class="r5">${t( 'base.delete' )}</p>
                    </div>
                </li>
            `);
            fileType = file.name.split( '.' ).pop().toLowerCase();
            const img = [ 'jpg', 'png', 'gif', 'svg', 'jpeg' ];
            if ( img.includes( fileType ) ) {
                let reader = new FileReader();
                reader.onload = function( e ) {
                    $input.find( `ul.uploadFiles span.uploadFile li[rid="${id}"] img` ).attr( 'src', e.target.result ).show();
                };
                reader.readAsDataURL( file );
            }else {
                $input.find( `ul.uploadFiles span.uploadFile li[rid="${id}"] img` ).attr( 'src', '/viewrenderer/assets?file=icon/file.png' );
            }
            // 上传内容
            let formData = new FormData();
            formData.append( 'upload', file );
            $input.find( 'ul.uploadFiles li.uploadButton' ).addClass( 'hidden' );
            tc.send({
                url: callback,
                data: formData,
                check: true,
                other: {
                    contentType: false,
                    processData: false,
                    xhr: function() {
                        let xhr = new XMLHttpRequest();
                        xhr.upload.onprogress = function( event ) {
                            if ( event.lengthComputable ) {
                                let percent = Math.round( ( event.loaded / event.total ) * 100 );
                                $input.find( `ul.uploadFiles span.uploadFile li[rid="${id}"]` ).css({ '--uploading': `${percent}%` });
                            }
                        };
                        return xhr;
                    },
                },
                run: function( res ) {
                    $input.find( `ul.uploadFiles span.uploadFile li[rid="${id}"]` ).removeClass( 'uploading' );
                    oldValue.push( res );
                    if ( !is_number( rule.max ) || oldValue.length < rule.max ) {
                        $input.find( 'ul.uploadFiles li.uploadButton' ).removeClass( 'hidden' );
                    }
                    $input.find( `ul.uploadFiles span.uploadFile li[rid="${id}"]` ).attr( 'bind', encodeURIComponent( res ) );
                    $save.val( JSON.stringify( oldValue ) );
                    tc.unit.toast( t( 'base.upload:base.true' ) );
                },
                error: function( res ) {
                    $input.find( 'ul.uploadFiles li.uploadButton' ).removeClass( 'hidden' );
                    $input.find( `ul.uploadFiles span.uploadFile li[rid="${id}"]` ).remove();
                }
            });
        },
        /**
         * 删除上传的文件
         * @param {string} rid 输入框的 rid 属性值
         * @param {string} id 上传文件的唯一标识符
         * @returns {boolean} 返回 true 如果删除成功，否则返回 false
         * */
        uploadDelete: function( rid, id ) {
            const $input = $( `div[input][rid="${rid}"]` );
            const $save = $input.find( `input[name="${$input.find( 'input[type="file"]' ).attr( 'to' )}"]` );
            let oldValue = $save.val(); oldValue = is_json( oldValue ) ? JSON.parse( oldValue ) : [];
            const $item = $input.find( `ul.uploadFiles span.uploadFile li[rid="${id}"]` );
            if ( $item.length === 0 ) { return false; }
            const bind = decodeURIComponent( $item.attr( 'bind' ) );
            const rule = tc.cache.form[rid] ?? {};
            // 删除预览
            $item.remove();
            // 删除数据
            oldValue = oldValue.filter( item => item !== bind );
            $save.val( JSON.stringify( oldValue ) );
            if ( !is_number( rule.max ) || oldValue.length < rule.max ) {
                $input.find( 'ul.uploadFiles li.uploadButton' ).removeClass( 'hidden' );
            }
            tc.unit.toast( t( 'base.delete:base.true' ) );
        }
    },
    markdown: {
        // 获取输入框的内容
        input: function( rid, paragraph = false ) {
            const $box = $( `div.markdownEditor[bind="${rid}"]` );
            const $textarea = $box.find( 'textarea' )[0];
            let start = ''; let end = ''; let focus = '';
            if ( paragraph ) {
                start = $textarea.value.substring( 0, $textarea.selectionStart );
                // 找最后一个换行符，如果有则从开始计算到最后一个换行符
                const lastNewlineIndex = start.lastIndexOf( '\n' );
                if ( lastNewlineIndex !== -1 ) {
                    start = start.substring( 0, lastNewlineIndex );
                    end = $textarea.value.substring( lastNewlineIndex ).replace( /^\n/, '' );
                }else {
                    start = '';
                    end = $textarea.value.substring( 0 );
                }
                focus = $textarea.selectionStart;
            }else {
                start = $textarea.value.substring( 0, $textarea.selectionStart );
                end = $textarea.value.substring( $textarea.selectionEnd );
                focus = $textarea.selectionStart;
            }
            // 选中内容
            const selected = $textarea.value.substring( $textarea.selectionStart, $textarea.selectionEnd );
            return { start, end, selected, focus };
        },
        // 设置输入框的值
        value: function( rid, value ) {
            $( `div.markdownEditor[bind="${rid}"]` ).find( 'textarea' )[0].value = value;
            tc.markdown.change( rid );
        },
        // 设置输入框的值并更新内容
        focus: function( rid, startNum, endNum = null ) {
            if ( empty( endNum ) ) { endNum = startNum; }
            $( `div.markdownEditor[bind="${rid}"]` ).find( 'textarea' ).focus()[0].setSelectionRange( startNum, endNum );
        },
        // 打开或关闭标题编辑器
        titleOpen: function( rid, state = true ) {
            const $box = $( `div.markdownEditor[bind="${rid}"]` );
            if ( !state ) {
                $box.find( 'div.titles' ).removeClass( 'active' );
                return true;
            }
            $box.find( 'div.titles' ).addClass( 'active' );
            return true;
        },
        // 打开或关闭预览
        preview: function( rid, state = true ) {
            const $box = $( `div.markdownEditor[bind="${rid}"]` );
            if ( !state ) {
                $box.find( 'div.markdownPreview' ).removeClass( 'active' );
                return true;
            }
            $box.find( 'div.markdownPreview' ).addClass( 'active' );
            const value = $box.find( 'textarea' )[0].value;
            $box.find( 'div.markdownPreview div.previewContent' ).html( tc.markdown.render( value ) );
            return true;
        },
        // 输入变化事件
        change: function( rid ) {
            const $box = $( `div.markdownEditor[bind="${rid}"]` );
            const value = $box.find( 'textarea' )[0].value;
            // 更新字数
            $box.find( 'div.markdownInfo span.wordCount' ).text( value.length );
            // 检查备份
            if ( !empty( get( `markdown_${$box.find( 'textarea' ).attr( 'name' )}` ) ) ) {
                $box.find( 'div.markdownInfo span.record' ).removeClass( 'hidden' );
            }else {
                $box.find( 'div.markdownInfo span.record' ).addClass( 'hidden' );
            }
        },
        keydown: function( rid, event ) {
            if ( event.key === 'Enter' ) {
                const { start, end } = tc.markdown.input( rid );
                let lines = start.split('\n');
                let lastLine = lines[lines.length - 1];
                let unorderedMatch = lastLine.match(/^\s*([-*+])\s+/);
                if (unorderedMatch) {
                    let symbol = unorderedMatch[1];
                    tc.markdown.value( rid, `${start}\n${symbol} ${end}`);
                    tc.markdown.focus( rid, start.length + 1 + 2); // 换行+符号+空格
                    event.preventDefault();
                    return;
                }
                let orderedMatch = lastLine.match(/^(\s*)(\d+)\.\s+/);
                if (orderedMatch) {
                    let prefix = orderedMatch[1];
                    let number = parseInt(orderedMatch[2], 10) + 1;
                    tc.markdown.value( rid, `${start}\n${prefix}${number}. ${end}`);
                    tc.markdown.focus( rid, start.length + prefix.length + number.toString().length + 3); // 换行+数字+点+空格
                    event.preventDefault();
                    return;
                }
            }
        },
        // 插入标题
        title: function( rid, level ) {
            const { start, end, focus } = tc.markdown.input( rid, true );
            let title = '';
            switch ( level ) {
                case 1: title = '# '; break;
                case 2: title = '## '; break;
                case 3: title = '### '; break;
                case 4: title = '#### '; break;
                case 5: title = '##### '; break;
                case 6: title = '###### '; break;
                default: title = '# '; break;
            }
            tc.markdown.value( rid, `${start}${start === '' ? '' : '\n'}${title}${end}` );
            tc.markdown.focus( rid, focus + level + 2 );
            tc.markdown.titleOpen( rid, false );
        },
        // 粗体文本
        bold: function( rid ) {
            const { start, end, selected } = tc.markdown.input( rid );
            if ( selected ) {
                tc.markdown.value( rid, `${start}**${selected}**${end}` );
                tc.markdown.focus( rid, start.length + 2, start.length + selected.length + 2 );
            } else {
                tc.markdown.value( rid, `${start}****${end}` );
                tc.markdown.focus( rid, start.length + 2 );
            }
        },
        // 斜体文本
        italic: function( rid ) {
            const { start, end, selected } = tc.markdown.input( rid );
            if ( selected ) {
                tc.markdown.value( rid, `${start}*${selected}*${end}` );
                tc.markdown.focus( rid, start.length + 1, start.length + selected.length + 1 );
            } else {
                tc.markdown.value( rid, `${start}**${end}` );
                tc.markdown.focus( rid, start.length + 1 );
            }
        },
        // 删除线
        strikethrough: function( rid ) {
            const { start, end, selected } = tc.markdown.input( rid );
            if ( selected ) {
                tc.markdown.value( rid, `${start}~~${selected}~~${end}` );
                tc.markdown.focus( rid, start.length + 2, start.length + selected.length + 2 );
            } else {
                tc.markdown.value( rid, `${start}~~~~${end}` );
                tc.markdown.focus( rid, start.length + 2 );
            }
        },
        // 上标文本
        superscript: function( rid ) {
            const { start, end, selected } = tc.markdown.input( rid );
            if ( selected ) {
                tc.markdown.value( rid, `${start}^${selected}^${end}` );
                tc.markdown.focus( rid, start.length + 1, start.length + selected.length + 1 );
            } else {
                tc.markdown.value( rid, `${start}^^${end}` );
                tc.markdown.focus( rid, start.length + 1 );
            }
        },
        // 下标文本
        subscript: function( rid ) {
            const { start, end, selected } = tc.markdown.input( rid );
            if ( selected ) {
                tc.markdown.value( rid, `${start}~${selected}~${end}` );
                tc.markdown.focus( rid, start.length + 1, start.length + selected.length + 1 );
            } else {
                tc.markdown.value( rid, `${start}~~${end}` );
                tc.markdown.focus( rid, start.length + 1 );
            }
        },
        // 分隔线
        dividing: function( rid ) {
            const { start, end } = tc.markdown.input( rid );
                tc.markdown.value( rid, `${start}\n\n---\n\n${end}` );
                tc.markdown.focus( rid, start.length + 7 );
        },
        // 引用文本
        quote: function( rid ) {
            const { start, end, focus } = tc.markdown.input( rid, true );
            tc.markdown.value( rid, `${start}${start === '' ? '' : '\n'}> ${end}` );
            tc.markdown.focus( rid, focus + 2 );
        },
        // 无序列表
        unorderedList: function( rid ) {
            const { start, end, focus } = tc.markdown.input( rid, true );
            tc.markdown.value( rid, `${start}${start === '' ? '' : '\n'}- ${end}` );
            tc.markdown.focus( rid, focus + 2 );
        },
        // 有序列表
        orderedList: function( rid ) {
            const { start, end, focus } = tc.markdown.input( rid, true );
            tc.markdown.value( rid, `${start}${start === '' ? '' : '\n'}1. ${end}` );
            tc.markdown.focus( rid, focus + 3 );
        },
        // 链接
        link: function( rid ) {
            const { start, end, selected } = tc.markdown.input( rid );
            if ( selected ) {
                tc.markdown.value( rid, `${start}[${selected}](https://)${end}` );
                tc.markdown.focus( rid, start.length + 1, start.length + selected.length + 1 );
            } else {
                tc.markdown.value( rid, `${start}[](https://)${end}` );
                tc.markdown.focus( rid, start.length + 1 );
            }
        },
        // 行代码
        code: function( rid ) {
            const { start, end, selected } = tc.markdown.input( rid );
            if ( selected ) {
                tc.markdown.value( rid, `${start}\`${selected}\`${end}` );
                tc.markdown.focus( rid, start.length + 1, start.length + selected.length + 1 );
            } else {
                tc.markdown.value( rid, `${start}\`\`${end}` );
                tc.markdown.focus( rid, start.length + 1 );
            }
        },
        // 代码块
        codeBlock: function( rid ) {
            const { start, end, focus } = tc.markdown.input( rid );
            tc.markdown.value( rid, `${start}\n\`\`\`\n\n\`\`\`\n${end}` );
            tc.markdown.focus( rid, focus + 5 );
        },
        // 全屏调整
        window: function( rid ) {
            const $box = $( `div.markdownEditor[bind="${rid}"]` );
            if ( $box.hasClass( 'full' ) ) {
                $box.removeClass( 'full' );
                $box.find( 'i.window' ).attr( 'class', 'window bi-arrow-up-right-square block' );
            }else {
                $box.addClass( 'full' );
                $box.find( 'i.window' ).attr( 'class', 'window bi-arrow-down-left-square block' );
            }
        },
        // 保存内容
        save: function( rid ) {
            const $input = $( `div.markdownEditor[bind="${rid}"] textarea` );
            const value = $input.val();
            if ( empty( value ) ) {
                del( `markdown_${$input.attr( 'name' ) }` );
            }else {
                set( `markdown_${$input.attr( 'name' )}`, value );
            }
            tc.unit.toast( t( 'base.save:base.true' ) );
            tc.markdown.change( rid );
        },
        // 还原内容
        revert: function( rid ) {
            const $box = $( `div.markdownEditor[bind="${rid}"]` );
            const value = get( `markdown_${$box.find( 'textarea' ).attr( 'name' )}` );
            if ( value ) {
                tc.markdown.value( rid, value );
                return tc.unit.toast( t( 'base.revert:base.true' ) );
            }
            tc.unit.toast( t( 'base.revert:base.false' ) );
        },
        // 上传图片
        image: function( rid, file ) {
            // 类型要求
            fileType = file.name.split( '.' ).pop().toUpperCase();
            const allow = [ 'JPG', 'JPEG', 'PNG', 'GIF', 'WEBP' ];
            if ( !allow.includes( fileType ) && !allow.includes( fileType.toLowerCase() ) ) {
                tc.unit.toast( [ 'vaildata.type.upload', { name: 'Image' } ], true );
                return false;
            }
            // 上传内容
            tc.unit.load( [ `div[input][rid="${rid}"] div.markdownEditor`, true ] );
            let formData = new FormData();
            formData.append( 'upload', file );
            tc.send({
                url: '/storage/upload/cache',
                data: formData,
                check: true,
                other: {
                    contentType: false,
                    processData: false,
                    timeout: 30000,
                },
                run: function( res ) {
                    tc.unit.load( [ `div[input][rid="${rid}"] div.markdownEditor`, false ] );
                    tc.unit.toast( t( 'base.upload:base.true' ) );
                    const { start, end, selected } = tc.markdown.input( rid );
                    if ( selected ) {
                        tc.markdown.value( rid, `${start}![${selected}](${res})${end}` );
                        tc.markdown.focus( rid, start.length + 2, start.length + selected.length + 2 );
                    } else {
                        tc.markdown.value( rid, `${start}![](${res})${end}` );
                        tc.markdown.focus( rid, start.length + 2 );
                    }
                },
                error: function( res ) {
                    tc.unit.load( [ `div[input][rid="${rid}"] div.markdownEditor`, false ] );
                }
            });
        }
    }
};
/**
 * 判断变量是否有值
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
 * 时间补全
 * @param {any} inputValue 参数
 * @returns string
 */
function completionTime( inputValue ) {
    if ( !inputValue ) return '';
    let [hours, minutes, seconds = '00'] = inputValue.split( ':' );
    return `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}:${seconds.padStart(2, '0')}`;
}
/**
 * 完整日期时间补全
 * @param {any} inputValue 参数
 * @returns string
 */
function completionDatetime( inputValue ) {
    if ( !inputValue ) return '';
    const date = new Date( inputValue );
    const year = date.getFullYear();
    const month = String( date.getMonth() + 1 ).padStart( 2, '0' );
    const day = String( date.getDate() ).padStart( 2, '0' );
    const hours = String( date.getHours() ).padStart( 2, '0' );
    const minutes = String( date.getMinutes() ).padStart( 2, '0' );
    const seconds = String( date.getSeconds() ).padStart( 2, '0' );
    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}
/**
 * Hash
 * @param {any} value 参数
 * @returns string
 */
function hash( value ) {
    if ( value === '' || typeof value !== 'string' ) { return ''; }
    return CryptoJS.SHA256( value ).toString();
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
// 初始化
tc.initialization();