(function(){
    "use strict";

    new Vue({
        el: '#app' ,

        data: {
            val: {
                qrcode: '' ,
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
                    url: 'ws://192.168.145.129:10001' ,
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
                // 生成二维码
                this.ins.rtc.loginQRCodeForTest(null , (res) => {
                    if (res.code != 200) {
                        console.log('ws 接口获取到了错误信息：' . res.data);
                        return ;
                    }
                    this.val.qrcode = res.data;
                });

                this.ins.rtc.on('login_user' , function(data){
                    self.val.qrcode = data.avatar;
                    console.log('登录用户信息' , data);
                });

                // 监听 ws 推送
                this.ins.rtc.on('logined' , function(){
                    console.log("成功登录信息");
                });

            } ,

            run () {
                this.initWebSocket();
            } ,
        } ,
    });
})();