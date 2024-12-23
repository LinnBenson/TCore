class wsStructure {
    /**
     * 构造函数
     */
    constructor( data = {}, link = false ) {
        this.editConfig( data );
        if ( link ) { this.link(); }
    }
    /**
     * 修改配置信息
     * @param {object} data 新配置
     */
    editConfig( data ) {
        this.state = false;
        this.server = false;
        this.host = data['host'] ?? ''; // 请求开始执行函数
        this.start = data['start'] ?? function(){}; // 请求开始执行函数
        this.end = data['end'] ?? function(){}; // 请求结束执行函数
        this.heartbeatTime = data['heartbeatTime'] ?? 15000; // 心跳检查时间
        this.restart = data.restart ? true : false; // 自动重连
        this.method =  data['method'] ?? {}; // 自动执行内容
        if ( typeof data['check'] === 'function' ) { this.check = data['check']; }
    }
    /**
     * 连接服务器
     */
    link() {
        this.server = new WebSocket( this.host );
        // 连接成功
        this.server.onopen = () => {
            this.state = true;
            // 挂载心跳
            this.heartbeatInt = setInterval( () => {
                this.heartbeat();
            }, this.heartbeatTime );
            // 连接开始
            if ( typeof this.start === 'function' ) { this.start( this ); }
        };
        // 监听到消息
        this.server.onmessage = ( e ) => {
            let data = e.data;
            if ( is_json( data ) ) { data = JSON.parse( data ); }
            this.message = data;
            if ( typeof this.start === 'onmessage' ) { this.start( data ); }
            // 收到消息
            if ( typeof this.check === 'function' ) { this.check( data, this ); }
        };
        // 连接断开
        this.server.onclose = () => {
            // 连接结束
            if ( typeof this.end === 'function' ) { this.end( this ); }
            this.state = false;
            clearInterval( this.heartbeatInt );
            // 自动重连
            if ( this.restart === true ) {
                setTimeout( () => { this.link(); }, 3000 );
            }
        };
    }
    check( res ) {
        if ( typeof res !== 'object' || empty( res.s ) ) {
            return res;
        }
        switch ( res.s ) {
            case 'success':
                if ( !empty( res.m ) && typeof this.method[res.m] === 'function' ) {
                    this.method[res.m]( res.d, this );
                }
                break;
            case 'fail':
                if ( typeof res.d === 'string' ) { unit.toast( res.d ); }
                break;
            case 'error':
                if ( typeof res.d === 'string' ) { unit.toast( res.d, true ); }
                break;
            case 'warn':
                if ( typeof res.d === 'string' ) { unit.toast( res.d, true ); }

            default:
                if ( typeof res.d === 'string' ) { unit.toast( res.d, true ); }
        }
        return res;
    }
    /**
     * 心跳处理方式
     * @returns boolean
     */
    heartbeat() {
        if ( this.state === true ) {
            return this.send( 'ping' );
        }
        return false;
    }
    /**
     * 发送消息到服务器
     * @param {any} data 发送数据
     * @returns boolean
     */
    send( data ) {
        if ( !this.state ) { this.link(); }
        if ( this.state === true ) {
            if ( is_array( data ) ) { data = JSON.stringify( data ); }
            return this.server.send( data );
        }
        return false;
    }
    /**
     * 主动关闭连接
     */
    close() {
        this.state = this.restart = false;
        clearInterval( this.heartbeatInt );
        this.server.close();
    }
    /**
     * 添加回调函数
     * @param {string} name 函数名
     * @param {function} func 方法
     */
    addMethod( name, func ) {
        this.method[name] = func;
    }
    /**
     * 删除回调函数
     * @param {string} name 函数名
     */
    delMethod( name ) {
        if ( typeof this.method[name] === 'function' ) {
            delete this.method[name];
        }
    }
}