(function(global , factory){
    "use strict";

    if (typeof modules === 'object' && typeof modules.exports == 'object') {
        modules.exports = factory(global , true);
    } else {
        factory(global);
    }

})(typeof window === 'undefined' ? this : window , function(window , noGlobal){
    function Socket(option){
        if (!G.isObject(option)) {
            option = this.option;
        }

        this.option.identifier  = G.isString(option.identifier) ? option.identifier : this.default.identifier;
        this.option.unique_code = G.isString(option.unique_code) ? option.unique_code : this.default.unique_code;
        this.option.platform    = G.isString(option.platform) ? option.platform : this.default.platform;
        this.option.websocket   = G.isString(option.websocket) ? option.websocket : this.default.websocket;
        // 回调函数
        this.option.open    = option.open;
        this.option.message = option.message;
        this.option.close   = option.close;
        this.option.error   = option.error;
        // 业务回调
        this.option.login   = option.login;

        this.run();
    }

    Socket.prototype = {
        author: 'grayVTouch' ,
        time: '2019-05-21' ,
        option: {
            // 项目标识符
            identifier: '项目标识符' ,
            // 标识符
            unique_code: '' ,
            // pc | andorid | ios | unknow
            platform: 'unknow' ,
            // websocket 地址
            websocket: 'ws://0.0.0.0:9000' ,
            open: null ,
            message: null ,
            close: null ,
            error: null ,
            login: null ,
        } ,

        // 存放回调
        callback: {} ,

        // 主动推送
        listen: {} ,

        // ws 连接实例
        conn: null ,

        // 连接是否打开
        isOpen: false ,

        // 注册后获取
        token: '' ,

        initStatic: function(){

        } ,

        initDynamic: function(){

        } ,

        initWebSocket: function(){
            var conn = new WebSocket(this.option.websocket);
            conn.addEventListener('open' , this.open.bind(this) , false);
            conn.addEventListener('message' , this.message.bind(this) , false);
            conn.addEventListener('close' , this.close.bind(this) , false);
            this.conn = conn;
        } ,

        /**
         * ************************
         * WebSocket 原生功能 start
         * ************************
         */
        open: function(){
            var self = this;
            this.isOpen = true;
            this.login(this.option.unique_code , function(res){
                if (res.code != 200) {
                    console.log('error: ' + res.data);
                    return ;
                }
                res = res.data;
                self.setToken(res);
                if (G.isFunction(self.option.login)) {
                    self.option.login();
                }
            });
            if (G.isFunction(this.option['open'])) {
                this.option['open'].call(this);
            }
        } ,

        message: function(e){
            var res = e.data;
            res = G.jsonDecode(res);
            switch (res.type)
            {
                case 'response':
                    // 响应
                    if (G.isFunction(this.callback[res.request])) {
                        this.callback[res.request](res.data);
                    }
                    break;
                default:
                    // 推送
                    if (G.isFunction(this.listen[res.type])) {
                        this.listen[res.type](res.data);
                    }
            }
            if (G.isFunction(this.option['message'])) {
                this.option['message'].call(this);
            }
        } ,

        close: function(){
            if (G.isFunction(this.option['close'])) {
                this.option['close'].call(this);
            }
        } ,

        error: function(){
            if (G.isFunction(this.option['error'])) {
                this.option['error'].call(this);
            }
        } ,

        /**
         * ************************
         * WebSocket 原生功能 end
         * ************************
         */


        /**
         * ************************
         * 核心功能 start
         * ************************
         */
        on:  function(type , callback){
            this.listen[type] = callback;
        } ,

        // 生成唯一id（针对每一次 socket 通信）
        requestId: function(){
            return G.randomArr(256 , 'mixed' , true);
        } ,

        // 设置 token
        setToken: function(token){
            this.token = token;
        } ,

        // 发送数据
        send: function(router , data , callback){
            data = this.data(router , data);
            this.callback[data.request] = callback;
            return this.websocketSend(data);
        } ,

        reconnect: function(){
            this.initWebSocket();
        } ,

        // websocket 数据发送
        websocketSend: function(data){
            if (!this.isOpen) {
                console.log('websocket 连接已经关闭！开始自动重连...');
                return ;
            }
            return this.conn.send(G.jsonEncode(data));
        } ,

        // 生成规范数据
        data: function(router , data) {
            data = G.isValid(data) ? data : {};
            return {
                router: router ,
                identifier: this.option.identifier ,
                token: this.token ,
                request: this.requestId() ,
                data: data ,
            };
        } ,

        /**
         * ************************
         * WebSocket 原生功能 end
         * ************************
         */

        /**
         * ******************************
         * 业务功能 start
         * ******************************
         */

        // 登录
        login: function(unique_code , callback){
            var self = this;
            return this.send('Login/login' , {
                unique_code: unique_code ,
            } , callback);
        } ,

        // 会话列表
        getSession: function(callback){
            return this.send('Message/session' , null , callback);
        } ,

        // 获取用户信息
        getUser: function(callback){
            return this.send('User/info' , null , callback);
        } ,

        // 历史记录
        groupHistory: function(group_id , group_message_id , callback){
            return this.send('Message/groupHistory' , {
                group_id: group_id ,
                group_message_id: group_message_id ,
            } , callback);
        } ,

        // 获取用户信息
        groupRecent: function(group_id , callback){
            return this.send('Message/groupRecent' , {
                group_id: group_id
            } , callback);
        } ,

        // 平台咨询
        advoise: function(group_id , type , message , extra , callback){
            return this.send('Chat/advoise' , {
                group_id: group_id ,
                type: type ,
                message: message ,
                extra: extra ,
            } , callback);
        } ,

        // 更新群未读消息
        resetGroupUnread: function(group_id , callback){
            return this.send('Message/resetGroupUnread' , {
                group_id: group_id
            } , callback);
        } ,

        /**
         * ******************************
         * 业务功能 end
         * ******************************
         */

        run: function () {
            this.initStatic();
            this.initDynamic();
            this.initWebSocket();
        }
    };

    if (!noGlobal) {
        window.Socket = Socket;
    }

    return Socket
});