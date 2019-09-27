/**
 * *******************
 * 侧边栏 by 陈学龙
 * *******************
 */
(function(global , factory){
    "use strict";

    if (typeof module !== 'undefined' && typeof module.exports === 'object') {
        module.exports = factory(global , true);
    } else {
        factory(global);
    }
})(typeof window === 'undefined' ? this : window , function(window , noGlobal){
    "use strict";

    function Slidebar(selector , option){
        var thisRange = [null , undefined , window];

        if (G.contain(this , thisRange) || this.constructor !== Slidebar) {
            return new Slidebar(selector , option);
        }

        this._default = {
            // 动画时间
            time: 300 ,
            // 滑块宽度
            width: '75%' ,
            // 滑块遮罩层透明度
            opacity: 0.3 ,
            // 状态：show | hide
            status: 'show' ,
            // 点击时回调
            click: null ,
            // 侧边栏方向: left , right
            dir: 'left' ,
            // 侧边栏展示时回调
            open: null ,
            // 侧边栏隐藏时回调
            close: null ,
        };

        if (G.isUndefined(option)) {
            option = this._default;
        }

        this._con = G(selector);

        this._statusRange = ['show' , 'hide'];
        this._dirRange = ['left' , 'right'];

        this._time      = G.isNumber(option.time) ? option.time : this._default.time;
        this._width     = G.isValid(option.width) ? option.width : this._default.width;
        this._opacity   = G.isFloat(option.opacity) ? option.opacity : this._default.opacity;
        this._status    = G.contain(option.status , this._statusRange) ? option.status : this._default.status;
        this._dir = G.contain(option.dir , this._dirRange) ? option.dir : this._default.dir;
        this._open  = option.open;
        this._close  = option._close;

        // 相关参数初始化
        this._run();
    }

    Slidebar.prototype = {
        constructor: Slidebar ,

        _initStaticHTML: function(){

        } ,

        _initStaticArgs: function(){
            this._slidebar = G('.slidebar' , this._con.get(0));
            this._mask = this._slidebar.children({
                class: 'mask'
            });
            this._con_ = this._slidebar.children({
                class: 'con'
            });

            this._browser = G.browser();
            this._canMove = false;
            this._wait = false;

            this._startX = 0;
            this._endX = 0;
            this._ratio = 1/3;
            this._duration = 200;
            this._sTime = 0;
            this._eTime = 0;
            this._minRatio = 0.05;

            this._attr = this._dir;
            this._startVal = 0;
            this._endVal = 0;
            this._once = true;

            this.status = this._status;
        } ,

        _initStatic: function(){
            this._con_.css({
                width: this._width
            });
            this._slidebar.removeClass('hide');
            this._mask.css('opacity' , this._minOpacity);
        } ,

        _initDynamicHTML: function(){

        } ,

        _initDynamicArgs: function(){
            this._conW = this._con_.width('border-box');
            this._conH = this._con_.height('border-box');
            this._maxL = 0;
            this._minL = -this._conW;
            this._minR = -this._conW;
            this._maxR = 0;
            this._minOpacity = 0;
            this._maxOpacity = this._opacity;

            this._amount = this._conW * this._ratio;
            this._minAmount = this._conW * this._minRatio;

            this._conMinY = this._con_.getWindowOffsetVal('top');
            this._conMaxY = this._conMinY + this._conH;

            this._minVal = this._attr === 'left' ? this._minL : this._minR;
            this._maxVal = this._attr === 'left' ? this._maxL : this._maxR;
        } ,

        _initDynamic: function(){
            if (this._once) {
                this._once = false;
                this._con_.css(this._attr , this._minVal + 'px');
                this._conMinX = this._dir === 'left' ? this._con_.getWindowOffsetVal('left') + this._conW : this._con_.getWindowOffsetVal('left') - this._conW;
                this._conMaxX = this._conMinX + this._conW;
            }
        } ,

        _initialize: function(){
            if (this._status === 'show') {
                this.show();
            } else {
                this._con.addClass('hide');
            }
        } ,

        _before: function(){

        } ,

        _after: function(){

        } ,

        // 显示
        show: function(){
            this.status = 'show';
            this._con.removeClass('hide');

            var endOpacity  = this._maxOpacity;
            var endVal      = this._maxVal + 'px';
            var json        = {};
            var self        = this;

            json[this._attr] = endVal;

            this._con_.animate(json , null , this._time);
            this._mask.animate({
                opacity: endOpacity
            } , function(){
                if (G.isFunction(self._open)) {
                    self._open();
                }
            } , this._time);
        } ,

        // 隐藏
        hide: function(){
            var self = this;
            this.status = 'hide';
            var endOpacity  = this._minOpacity;
            var endVal      = this._minVal + 'px';
            var json        = {};

            json[this._attr] = endVal;

            this._con_.animate(json , function(){
                self._con.addClass('hide');
                if (G.isFunction(self._close)) {
                    self._close();
                }
            } , this._time);
            this._mask.animate({
                opacity: endOpacity
            } , null , this._time);
        } ,

        // 还原
        origin: function(){
            if (this.status === 'show') {
                this.show();
            } else {
                this.hide();
            }
        },

        _mousedownEvent: function(e){
            this._canMove = true;
            this._sTime = new Date().getTime();
            this._startX = this._browser === 'mobile' ? e.touches[0].clientX : e.clientX;
            this._startVal = this._con_.getCoordVal(this._attr);
        } ,

        _mouseupEvent: function(){
            this._wait = false;

            if (!this._canMove) {
                return ;
            }

            this._canMove = false;
            this._endVal = this._con_.getCoordVal(this._attr);

            var distance = Math.abs(this._endVal - this._startVal);

            if (distance <= this._minAmount) {
                return this.origin();
            }

            this._eTime = new Date().getTime();

            var status = null;

            if (this._dir === 'left') {
                status = this._endX > this._startX ? 'show' : 'hide';
            } else {
                status = this._endX > this._startX ? 'hide' : 'show';
            }

            var duration = this._eTime - this._sTime;

            if (duration <= this._duration || distance > this._amount) {
                this.status = status;
            }

            this.origin();
        } ,

        _mousemoveEvent: function(e){
            if (!this._wait && !this._canMove) {
                return ;
            }

            var endY    = this._browser === 'mobile' ? e.touches[0].clientY : e.clientY;
            this._endX  = this._browser === 'mobile' ? e.touches[0].clientX : e.clientX;

            if (this._wait) {
                if (this._endX >= this._conMinX && this._endX <= this._conMaxX && endY >= this._conMinY && endY <= this._conMaxY) {
                    this._wait = false;
                    this._mousedownEvent.call(this , e);
                }
                return ;
            }

            var ox      = this._endX - this._startX;
            var ratio   = 1;

            if (this._dir === 'left') {
                ratio = this._endX <= this._startX ? 1 - Math.abs(ox) / this._conW : 1;
            } else {
                ratio = this._endX <= this._startX ? 1 : 1 - Math.abs(ox) / this._conW;
            }

            var endOpacity = Math.max(this._minOpacity , Math.min(this._maxOpacity , this._maxOpacity * ratio));

            this._endVal = Math.max(this._minVal , Math.min(this._maxVal , this._dir === 'left' ? this._startVal + ox : this._startVal - ox));

            this._con_.css(this._attr , this._endVal + 'px');
            this._mask.css('opacity' , endOpacity);
        } ,

        _mousedonwEvent_: function(){
            this._wait = true;
        } ,

        _defineEvent: function(){
            var win = G(window);

            this._slidebar.on('click' , this.hide.bind(this) , true , false);
            this._con_.on('click' , G.stop.bind(G) , true , false);

            var mousedown   = this._browser === 'mobile' ? 'touchstart' : 'mousedown';
            var mouseup     = this._browser === 'mobile' ? 'touchend' : 'mouseup';
            var mousemove   = this._browser === 'mobile' ? 'touchmove' : 'mousemove';

            this._con_.on(mousedown , this._mousedownEvent.bind(this) , true , false);
            win.on(mouseup , this._mouseupEvent.bind(this) , true , false);
            win.on(mousemove , this._mousemoveEvent.bind(this) , true , false);

            this._slidebar.on(mousedown , this._mousedonwEvent_.bind(this) , true , false);
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
        window.Slidebar = Slidebar;
    }

    return Slidebar;
});