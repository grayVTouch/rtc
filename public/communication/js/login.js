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
                        self.initialize();
                    } ,
                });
            } ,

            initialize () {
                this.ins.rtc.loginQRCode(null , (res) => {
                    if (res.code != 200) {
                        console.log('ws 接口获取到了错误信息：' . res.data);
                        return ;
                    }
                    this.val.qrcode = res.data;
                });
            } ,

            run () {
                this.initWebSocket();
            } ,
        } ,
    });
})();