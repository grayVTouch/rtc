/*
 * author 陈学龙 2016-12-12 17:03:00
 * 兼容性：IE 10及以上或同等级别其他浏览器
 */
(function(global , factory) {
    'use strict';

    if (typeof module != 'undefined' && typeof module.exports == 'object') {
        module.exports = factory(global , true);
    } else {
        factory(window);
    }
})(typeof window !== 'undefined' ? window : this , function(window , noGlobal){
    function Loading(selector , opt){
        var thisRange = [undefined , null , window];
        if (G.contain(this , thisRange) || !G.contain(this , thisRange) && this.constructor !== Loading) {
            return new Loading(selector , opt);
        }

        this._default = {
            // 动画时间
            time: 300 ,
            // 类名
            class: 'Loading' ,
            // 支持 'show' , 'hide'
            status: 'show' ,
            // 具体风格: line-scale ball-pulse
            type: 'line-scale' ,
            // 加载文本提示，支持字符串 & 数组
            // 实际上就是展示进度信息
            text: '' ,
            // 点击关闭后回调函数
            close: null ,
        };

        this._statusRange = ['show' , 'hide'];
        this._typeRange = ['roll-loader' , 'line-scale' , 'ball-pulse'];
        this._textRange = ['String' , 'Array'];

        this._con	  		= G(selector);
        this._time 			= G.type(opt['time'])	 !== 'Number'			 			? this._default['time']	   : opt['time'];
        this._status 		= !G.contain(opt['status'] , this._statusRange) 			? this._default['status']  : opt['status'];
        this._type 			= !G.contain(opt['type'] , this._typeRange) 				? this._default['type']  : opt['type'];
        this._text 			= !G.contain(G.type(opt['text']) , this._textRange) ? this._default['text'] : opt['text'];
        this._close 	    = G.isFunction(opt.close) ? opt.close : this._default.close;

        this._run();
    }

    Loading.prototype = {
        author: '陈学龙' ,
        cTime: '2016/12/12 17:53:00' ,
        constructor: Loading ,

        // close 回调函数接收的参数
        args: null ,
        // close 上下文环境
        context: null ,

        _initStaticHTML: function(){

        } ,

        _initStaticArgs: function(){
            // this._loading	= G('.Loading' , this._con.get(0)).first();
            this._loading   = this._con.children({
                tagName: 'div' ,
                className: 'Loading'
            } , false , true).first();
            this._bg		= G('.bg' , this._loading.get(0));
            this._cons		= G('.cons' , this._loading.get(0));
            this._text_		= G('.text' , this._cons.get(0));
            this._animate	= G('.animate' , this._cons.get(0));
            this._items		= G('.item' , this._animate.get(0));
            this._btns      = G('.btns' , this._loading.get(0)).first();
            this._close_    = G('.close' , this._btns.get(0));

            // 获取容器元素高度
            this._startOpacity  = 0;
            this._endOpacity    = 1;

            this.status = this._status;
        } ,

        _initStatic: function(){
            var self = this;

            // 处理文本
            this.text(this._text);

            // 仅显示给定的加载容器
            this._items.each(function(dom){
                dom = G(dom);
                if (dom.hasClass(self._type)) {
                    dom.removeClass('hide');
                }
            });
        } ,

        _initDynamicHTML: function(){

        } ,

        _initDynamicArgs: function(){
            // 要获取 offsetParent 就必须要元素存在于文档中
            this._loading.removeClass('hide');
            this._offsetParent = this._loading.offsetParent();
            // 要获取元素的真实尺寸，就必须不能让其受 Loading 元素的影响
            this._loading.addClass('hide');
            this._offsetParentScrollW = this._offsetParent.isDom() ? this._offsetParent.scrollWidth() : 0;
            this._offsetParentScrollH = this._offsetParent.isDom() ? this._offsetParent.scrollHeight() : 0;
        } ,

        _initDynamic: function(){
            // 设置容器高度
            this._loading.css({
                width: this._offsetParentScrollW + 'px' ,
                height: this._offsetParentScrollH + 'px'
            });
        } ,

        _initialize: function(){
            this._loading.css({
                opacity: 1
            });

            // 初始化状态设置
            if (this._status === 'hide') {
                this.hide();
            } else {
                this.show();
            }
        } ,

        show: function(){
            this._initDynamicHTML();
            this._initDynamicArgs();
            this._initDynamic();

            this._loading.removeClass('hide');
            this.status = 'show';

            this._loading.animate({
                opacity: this._endOpacity
            });
        } ,

        hide: function(){
            var self = this;
            this.status = 'hide';
            this._loading.animate({
                opacity: this._startOpacity
            } , function(){
                self._loading.addClass('hide');
            });
        } ,

        text: function(text){
            if (!G.isValid(text)) {
                this._text_.html('');
                return ;
            }
            var self = this;
            var res = [];
            if (!G.isValid(text)) {
                this._text_.addClass('hide');
                return ;
            }
            this._text_.removeClass('hide');
            if (G.isString(text)) {
                res.push(text);
            }
            if (G.isArray(text)) {
                res = text;
            }
            // 清空原内容
            this._text_.html('');
            // 新增内容
            res.forEach(function(v){
                var span = document.createElement('span');
                span = G(span);
                span.attr('class' , 'line');
                span.text(v);
                self._text_.append(span.get(0));
            });
        } ,

        // 设置点击关闭按钮时，传递给 close 回调函数的参数
        setArgs: function(){
            this.args = arguments;
        } ,

        // 设置环境变量
        setContext: function(context){
            this.context = context;
        } ,

        // 缩放事件
        _resizeEvent: function(){
            this._initDynamicHTML();
            this._initDynamicArgs();
            this._initDynamic();

            // 根据当前状态设置
            if (this.status === 'show') {
                this._loading.removeClass('hide');
            }
        } ,

        _closeEvent: function(){
            this.hide();

            if (G.isFunction(this._close)) {
                this._close.apply(this.context , this.args);
            }
        } ,

        // 定义事件
        _defineEvent: function(){
            var win = G(window);
            this._close_.on('click' , this._closeEvent.bind(this) , true , false);
            win.on('resize' , this._resizeEvent.bind(this) , true , false);
        } ,

        _run: function(){
            this._initStaticHTML();
            this._initStaticArgs();
            this._initStatic();
            this._initDynamicHTML();
            this._initDynamicArgs();
            this._initDynamic();
            this._initialize();
            this._defineEvent();
        }
    };

    if (!noGlobal) {
        window.Loading = Loading;
    }

    return Loading;
});