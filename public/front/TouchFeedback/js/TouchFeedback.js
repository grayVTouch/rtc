/**
 * ********************
 * 动作反馈 by 陈学龙
 * 2019-02-23 01:06
 * ********************
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

    function TouchFeedback(selector , option){
        var thisRange = [window , null , undefined];

        if (G.contain(this , thisRange) || (!G.contain(this , thisRange) && this.constructor !== TouchFeedback)) {
            return new TouchFeedback(selector , option);
        }

        this.default = {
            time: 700 ,
            backgroundColor: '#626a77' ,
        };

        if (!G.isObject(option)) {
            option = this.default;
        }

        this.con = G(selector);
        this.time = G.isNumber(option.time) ? option.time : this.default.time;
        this.backgroundColor = G.isString(option.backgroundColor) ? option.backgroundColor : this.default.backgroundColor;

        this.run();
    }

    TouchFeedback.prototype = {
        constructor: TouchFeedback ,

        initStaticHTML: function(){

        } ,

        initStaticArgs: function(){
            var touchFeedback = document.createElement('div');
                touchFeedback.className = 'touch-feedback';
            this.con.append(touchFeedback);
            this.touchFeedback = G(touchFeedback);

            this.conW = this.touchFeedback.width('border-box');
            this.conH = this.touchFeedback.height('border-box');

            this.sW = 0;
            this.sH = 0;
            this.maxOpacity = 1;
            this.domLen = 0;
        } ,

        initStatic: function(){
            this.touchFeedback.addClass('hide');
        } ,

        initDynamicHTML: function(){

        } ,

        initDynamicArgs: function(){

        } ,

        initDynamic: function(){

        } ,

        start: function(){

        } ,

        create: function(){
            var div = document.createElement('div');
                div = G(div);
                div.addClass('circle');
            this.touchFeedback.append(div.get(0));
            this.domLen++;
            div.css({
                opacity: this.maxOpacity ,
                backgroundColor: this.backgroundColor ,
            });
            // this.domLen
            return div.get(0);
        } ,

        clickEvent: function(e){
            this.touchFeedback.removeClass('hide');
            var dom = this.create();
            var x = e.clientX;
            var y = e.clientY;
            var bX = this.con.getWindowOffsetVal('left');
            var bY = this.con.getWindowOffsetVal('top');
            var left = Math.abs(x - bX);
            var top = Math.abs(y - bY);
            var leftTop = Math.ceil(Math.sqrt(Math.pow(left , 2) + Math.pow(top , 2)));
            var leftBtm = Math.ceil(Math.sqrt(Math.pow(left , 2) + Math.pow(this.conH - top , 2)));
            var rightTop = Math.ceil(Math.sqrt(Math.pow(this.conW - left , 2) + Math.pow(top , 2)));
            var rightBtm = Math.ceil(Math.sqrt(Math.pow(this.conW - left , 2) + Math.pow(this.conH - top , 2)));
            var radius = Math.max(leftTop , leftBtm , rightTop , rightBtm);
            var endW = radius * 2;
            var endH = endW;
            this.animate(dom , left , top , endW , endH);
        } ,

        animate: function(dom , left , top , w , h){
            dom = G(dom);
            G.CAF(dom.get(0).__touch_feedback_timer__);
            window.clearTimeout(dom.get(0).__touch_feedback_timer_1__);
            var wAmount = w - this.sW;
            var hAmount = h - this.sH;
            var sTime = new Date().getTime();
            var eTime = sTime;
            var duration = 0;
            var ratio = 0;
            var _wAmount = 0;
            var _hAmount = 0;
            var endLeft = 0;
            var endTop = 0;
            var endW = 0;
            var endH = 0;
            var opacity = this.maxOpacity;
            var opacityAmount = this.maxOpacity;
            var _opacityAmount = 0;
            var endOpacity = 0;
            var self = this;
            var animate = function(){
                eTime = new Date().getTime();
                duration = eTime - sTime;
                ratio = duration / self.time;
                ratio = Math.min(ratio , 1);
                _wAmount = wAmount * ratio;
                _hAmount = hAmount * ratio;
                _opacityAmount = opacityAmount * ratio;
                endW = self.sW + _wAmount;
                endH = self.sH + _hAmount;
                endTop = top - _hAmount / 2;
                endLeft = left - _wAmount / 2;
                endOpacity = opacity - _opacityAmount;
                dom.css({
                    width: endW + 'px' ,
                    height: endH + 'px' ,
                    top: endTop + 'px' ,
                    left: endLeft + 'px' ,
                    opacity: endOpacity ,
                });

                if (ratio != 1) {
                    dom.get(0).__touch_feedback_timer__ = G.RAF(animate);
                    return ;
                }
                dom.get(0).__touch_feedback_timer_1__ = window.setTimeout(function(){
                    dom.parent().remove(dom.get(0));
                    self.domLen--;
                    if (self.domLen == 0) {
                        self.touchFeedback.addClass('hide');
                    }
                } , 100);
            };
            animate();
        } ,

        defineEvent: function(){
            this.con.on('click' , this.clickEvent.bind(this) , true , false);
        } ,
        run: function(){
            this.initStaticHTML();
            this.initStaticArgs();
            this.initStatic();
            this.initDynamicHTML();
            this.initDynamicArgs();
            this.initDynamic();
            this.defineEvent();
        } ,
    };

    if (!noGlobal) {
        window.TouchFeedback = TouchFeedback;
    }

    return TouchFeedback;
});