(function(){
    "use strict";

    new Vue({
        el: '#app' ,

        data: {
            val: {
                qrcode: '' ,
                avatar: '' ,
                mode: 'qrcode' ,
            } ,
            ins: {
                rtc: null ,
            } ,
        } ,

        created () {

        } ,

        mounted () {
            this.run();
        } ,

        methods: {

            initWebSocket () {
                const self = this;
                this.ins.rtc = new RTC({
                    url: topContext.websocket ,
                    identifier: 'nimo' ,
                    platform: 'web' ,
                    open () {
                        // 连接打开的情况下才能够进行初始化
                        self.initialize();
                    } ,
                });
            } ,

            initialize () {
                const self = this;

                this.getQRCode();

                this.ins.rtc.on('avatar' , function(res){
                    self.val.avatar = res;
                    self.val.mode = 'user';
                });

                // 监听 ws 推送
                this.ins.rtc.on('logined' , function(res){
                    // 将用户的 token 信息存储到 SessionStorage 中
                    G.session.set('user_id' , res.user_id);
                    G.session.set('token' , res.token);
                    window.location.href = 'index.html';

                });

            } ,

            // 获取二维码
            getQRCode () {
                // 生成二维码
                this.ins.rtc.loginQRCodeForTest(null , (res) => {
                    if (res.code != 200) {
                        console.log('ws 接口获取到了错误信息：' . res.data);
                        return ;
                    }
                    this.val.qrcode = res.data;
                });
            } ,

            // 切换账号
            switchUser () {
                this.val.mode = 'qrcode';
                this.getQRCode();
            } ,

            run () {
                this.initWebSocket();
            } ,
        } ,
    });
})();