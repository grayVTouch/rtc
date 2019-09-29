/*
 ***********************************
 * Author 陈学龙 2016-11-16 22:20:00
 * 修改时间

   第一次修改时间：2017/02/24 15:13:00
 ***********************************
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

	function Boundary(selector , option){
        var thisRange = [null , undefined , window];

        if (G.contain(this , thisRange) || this.constructor !== Boundary) {
            return new Boundary(con , option);
        }

        this._default = {
        	// 初次检查
			once: true ,
        	// 到顶部回调
        	top: null ,
			// 到底部回调
			bottom: null ,
			// 滚动时回调
			scroll: null
        };

        if (G.isUndefined(option)) {
            option = this._default;
        }

        this._con = G(selector);
        this._once  = G.isBoolean(option.once) ? option.once : this._default.once;
        this._top 	= G.isFunction(option.top) ? option.top : this._default.top;
        this._bottom = G.isFunction(option.bottom) ? option.bottom : this._default.bottom;
        this._scroll = G.isFunction(option.scroll) ? option.scroll : this._default.scroll;

        // 相关参数初始化
        this._run();
	}

	Boundary.prototype = {
		constructor: Boundary ,

        _initStaticHTML: function(){

		} ,
        _initStaticArgs: function(){
            this._prevTime = 0;
		} ,
        _initStatic: function(){} ,
        _initDynamicHTML: function(){} ,
        _initDynamicArgs: function(){} ,
        _initDynamic: function(){} ,

		_initialize: function(){
			if (this._once) {
				this._check();
			}
		} ,

		// 检查
		_check: function(){
            if (this._con.isTop() && G.isFunction(this._top)) {
                this._top();
            }

            if (this._con.isBottom() && G.isFunction(this._bottom)) {
                this._bottom();
            }
        } ,

		// 滚动事件
		_scrollEvent: function(e){
			this._check();

			if (G.isFunction(this._scroll)) {
				this._scroll();
			}
		} ,

        _defineEvent: function(){
			this._con.on('scroll' , this._scrollEvent.bind(this) , true , false);
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
        window.Boundary = Boundary;
    }

    return Boundary;


});