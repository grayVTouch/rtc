/**
 * **************************
 * author 陈学龙 2018-04-23
 * Socket 通信类库
 * ***************************
 */
(function(){
    "use strict";

    function Socket(link , config){
        var thisRange = [window , null , undefined];

        if (G.contain(this , thisRange) || (!G.contain(this , thisRange) && this.constructor !== Socket)) {
            return new Socket(link , config);
        }

        // 默认配置文件
        // 以下定义的回调函数都是每次都会触发的
        this._default = {
            // 登录平台：pc | mobile | app
            platform: null ,

            // 待注册的用户信息
            user: {
                // 用户类型
                userType: null ,
                // 用户 id
                userId: null ,
            } ,
            // websocket 相关事件
            socketEvents: {
                // 连接打开
                open: null ,
                // 接收到消息回调（没做业务区分的消息，仅做测试使用）
                message: null ,
                // 连接关闭之后
                close: null ,
                // websocket 连接发生错误的时候
                error: null
            } ,
            // 消息事件
            messageEvents: {
                // 信息注册成功
                login: null ,
                // 初始化聊天记录同步
                history: null ,
                // 接收到新增未读消息数量的时候
                unreadMsgCount: null ,
                // 手动消息记录同步
                syn: null ,
                // 聊天室成员同步
                user: null ,
                // 聊天室新增成员时回调
                addUser: null ,
                // 聊天室成员上下线回调
                notification: null ,
                // 接收到平台咨询服务端响应时回调
                advoise: null ,
                // 接收到订单咨询服务端响应时回调
                order: null ,
                // 接收到系统消息通知回调（未开发）
                broadcast: null ,
                // 自动分配客服成功或失败时回调
                autoAllocate: null ,
                // 新增会话时回调
                session: null ,
                // 聊天室正在咨询的订单
                orderConsultation: null ,
                // 清除正在咨询的订单
                unlockOrder: null ,
                // 服务端逻辑判断失败时回调
                error: null ,
                // 测试用！！
                test: null
            }
        };

        if (!G.isValidVal(config)) {
            config = this._default;
        }

        this._link      = link;

        // 用户信息
        this._user = {};
        // 注册事件
        this._socketEvents = {};
        this._messageEvents = {};

        // 平台标识
        this.platform = config['platform'];

        // 用户信息
        this._user['userType']  = config['user']['userType'];
        this._user['userId']    = config['user']['userId'];

        // socket 事件
        this._socketEvents['open']     = G.isFunction(config['socketEvents']['open']) ? config['socketEvents']['open'] : this._default['open'];
        this._socketEvents['message']  = G.isFunction(config['socketEvents']['message']) ? config['socketEvents']['message'] : this._default['message'];
        this._socketEvents['close']    = G.isFunction(config['socketEvents']['close']) ? config['socketEvents']['close'] : this._default['close'];
        this._socketEvents['error']    = G.isFunction(config['socketEvents']['error']) ? config['socketEvents']['error'] : this._default['error'];

        // 消息事件
        this._messageEvents['history']          = G.isFunction(config['messageEvents']['history']) ? config['messageEvents']['history'] : this._default['history'];
        this._messageEvents['unreadMsgCount']   = G.isFunction(config['messageEvents']['unreadMsgCount']) ? config['messageEvents']['unreadMsgCount'] : this._default['unreadMsgCount'];
        this._messageEvents['syn']       = G.isFunction(config['messageEvents']['syn']) ? config['messageEvents']['syn'] : this._default['syn'];
        this._messageEvents['user']       = G.isFunction(config['messageEvents']['user']) ? config['messageEvents']['user'] : this._default['user'];
        this._messageEvents['addUser']    = G.isFunction(config['messageEvents']['addUser']) ? config['messageEvents']['addUser'] : this._default['addUser'];
        this._messageEvents['notification'] = G.isFunction(config['messageEvents']['notification']) ? config['messageEvents']['notification'] : this._default['notification'];
        this._messageEvents['advoise']    = G.isFunction(config['messageEvents']['advoise']) ? config['messageEvents']['advoise'] : this._default['advoise'];
        this._messageEvents['order']      = G.isFunction(config['messageEvents']['order']) ? config['messageEvents']['order'] : this._default['order'];
        this._messageEvents['broadcast']  = G.isFunction(config['messageEvents']['broadcast']) ? config['messageEvents']['broadcast'] : this._default['broadcast'];
        this._messageEvents['autoAllocate'] = G.isFunction(config['messageEvents']['autoAllocate']) ? config['messageEvents']['autoAllocate'] : this._default['autoAllocate'];
        this._messageEvents['session']   = G.isFunction(config['messageEvents']['session']) ? config['messageEvents']['session'] : this._default['session'];
        this._messageEvents['error']      = G.isFunction(config['messageEvents']['error']) ? config['messageEvents']['error'] : this._default['error'];
        this._messageEvents['orderConsultation']      = G.isFunction(config['messageEvents']['orderConsultation']) ? config['messageEvents']['orderConsultation'] : this._default['orderConsultation'];
        this._messageEvents['unlockOrder']      = G.isFunction(config['messageEvents']['unlockOrder']) ? config['messageEvents']['unlockOrder'] : this._default['unlockOrder'];
        this._messageEvents['test']      = G.isFunction(config['messageEvents']['test']) ? config['messageEvents']['test'] : this._default['test'];

        this._run();
    }

    Socket.prototype = {
        constructor: Socket ,

        // 支持的消息内容类型
        typeRange: ['text' , 'json:image' , 'json:file' , 'json:order'] ,

        // 生成房间 id
        _loginFns: {} ,

        // 生成房间 id
        _genRoomIdFns: {} ,

        // 创建房间 id
        _createRoomFns: {} ,

        // 锁定聊天室订单
        _orderConsultationFns: {} ,

        // 更新聊天室会话顺序
        _updateRoomSortFns: {} ,

        // 获取聊天室信息后的回调函数列表
        _getLockOrderFns: {} ,

        // 申请争议
        _addOrderDisputeFns: {} ,

        // 供测试用
        _testFns: {} ,

        // 获取聊天室相关主题
        _getRelatedThingsFns: {} ,

        // 加入房间后回调函数
        _joinRoomFns: {} ,

        // 自动分配客服成功后回调函数
        _autoAllocateFns: {} ,

        // 设置未读消息回调函数
        _emptyMsgCountFns: {} ,

        // 消息同步成功后回调函数
        _synFns: {} ,

        // 聊天室用户同步成功后回调函数
        _userFns: {} ,

        // 接收到 advoise 消息后回调函数
        _advoiseFns: {} ,

        // 订单消息发送成功后
        _orderFns: {} ,

        // 获取指定用户房间信息
        _getRoomFns: {} ,

        // 设置聊天室状态
        _setRoomUserTipFns: {} ,

        // websocket 连接
        _ws: null ,

        // 获取 websocket 连接
        get: function(){
            return this._ws;
        } ,

        // 初始化 websocket
        _connect: function(){
            this._ws    = new WebSocket(this._link);
            this.conn   = G(this._ws);
        } ,

        _initStaticArgs: function(){

        } ,

        _initDynamicArgs: function(){

        } ,

        // 链接打开时
        _openEvent: function(){
            var self = this;

            // 注册用户信息
            this.login(function(data){
                if (data['status'] === 'error') {
                    console.log(data['msg']);
                    return ;
                }

                data = data['msg'];

                // 注册连接信息成功后
                self.clientId = data['client_id'];

                // 之后都使用解密数据
                self._user['userType']  = data['user_type'];
                self._user['userId']    = data['user_id'];
            });

            if (G.isFunction(this._socketEvents['open'])) {
                this._socketEvents['open'].call(this);
            }
        } ,

        // 注册成功时回调
        _loginEvent: function(data){
            if (G.isFunction(this._messageEvents['login'])) {
                this._messageEvents['login'].call(this , data['data']);
            }

            if (G.isFunction(this._loginFns[data['callback']])) {
                this._loginFns[data['callback']].call(this , data['data']);
            }

            delete this._loginFns[data['callback']];
        } ,

        // 历史记录同步时
        _historyEvent: function(data){
            if (G.isFunction(this._messageEvents['history'])) {
                this._messageEvents['history'].call(this , data['data']);
            }
        } ,

        // 聊天室成员同步同步时
        _userEvent: function(data){
            if (G.isFunction(this._messageEvents['user'])) {
                this._messageEvents['user'].call(this , data['data']);
            }

            if (G.isFunction(this._userFns[data['callback']])) {
                this._userFns[data['callback']].call(this , data['data']);
            }

            delete this._userFns[data['callback']];
        } ,

        // 接收到平台咨询消息时回调
        _advoiseEvent: function(data){
            // 每次都会触发的函数
            // 监听器
            if (G.isFunction(this._messageEvents['advoise'])) {
                this._messageEvents['advoise'].call(this , data['data']);
            }

            // 针对每个请求仅触发一次
            if (G.isFunction(this._advoiseFns[data['callback']])) {
                this._advoiseFns[data['callback']].call(this , data['data']);
            }

            // 删除 key
            delete this._advoiseFns[data['callback']];
        } ,

        // 接收到订单咨询消息时回调
        _orderEvent: function(data){
            if (G.isFunction(this._messageEvents['order'])) {
                this._messageEvents['order'].call(this , data['data']);
            }

            // 针对每个请求仅触发一次
            if (G.isFunction(this._orderFns[data['callback']])) {
                this._orderFns[data['callback']].call(this , data['data']);
            }

            // 删除 key
            delete this._orderFns[data['callback']];
        } ,

        // 聊天室新增成员时回调函数
        _addUserEvent: function(data){
            if (G.isFunction(this._messageEvents['addUser'])) {
                this._messageEvents['addUser'].call(this , data['data']);
            }
        } ,

        // 自动分配客服成功时回调函数
        _autoAllocateEvent: function(data){
            var self = this;

            if (G.isFunction(this._messageEvents['autoAllocate'])) {
                this._messageEvents['autoAllocate'].call(this , data['data']);
            }

            if (G.isFunction(this._autoAllocateFns[data['callback']])) {
                this._autoAllocateFns[data['callback']].call(this , data['data']);
            }

            delete this._autoAllocateFns[data['callback']];
        } ,

        // 新增会话时
        _sessionEvent: function(data){
            if (G.isFunction(this._messageEvents['session'])) {
                this._messageEvents['session'].call(this , data['data']);
            }
        } ,

        // 加入聊天室成功
        _joinRoomEvent: function(data){
            // 每次都会触发的回调函数
            if (G.isFunction(this._messageEvents['joinRoom'])) {
                this._messageEvents['joinRoom'].call(this , data['data']);
            }

            if (G.isFunction(this._joinRoomFns[data['callback']])) {
                this._joinRoomFns[data['callback']].call(this , data['data']);
            }

            delete this._joinRoomFns[data['callback']];
        } ,

        // 设置未读消息数量
        _emptyMsgCountEvent: function(data){
            if (G.isFunction(this._emptyMsgCountFns[data['callback']])) {
                this._emptyMsgCountFns[data['callback']].call(this , data['data']);
            }

            delete this._emptyMsgCountFns[data['callback']];
        } ,

        // 接收到房间信息时回调
        _getRoomEvent: function(data){
            if (G.isFunction(this._getRoomFns[data['callback']])) {
                this._getRoomFns[data['callback']].call(this , data['data']);
            }

            delete this._getRoomFns[data['callback']];
        } ,

        // 设置聊天室消息提醒状态
        setRoomUserTipEvent: function(data){
            if (G.isFunction(this._setRoomUserTipFns[data['callback']])) {
                this._setRoomUserTipFns[data['callback']].call(this , data['data']);
            }

            delete this._setRoomUserTipFns[data['callback']];
        } ,

        // 生成聊天室 id
        _genRoomIdEvent: function(data){
            if (G.isFunction(this._genRoomIdFns[data['callback']])) {
                this._genRoomIdFns[data['callback']].call(this , data['data']);
            }

            delete this._genRoomIdFns[data['callback']];
        } ,

        // 创建聊天室
        _createRoomEvent: function(data){
            if (G.isFunction(this._createRoomFns[data['callback']])) {
                this._createRoomFns[data['callback']].call(this , data['data']);
            }

            delete this._createRoomFns[data['callback']];
        } ,

        // 接受到新增未读消息时
        _unreadMsgCountEvent: function(data){
            if (G.isFunction(this._messageEvents['unreadMsgCount'])) {
                this._messageEvents['unreadMsgCount'](data['data']);
            }
        } ,

        // 接收到锁定正在咨询的订单后回调
        _orderConsultationEvent: function(data){
            if (G.isFunction(this._messageEvents['orderConsultation'])) {
                this._messageEvents['orderConsultation'](data['data']);
            }

            if (G.isFunction(this._orderConsultationFns[data['callback']])) {
                this._orderConsultationFns[data['callback']].call(this , data['data']);
            }

            delete this._orderConsultationFns[data['callback']];
        } ,

        // 接受到聊天室相关事务的回调后
        _getRelatedThingsEvent: function(data){
            if (G.isFunction(this._messageEvents['getRelatedThings'])) {
                this._messageEvents['getRelatedThings'](data['data']);
            }

            if (G.isFunction(this._getRelatedThingsFns[data['callback']])) {
                this._getRelatedThingsFns[data['callback']].call(this , data['data']);
            }

            delete this._getRelatedThingsFns[data['callback']];
        } ,

        // 更新会话顺序
        _updateRoomSortEvent: function(data){
            if (G.isFunction(this._updateRoomSortFns[data['callback']])) {
                this._updateRoomSortFns[data['callback']].call(this , data['data']);
            }

            delete this._updateRoomSortFns[data['callback']];
        } ,

        // 用户注册后，服务器返回此次连接的标识符
        _identifierEvent: function(data){
            if (data['status'] === 'error') {
                console.log(data['msg']);
                return ;
            }

            // 设置连接的标识符
            this.identfier = data['msg'];
        } ,

        // 用户注册后，服务器返回此次连接的标识符
        _getLockOrderEvent: function(data){
            if (G.isFunction(this._getLockOrderFns[data['callback']])) {
                this._getLockOrderFns[data['callback']].call(this , data['data']);
            }

            delete this._getLockOrderFns[data['callback']];
        } ,

        // 申请争议
        _addOrderDisputeEvent: function(data){
            if (G.isFunction(this._addOrderDisputeFns[data['callback']])) {
                this._addOrderDisputeFns[data['callback']].call(this , data['data']);
            }

            delete this._addOrderDisputeFns[data['callback']];
        } ,

        // 清除正在咨询的订单（咨询人下线了！！）
        _unlockOrderEvent: function(data){
            if (G.isFunction(this._messageEvents['unlockOrder'])) {
                this._messageEvents['unlockOrder'].call(this , data['data']);
            }
        } ,

        // 测试用接口
        _testEvent: function(data){
            if (G.isFunction(this._messageEvents['test'])) {
                this._messageEvents['test'].call(this , data['data']);
            }

            if (G.isFunction(this._testFns[data['callback']])) {
                this._testFns[data['callback']].call(this , data['data']);
            }

            delete this._testFns[data['callback']];
        } ,

        _synEvent: function(data){
            var self = this;

            if (G.isFunction(this._messageEvents['syn'])) {
                this._messageEvents['syn'].call(this , data['data']);
            }

            if (G.isFunction(this._synFns[data['callback']])) {
                this._synFns[data['callback']].call(this , data['data']);
            }

            delete this._synFns[data['callback']];
        } ,

        // 用户上下线通知
        _notificationEvent: function(data){
            if (G.isFunction(this._messageEvents['notification'])) {
                this._messageEvents['notification'].call(this , data['data']);
            }
        } ,

        // 消息事件：系统消息推送
        _broadcastEvent: function(data){
            if (G.isFunction(this._messageEvents['broadcast'])) {
                this._messageEvents['broadcast'].call(this , data['data']);
            }
        } ,

        // 消息事件：发生错误的时候
        _errorEvent: function(data){
            if (G.isFunction(this._messageEvents['error'])) {
                this._messageEvents['error'].call(this , data['data']);
            }
        } ,

        // 连接关闭时
        _closeEvent: function(event){
            if (G.isFunction(this._socketEvents['close'])) {
                this._socketEvents['close'].call(this);
            }
        } ,

        // websocket 连接发生错误的时候
        _wErrorEvent: function(){
            if (G.isFunction(this._socketEvents['error'])) {
                this._socketEvents['error'].call(this);
            }
        } ,

        // 关闭 websocket 链接
        close: function(data){

            console.log('链接关闭' , data['data']['msg']);
            this._ws.close();
        } ,

        // message
        _messageEvent: function(event){
            var json = event.data;
            var data = G.jsonDecode(json);

            switch (data['msg_type'])
            {
                case 'login':
                    this._loginEvent(data['content']);
                    break;
                case 'history':
                    this._historyEvent(data['content']);
                    break;
                case 'user':
                    this._userEvent(data['content']);
                    break;
                case 'advoise':
                    this._advoiseEvent(data['content']);
                    break;
                case 'order':
                    this._orderEvent(data['content']);
                    break;
                case 'add_user':
                    this._addUserEvent(data['content']);
                    break;
                case 'auto_allocate':
                    this._autoAllocateEvent(data['content']);
                    break;
                case 'session':
                    this._sessionEvent(data['content']);
                    break;
                case 'join_room':
                    this._joinRoomEvent(data['content']);
                    break;
                case 'syn':
                    this._synEvent(data['content']);
                    break;
                case 'notification':
                    this._notificationEvent(data['content']);
                    break;
                case 'broadcast':
                    this._broadcastEvent(data['content']);
                    break;
                case 'error':
                    this._errorEvent(data['content']);
                    break;
                case 'empty_msg_count':
                    this._emptyMsgCountEvent(data['content']);
                    break;
                case 'get_room':
                    this._getRoomEvent(data['content']);
                    break;
                case 'set_room_user_tip':
                    this.setRoomUserTipEvent(data['content']);
                    break;
                case 'unread_msg_count':
                    this._unreadMsgCountEvent(data['content']);
                    break;
                case 'order_consultation':
                    // 锁定当前正在咨询的订单
                    this._orderConsultationEvent(data['content']);
                    break;
                case 'get_related_things':
                    // 手动获取聊天室当前正在咨询的订单
                    this._getRelatedThingsEvent(data['content']);
                    break;
                case 'update_room_sort':
                    this._updateRoomSortEvent(data['content']);
                    break;
                case 'identifier':
                    this.identifierEvent(data['content']);
                    break;
                case 'get_lock_order':
                    this._getLockOrderEvent(data['content']);
                    break;
                // 申请争议解决
                case 'add_order_dispute':
                    this._addOrderDisputeEvent(data['content']);
                    break;
                case 'unlock_order':
                    this._unlockOrderEvent(data['content']);
                    break;
                case 'test':
                    this._testEvent(data['content']);
                    break;
                case 'close':
                    this.close(data['content']);
                    break;
                default:
                    throw new TypeError("不支持的消息类型");
            }

            if (G.isFunction(this._message)) {
                this._message.call(this);
            }
        } ,

        // 用户注册
        login: function(fn){
            var callback = this._genCode();

            var json = this._getJson('login' , {
                callback: callback
            });

            this.send(json);

            if (G.isFunction(fn)) {
                this._loginFns[callback] = fn;
            }
        } ,

        // 手动同步聊天室最近消息
        syn: function(roomId , fn){
            var callback = this._genCode();

            var json = this._getJson('syn' , {
                room_id: roomId ,
                callback: callback
            });

            this.send(json);

            if (G.isFunction(fn)) {
                this._synFns[callback] = fn;
            }
        } ,

        // 手动同步聊天室用户
        user: function(roomId , fn){
            var callback = this._genCode();

            var json = this._getJson('user' , {
                room_id: roomId ,
                callback: callback
            });

            this.send(json);

            if (G.isFunction(fn)) {
                this._userFns[callback] = fn;
            }
        } ,

        // 发送平台咨询
        advoise: function(type , roomType , roomId , msg , fn){
            var callback = this._genCode();

            if (!G.contain(type , this.typeRange)) {
                throw new Error('不支持的数据类型：' + type + '，当前受支持的数据类型有：' + typeRange.join('、'));
            }

            var json = this._getJson('advoise' , {
                type: type ,
                room_type: roomType ,
                room_id: roomId ,
                content: msg ,
                callback: callback
            });

            this.send(json);

            if (G.isFunction(fn)) {
                this._advoiseFns[callback] = fn;
            }
        } ,

        // 发送订单咨询
        // 支持的类型有 ['text' , 'image' , 'file']
        order: function(type , roomType , roomId , msg , fn){
            if (!G.contain(type , this.typeRange)) {
                throw new Error('不支持的数据类型：' + type + '，当前受支持的数据类型有：' + typeRange.join('、'));
            }

            var callback = this._genCode();

            var json = this._getJson('order' , {
                type: type ,
                room_type: roomType ,
                room_id: roomId ,
                content: msg ,
                callback: callback
            });

            this.send(json);

            if (G.isFunction(fn)) {
                this._orderFns[callback] = fn;
            }
        } ,

        // 生成随机码
        _genCode: function(){
            return G.randomArr(256 , 'mixed').join('');
        } ,

        // 加入聊天室
        joinRoom: function(roomType , roomId , users , fn){
            var callback = this._genCode();

            var json  = this._getJson('join_room' , {
                room_type: roomType ,
                room_id: roomId ,
                users: users ,
                callback: callback
            });

            this.send(json);

            // 注册一次性回调函数
            if (G.isFunction(fn)) {
                this._joinRoomFns[callback] = fn;
            }
        } ,

        // 直接发送消息
        send: function(value){
            this._ws.send(value);
        } ,

        // 系统消息通知
        broadcast: function(){
            
        } ,

        // 获取房间信息
        getRoom: function(roomId , fn){
            var callback = this._genCode();

            var json = this._getJson('get_room' , {
                room_id: roomId ,
                callback: callback
            });

            this.send(json);

            if (G.isFunction(fn)) {
                this._getRoomFns[callback] = fn;
            }
        } ,

        // 设置房间状态
        setTipForRoom: function(roomId , tip , fn){
            var callback = this._genCode();

            var json = this._getJson('set_tip_for_room' , {
                room_id: roomId ,
                tip: tip ,
                callback: callback
            });

            this.send(json);

            if (G.isFunction(fn)) {
                this._setRoomUserTipFns[callback] = fn;
            }
        } ,

        // 自动分配客服
        autoAllocate: function(roomType , roomId , fn){
            var callback = this._genCode();

            var json = this._getJson('auto_allocate' , {
                room_type: roomType ,
                room_id: roomId ,
                callback: callback
            });

            this.send(json);

            if (G.isFunction(fn)) {
                this._autoAllocateFns[callback] = fn;
            }
        } ,

        // 设置未读消息数量
        emptyMsgCount: function(roomId , fn){
            var callback = this._genCode();

            var json = this._getJson('empty_msg_count' , {
                room_id: roomId ,
                callback: callback
            });

            this.send(json);

            if (G.isFunction(fn)) {
                this._emptyMsgCountFns[callback] = fn;
            }
        } ,

        // 生成聊天室 id
        genRoomId: function(orderId , fn){
            var callback = this._genCode();

            var json = this._getJson('gen_room_id' , {
                order_id: orderId ,
                callback: callback
            });

            this.send(json);

            if (G.isFunction(fn)) {
                this._genRoomIdFns[callback] = fn;
            }
        } ,

        // 创建聊天室
        createRoom: function(orderId , fn){
            var callback = this._genCode();

            var json = this._getJson('create_room' , {
                order_id: orderId ,
                callback: callback
            });

            this.send(json);

            if (G.isFunction(fn)) {
                this._createRoomFns[callback] = fn;
            }
        } ,

        // 发送订单信息
        orderConsultation: function(roomId , orderId , fn){
            var callback = this._genCode();

            var json = this._getJson('order_consultation' , {
                room_id: roomId ,
                order_id: orderId ,
                callback: callback
            });

            this.send(json);

            if (G.isFunction(fn)) {
                this._orderConsultationFns[callback] = fn;
            }
        } ,

        // 手动获取聊天室正在咨询的订单
        updateRoomSort: function(roomId , top , fn){
            var topRange = ['y' , 'n'];

            top = G.contain(top , topRange) ? top : 'n';

            var callback = this._genCode();

            var json = this._getJson('update_room_sort' , {
                room_id: roomId ,
                top: top ,
                callback: callback
            });

            this.send(json);

            if (G.isFunction(fn)) {
                this._updateRoomSortFns[callback] = fn;
            }
        } ,

        // 获取聊天室锁定的订单（正在咨询的订单）
        getLockOrder: function(roomId , fn){
            var callback = this._genCode();

            var json = this._getJson('get_lock_order' , {
                room_id: roomId ,
                callback: callback
            });

            this.send(json);

            if (G.isFunction(fn)) {
                this._getLockOrderFns[callback] = fn;
            }
        } ,

        // 获取聊天室锁定的订单（正在咨询的订单）
        addOrderDispute: function(roomType , roomId , orderId , title , description , fn){
            var callback = this._genCode();

            var json = this._getJson('add_order_dispute' , {
                room_type: roomType ,
                room_id: roomId ,
                order_id: orderId ,
                title: title ,
                description: description ,
                callback: callback
            });

            this.send(json);

            if (G.isFunction(fn)) {
                this._addOrderDisputeFns[callback] = fn;
            }
        } ,

        // socket 发送测试数据
        test: function(msg , fn){
            var callback = this._genCode();

           var json = this._getJson('test' , {
               msg: msg ,
               callback: callback
           });

            this.send(json);

            if (G.isFunction(fn)) {
                this._testFns[callback] = fn;
            }
        } ,

        // 注册每次都会触发的事件
        // 重复的事件会被覆盖
        on: function(event , callback){
            this._messageEvents[event] = callback;
        } ,

        // 返回 json
        _getJson: function(msgType , send){
            send = this._dataHandle(send);

            return G.jsonEncode({
                msg_type: msgType ,
                content: send
            });
        } ,

        // 对待发送数据进行一次最后的封装
        _dataHandle: function(data){
            // 连接id
            data['user_type']   = this._user['userType'];
            data['user_id']     = this._user['userId'];
            data['client_id']     = this.clientId;
            data['platform']    = this.platform;

            return data;
        } ,

        // 定义事件
        _defineEvent: function(){
            // 链接打开后
            this.conn.loginEvent('open'     , this._openEvent.bind(this)    , true , false);
            this.conn.loginEvent('message'  , this._messageEvent.bind(this) , true , false);
            this.conn.loginEvent('close'    , this._closeEvent.bind(this)   , true , false);
            this.conn.loginEvent('error'    , this._wErrorEvent.bind(this)  , true , false);
        } ,

        // 初始化程序
        _run: function(){
            this._connect();
            this._initStaticArgs();
            this._initDynamicArgs();
            this._defineEvent();
        }
    };

    // 提供冲突解决办法
    Socket.noConflict = function(apiName){
        if (apiName === 'Socket') {
            return ;
        }

        window[apiName] = Socket;
        window.Socket = window.__socket__;
    };

    // Socket
    if (G.getValType(Socket) !== 'Undefined') {
        window.__socket__ = window.Socket;
    }

    window.Socket = Socket;
})();