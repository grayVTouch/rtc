(function(global , factory){
    "use strict";

    if (typeof module !== 'undefined' && typeof module.exports === 'object') {
        module.exports = factory(global , true);
    } else {
        factory(global);
    }
})(typeof window === 'undefined' ? this : window , function(window , noGlobal){
    "use strict";

    function FunctionNav(selector , option){
        var thisRange = [null , undefined , window];

        if (G.contain(this , thisRange) || this.constructor !== FunctionNav) {
            return new FunctionNav(con , option);
        }

        this._default = {
            id: 'image' ,
            time: 300 ,
            switch: null ,
            click: null
        };

        if (G.isUndefined(option)) {
            option = this._default;
        }

        this._con = G(selector);

        this.id     = G.isValid(option.id) ? option.id : this._default.id;
        this._time  = G.isNumber(option.time) ? option.time : this._default.time;
        this._switch = G.isFunction(option.switch) ? option.switch : this._default.switch;
        this._click = G.isFunction(option.click) ? option.click : this._default.click;

        // 相关参数初始化
        this._run();
    }

    FunctionNav.prototype = {
        constructor: FunctionNav ,

        _initStaticHTML: function(){} ,

        _initStaticArgs: function(){
            this._functionNav   = G('.function-nav' , this._con.get(0));
            this._functions     = G('.functions' , this._functionNav.get(0));
            this._functions_    = G('.function' , this._functions.get(0));
            this._slider        = G('.slider' , this._functionNav.get(0));

            this.id = G.isValid(this.id) ? this.id : this._functions_.first(true).data('id');
        } ,

        _initStatic: function(){

        } ,

        _initDynamicHTML: function(){

        } ,

        _initDynamicArgs: function(){
            // 容器额外的宽度
            this._functionsExtraW = this._functions_.length;
            // 设置容器宽度
            this._functionsW = this._functionsExtraW;
            var i   = 0;
            var cur = null;
            var curW = 0;
            for (i = 0; i < this._functions_.length; ++i)
            {
                cur  = this._functions_.jump(i , true);
                curW = cur.width('border-box');
                this._functionsW += curW;
            }

            // 追加显示数量
            this._count = 1;
        } ,

        _initDynamic: function(){
            this._functions.css({
                width: this._functionsW + 'px'
            });
            this.switch(this.id);
        } ,

        _initialize: function(){

        } ,

        // 获取 id 当前 id 对应项
        find: function(id) {
            var i   = 0;
            var cur = null;
            for (; i < this._functions_.length; ++i)
            {
                cur = this._functions_.jump(i , true);
                if (cur.data('id') == id) {
                    return cur.get(0);
                }
            }
            throw new Error('未找到当前提供id: ' + id + ' 对应项');
        } ,

        // 获取滑块的 left 值
        sliderLeft: function(id){
            var _function = G(this.find(id));
            var prevSbilings = _function.prevSiblings();
            var left = 0;
            prevSbilings.each(function(dom){
                dom = G(dom);
                left += dom.width('border-box');
            });
            return left;
        } ,

        switch (id) {
            var self = this;
            // 当前选中项
            var _function = G(this.find(id));
            // 设置滑块宽度
            var sliderW = _function.width('border-box');
            var sliderL = this.sliderLeft(id);

            // 滑块移动
            this._slider.animate({
                width: sliderW + 'px' ,
                left: sliderL + 'px'
            } , function(){
                _function.highlight('cur' , self._functions_.get());

                if (G.isFunction(self._switch)) {
                    self._switch(id);
                }
            } , this._time);

            // 容器移动
            var prevSibling = _function.prevSibling();
            prevSibling = prevSibling.isDom() ? prevSibling : _function;

            prevSibling.scrollIntoView(this._functionNav.get(0) , this._time);
        } ,

        _clickEvent: function(e) {
            var tar = G(e.currentTarget);
            var id = tar.data('id');

            this.switch(id);

            if (G.isFunction(this._click)) {
                this._click(id);
            }
        } ,

        _resizeEvent: function(){
            this._initDynamicHTML();
            this._initDynamicArgs();
            this._initDynamic();
        } ,

        _defineEvent: function(){
            var win = G(window);
            this._functions_.on('click' , this._clickEvent.bind(this) , true , false);

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
        window.FunctionNav = FunctionNav;
    }

    return FunctionNav;
});