(function(){
    "use strict";

    function RTC(option)
    {
        this._default = {
            url: 'ws://127.0.0.1:80' ,
            // ws 打开回调
            open: null ,
            // ws 收到消息回调
            message: null ,
            // ws 关闭回调
            close: null ,
            // ws 发生错误回调
            error: null ,
            // ws 重连成功回调
            reconnect: null ,
            // 项目标识符
            identifier: '' ,
            // 平台标识符
            // 支持的平台标识符有： app|web
            platform: '' ,
            // 调试模式
            debug: false ,
            // 用户身份认证问题
            token: null ,
        };
        if (G.isUndefined(option)) {
            option = this._default;
        }
        this.websocket = null;
        this.url = G.isValid(option.url) ? option.url : this._default.url;

        // 回调函数列表
        this.callback = {};
        // 监听回调函数列表
        this.listen = {};
        // ws 是否已经打开
        this.opened = false;
        // 是否第一次连接
        this.once = true;
        // 项目标识符
        this.identifier = G.isValid(option.identifier) ? option.identifier : this._default.identifier;
        this.platform = G.isValid(option.platform) ? option.platform : this._default.platform;
        this.debug = G.isValid(option.debug) ? option.debug : this._default.debug;
        this.token = G.isValid(option.token) ? option.token : this._default.token;
        // 模拟数据
        this.simulation = {
            // 调试模式
            debug: this.debug ? 'running' : '' ,
            // 模拟的用户
            user_id: 0
        };
        this.open = G.isFunction(option.open) ? option.open : this._default.open;
        this.message = G.isFunction(option.message) ? option.message : this._default.message;
        this.close = G.isFunction(option.close) ? option.close : this._default.close;
        this.error = G.isFunction(option.error) ? option.error : this._default.error;
        this.reconnect = G.isFunction(option.reconnect) ? option.reconnect : this._default.reconnect;

        // 运行程序
        this.run();
    }

    RTC.prototype = {
        // 建立连接
        connect () {
            this.websocket = new WebSocket(this.url);
        } ,

        // 生成随机数
        genRequestId () {
            return G.randomArr(255 , 'mixed' , true);
        } ,

        openEvent () {
            this.opened = true;
            if (G.isFunction(this.open)) {
                this.open.call(this);
            }
            if (!this.once) {
                // 并非第一次打开
                if (G.isFunction(this.reconnect)) {
                    this.reconnect.call(this);
                }
            }
            this.once = false;
        } ,

        messageEvent (e) {
            let data = e.data;
            if (G.isFunction(this.message)) {
                this.message.call(this , data);
            }
            if (!G.isValid(data)) {
                console.log('messageEvent 接收到无效的服务端数据: ' . data);
                return ;
            }
            data = G.jsonDecode(data);
            let callback;
            switch(data.type)
            {
                case 'response':
                    callback = this.callback[data.request];
                    break;
                default:
                    callback = this.listen[data.type];
            }
            if (G.isFunction(callback)) {
                callback.call(this , data.data);
            }
        } ,

        on (type , callback) {
            this.listen[type] = callback;
        } ,

        closeEvent () {
            // 重置链接打开状态
            this.opened = false;
            // 重连
            this.connect();
        } ,

        errorEvent (e) {
            this.opened = false;
            console.log('ws 发生错误' , e);
        } ,

        // ws 发送消息
        send (router , data , callback) {
            if (!this.opened) {
                console.log('websocket 连接尚未打开');
                return false;
            }
            const request = this.genRequestId();
            this.callback[request] = callback;
            data = G.isValid(data) ? data : {};
            data = {
                // 路由地址
                router ,
                // 项目标识符
                identifier: this.identifier ,
                // 平台标识符
                platform: this.platform ,
                // 请求
                request ,
                // 模拟的用户，如果开启了 debug 模式
                user_id: this.simulation.user_id ,
                // 调试模式
                debug: this.simulation.debug ,
                // 登录身份
                token: this.token ,
                // 发送的数据
                data ,
            };
            this.websocket.send(G.jsonEncode(data));
        } ,

        defineEvent () {
            this.websocket.onopen = this.openEvent.bind(this);
            this.websocket.onmessage = this.messageEvent.bind(this);
            this.websocket.onclose = this.closeEvent.bind(this);
            this.websocket.onerror = this.errorEvent.bind(this);
        } ,

        // 登录二维码
        loginQRCode (data , callback) {
            this.send('/Login/loginQRCode' , data , callback);
        } ,

        // 登录二维码
        loginQRCodeForTest (data , callback) {
            this.send('/Login/loginQRCodeForTest' , data , callback);
        } ,

        // 获取会话列表
        session (data , callback) {
            this.send('/Session/session' , data , callback);
        } ,

        // 查看自身用户信息
        self (data , callback) {
            this.send('/User/self' , data , callback);
        } ,

        run () {
            this.connect();
            this.defineEvent();
        } ,
    };

    window.RTC = RTC;
})();