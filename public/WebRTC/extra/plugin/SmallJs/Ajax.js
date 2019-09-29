/*
     * Ajax 操作类
     * ajax 请求都会带有一个 AJAX-REQUEST 请求头
     * todo
     */
function Ajax(url , option){
    var thisRange = [window , null , undefined];

    if (g.contain(this , thisRange) || this.constructor !== Ajax) {
        return new Ajax(url , option);
    }
    
    this.default = {
        header:   {} ,                          // 发送的请求头部信息 格式： {'Content-Type' : 'text/html; charset=utf-8' , 'Cache-Control' : 'false'}
        method: 'get' ,                          // 请求方法 get | GET | post | POST
        url: '' ,                                // 请求路径
        async: true ,                          // 是否异步
        data: null ,                         // 发送的数据
        direct: false ,                      // 直接发出请求，这将绕过生命周期钩子的拦截
        responseType: 'text' ,                       // 相应类型
        additionalTimestamp: true , 			 // 是否在 url 末尾追加时间戳
        wait: 0 ,                         // 请求：设置超时时间，单位：ms，默认值：0
        withCredentials: false ,					 // 跨域请求是否允许携带 cookie

        // 下载事件
        before: null ,  // 发送请求之前（已经创建 xhr）
        after: null ,
        success: null ,                          // 请求：成功时回调
        netError: null ,                         // 请求：失败时回调
        error: null ,                            // 请求：失败时回调
        progress: null ,                         // 请求：加载时回调
        load: null ,                             // 请求：加载完成时回调
        timeout: null ,                          // 请求：超时回调
        abort: null ,                            // 请求：中断是回调
        loadstart: null ,						 // 请求：接收到响应的时候触发
        loadend: null , 						 // 请求：响应结束的时候触发（导致结束的原因：error , timeout , load，未知）

        // 上传事件
        uLoad: null ,							 // 上传：上传完成时回调
        uLoadstart: null ,						 // 上传：上传开始时回调
        uTimeout: null ,						 // 上传：上传开始超时回调
        uError: null ,							 // 上传：上传发生错误时回调
        uProgress: null ,						 // 上传：上传中回调
        uLoadend: null ,						 // 上传：上传终止时超时回调（有可能是发生错误而终止、有可能是超时终止...）
        uAbort: null ,							 // 上传：上传中断

        // 相关属性
        isReturnXHR: false ,					 // 是否返回 XHR 对象
        username: '' ,							 // http 验证的用户名
        password: '' ,							 // http 验证的密码
        // isUpload: false ,                        // 上传文件还是下载文件！ 决定了事件时定义在上传对象 还是 在下载对象上！

        // 是否允许携带用于区分普通请求 和 ajax 请求的请求头（标识）
        isAllowAjaxHeader: true
    };

    if (g.isString(url)) {
        option = g.isObject(option) ? option : {};
        option['url'] = url;
    } else {
        if (!g.isObject(url)) {
            throw new Error('未传入配置参数');
        }
        option = url
    }

    // this.methodRange		 = ['GET' , 'POST' , 'PUT' , 'DISPATCH' , 'DELETE'];
    this.dataType		     = ['String' , 'FormData' , 'Blob'];
    // 会根据不同的 responseType 将响应数据做一些转换后在返回给用户
    this.responseTypeRange	 = ['' , 'text' , 'document' , 'json' , 'blob'];
    this.enctypeRange		 = ['text/plain' , 'application/x-www-form-urlencoded' , 'multipart/form-data'];
    this.header			 = g.type(option['header']) === 'Undefined'				? this.default['header']		: option['header'];

    // this.method			 = !g.contain(option['method'] , this.methodRange)				? this.default['method']		: option['method'];
    this.method			 = option.method.toUpperCase();
    this.url				 = !g.isValid(option['url'])									? this.default['url']			: option['url'];
    this.async			        = g.type(option['async']) !== 'Boolean'					? this.default['async']		: option['async'];
    this.additionalTimestamp = g.type(option['additionalTimestamp']) !== 'Boolean'					? this.default['additionalTimestamp']		: option['additionalTimestamp'];
    this.data                  = g.isObject(option.data) ? g.buildQuery(option.data) : option.data;
    this.data			        = !g.contain(g.type(this.data) , this.dataType) ? this.default['data']		: this.data;
    this.responseType	 	    = !g.contain(option['responseType'] , this.responseTypeRange)  ? this.default['responseType']	: option['responseType'];
    this.wait		            = g.type(option['wait']) !== 'Number'				? this.default['wait']	: option['wait'];
    this.withCredentials		= g.type(option['withCredentials']) !== 'Boolean'				? this.default['withCredentials']	: option['withCredentials'];

    // 下载事件
    this.before			 = g.type(option['before']) !== 'Function'				? this.default['before']		: option['before'];
    this.success			 = g.type(option['success']) !== 'Function'				? this.default['success']		: option['success'];
    this.netError			 = g.type(option['netError']) !== 'Function'				? this.default['netError']		: option['netError'];
    this.error				 = g.type(option['error']) !== 'Function'					? this.default['error']			: option['error'];
    this.progress			 = g.type(option['progress']) !== 'Function'				? this.default['progress']		: option['progress'];
    this.loadstart			 = g.type(option['loadstart']) !== 'Function'				? this.default['loadstart']		: option['loadstart'];
    this.load				 = g.type(option['load']) !== 'Function'					? this.default['load']			: option['load'];
    this.loadend			 = g.type(option['loadend']) !== 'Function'				? this.default['loadend']	    : option['loadend'];
    this.timeout			 = g.type(option['timeout']) !== 'Function'				? this.default['timeout']		: option['timeout'];
    this.abort				 = g.type(option['abort']) !== 'Function'					? this.default['abort']			: option['abort'];

    // 上传事件
    this.uError			 = g.type(option['uError']) !== 'Function'					? this.default['uError']		: option['uError'];
    this.uProgress			 = g.type(option['uProgress']) !== 'Function'				? this.default['uProgress']		: option['uProgress'];
    this.uLoadstart		 = g.type(option['uLoadstart']) !== 'Function'				? this.default['uLoadstart']	: option['uLoadstart'];
    this.uLoad				 = g.type(option['uLoad']) !== 'Function'					? this.default['uLoad']			: option['uLoad'];
    this.uLoadend			 = g.type(option['uLoadend']) !== 'Function'				? this.default['uLoadend']	    : option['uLoadend'];
    this.uTimeout			 = g.type(option['uTimeout']) !== 'Function'				? this.default['uTimeout']		: option['uTimeout'];
    this.uAbort			 = g.type(option['uAbort']) !== 'Function'					? this.default['uAbort']		: option['uAbort'];

    this.isReturnXHR		 = g.type(option['isReturnXHR']) !== 'Boolean'				? this.default['isReturnXHR']   : option['isReturnXHR'];

    this.username			 = !g.isValid(option['username'])								? this.default['username']		: option['username'];
    this.password			 = !g.isValid(option['password'])								? this.default['password']		: option['password'];

    this.isAllowAjaxHeader = !g.isValid(option['isAllowAjaxHeader'])								? this.default['isAllowAjaxHeader']		: option['isAllowAjaxHeader'];
    this.direct = g.isBoolean(option.direct) ? option.direct : this.default.direct;

    this.run();
}

