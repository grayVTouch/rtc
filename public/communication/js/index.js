(function(){
    "use strict";

    new Vue({
        el: '#app' ,
        data: {
            val: {
                showFunction: false ,
                user: {} ,
            } ,

            ins: {
                rtc: null
            } ,
        } ,

        created () {

        } ,

        mounted () {
            this.run();
        },

        methods: {

            // 初始化 ws
            initWebSocket () {
                const self = this;

                const token = G.session.get('token');

                console.log("用户的登陆凭证: " , token);

                this.ins.rtc = new RTC({
                    url: topContext.websocket ,
                    identifier: topContext.identifier ,
                    // 获取用户的登录凭证
                    token ,
                    open () {
                        self.initialize();
                    } ,
                });
            } ,

            // ws 方法调用的全局方法
            send (method , data , callback) {
                let res = this.ins.rtc[method](data , (res) => {
                    console.log('res 结果：' , res);
                    if (res.code == 1000) {
                        // 用户认证失败，退出登录
                        // this.logout();
                        return ;
                    }
                    if (G.isFunction(callback)) {
                        callback.call(this.ins.rtc , res);
                    }
                });
            } ,

            showFunction () {
                this.val.showFunction = true;
            } ,

            hideFunction () {
                this.val.showFunction = false;
            } ,

            // 项目初始化
            initialize () {
                const self = this;
                this.send('self' , null , (res) => {
                    if (res.code != 200) {
                        console.log(res.data);
                        return ;
                    }
                    res = res.data;
                    self.val.user = res;
                });
            } ,

            defineEvent () {
                // 定义窗口事件
                window.addEventListener('click' , this.hideFunction.bind(this))
            } ,

            logout () {
                G.session.del('user_id');
                G.session.del('token');
                window.location.href = 'login.html';
            } ,

            run () {
                this.initWebSocket();
                this.defineEvent();
            } ,
        } ,
    });
})();