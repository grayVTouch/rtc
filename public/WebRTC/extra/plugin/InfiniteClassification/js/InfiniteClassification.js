/**
 * InfiniteClassification 无限极分类
 * author 陈学龙 2017-09-10 14:00:00
 *
 * 具备子级的项
 * 不具备子级的项
 *
 * 具备子级的项，点击（父级项）
 * 不具备子级的项，点击（子级项）
 */
(function(global , factory) {
    "use strict";

    if (typeof module != 'undefined' && typeof module.exports == 'object') {
        module.exports =  factory(global , true);
    } else {
        factory(window);
    }
})(typeof window !== 'undefined' ? window : this , function(global , noGlobal){
	"use strict";

    function InfiniteClassification(selector , opt){
        var thisRange = [undefined , null , window];

        if (G.contain(this , thisRange) || !G.contain(this , thisRange) && this.constructor !== InfiniteClassification) {
            return new InfiniteClassification(selector , opt);
        }

        // 默认设置
        this._default = {
            // 菜单展开动画过渡时间
            time: 300 ,
            // 次要的图标类型：number || switch
            icon: 'switch' ,
            // 标识符，展开的项；1. 在元素里面设置 data-focus='y' +
            id: [] ,
            // 初始状态，spread || shrink
            status: 'shrink' ,
            // 层级视觉显示效果
            amount: 12 ,
            // 同层级是否互斥
            exclution: false ,
            // 展开回调
            spread: null ,
            // 收缩回调
            shrink: null ,
            // 项点击回调函数
            click: null ,
            // 是否菜单也可被选中
            menuFocus: true ,
            // 点击项后是否选中
            focus: true ,
            // 是否选中顶级菜单
            topFocus: false ,
            // 父级项点击后回调 ,
            parent: null ,
            // 子级项点击后回调
            child: null
        };

        if (G.isUndefined(opt)) {
            opt = this._default;
        }

        // 元素容器
        this._con = G(selector);

        this._iconRange  	= ['number' , 'switch'];
        this._statusRange    		= ['spread' , 'shrink'];

        this._time 	 	= G.isNumber(opt.time) 	? opt.time : this._default.time;
        this._id 		= G.isArray(opt.id) 		? opt.id : this._default.id;
        this._status    = G.contain(opt.status , this._statusRange) ? opt.status : this._default.status;
        this._amount    = G.isNumber(opt.amount) 	? opt.amount : this._default.amount;
        this._icon      = G.contain(opt.icon , this._iconRange) 	? opt.icon : this._default.icon;
        this._exclution  	= G.isBoolean(opt.exclution) ? opt.exclution : this._default.exclution;
        this._spread   	= G.isFunction(opt.spread) 	? opt.spread : this._default.spread;
        this._shrink   	= G.isFunction(opt.shrink) 	? opt.shrink : this._default.shrink;
        this._click   	= G.isFunction(opt.click) 	? opt.click : this._default.click;
        this._menuFocus  	= G.isBoolean(opt.menuFocus) ? opt.menuFocus : this._default.menuFocus;
        this._focus  	= G.isBoolean(opt.focus) ? opt.focus : this._default.focus;
        this._topFocus  	= G.isBoolean(opt.topFocus) ? opt.topFocus : this._default.topFocus;
        this._parent   	= G.isFunction(opt.parent) 	? opt.parent : this._default.parent;
        this._child   	= G.isFunction(opt.child) 	? opt.child : this._default.child;

        this._run();
    }

    InfiniteClassification.prototype = {
        constructor: InfiniteClassification ,
        version: '1.0' ,
        author: '陈学龙' ,

        _initStaticHTML: function(){

        } ,

        _initStaticArgs: function(){
            this._infiniteClassification = G('.infinite-classification' , this._con.get(0));
            this._items = G('.item' , this._infiniteClassification.get(0));
            this._functions = G('.function' , this._infiniteClassification.get(0));
        } ,

        _initStatic: function(){
            var self = this;
            var _count = 0;
            var initialize = function(container , floor){
                var list = G('.list' , container).first();
                var items = list.children();
                var count = 0;

                _count++;

                items.each(function(dom){
                    dom = G(dom);

                    var id          = dom.data('id');
                    var _function   = G('.function' , dom.get(0));
                    var child       = G('.child' , dom.get(0)).first();
                    var icon        = G('.icon' , _function.get(0)).first();
                    var explain     = G('.explain' , _function.get(0)).first();
                    var icoForExplain = G('.ico' , explain.get(0));
                    var flag        = G('.flag' , _function.get(0)).first();
                    var _amount     = floor * self._amount;
                    var empty       = G('.list > .item' , child.get(0)).length === 0;

                    dom.data('isEmpty' , empty ? 'y' : 'n');

                    if (!empty) {
                        dom.data('status' , 'shrink');
                    }

                    if (floor > 1) {
                        var textForIcon = G('.text' , icon.get(0));
                        var textContent = G('.text' , explain.get(0)).text()[0];
                        textForIcon.text(textContent);
                        icon.addClass('hide');
                        explain.css({
                            paddingLeft: _amount + 'px'
                        });
                    } else {
                        icoForExplain.addClass('hide');
                    }

                    count++;
                    var k = 'infinite_classification_count_' + floor + '_' + count + '_' + _count;
                    var v = 0;
                    dom.data('countKey' , k);
                    dom.data('floor' , floor);

                    if (!G.s.exists(k) || G.s.get(k) == 0) {
                        G.s.set(k , v);
                    }

                    // 设置图标
                    self.flag(id);
                    flag.removeClass('hide');

                    // 注册事件
                    self._on(dom.get(0));
                    // 初始化子级
                    initialize(child.get(0) , floor + 1);
                });
            };
            initialize(this._infiniteClassification.get(0) , 1);

            if (this._status === 'spread') {
                if (this._exclution) {
                    console.warn('展开所有 和 同层级互斥是互斥行为！将产生不可预测的结果');
                }
                this.spreadAll();
            }

            if (this._status === 'shrink') {
                this.shrinkAll();
            }

            if (this._id.length > 0) {
                this.spreadSpecified(this._id);
            }
        } ,

        _initDynamicHTML: function(){

        } ,

        _initDynamicArgs: function(){

        } ,

        _initDynamic: function(){

        } ,

		_initialize: function(){

		} ,

        // 获取指定项
        item: function(id){
            var i = 0;
            var cur = null;
            var res = [];
            for (; i < this._items.length; ++i)
            {
                cur = this._items.jump(i , true);
                if (cur.data('id') == id) {
                    res.push(cur.get(0));
                }
            }

            if (res.length === 0) {
                throw new Error('未找到当前提供 id: ' + id + '对应项');
            }

            if (res.length > 1) {
                console.warn('存在 id 重复项！重复id: ' + id + '，这将产生不可预料的行为！');
            }

            return res.shift();
        } ,

        all: function(type){
            var self = this;

            if (!G.contain(type , this._statusRange)) {
                throw new Error('不支持的操作类型：' + type);
            }

            var operation = function(container){
                var list  = G('.list' , container).first();
                var items = list.children();

                // 展开子级
                items.each(function(dom){
                    dom = G(dom);

                    var id = dom.data('id');
                    var child = G('.child' , dom.get(0));

                    self[type](id , function(){
                        operation(child.get(0));
                    });
                });
            };
            operation(this._infiniteClassification.get(0));
        } ,

        // 展开所有项
        spreadAll: function(){
            this.all('spread');
        } ,

        // 收缩所有项
        shrinkAll: function(){
            this.all('shrink');
        } ,

        // 展开指定项
        spreadSpecified: function(id){
            var self = this;
            id.forEach(function(_id){
                var item = G(self.item(_id));
                var parents = item.parents({
                    tagName: 'div' ,
                    class: 'item'
                } , self._infiniteClassification.get(0));
                var res = parents.unshift(item.get(0)).get().reverse();
                // 展开
                var spread = function(){
                    var item = G(res.shift());
                    var id = item.data('id');
                    var isEmpty = item.data('isEmpty');

                    self.spread(id , function(){
                        if (res.length > 0) {
                            spread();
                        }
                    });
                    if (res.length == 0) {
                        if (self._topFocus) {
                            self.topFocus(id);
                        }
                        if (self._focus) {
                            self.focus(id);

                            if (G.isFunction(self._click)) {
                                self._click.call(this , id);
                            }
                        }
                        if (isEmpty == 1 && G.isFunction(self._child)) {
                            self._child.call(self , id);
                        }
                        if (isEmpty == 'n' && G.isFunction(self._parent)) {
                            self._parent.call(self , id);
                        }
                    }
                };
                spread();
            });
        } ,

        // 选中子项的顶级项
        topFocus: function(id){
            var item = G(this.item(id));
            var parents = item.parents({
                tagName: 'div' ,
                class: 'item'
            } , this._infiniteClassification.get(0));

            // 说明当前项就是顶级项
            if (parents.length === 0) {
                return ;
            }

            parents = parents.get().reverse();
            var list    = G('.list' , this._infiniteClassification.get(0)).first();
            var items   = list.children();
            var functions = [];
            var _function = G('.function' , parents[0]).first();

            items.each(function(dom){
                dom = G(dom);
                var _function = G('.function' , dom.get(0)).first();
                functions.push(_function.get(0));
            });

            _function.highlight('top-cur' , functions);
        } ,

        // 展开指定项
        spread: function(id , callback){
            var item    = G(this.item(id));
            var countKey = item.data('countKey');
            var isEmpty = item.data('isEmpty');
            var status = item.data('status');

            // 记录点击次数
            this.inc(countKey);
            // 切换图标
            this.flag(id);

            if (isEmpty === 1) {
                return ;
            }

            if (status === 'spread') {
                if (G.isFunction(callback)) {
                    callback();
                }
                return ;
            }

            status = 'spread';

            var child   = G('.child' , item.get(0)).first();
            var list    = G('.list' , child.get(0)).first();
            var endH = list.height('border-box');

            item.data('status' , status);

            // 设置类名
            this.setClass(id , status);

            var self = this;

            // 是否开启了同层级互斥
            if (this._exclution) {
                item.siblings().each(function(dom){
                    dom = G(dom);
                    var id = dom.data('id');
                    self.shrink(id);
                });
            }

            child.animate({
                height: endH + 'px'
            } , function(){
                child.css({
                    height: 'auto'
                });

                if (G.isFunction(callback)) {
                    callback();
                }

                if (G.isFunction(self._spread)) {
                    self._spread.call(this , id);
                }
            } , this._time);
        } ,

        // 收缩指定项
        shrink: function(id , callback){
            var item = G(this.item(id));
            var countKey = item.data('countKey');
            var isEmpty = item.data('isEmpty');
            var status = item.data('status');

            // 记录点击次数
            this.inc(countKey);
            // 切换图标
            this.flag(id);

            if (isEmpty === 1) {
                return ;
            }

            if (status === 'shrink') {
                if (G.isFunction(callback)) {
                    callback();
                }
                return ;
            }

            status  = 'shrink';
            var self    = this;
            var child   = G('.child' , item.get(0)).first();

            item.data('status' , status);
            // 设置类名
            this.setClass(id , status);

            child.animate({
                height: '0px'
            } , function(){
                if (G.isFunction(callback)) {
                    callback();
                }

                if (G.isFunction(self._shrink)) {
                    self._shrink.call(this , id);
                }
            } , this._time);
        } ,

        _clickEvent: function(e){
            var tar     = G(e.currentTarget);
            var item    = tar.parent();
            var id      = item.data('id');
            var status  = item.data('status');
            var isEmpty = item.data('isEmpty');

            if (status === 'spread') {
                this.shrink(id);
            } else {
                this.spread(id);
            }
            if (this._focus) {
                this.focus(id);
            }
            if (this._topFocus) {
                this.topFocus(id);
            }
            if (isEmpty == 1 && G.isFunction(this._child)) {
                this._child.call(this , id);
            }
            if (isEmpty == 'n' && G.isFunction(this._parent)) {
                this._parent.call(this , id);
            }
            if (G.isFunction(this._click)) {
                this._click.call(this , id);
            }
        } ,

        setClass: function(id , _class){
            var item = G(this.item(id));
            var _function = G('.function' , item.get(0)).first();

            _function.removeClass(['shrink' , 'spread']);
            _function.addClass(_class);
        } ,

        // 选中项
        focus: function(id){
            var item = G(this.item(id));
            var isEmpty = item.data('isEmpty');

            if (!this._menuFocus && isEmpty === 'n') {
                return ;
            }

            var _function = G('.function' , item.get(0)).first();
            _function.highlight('cur' , this._functions.get());
        } ,

        // 增加项的点击次数
        inc: function(k){
            var count = G.s.get(k);
            count = G.isNull(count) ? 0 : ++count;
            G.s.set(k , count);
        } ,

        flag: function(id){
            var item    = G(this.item(id));
            var isEmpty = item.data('isEmpty');
            var flag    = G('.function .flag' , item.get(0)).first();
            var children = flag.children();
            var _new    = G('.new' , flag.get(0));
            var _number = G('.number' , flag.get(0));
            var _switch = G('.switch' , flag.get(0));
            var countKey = item.data('countKey');
            if (!G.s.exists(countKey) || G.s.get(countKey) == 0) {
                _new.highlight('hide' , children.get() , true);
            } else {
                if (this._icon === 'number') {
                    _number.highlight('hide' , children.get() , true);
                } else {
                    if (isEmpty !== 1) {
                        _switch.highlight('hide' , children.get() , true);
                    } else {
                        // console.log('为空');
                        children.addClass('hide');
                    }
                }
            }
        } ,

        // 获取选中项的相关数据
        data: function(id){
            var item = G(this.item(id));
            var _function = G('.function' , item.get(0));
            var text    = G('.explain .in .text' , _function.get(0)).text();
            var parents = item.parents({
                tagName: 'div' ,
                class: 'item'
            });

            var data = {
                // 选中 id
                id: id ,
                // 名称
                name: text ,
                // 上级名称
                parent: '' ,
                // 顶级名称
                top: ''
            };

            if (parents.length !== 0) {
                var parent  = parents.first(true);
                var _top    = parents.last(true);
                var _function_  = G('.function' , parent.get(0)).first();
                var _text       = G('.explain .in .text' , _function_.get(0)).text();

                data.parent = _text;

                _function_  = G('.function' , _top.get(0)).first();
                _text       = G('.explain .in .text' , _function_.get(0)).text();

                data.top = _text;
            }

            return data;
        } ,

        attr: function(id , attr){
            var item = G(this.item(id));
            return item.data(attr);
        } ,

        // 是否不存在子级
        isEmpty: function(id){
            var item = G(this.item(id));
            return item.data('isEmpty') === 1;
        } ,

        // 展示图标切换
        icon: function(type) {
            var typeRange = ['text' , 'icon' , 'none'];
            if (!G.contain(type , typeRange)) {
                throw new Error('参数 1 不支持的类型，受支持的类型有：' + typeRange.join(' , '));
            }
            var i = 0;
            var cur = null;
            var icon = null;
            var image = null;
            var text = null;
            for (; i < this._items.length; ++i)
            {
                cur     = this._items.jump(i , true);
                if (cur.data('floor') == 1) {
                    continue ;
                }
                icon    = G('.function > .icon' , cur.get(0)).first();
                image   = G('.image' , icon.get(0));
                text    = G('.text' , icon.get(0));
                if (type != 'none') {
                    icon.removeClass('hide');
                    if (type == 'text') {
                        text.highlight('hide' , icon.children().get() , true);
                        continue ;
                    }
                    text.highlight('hide' , icon.children().get() , true);
                    continue ;
                }
                icon.addClass('hide');
            }
        } ,

        // 注册项相关事件
        _on: function(item){
            item = G(item);

            var _function = G('.function' , item.get(0)).first();

            _function.on('click' , this._clickEvent.bind(this) , true , false);
        } ,

        _defineEvent: function(){

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
    	window.InfiniteClassification = InfiniteClassification;
	}
	
    return InfiniteClassification;
});