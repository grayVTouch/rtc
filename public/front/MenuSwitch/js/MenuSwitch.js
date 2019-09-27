/*
 * 导航菜单切换
 * author 陈学龙 2017-09-18 14:29:00
 */
(function(global , factory){
    if (typeof module !== 'undefined' && typeof module.exports === 'object') {
        module.exports = factory(global , true);
    } else {
        factory(global);
    }
})(typeof window === 'undefined' ? this : window , function(window , noGlobal){
    'use strict';

    function MenuSwitch(selector , opt){
        var thisRange = [undefined , null , window];

        if (G.contain(this , thisRange) || this.constructor !== MenuSwitch) {
            return new MenuSwitch(selector , opt);
        }

        this._default = {
            // 默认展示的界面
            id: '' ,
            // 菜单展示风格
            type: 1 ,
            // 项点击后回调
            click: null ,
            // 切换后回调
            switch: null
        };

        if (G.isUndefined(opt)) {
            opt = this._default;
        }

        this._typeRange = [1 , 2 , 3 , 4];

        this._con = G(selector);

        // 参数
        this._id        = G.isValid(opt.id)     ? opt.id : this._default.id;
        this._type      = G.contain(opt.type , this._typeRange)  ? opt.type : this._default.type;
        this._click     = G.isFunction(opt.click)  ? opt.click : this._default.click;
        this._switch    = G.isFunction(opt.switch)  ? opt.switch : this._default.switch;

        this._run();
    }

    MenuSwitch.prototype = {
        constructor: MenuSwitch ,

        _initStaticHTML: function(){

        } ,

        _initStaticArgs: function(){
            this._menuSwitch = G('.menu-switch'	, this._con.get(0));
            this._items      = G('.item'        , this._menuSwitch.get(0));
        } ,

        _initStatic: function(){
            // 设置容器元素样式风格
            this._menuSwitch.addClass('menu-switch-for-' + this._type);

            // 针对未设置 id 的元素设置 id!
            // 确保导航菜单项能够通过给定一个标识符被唯一找到！！
            var i   = 0;
            var cur = null;
            var id  = null;

            for (; i < this._items.length; ++i)
            {
                cur = this._items.jump(i , true);
                if (!G.isValid(cur.data('id'))) {
                    cur.data('id' , G.randomArr(100 , 'mixed' , true));
                }

                if (id === null) {
                    id = cur.data('id');
                }
            }

            this._id = G.isValid(this._id) ? this._id : id;
            this.switch(this._id);
        } ,

        _initDynamicHTML: function(){

        } ,

        _initDynamicArgs: function(){

        } ,

        _initDynamic: function(){

        } ,

        _initialize: function(){

        } ,


        // 切换到指定 id
        switch: function(id){
            var item = G(this.item(id));
            item.highlight('cur' , this._items.get());
            if (G.isFunction(this._switch)) {
                this._switch(id);
            }
        } ,

        // 获取指定 id 对应的项
        item: function(id){
            var i   = 0;
            var cur = null;

            for (; i < this._items.length; ++i)
            {
                cur = this._items.jump(i , true);
                if (cur.data('id') == id) {
                    return cur.get(0);
                }
            }

            throw new Error("未找到当前项");
        } ,

        // 获取当前项
        current: function(){
            var i   = 0;
            var cur = null;

            for (; i < this._items.length; ++i)
            {
                cur = this._items.jump(i , true);
                if (cur.hasClass('cur')) {
                    return cur.get(0);
                }
            }
            throw new Error("未找到当前项");
        } ,

        // 获取当前项的 id
        id: function(){
            var item = G(this.current());
            return item.data('id');
        } ,

        // 导航标签点击事件
        _clickEvent: function(e){
            var tar = G(e.currentTarget);
            var id  = tar.data('id');
            this.switch(id);
            if (G.isFunction(this._click)) {
                this._click(id);
            }
        } ,


        _defineEvent: function(){
            // 标签切换事件
            this._items.on('click' , this._clickEvent.bind(this) , true , false);
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
        window.MenuSwitch = MenuSwitch;
    }

    return MenuSwitch;
});