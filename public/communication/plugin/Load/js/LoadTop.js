/**
 * *******************
 * 下拉刷新 by 陈学龙
 * *******************
 */
(function(global , factory){
    "use strict";

    if (typeof module !== 'undefined' && typeof module.exports === 'object') {
        module.exports = factory(global , true);
    } else {
        factory(global);
    }
})(typeof window === 'undefined' ? this : window , function(window , noGlobal) {
    "use strict";

    function LoadTop(selector , option){
        var thisRange = [null , undefined , window];

        if (G.contain(this , thisRange) || this.constructor !== LoadTop) {
            return new LoadTop(selector , option);
        }

        this._default = {
            time: 300 ,
            // 允许的值：show | hide
            status: 'show' ,
            text: '正在加载' ,
            // 加载代码
            load: null
        };

        if (G.isUndefined(option)) {
            option = this._default;
        }

        this._statusRange = ['show' , 'hide'];

        this._con = G(selector);
        this._status = G.contain(option.status , this._statusRange) ? option.status : this._default.status;
        this._text = G.isValid(option.text) ? option.text : this._default.text;
        this._load = G.isFunction(option.load) ? option.load : this._default.load;

        // 相关参数初始化
        this._run();
    }

    LoadTop.prototype = {
        constructor: LoadTop ,

        _before: function(){


            // this._LoadTop.removeClass('hide');
        } ,

        _initStaticHTML: function(){

        } ,
        _initStaticArgs: function(){
            this._LoadTop = this._con.children({
                class: 'Load-Top'
            });
            this._text_ = G('.text' , this._LoadTop.get(0));

            this._LoadTopH = this._LoadTop.height('border-box');
            this._minOpacity = 0;
            this._maxOpacity = 1;

            this._minT  = -this._LoadTopH;
            this._hideT = -this._LoadTopH;
            this._showT = 0;
            // 加载 top
            this._rangeT = 0;

            this._browser = G.browser();

            this._canMove   = false;
            this._startX    = 0;
            this._startY    = 0;
            this._endX      = 0;
            this._endY      = 0;
            this._startT    = 0;
            this._endT      = 0;
            // 防止拉伸过度，这边仅获取偏移量的一定比例数值
            this._ratio     = 0.5;

            // 刷新状态不允许操作
            this._isLoading = false;
            this._blocked = false;
            this._moving = false;
        } ,

        _initStatic: function(){
            this._LoadTop.css({
                opacity: this._minOpacity ,
                marginTop: this._hideT + 'px'
            });
        } ,

        _initDynamicHTML: function(){} ,

        _initDynamicArgs: function(){} ,

        _initDynamic: function(){} ,

        _after: function(){
            // this._LoadTop.addClass('hide');
        } ,

        _initialize: function(){
            this.text(this._text);

            if (this._status === 'show') {
                this.show();
            }
        } ,

        // 显示
        show: function(text){
            this._status = 'hide';
            this.text(text);
            // this._LoadTop.removeClass('hide');
            this._LoadTop.animate({
                marginTop: this._showT + 'px' ,
                opacity: this._maxOpacity
            });
        } ,

        hide: function(text){
            var self = this;
            this._status = 'hide';
            this.text(text);
            this._isLoading = false;
            this._LoadTop.animate({
                marginTop: this._hideT + 'px' ,
                opacity: this._minOpacity
            } , function(){
                // self._LoadTop.addClass('hide');
            } , this._time);
        } ,

        // 设置显示文本
        text: function(text){
            if (!G.isValid(text)) {
                return ;
            }
            this._text_.text(text);
        } ,

        _mousedonwEvent: function(e){
            if (this._isLoading || !this._con.isTop()) {
                return ;
            }

            this._LoadTop.removeClass('hide');
            this._canMove = true;
            this._blocked = false;
            this._moving = false;
            this._startX = this._browser === 'mobile' ? e.touches[0].clientX : e.clientX;
            this._startY = this._browser === 'mobile' ? e.touches[0].clientY : e.clientY;
            this._startT = this._LoadTop.getCoordVal('marginTop');
            this._sTime = new Date().getTime();

            this.text('下拉可以刷新');
        } ,

        _mouseupEvent: function(){
            if (this._isLoading || !this._canMove) {
                return ;
            }
            this._canMove = false;
            this._endT = this._LoadTop.getCoordVal('marginTop');

            var distance = Math.abs(this._endT - this._startT);

            if (distance <= this._LoadTopH) {
                return this.hide();
            }

            this._isLoading = true;
            this.text('正在刷新');
            this.show();

            if (G.isFunction(this._load)) {
                this._load.call(this);
            }
        } ,

        _mousemoveEvent: function(e){
            if (this._isLoading || !this._canMove) {
                return ;
            }

            this._endX = this._browser === 'mobile' ? e.touches[0].clientX : e.clientX;
            this._endY = this._browser === 'mobile' ? e.touches[0].clientY : e.clientY;
            var _ox = this._endX - this._startX;
            var _oy = this._endY - this._startY;
            var dir = G.gesture(_ox , _oy , true);

            if (!this._moving) {
                if (this._blocked || dir === 'none') {
                    return ;
                }

                if (dir === 'horizontal') {
                    this._blocked = true;
                    return ;
                }
            }
            this._moving = true;
            G.stopImmediate(e);
            // 降低移动带来的实际变化距离（不需要那么多）
            var oy  = (this._endY - this._startY) * this._ratio;
            this._endT = Math.max(this._minT , this._startT + oy);
            var ratio   = this._endT >= this._showT ? 1 : Math.max(0 , Math.min(1 , this._endY >= this._startY ? Math.abs(oy) / this._LoadTopH : 1 - Math.abs(oy) / this._LoadTopH));
            var opacity = Math.max(this._minOpacity , Math.min(this._maxOpacity , this._maxOpacity * ratio));

            this._LoadTop.css({
                marginTop: this._endT + 'px' ,
                opacity: opacity
            });

            if (this._endT >= this._rangeT) {
                this.text('松开立即刷新');
            } else {
                this.text('下拉可以刷新');
            }
        } ,

        _defineEvent: function(){
            var win = G(window);
            var mousedown   = this._browser === 'mobile' ? 'touchstart' : 'mousedown';
            var mouseup     = this._browser === 'mobile' ? 'touchend' : 'mouseup';
            var mousemove   = this._browser === 'mobile' ? 'touchmove' : 'mousemove';

            this._con.on(mousedown , this._mousedonwEvent.bind(this) , true , false);
            win.on(mousemove , this._mousemoveEvent.bind(this) , true , false);
            win.on(mouseup , this._mouseupEvent.bind(this) , true , false);
        } ,

        _run: function(){
            this._before();
            this._initStaticHTML();
            this._initStaticArgs();
            this._initStatic();
            this._initDynamicHTML();
            this._initDynamicArgs();
            this._initDynamic();
            this._after();
            this._initialize();
            this._defineEvent();
        }
    };

    if (!noGlobal) {
        window.LoadTop = LoadTop;
    }

    return LoadTop;
});