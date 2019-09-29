/**
 * **************************************
 * author 陈学龙 2017-07-19 14:57:00
 * 水平轮播
 * **************************************
 */
(function(global , factory){
    "use strict";

    if (typeof module !== 'undefined' && typeof module.exports === 'object') {
        module.exports = factory(global , true);
    } else {
        factory(global);
    }
})(typeof window === 'undefined' ? this : window , function(window , noGlobal){
    'use strict';

    function Switch(selector , option){
        var thisRange = [window , null , undefined];

        if (G.contain(this , thisRange) || this.constructor !== Switch) {
            return new Switch(selector , option);
        }
        this._default = {
            id: '' ,
            // 动画过度时间
            time: 300 ,
            // 切换事件
            switch: null
        };

        if (G.isUndefined(option)) {
            option = this._default;
        }

        this._con   = G(selector);
        this._time  = G.isInt(option.time)  ? option.time   : this._default.time;
        this._id    = G.isValid(option.id) ? option.id  : this._default.id;
        this._switch = G.isFunction(option.switch) ? option.switch  : this._default.switch;
        this._run();
    }

    Switch.prototype = {
        constructor: Switch ,

        _initStaticHTML: function(){

        } ,

        _initStaticArgs: function(){
            this._switch_ = this._con.children().filter({
                class: 'switch'
            });
            this._items = this._switch_.children().filter({
                class: 'items'
            });
            this._items_ = this._items.children().filter({
                class: 'item'
            });

            // 超过判断时间，手指仍按住移动，超过 1/3 宽度，则切换到另一项
            this._ratio = 1/3;
            // 最少移动 2%，否则不做处理
            this._minRatio = 0.05;
            // 滑动时间在 200ms 以内，切换到另一项
            this._duration = 200;
            this._sTime = 0;
            this._eTime = 0;
            this._canMove = false;
            this._startX = 0;
            this._endX = 0;
            this._startY = 0;
            this._endY = 0;
            this._startL = 0;
            this._endL = 0;
            this._startT = 0;
            this._endT = 0;
            this._minIndex = 1;
            this._maxIndex = this._items_.length;

            this._platform = G.browser();
            this._index = G.isValid(this._id) ? this.index(this._id) : null;

            this._blocked  = false;
            this._moving   = false;
        } ,

        _initStatic: function(){

        } ,

        _initDynamicHTML: function(){

        } ,

        _initDynamicArgs: function(){
            var self = this;

            this._switchW = this._switch_.width();
            this._itemsW = this._switchW * this._items_.length;
            this._itemW = this._switchW;
            this._amount = Math.ceil(this._itemW * this._ratio);
            this._minAmount = Math.ceil(this._itemW * this._minRatio);

            // 获取每一个索引的距离
            this._posRange = [];
            var count = 0;
            this._items_.each(function(){
                self._posRange.push(-count * self._itemW);
                count++;
            });

            this._minL = this._posRange[this._posRange.length - 1];
            this._maxL = this._posRange[0];
        } ,

        _initDynamic: function(){
            this._items.css({
                width: this._itemsW + 'px'
            });
            this._items_.css({
                width: this._itemW + 'px'
            });
        } ,

        _initialize: function(){
            if (G.isValid(this._index)) {
                this.switch(this._index);
            }
        } ,

        // 获取索引
        index: function(id){
            var i = 0;
            var cur = null;

            for (; i < this._items_.length; ++i)
            {
                cur = this._items_.jump(i , true);
                if (cur.data('id') == id) {
                    return i + 1;
                }
            }

            throw new Error('未找到当前提供id:' + id + ' 对应项');
        } ,

        id: function(id){
            this.switch(this.index(id));
        } ,

        // 定位到指定位置
        switch: function(index){
            var self = this;
            index = Math.max(this._minIndex , Math.min(this._maxIndex , index));
            var left = this._posRange[index - 1];
            var item = G(this.item(index));
            var id = item.data('id');

            this._items.animate({
                left: left + 'px'
            } , function(){
                if (G.isFunction(self._switch)) {
                    self._switch(id);
                }
            } , this._time);
        } ,

        // 通过索引获取项
        item: function(index){
            return this._items_.jump(index - 1 , true).get(0);
        } ,

        // 通过 id 获取项
        find: function(id){
            return this.item(this.index(id));
        } ,

        _mousedownEvent: function(e){
            this._canMove = true;
            this._startX    = this._platform === 'mobile' ? e.touches[0].clientX : e.clientX;
            this._startY    = this._platform === 'mobile' ? e.touches[0].clientY : e.clientY;
            this._startL    = this._items.getCoordVal('left');
            this._startT    = this._items.getCoordVal('top');
            this._sTime     = new Date().getTime();
        } ,

        _mouseupEvent: function(e){
            this._blocked = false;
            this._moving = false;
            if (!this._canMove) {
                return ;
            }
            this._canMove = false;
            this._endL = this._items.getCoordVal('left');
            this._endT = this._items.getCoordVal('top');
            var distance = Math.abs(this._endL - this._startL);

            if (distance <= this._minAmount) {
                return this.switch(this._index);
            }

            this._eTime = new Date().getTime();

            var direction = this._endX > this._startX ? 'prev' : 'next';
            var duration = this._eTime - this._sTime;

            if (duration <= this._duration || distance > this._amount) {
                // 时间内 or 时间外 + 距离超过 1/3
                if (direction === 'prev') {
                    this._index = Math.max(this._minIndex , Math.min(this._maxIndex , this._index - 1));
                } else {
                    this._index = Math.max(this._minIndex , Math.min(this._maxIndex , this._index + 1));
                }
            }

            this.switch(this._index);
        } ,

        _mousemoveEvent: function(e){
            if (!this._canMove) {
                return ;
            }

            this._endX = this._platform === 'mobile' ? e.touches[0].clientX : e.clientX;
            this._endY = this._platform === 'mobile' ? e.touches[0].clientY : e.clientY;

            var ox = this._endX - this._startX;
            var oy = this._endY - this._startY;
            var dir = G.gesture(ox , oy , true);

            if (!this._moving) {
                if (Math.abs(oy) >= 6 || dir === 'vertical') {
                    return this._blocked = true;
                }
                
                if (this._blocked) {
                    // 防止误操作
                    return ;
                }
            }

            G.stop(e);

            this._moving = true;

            this._endL  = Math.max(this._minL , Math.min(this._maxL , this._startL + ox));
            this._endT  = this._startT + oy;

            this._items.css({
                left: this._endL + 'px' ,
                // top: this._endT + 'px'
            });
        } ,

        _resizeEvent: function(){
            this._initDynamicHTML();
            this._initDynamicArgs();
            this._initDynamic();
        } ,

        _prevent: function(e){
            if (this._canMove) {
                // G.prevent(e);
            }
        } ,

        _defineEvent: function(){
            var win = G(window);

            var mousedonw   = this._platform === 'mobile' ? 'touchstart' : 'mousedown';
            var mouseup     = this._platform === 'mobile' ? 'touchend' : 'mouseup';
            var mousemove   = this._platform === 'mobile' ? 'touchmove' : 'mousemove';

            // pc 端事件
            this._switch_.on(mousedonw , this._mousedownEvent.bind(this) , true , false);
            win.on(mouseup , this._mouseupEvent.bind(this) , true , false);
            win.on(mousemove , this._mousemoveEvent.bind(this) , true , {
                capture: false ,
                passive: false
            });
            this._items_.on(mousemove , this._prevent.bind(this) , true , false);

            // 尺寸变化事件
            win.on('resize' , this._resizeEvent.bind(this) , true);
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
        window.Switch = Switch;
    }

    return Switch;
});