// 在各类钩子函数中都可以拦截请求的执行流程

// 创建 xhr 之前
Ajax.before = null;
// 创建 xhr 之后
Ajax.created = null;
// xhr open 之后
Ajax.opened = null;
// xhr 接收到响应之后
Ajax.responded = null;
// 发送请求之后
Ajax.after = null;

Ajax.prototype = {
    version: '1.0' ,
    cTime: '2016/10/25 17:32:00' ,
    author: '陈学龙' ,
    constructor: Ajax ,

    // 当前创建的 XMLHttpRequest
    xhr: null ,
    // 请求头
    url: null ,
    method: null ,
    async: null ,
    header: null ,
    additionalTimestamp: null ,
    data: null ,
    responseType: null ,
    wait: null ,
    withCredentials: null ,
    before: null ,
    success: null ,
    netError: null ,
    error: null ,
    progress: null ,
    loadstart: null ,
    load: null ,
    loadend: null ,
    timeout: null ,
    abort: null ,
    uError: null ,
    uProgress: null ,
    uLoadstart: null ,
    uLoad: null ,
    uLoadend: null ,
    uTimeout: null ,
    uAbort: null ,
    isReturnXHR: null ,
    username: null ,
    password: null ,
    isAllowAjaxHeader: null ,
    direct: null ,

    // 调用原生方法
    native: function(event){
        var args = arguments;
        args = g.array(args);
        args = args.slice(1);
        return this.xhr[event].apply(this.xhr , args);
    } ,

    // 获取或设置原生属性
    attr: function(key , val){
        if (G.isUndefined(val)) {
            return this.xhr[key];
        }
        this.xhr[key] = val;
    } ,

    // 获取请求头
    getHeader: function(key){
        return this.header[key];
    } ,

    // 设置请求头
    setHeader: function(key , val){
        this.header[key] = val;
    } ,

    // 从现有请求头集合中移除指定请求头
    removeHeader: function(key){
        delete this.header[key];
    } ,

    // 获取 XMLHttpRequest 对象
    get: function(){
        return this.xhr;
    } ,

    // 创建 ajax
    create: function(){
        var xhr = this.xhr = new XMLHttpRequest();
        if (!this.direct && g.isFunction(Ajax.created)) {
            if (Ajax.created.call(this) !== true) {
                return false;
            }
        }
        if (g.isFunction(this.before)) {
            this.before.call(this);
        }
        return true;
    } ,

    // 初始化 ajax
    initialize: function(){
        /**
         * ***********************
         * 初始化请求
         * ***********************
         */
        if (this.additionalTimestamp) {
            // 是否追加时间戳，防止请求被缓存
            var time = new Date().getTime();
            if (this.url.lastIndexOf('?') === -1) {
                this.url += '?';
            } else {
                this.url += '&';
            }
            this.url += '__timestamp__=' + time;
        }
        // 初始化要设置的请求头
        if (g.type(this.data) !== 'FormData') {
            // 表单提交默认的 content-type
            this.setHeader('Content-Type' , 'application/x-www-form-urlencoded');
        } else {
            // 如果是 FormData，请勿设置任何请求头
            this.removeHeader('Content-Type');
        }
        // 追加 AJAX 请求标识符头部
        // 这里请求设置有一个要求！不允许使用 _（下划线） ！！只能使用 - （中划线）
        if (this.isAllowAjaxHeader) {
            // 兼容使用 jQuery 库的项目
            this.setHeader('X-Request-With' , 'XMLHttpRequest');
            // SmallJs 独有
            this.setHeader('Ajax-Request' , 'yes');
        }
        // 设置请求头
        this.setRequestHeader();
        /**
         * **************************
         * 初始化必要属性
         * **************************
         */
        this.attr('timeout' , this.timeout);
        /**
         * ************************
         * 初始化响应
         * ************************
         */
        this.attr('responseType' , this.responseType);
        /**
         * ******************
         * 初始化事件
         * ******************
         */
        this.defineEvent();
    } ,

    // 初始化上传对象
    initializeUpload: function(){

    } ,

    // 打开 ajax 请求
    open: function(){
        /**
         * 支持使用了验证的请求
         */
        this.xhr.open(this.method , this.url , this.async , this.username , this.password);
        if (!this.direct && g.isFunction(Ajax.opened)) {
            return Ajax.opened.call(this);
        }
        return true;
    } ,

    // 设置 ajax 请求头
    setRequestHeader: function(){
        for (var key in this.header)
        {
            this.native('setRequestHeader' , key , this.header[key]);
        }
    } ,

    // 定义 xhr 事件
    defineEvent: function(){
        var self    = this;
        var xhr     = g(this.xhr);
        var upload  = g(this.xhr.upload);

        // 响应
        xhr.on('readystatechange' , function(){
            /**
             * 针对 readyState 代码含义
             * 0 未 open，未 send
             * 1 已 open，未 send
             * 2 已 send
             * 3 正在下载响应体
             * 4 请求完成
             *
             * 针对 status 的代码的含义
             * 如果 status !== 200 ，则表示发生了错误，否则表示传输完成
             * 可能是 0 （canceld），500 服务器内部错误等.....
             *
             */
            if (this.readyState === 4) {
                var response = this.response;
                var status   = this.status;
                var contentType = self.native('getResponseHeader' , 'Content-Type');
                    contentType = g.type(contentType) == 'String' ? contentType.toLowerCase() : '';
                if (contentType == 'application/json' && self.responseType != 'json') {
                    response = g.jsonDecode(this.response);
                }
                if (!this.direct && g.isFunction(Ajax.responded)) {
                    var next = Ajax.responded.call(self , response , status);
                    if (next === false) {
                        return ;
                    }
                }
                if (g.type(this.success) === 'Function') {
                    if (this.isReturnXHR) {
                        this.success(response , status , self.xhr);
                    } else {
                        // 可能是 responseText || responseXML
                        this.success(response , status);
                    }
                }
            }
        } , true , false);

        /*** 下载事件 ***/

        // error
        if (g.type(this.error) === 'Function') {
            xhr.on('error' , self.error , true , false);
        }

        // timeout
        if (g.type(this.timeout) === 'Function') {
            xhr.on('timeout' , this.timeout , true , false);
        }

        // loadstart
        if (g.type(this.loadstart) === 'Function') {
            xhr.on('loadstart' , this.loadstart , true , false);
        }

        // progress
        if (g.type(this.progress) === 'Function') {
            xhr.on('timeout' , this.progress , true , false);
        }

        // load
        if (g.type(this.load) === 'Function') {
            xhr.on('load' , this.load , true , false);
        }

        // loadend
        if (g.type(this.loadend) === 'Function') {
            xhr.on('loadend' , this.loadend , true , false);
        }

        // abort
        if (g.type(this.abort) === 'Function') {
            xhr.on('abort' , this.abort , true , false);
        }

        /*
         * 上传事件:
         * onloadstart
         * onprogress
         * onabort
         * onerror
         * onload
         * ontimeout
         * onloadend
         */
        // error
        if (g.type(this.uError) === 'Function') {
            upload.on('error' , self.uError , true , false);
        }

        // timeout
        if (g.type(this.uTimeout) === 'Function') {
            upload.on('timeout' , this.uTimeout , true , false);
        }

        // loadstart
        if (g.type(this.uLoadstart) === 'Function') {
            // console.log('load start');
            upload.on('loadstart' , this.uLoadstart , true , false);
        }

        // progress
        if (g.type(this.uProgress) === 'Function') {
            // console.log('你正在定义上传进度事件！' , this.uProgress);
            upload.on('progress' , this.uProgress , true , false);
        }

        // load
        if (g.type(this.uLoad) === 'Function') {
            console.log('load start');
            upload.on('load' , this.uLoad , true , false);
        }


        // loadend
        if (g.type(this.uLoadend) === 'Function') {
            upload.on('loadend' , this.uLoadend , true , false);
        }

        // abort
        if (g.type(this.uAbort) === 'Function') {
            upload.on('abort' , this.uAbort , true , false);
        }
    } ,

    // 发送请求
    send: function(){
        if (this.withCredentials) {
            this.xhr.withCredentials = this.withCredentials;
        }
        if (this.method === 'GET') {
            // get 方法在 url 中发送数据
            this.xhr.send(null);
        } else {
            // post 方法在 send 方法参数中发送数据
            this.xhr.send(this.data);
        }
    } ,

    // 初始化前调用
    _before_: function() {
        if (!this.direct && g.isFunction(Ajax.before)) {
            return Ajax.before.call(this);
        }
        return true;
    } ,

    // 初始化后调用
    _after_: function(){
        if (!this.direct && g.isFunction(Ajax.after)) {
            Ajax.after.call(this);
        }
    } ,

    // 重新执行
    restart: function(){
        this.run();
    } ,

    // 开始运行程序
    run: function(){
        if (this._before_() != true) {
            // 被用户手动拦截
            return ;
        }
        if (this.create() != true) {
            // 被用户手动拦截
            return ;
        }
        if (this.open() != true) {
            // 被用户手动拦截
            return ;
        }
        // 该方法必须在请求 open 之后调用
        this.initialize();
        if (this.send() != true) {
            // 被用户手动拦截
            return ;
        }
        // 请求在调用之后
        this._after_();
    }
};