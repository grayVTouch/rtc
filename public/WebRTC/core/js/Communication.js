(function(global , factory){
    "use strict";

    if (typeof modules == 'object' && typeof modules.exports == 'object') {
        return modules.exports = factory(global , false);
    } else {
        return factory(global , false);
    }
})(typeof window != 'undefined' ? window : this , function (window , noGlobal) {
    "use strict";

    function Communication(selector , option) {
        var container = G(selector);
        if (!container.isDom()) {
            throw new Error('参数 1 错误');
        }
        this.option = {
            // 项目标识符
            identifier: '' ,
            // 唯一码
            unique_code: '' ,
            // pc | andorid | ios | unknow
            platform: 'unknow' ,
            // websocket 地址
            websocket: 'ws://0.0.0.0:9000' ,
            // todo 上线前请求修改
            host: 'unknow host' ,
        };
        if (!G.isObject(option)) {
            option = this.option;
        }
        this.dom = {};
        this.dom.container = container;
        this.option.websocket = G.isString(option.websocket) ? option.websocket : this.option.websocket;
        this.option.unique_code = G.isString(option.unique_code) ? option.unique_code : this.option.unique_code;
        this.option.platform = G.isString(option.platform) ? option.platform : this.option.platform;
        this.option.identifier = G.isString(option.identifier) ? option.identifier : this.option.identifier;
        this.option.host = G.isString(option.host) ? option.host : this.option.host;
        this.option.unread = option.unread;
        this.run();
    }

    Communication.prototype = {
        author: 'grayVTouch' ,
        time: '2019/06/08' ,
        initStatic: function(){
            this.vue = null;
            this.dom.realTimeCommunication = this.dom.container.children({
                className: 'real-time-communication' ,
                tagName: 'div' ,
            } , false , true).first();
        } ,

        initDynamic: function(){

        } ,

        initVue: function(){
            this.vue = new Vue({
                el: this.dom.realTimeCommunication.get(0) ,
                data: {
                    session: [] ,
                    conn: null ,
                    parent: this ,
                    user: {} ,
                    dom: {} ,
                    // 当前会话
                    current: {} ,
                    histories: {} ,
                    history: {
                        loading: false ,
                        all: false ,
                        history: [] ,
                    } ,
                    value: {} ,
                    sessionTempValue: {} ,
                    message: '' ,
                    // 是否尚未选择会话
                    once: true ,
                    loading: {} ,
                    duration: 200 ,
                    isLogin: false ,
                    unread: 0 ,
                    isOnceInit: true ,
                } ,


                mounted: function(){
                    this.initDom();
                    this.initStatic();
                    this.initDynamic();
                    this.initSocket();
                    this.defineEvent();
                } ,
                methods: {
                    initDom: function(){
                        this.dom.container  = G(this.$el);
                        this.dom.close    = G(this.$refs.close);
                        this.dom.leftTop    = G(this.$refs['left-top']);
                        this.dom.leftMid    = G(this.$refs['left-mid']);
                        this.dom.leftBtm    = G(this.$refs['left-btm']);
                        this.dom.history    = G(this.$refs.history);
                        this.dom.input      = G(this.$refs.input);
                        this.dom.textarea      = G(this.$refs.textarea);
                        this.dom.message    = G(this.$refs.message);
                        this.dom.send       = G(this.$refs.send);
                        this.dom.user       = G(this.$refs.user);
                        this.dom.userOuter       = G(this.$refs['user-outer']);
                        this.dom.userIn       = G(this.$refs['user-in']);
                        this.dom.mask       = G(this.$refs.mask);
                        this.dom.avatar       = G(this.$refs.avatar);
                        this.dom.realTimeCommunicationMinimum       = G(this.$refs['real-time-communication-minimum']);
                        this.dom.realTimeCommunicationMaximum       = G(this.$refs['real-time-communication-maximum']);
                    } ,

                    initStatic: function(){
                        this.dom.realTimeCommunicationMaximum.removeClass('hide');
                        this.dom.user.removeClass('hide');
                        this.value.realTimeCommunicationMaximumW       = this.dom.realTimeCommunicationMaximum.width('content-box');
                        this.value.realTimeCommunicationMaximumH       = this.dom.realTimeCommunicationMaximum.height('content-box');
                        this.value.minW = 0;
                        this.value.minH = 0;

                        this.value.userW = this.dom.user.width('content-box');
                        this.value.userH = this.dom.user.height('content-box');
                        this.value.endUserW = this.value.userW * 0.6;
                        this.value.endUserH = this.value.userH * 0.6;
                        this.value.endLeft = (this.value.userW - this.value.endUserW) / 2;
                        this.value.endTop = (this.value.userH - this.value.endUserH) / 2;
                        this.value.endOpacity = 0.6;

                        this.value.time = 300;
                        this.value.short = 150;
                        this.value.extra = 20;
                        this.dom.realTimeCommunicationMaximum.addClass('hide');
                        this.dom.user.addClass('hide');

                    } ,

                    initDynamic: function(){
                        this.value.maxLeft = document.documentElement.clientWidth;
                        this.value.maxTop = document.documentElement.clientHeight;

                        this.value.leftVal = this.value.maxLeft - this.value.realTimeCommunicationMaximumW - this.value.extra;
                        this.value.leftVal = Math.max(0 , this.value.leftVal);
                        this.value.topVal  = this.value.maxTop - this.value.realTimeCommunicationMaximumH - this.value.extra;
                        this.value.topVal = Math.max(0 , this.value.topVal);

                        if (this.isOnceInit) {
                            this.isOnceInit = false;
                            this.dom.realTimeCommunicationMaximum.css({
                                left: this.value.maxLeft + 'px' ,
                                top: this.value.maxTop + 'px' ,
                                width: this.value.minW + 'px' ,
                                height: this.value.minH + 'px' ,
                                right: 'auto' ,
                                opacity: 0 ,
                            });

                            this.dom.userOuter.css({
                                opacity: this.value.endOpacity ,
                                width: this.value.endUserW + 'px' ,
                                height: this.value.endUserH + 'px' ,
                                left: this.value.endLeft + 'px' ,
                                top: this.value.endTop + 'px' ,
                            });
                            this.dom.userIn.css({
                                left: -this.value.endLeft + 'px' ,
                                top:  -this.value.endTop + 'px' ,
                            });

                            this.dom.realTimeCommunicationMaximum.move(document.body , true);
                            this.dom.realTimeCommunicationMinimum.move(document.body , true);
                        }
                    } ,

                    showMaximum: function(){
                        var self = this;
                        this.dom.realTimeCommunicationMaximum.removeClass('hide');
                        this.dom.realTimeCommunicationMaximum.animate({
                            opacity: 1 ,
                            left: this.value.leftVal + 'px' ,
                            top: this.value.topVal + 'px' ,
                            width: this.value.realTimeCommunicationMaximumW + 'px' ,
                            height: this.value.realTimeCommunicationMaximumH + 'px' ,
                        } , function(){
                            // 滚动到底部
                            self.scrollBottom();
                        } , this.value.time);
                    } ,

                    hideMaximum: function(){
                        var self = this;
                        this.dom.realTimeCommunicationMaximum.animate({
                            opacity: 0 ,
                            left: this.value.maxLeft + 'px' ,
                            top: this.value.maxTop + 'px' ,
                            width: '0px' ,
                            height: '0px' ,
                        } , function(){
                            self.dom.realTimeCommunicationMaximum.addClass('hide');
                        } , this.value.time);
                    } ,

                    showMinimum: function(){
                        this.dom.realTimeCommunicationMinimum.removeClass('hide');
                    } ,

                    hideMinimum: function(){
                        this.dom.realTimeCommunicationMinimum.addClass('hide');
                    } ,

                    initSocket: function(){
                        var vue = this;
                        var unique_code = this.parent.option.unique_code;
                        unique_code = G.isValid(unique_code) ?
                            unique_code :
                            (G.s.exists('unique_code') ?
                                G.s.get('unique_code') :
                                '');
                        // websocket 连接
                        this.conn = new Socket({
                            // 基本数据
                            identifier: this.parent.option.identifier ,
                            unique_code: unique_code ,
                            platform: this.parent.option.platform ,
                            websocket: this.parent.option.websocket ,
                            // WebSocket 回调函数
                            login: function(){
                                // 登录成功
                                vue.isLogin = true;
                                // 获取用户信息
                                this.getUser(vue.response.bind(vue , function (res) {
                                    // 活动
                                    vue.user = res;
                                    // 获取会话列表
                                    vue.conn.getSession(vue.response.bind(vue , function(res){
                                        vue.session = res;
                                        if (vue.user.role == 'user') {
                                            // todo 特殊！！！待优化
                                            vue.value.realTimeCommunicationMaximumW = 500;
                                            if (!G.isValid(this.value.repeatInit)) {
                                                vue.value.realTimeCommunicationMaximumLeftVal += 301;
                                                vue.value.repeatInit = true;
                                            }
                                            // 前用户
                                            vue.switchSession(vue.sessionIdForAdvoise());
                                        }
                                    } , null));
                                } , null));

                                // 获取未读消息数量
                                vue.refreshUnreadMessage();
                            } ,
                        });

                        // 群消息
                        this.conn.on('group_message' , function(res){
                            // console.log(res);
                            vue.refreshSession();
                            if (vue.current.session_id != res.session_id) {
                                vue.play();
                                return ;
                            }
                            vue.handleForMessage(res , false , '');
                            vue.conn.resetGroupUnread(vue.current.group_id);
                            vue.refreshUnreadMessage();
                            var scrollTop = vue.dom.history.scrollTop();
                            var history = vue.getHistory(res.session_id);
                            history.history.push(res);
                            vue.$nextTick(function () {
                                this.dom.history.scrollTop(scrollTop);
                                this.scrollBottom();
                            });
                        });

                        // 刷新会话
                        this.conn.on('refresh_session' , function () {
                            // 刷新会话
                            vue.refreshSession();
                        });

                        // 临时用户
                        this.conn.on('unique_code' , function(res){
                            // 保存临时的 unique_code
                            G.s.set('unique_code' , res);
                        });

                        // 刷新未读消息总数
                        this.conn.on('refresh_unread_message' , function(){
                            vue.refreshUnreadMessage();
                        });

                        //
                    } ,

                    // 刷新未读消息数量
                    refreshUnreadMessage: function(){
                        var self = this;
                        this.conn.unreadCount(this.response.bind(this , function(res){
                            this.unread = res;
                            if (G.isFunction(self.parent.option.unread)) {
                                self.parent.option.unread(res);
                            }
                        } , null));
                    } ,

                    play: function(){
                        var audio = new Audio();
                        audio.src = this.parent.option.host + '/static/media/new_msg.wav';
                        audio.play();
                    } ,

                    // 找到平台咨询通道
                    sessionIdForAdvoise: function(){
                        var i   = 0;
                        var cur = null;
                        var group = null;
                        for (; i < this.session.length; ++i)
                        {
                            cur     = this.session[i];
                            group   = cur.group;
                            if (group.is_service == 'y') {
                                return cur.session_id;
                            }
                        }
                        return '';
                    } ,

                    // 响应处理
                    response: function(success , error ,  res){
                        if (res.code != 200) {
                            if (res.code == 400) {
                                this.formError(res.data);
                            } else {
                                this.msg(res.data);
                            }
                            if (G.isFunction(error)) {
                                error.call(this , res.data);
                            }
                            return ;
                        }
                        res = res.data;
                        if (G.isFunction(success)) {
                            success.call(this , res);
                        }
                    } ,

                    // 切换聊天窗口
                    switchSession: function(session_id){
                        var self = this;
                        // 保存当前输入框内容
                        if (!this.once) {
                            this.setSessionTempValue(this.current.session_id , this.message);
                        }
                        // 切换会话
                        var current = this.current = this.sessionBySessionId(session_id);
                        this.once = false;
                        // 获取历史聊天记录
                        if (current.type == 'group') {
                            this.conn.groupRecent(current.group_id , this.response.bind(this , function(res){
                                res.forEach(function(v){
                                    // 数据处理
                                    self.handleForMessage(v , false , '');
                                });
                                var history = {
                                    loading: false ,
                                    history: res ,
                                    all: false ,
                                };
                                self.setHistory(session_id , history);
                                self.history = history;
                                self.$nextTick(function(){
                                    this.scrollBottom();
                                });
                            } , null));
                            // 设置输入框内容
                            this.message = this.getSessionTempValue(session_id);
                            // 更新该群的未读消息数量
                            this.conn.resetGroupUnread(current.group_id);
                            this.refreshUnreadMessage();
                            this.$nextTick(function(){
                                this.dom.textarea.trigger('focus');
                            });
                        } else {
                            // todo 其他
                        }
                    } ,

                    // 发送消息
                    send: function(type){
                        if (this.current.type == 'group') {
                            // 群消息
                            if (this.current.group.is_service == 'y') {
                                // 咨询通道
                                switch (type)
                                {
                                    case 'text':
                                        // 文本
                                        this.advoiseWithText(this.current.session_id);
                                        break;
                                }
                            } else {
                                // todo 群聊
                            }
                        } else {
                            // todo 私聊
                        }
                    } ,

                    // 生成规范名称
                    key: function(){
                        var i = 0;
                        var cur = null;
                        var str = '';
                        for (; i < arguments.length; ++i)
                        {
                            cur  = arguments[i];
                            str += cur + '_';
                        }
                        str = str.replace(/_$/ , '');
                        return str;
                    } ,

                    // 平台咨询：发送文本消息
                    // 会话id
                    advoiseWithText: function(session_id){
                        if (this.message.length == 0) {
                            // layer.
                            layer.tips('请输入内容', this.dom.textarea.get(0) , {
                                tips: [1, '#78BA32']
                            });
                            return ;
                        }
                        var self    = this;
                        var session = this.sessionBySessionId(session_id);
                        var data = {
                            group_id: session.group_id ,
                            type: 'text' ,
                            message: this.message ,
                            extra: '' ,
                        };
                        var tempId = this.unique();
                        // 发送数据
                        this.conn.group_text_advoise(data.group_id , data.type , data.message , data.extra , this.response.bind(this , function(message){
                            self.handleForMessage(message , false , '');
                            var history = self.getHistory(session_id);
                            var index   = self.messageIndexByTempId(session_id, tempId);
                            history.history.splice(index , 1 , message);
                            // 刷新会话列表
                            self.refreshSession();
                            var scrollTop = self.dom.history.scrollTop();
                            self.$nextTick(function(){
                                this.dom.history.scrollTop(scrollTop);
                                this.scrollBottom();
                            });
                            // 更新该群的未读消息数量
                            self.conn.resetGroupUnread(session.group_id);
                        } , function(error){
                            var message = self.messageByTempId(session_id , tempId);
                            if (message == false) {
                                // 未找到临时记录
                                return ;
                            }
                            self.handleForMessage(message , false , error);
                            var scrollTop = self.dom.history.scrollTop();
                            self.$nextTick(function(){
                                this.dom.history.scrollTop(scrollTop);
                                this.scrollBottom();
                            })
                        }));
                        data.message_type = 'group';
                        data.user       = this.user;
                        data.user_id    = this.user.id;
                        this.handleForMessage(data , true , '');
                        data.temp_id    = tempId;
                        var history = this.getHistory(session_id);
                            history.history.push(data);
                        this.message = '';
                        this.dom.textarea.html(this.message);
                        this.setSessionTempValue(session_id , this.message);
                        var scrollTop = this.dom.history.scrollTop();
                        this.$nextTick(function(){
                            this.dom.history.scrollTop(scrollTop);
                            // 滚动到底部
                            this.scrollBottom();
                        });
                    } ,

                    // 滚动到底部
                    scrollBottom: function(){
                        var clientH = this.dom.history.height('content-box');
                        var scrollH = this.dom.history.scrollHeight();
                        var distance = scrollH - clientH;
                        this.dom.history.vScroll(this.duration , distance);
                    } ,

                    // 刷新会话列表
                    refreshSession: function(){
                        if (!this.isLogin) {
                            return ;
                        }
                        var self = this;
                        this.conn.getSession(this.response.bind(this , function(res){
                            self.session = res;
                        } , null));
                    } ,

                    /**
                     * *********************************
                     * 事件定义 start
                     * *********************************
                     */
                    contentKeyUpEvent: function(e){
                        // if (e.ctrlKey && e.keyCode == 13) {
                        if (e.keyCode == 13) {
                            this.message = this.message.replace(/\n|\r/g , '');
                            this.dom.textarea.html(this.message);
                            this.send('text');
                        }
                    } ,

                    // 发送事件
                    sendEvent: function(){
                        this.send('text');
                    } ,

                    /**
                     * *********************************
                     * 事件定义 end
                     * *********************************
                     */

                    // 单条消息
                    messageByTempId: function(session_id , tempId){
                        var history = this.getHistory(session_id);
                            history = history.history;
                        var i       = 0;
                        var cur     = null;
                        for (; i < history.length; ++i)
                        {
                            cur = history[i];
                            if (cur.temp_id == tempId) {
                                return cur;
                            }
                        }
                        return false;
                        // throw new Error('未找到 temp_id = ' + tempId + '对应记录');
                    } ,

                    // 消息 index
                    messageIndexByTempId: function(session_id , tempId){
                        var history = this.getHistory(session_id);
                            history = history.history;
                        var i       = 0;
                        var cur     = null;
                        for (; i < history.length; ++i)
                        {
                            cur = history[i];
                            if (cur.temp_id == tempId) {
                                return i;
                            }
                        }
                        throw new Error('未找到 temp_id = ' + tempId + '对应记录');
                    } ,

                    // 会话
                    sessionBySessionId: function(session_id){
                        var i   = 0;
                        var cur = null;
                        for ( ; i < this.session.length; ++i)
                        {
                            cur = this.session[i];
                            if (cur.session_id == session_id) {
                                return cur;
                            }
                        }
                        throw new Error('未找到会话：session_id = ' + session_id);
                    } ,

                    // 获取：会话对应的聊天记录
                    getHistory: function(session_id){
                        return this.histories[session_id];
                    } ,

                    // 设置：会话对应的聊天记录
                    setHistory: function(session_id , value){
                        this.histories[session_id] = value;
                    } ,

                    // 设置：输入数据
                    setSessionTempValue: function(session_id , value){
                        this.sessionTempValue[session_id] = value;
                    } ,

                    // 获取：输入数据
                    getSessionTempValue: function(session_id){
                        var message = this.sessionTempValue[session_id];
                        return G.isString(message) ? message : '';
                    } ,

                    /**
                     * **************************
                     * 辅助函数 start
                     * **************************
                     */

                    // 唯一 id
                    unique: function(){
                        return G.randomArr(16 , 'mixed' , true);
                    } ,

                    // 错误提示
                    formError: function(obj){
                        var key = G.firstKey(obj);
                        var value = obj[key];
                        var msg = key + ' ' + value;
                        this.msg(msg);
                    } ,

                    // 成功
                    success: function(msg , option){
                        option = G.isObject(option) ? option : {};
                        option.icon = 1;
                        layer.alert(msg , option);
                    } ,

                    // 失败
                    error: function(msg , option){
                        option = G.isObject(option) ? option : {};
                        option.icon = 2;
                        layer.alert(msg , option);
                    } ,

                    info: function(msg , option){
                        option = G.isObject(option) ? option : {};
                        option.icon = 7;
                        layer.alert(msg , option);
                    } ,

                    msg: function(msg , option){
                        layer.msg(msg , option);
                    } ,

                    // 数据处理：消息
                    handleForMessage: function(message , loading , error){
                        message.loading = G.isBoolean(loading) ? loading : false;
                        message.myself = message.user_id == this.user.id;
                        message.error = G.isString(error) ? error : '';
                    } ,

                    // 查询：
                    getMessage: function(id){
                        var list = G('.message' , this.dom.message.get(0));
                        var cur = null;
                        var i   = 0;
                        for (; i < list.length; ++i)
                        {
                            cur = list.jump(i , true);
                            if (cur.data('id') == id) {
                                return cur.get(0);
                            }
                        }
                    } ,

                    // 查找消息
                    findMessageById: function(id){
                        var message = G('.message' , this.dom.message.get(0));
                        var i   = 0;
                        var cur = null;
                        for (; i < message.length; ++i)
                        {
                            cur = message.jump(i , true);
                            if (cur.data('id') == id) {
                                return cur.get(0);
                            }
                        }
                        throw new Error('未找到 id = ' + id + '的dom元素');
                    } ,

                    // 获取历史聊天记录
                    scrollEvent: function(e){
                        var tar = G(e.currentTarget);
                        if (!tar.isTop()) {
                            return ;
                        }
                        var session = G.copyObj(this.current , true);
                        // console.log(session , this.current);
                        var history = this.getHistory(session.session_id);
                        if (history.loading) {
                            // 加载中
                            return ;
                        }
                        if (history.all) {
                            // 已经加载完所有记录
                            return ;
                        }
                        if (history.history.length == 0) {
                            // 没有数据
                            return ;
                        }
                        var self = this;
                        history.loading = true;
                        if (this.current.type == 'group') {
                            var earliest = history.history[0];
                            this.conn.groupHistory(session.group_id , earliest.id , this.response.bind(this , function(res){
                                res.forEach(function(v){
                                    self.handleForMessage(v , false , '');
                                });
                                history.history = res.concat(history.history);
                                history.loading = false;
                                if (res.length == 0) {
                                    history.all = true;
                                }
                                if (self.current.session_id != session.session_id) {
                                    // 实现响应式
                                    // self.setHistory(session.session_id , history);
                                    // 已经切换到其他会话了
                                    return ;
                                }
                                self.history = history;
                                // self.setHistory(session.session_id , history);
                                self.$nextTick(function(){
                                    var previous = G(this.findMessageById(earliest.id));
                                    var topVal = previous.getDocOffsetVal('top' , self.dom.history.get(0));
                                    self.dom.history.scrollTop(topVal);
                                });
                            } , function(){
                                // 失败
                                history.loading = false;
                            }));
                        } else {
                            // todo 其他 ...
                        }
                    } ,

                    /**
                     * **************************
                     * 辅助函数 end
                     * **************************
                     */

                    closeClickEvent: function(e){
                        G.stop(e);
                        this.hideMaximum();
                        this.showMinimum();
                    } ,

                    // 用户点击事件
                    userClickEvent: function(e){
                        G.stop(e);
                    } ,

                    // 展示用户
                    showUser: function(){
                        this.dom.user.removeClass('hide');
                        this.dom.userOuter.animate({
                            opacity: 1 ,
                            width: this.value.userW + 'px' ,
                            height: this.value.userH + 'px' ,
                            left: '0px' ,
                            top: '0px' ,
                        } , null , this.value.short);
                        this.dom.userIn.animate({
                            left: '0px' ,
                            top: '0px'
                        } , null , this.value.short);
                    } ,

                    // 隐藏用户
                    hideUser: function(){
                        var self = this;
                        this.dom.userOuter.animate({
                            opacity: this.value.endOpacity ,
                            width: this.value.endUserW + 'px' ,
                            height: this.value.endUserH + 'px' ,
                            left: this.value.endLeft + 'px' ,
                            top: this.value.endTop + 'px' ,
                        } , function(){
                            self.dom.user.addClass('hide');
                        } , this.value.short);
                        this.dom.userIn.animate({
                            left: -this.value.endLeft + 'px' ,
                            top:  -this.value.endTop + 'px' ,
                        } , null , this.value.short);
                    } ,

                    avatarClickEvent: function(e){
                        var x = e.clientX;
                        var y = e.clientY;
                        var extra = 15;
                        this.dom.user.css({
                            left: (x + extra) + 'px' ,
                            top: (y + extra) + 'px'
                        });
                        this.showUser();
                    } ,

                    realTimeCommunicationMinimumClickEvent: function(){
                        this.showMaximum();
                        this.hideMinimum();
                    } ,

                    defineEvent: function(){
                        var win = G(window);

                        this.dom.history.on('scroll' , this.scrollEvent.bind(this) , true , false);
                        this.dom.close.on('click' , this.closeClickEvent.bind(this) , true , false);
                        this.dom.user.on('click' , this.userClickEvent.bind(this) , true , false);
                        this.dom.avatar.on('click' , this.avatarClickEvent.bind(this) , true , false);
                        this.dom.mask.on('click' , this.hideUser.bind(this) , true , false);
                        this.dom.realTimeCommunicationMinimum.on('click' , this.realTimeCommunicationMinimumClickEvent.bind(this) , true , false);

                        // 阻止默认事件
                        this.dom.avatar.on(G.mousedown , G.stop , true , false);
                        this.dom.user.on(G.mousedown , G.stop , true , false);
                        this.dom.leftMid.on(G.mousedown , G.stop , true , false);
                        this.dom.leftBtm.on(G.mousedown , G.stop , true , false);
                        this.dom.input.on(G.mousedown , G.stop , true , false);
                        this.dom.history.on(G.mousedown , G.stop , true , false);

                        win.on('resize' , () => {
                            this.initDynamic();
                        } , true , false);
                    } ,
                } ,

                watch: {

                } ,

            });
        } ,


        run: function () {
            this.initStatic();
            this.initDynamic();
            this.initVue();
        }
    };

    if (!noGlobal) {
        window.Communication = Communication;
    }

    return Communication;
});