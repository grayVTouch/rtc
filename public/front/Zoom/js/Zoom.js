/*
 ****************************************
 * Author 陈学龙 grayVTouch at 2018-09-05
 ****************************************
 */
(function(global , factory){
    "use strict";

    if (typeof module !== 'undefined' && typeof module.exports === 'object') {
        moudle.exports = factory(global , true);
    } else {
        factory(global);
    }
})(typeof window === 'undefined' ? this : window , function(window , noGlobal){
    "use strict";

    function Zoom(selector , option){
        var thisRange = [null , undefined , window];

        if (G.contain(this , thisRange) || this.constructor !== Zoom) {
            return new Zoom(con , option);
        }

        this._default = {

        };

        if (G.isUndefined(option)) {
            option = this._default;
        }

        this._con = G(selector);

        // 相关参数初始化
        this._run();
    }

    Zoom.prototype = {
        constructor: Zoom ,

        _before: function(){} ,
        _initStaticHTML: function(){} ,

        _initStaticArgs: function(){
            this._conW = this._con.width('border-box');
            this._conH = this._con.height('border-box');

            this._oneStartX = 0;
            this._twoStartX = 0;
            this._oneStartY = 0;
            this._twoStartY = 0;

            this._sW = this._conW;
            this._sH = this._conH;
            this._sL = this._con.getCoordVal('left');
            this._sT = this._con.getCoordVal('top');

            this._tmpOneX = 0;
            this._tmpTwoX = 0;

            // 缩放比例
            this._ratio = 0.03;

            // 最小
            this._minRatio = 0.8;
            this._minW = Math.round(this._conW * this._minRatio);
            this._minH = Math.round(this._conH * this._minRatio);

            this._midX = this._conW / 2;
            this._midY = this._conH / 2;
            this._oL = this._sL;
            this._oT = this._sT;
        } ,

        _initStatic: function(){

        } ,

        _initDynamicHTML: function(){

        } ,

        _initDynamicArgs: function(){
            this._canZoom = false;
        } ,

        _initDynamic: function(){

        } ,

        _after: function(){

        } ,

        _initialize: function(){

        } ,

        _startEvent: function(e){
            if (e.touches.length < 2) {
                return ;
            }
            this._canZoom = true;

            this._oneStartX = e.touches[0].clientX;
            this._oneStartY = e.touches[0].clientY;
            this._twoStartX = e.touches[1].clientX;
            this._twoStartY = e.touches[1].clientY;
            this._sL = this._con.getCoordVal('left');
            this._sT = this._con.getCoordVal('top');
            this._sW = this._con.width('border-box');
            this._sH = this._con.height('border-box');

            this._tmpOneX = 0;
            this._tmpTwoX = 0;
            this._wAmount = this._sW * this._ratio;
            this._hAmount = this._sH * this._ratio;
            this._count = 0;

            this._winL = this._con.getWindowOffsetVal('left');
            this._winT = this._con.getWindowOffsetVal('top');
        } ,
        _endEvent: function(e){
            if (e.touches.length > 0) {
                return ;
            }

            if (!this._canZoom) {
                return ;
            }
            this._canZoom = false;
        } ,
        _moveEvent: function(e){
            if (!this._canZoom || e.touches.length < 2) {
                return ;
            }

            G.prevent(e);
            G.stopImmediate(e);

            var oneEndX = e.touches[0].clientX;
            var oneEndY = e.touches[0].clientY;
            var twoEndX = e.touches[1].clientX;
            var twoEndY = e.touches[1].clientY;

            var oneOx = oneEndX - this._oneStartX;
            var twoOx = twoEndX - this._twoStartX;
            var oneOy = oneEndY - this._oneStartY;
            var twoOy = twoEndY - this._twoStartY;

            var oneStartX = this._tmpOneX === 0 ? this._oneStartX : this._tmpOneX;
            var twoStartX = this._tmpTwoX === 0 ? this._twoStartX : this._tmpTwoX;

            var oneDir = oneStartX > oneEndX ? 'left' : oneStartX < oneEndX ? 'right' : 'stop';
            var twoDir = twoStartX > twoEndX ? 'left' : twoStartX < twoEndX ? 'right' : 'stop';

            this._tmpOneX = oneEndX;
            this._tmpTwoX = twoEndX;

            var onePoint  = '';
            var type = '';
            // 判断缩放方向
            if (this._oneStartX > this._twoStartX) {
                onePoint = 'two';
                // 第一个点 two
                if (oneDir === twoDir) {
                    type = 'move';
                } else if (oneDir === 'left') {
                    type = 'down';
                } else if (oneDir === 'stop') {
                    type = twoDir === 'left' ? 'up' : 'down';
                } else {
                    type = 'up';
                }
            } else {
                onePoint = 'one';
                // 第一个点 one
                if (oneDir === twoDir) {
                    type = 'move';
                } else if (oneDir === 'left') {
                    type = 'up';
                } else if (oneDir === 'stop') {
                    type = twoDir === 'left' ? 'down' : 'up';
                } else {
                    type = 'down';
                }
            }

            switch (type)
            {
                case 'up':
                    this._count++;
                    break;
                case 'down':
                    this._count--;
                    break;
                case 'move':
                    break;
            }

            var wAmount = this._count * this._wAmount;
            var hAmount = this._count * this._hAmount;
            var endW = Math.max(this._minW , this._sW + wAmount);
            var endH = Math.max(this._minH , this._sH + hAmount);

            // 缩放中心
            var cenX = 0;
            var cenY = 0;

            // 计算当前的中心点
            if (Math.abs(oneOx) < 5 && Math.abs(oneOy) < 5) {
                cenX = oneEndX;
                cenY = oneEndY;
            } else if (Math.abs(twoOx) < 5 && Math.abs(twoOy) < 5) {
                cenX = twoEndX;
                cenY = twoEndY;
            } else {
                var ox = Math.abs(this._oneStartX - this._twoStartX);
                var oy = Math.abs(this._oneStartY - this._twoStartY);

                cenX = this._oneStartX > this._twoStartX ? this._oneStartX - ox / 2 : this._oneStartX + ox / 2;
                cenY = this._oneStartY > this._twoStartY ? this._oneStartY - oy / 2 : this._oneStartY + oy / 2;
            }

            cenX -= this._winL;
            cenY -= this._winT;

            var xRatio = cenX / this._sW;
            var yRatio = cenY / this._sH;

            // 放大后的缩放中心
            var midX = endW * xRatio;
            var midY = endH * yRatio;

            var lAmount = midX - cenX;
            var tAmount = midY - cenY;

            var endL = this._sL - lAmount;
            var endT = this._sT - tAmount;

            this._con.css({
                width: endW + 'px' ,
                height: endH + 'px' ,
                left: endL + 'px' ,
                top: endT + 'px'
            });
        } ,

        // 是否缩放状态
        status: function(){
            var conW = this._con.width('border-box');

            if (conW < this._conW) {
                return 'down';
            }

            if (conW > this._conW) {
                return 'up';
            }

            return 'none';
        } ,

        // 放大
        enlarge: function(){
            var ratio = 3.2;
            var endW = this._conW * ratio;
            var endH = this._conH * ratio;
            var midX = endW / 2;
            var midY = endH / 2;
            var lAmount = midX - this._midX;
            var tAmount = midY - this._midY;
            var endL = this._oL - lAmount;
            var endT = this._oT - tAmount;

            this._con.animate({
                width: endW + 'px' ,
                height: endH + 'px' ,
                left: endL + 'px' ,
                top: endT + 'px'
            });
        } ,

        // 还原
        origin: function(){
            this._con.animate({
                width: this._conW + 'px' ,
                height: this._conH + 'px' ,
                left: this._oL + 'px' ,
                top: this._oT + 'px'
            });
        } ,

        _defineEvent: function(){
            var win = G(window);
            this._con.on('touchstart' , this._startEvent.bind(this) , true , false);
            win.on('touchend' , this._endEvent.bind(this) , true , false);
            win.on('touchmove' , this._moveEvent.bind(this) , true , {
                capture: false ,
                passive: false
            });
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
        window.Zoom = Zoom;
    }

    return Zoom;


});