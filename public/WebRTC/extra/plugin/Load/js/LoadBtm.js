/**
 * *********************
 * 下拉刷新 by 陈学龙
 * *********************
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

    function LoadBtm(selector , option){
        var thisRange = [null , undefined , window];

        if (G.contain(this , thisRange) || this.constructor !== LoadBtm) {
            return new LoadBtm(selector , option);
        }

        this._default = {
            time: 300 ,
            // 允许的值：show | hide
            status: 'show' ,
            text: '正在加载'
        };

        if (G.isUndefined(option)) {
            option = this._default;
        }

        this._statusRange = ['show' , 'hide'];

        this._con = G(selector);
        this._status = G.contain(option.status , this._statusRange) ? option.status : this._default.status;
        this._text = G.isValid(option.text) ? option.text : this._default.text;

        // 相关参数初始化
        this._run();
    }

    LoadBtm.prototype = {
        constructor: LoadBtm ,

        _before: function(){
            this._LoadBtm = this._con.children({
                class: 'Load-Btm'
            });

            this._LoadBtm.removeClass('hide');
        } ,

        _initStaticHTML: function(){

        } ,
        _initStaticArgs: function(){
            this._text_ = G('.text' , this._LoadBtm.get(0));
        } ,

        _initStatic: function(){

        } ,

        _initDynamicHTML: function(){} ,

        _initDynamicArgs: function(){} ,

        _initDynamic: function(){} ,

        _after: function(){
            this._LoadBtm.addClass('hide');
        } ,

        _initialize: function(){
            this.text(this._text);

            if (this._status === 'show') {
                this.show();
            }
        } ,

        // 显示
        show: function(text){
            this.text(text);
            this._LoadBtm.removeClass('hide');
        } ,

        hide: function(text){
            this.text(text);
            this._LoadBtm.addClass('hide');
        } ,

        // 设置显示文本
        text: function(text){
            if (!G.isValid(text)) {
                return ;
            }
            this._text_.text(text);
        } ,

        _defineEvent: function(){} ,

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
        } ,

    };

    if (!noGlobal) {
        window.LoadBtm = LoadBtm;
    }

    return LoadBtm;
});