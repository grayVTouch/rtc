(function(){
    "use strict";

    function RTC(option)
    {
        // if () {
        //
        // }
        this._default = {
            address: 'ws://127.0.0.1:80' ,
        };
        this.websocket = null;
        // this.address =
    }

    RTC.prototype = {
        // 建立连接
        connect () {
            this.websocket = new WebSocket(this.address);
        } ,
        run () {
            this.connect();
        } ,
    };

    window.RTC = RTC;
})();