/**
 * authro 陈学龙 2017-09-12 15:18:00
 * 多标签页
 */

/**
 * 多标签页
 * 功能点介绍：
 * 1. 宽度缩放，规则：
 *      1.1 标签数量 * 单标签最大长度小于容器宽度，设置为单标签最大宽度
 *      1.2 单标签数量 * 单标签最大长度大于容器宽度，单标签长度 = 容器宽度 / 标签数量
 * 2. 标签拖拽规则
 *      2.1 鼠标在标签内释放的：
 *          2.1.1 单纯的移动标签，判定规则
 *              如果标签页进入另一个标签页内部长度超过 2/5，切换位置
 *      2.2 鼠标在标签外释放的
 *          2.2.1 关闭当前拖拽的标签
 *          2.2.2 获取他的链接，打开新的页面
 */
(function(global , factory){
    'use strict';

    if (typeof module !== 'undefined' && typeof module.exports === 'object') {
        module.exports = factory(global , true);
    } else {
        factory(global);
    }

})(typeof window === 'undefined' ? this : window , function(window , noGlobal){
    "use strict";

    function MultipleTab(selector , opt){
        var thisRange = [undefined , null , window];

        if (G.contain(this , thisRange) || !G.contain(this , thisRange) && this.constructor !== MultipleTab) {
            return new MultipleTab(selector , opt);
        }

        // 默认设置
        this._default = {
            // 新建标签后回调
            created: null ,
            // 标签页删除后回调函数
            deleted: null ,
            // 标签点击后回调
            click: null ,
            // 图标
            ico: '../image/default/default.png' ,
            // 内容
            title: '新标签页' ,
            // 动画时间
            time: 300
        };

        if (G.type(opt) === 'Undefined') {
            opt = this._default;
        }

        // 元素容器
        this._con = G(selector);

        this._created = G.type(opt['created']) !== 'Function' ? this._default['created']   : opt['created'];
        this._deleted = G.type(opt['deleted']) !== 'Function' ? this._default['deleted']   : opt['deleted'];
        this._click   = G.type(opt['click'])   !== 'Function' ? this._default['click']     : opt['click'];
        this._ico     = G.type(opt['ico'])     !== 'String'   ? this._default['ico']       : opt['ico'];
        this._title   = G.type(opt['title'])     !== 'String'   ? this._default['title']       : opt['title'];
        this._time   = G.type(opt['time'])     !== 'Number'   ? this._default['time']       : opt['time'];

        this._run();
    }

    MultipleTab.prototype = {
        authro: '陈学龙' ,
        version: '1.0' ,
        c_time: '2017-09-12 15:18:00' ,
        constructor: MultipleTab ,

        _initStaticHTML: function(){

        } ,

        _initStaticArgs: function(){
            // 元素相关
            this._multipleTab = G('.multiple-tab' , this._con.get(0));
            this._tabs = G('.tabs' , this._multipleTab.get(0));

            // 参数相关
            this._tabMaxW = 250;
            this._tabMinW = 0;

            // 设置进入当前标签进入其他标签的长度占标签长度的比率
            this._judgeRatio = 0.4;

            // 当前显示的标签页类名
            this._focus = 'cur';
        } ,

        _initStatic: function(){
            
        } ,

        _initDynamicHTML: function(){

        } ,

        _initDynamicArgs: function(){
            // 容器的最大宽度
            this._multipleTabW  = this._multipleTab.width('content-box');
            this._tabsW         = this._multipleTabW;
            this._tabs_         = G('.tab' , this._tabs.get(0));

            // 标签数量
            this._count   = this._tabs_.length;

            // 当前标签数量 * singleTabMaxW 合计占据的长度
            this._tabsMaxW = this._count * this._tabMaxW;

            // 计算出单标签的长度
            this._tabW = Math.floor(this._tabsMaxW > this._tabsW ? this._tabsW / this._count : this._tabMaxW);
        } ,

        _initDynamic: function(){
            var self = this;

            // 初始化标签
            this._tabs_.each(function(dom){
                dom = G(dom);

                var ico     = G('.ico'  , dom.get(0));
                var text    = G('.text' , dom.get(0));
                var close   = G('.close', dom.get(0));
                var icoW    = ico.width('border-box');
                var closeW  = close.width('border-box');
                var textML  = icoW + 10;
                var textMR  = closeW + 15;
                var textW   = self._tabW - textML - textMR;

                dom.css({
                    width: self._tabW + 'px'
                });

                text.css({
                    width: textW + 'px'
                });

                // 注册事件
                self._loginEvent(dom.get(0));
            });
        } ,

        _initialize: function(){

        } ,

        // 注册标签事件
        _loginEvent: function(tab){
            tab = G(tab);

            var close = G('.close' , tab.get(0));

            // 标签会有多个事件
            close.on('click'    , this._closeEvent.bind(this) , true , false);
            tab.on('dblclick'   , this._tabDoubleClickEvent.bind(this) , true , false);
            tab.on('click'      , this._tabClickEvent.bind(this) , true , false);
            tab.on('mousedown'  , this._tabMousedownEvent.bind(this) , true , false);
        } ,

        /**
         * 判断片段内是否有标签
         */
        _hasTab: function(){
            var index   = this.getIndex();
            var tabSet =  G('.tab' , this._tabs.get(0));
            var tabsTW  = tabSet.length * this._tabW;

            if (tabsTW <= (index - 1) * this._tabsW) {
                return false;
            }

            return true;
        } ,

        // 当前项是否是选中项
        _isFocus: function(tab){
            tab = G(tab);

            if (tab.hasClass('cur')) {
                return true;
            }

            return false;
        } ,

        // 删除指定标签
        deleteTab: function(tab){
            tab = G(tab);

            var self = this;
            var endW = '0px';
            var endOpacity = 0;

            tab.animate({
                width: endW ,
                opacity: endOpacity
            } , function(){
                // 切换标签
                if (self._isFocus(tab.get(0))) {
                    var nextTab = tab.nextSibling();

                    if (nextTab.isDom()) {
                        self.focus(nextTab.get(0));
                    } else {
                        var prevTab = tab.prevSibling();

                        if (prevTab.isDom()) {
                            self.focus(prevTab.get(0));
                        }
                    }
                }

                var id = tab.data('id');

                tab.parent().remove(tab.get(0));

                self._initDynamicArgs();
                self._initDynamic();

                if (G.type(self._deleted) === 'Function') {
                    self._deleted.call(self , id);
                }
            } , this._time);
        } ,

        // 关闭事件
        _closeEvent: function(e){
            G.stop(e);

            // close 元素
            var tar = G(e.currentTarget);
            var tab = tar.parents({
                tagName: 'div' ,
                class: 'tab'
            } , this._tabs.get(0)).not({
                class: 'tabs'
            });

            this.deleteTab(tab.get(0));
        } ,

        _tabDoubleClickEvent: function(e){
            G.stop(e);
            var tar = G(e.currentTarget);
            this.deleteTab(tar.get(0));
        } ,

        // 高亮显示给定项
        highlightTab: function(tab){
            tab = G(tab);
            tab.highlight(this._focus , this._tabs_.get());
        } ,

        /**
         * 生成标签唯一ID
         */
        genID: function(){
            return G.randomArr(100 , 'mixed').join('');
        } ,

        /*
         * 创建标签
         * opt = {
         *      text: '描述文本' ,
         *      ico: '描述图片' ,
         *      id: '标识符'
         * }
         */
        create: function(opt){
            var self       = this;
            var _default = {
                text: this._title ,
                ico: this._ico ,
                attr: {}
            };
            if (G.type(opt) === 'Undefined') {
                opt = _default;
            }
            opt['text']   = G.type(opt['text']) !== 'String' ? _default['text'] : opt['text'];
            opt['ico']    = G.type(opt['ico']) !== 'String' ? _default['ico'] : opt['ico'];
            opt['attr']   = !G.isObj(opt['attr']) ? _default['attr'] : opt['attr'];
            var id  = this.genID();
            var div = document.createElement('div');
                div = G(div);
            div.addClass('tab');
            div.data('id' , id);
            var k = null;
            var p = 'data-';
            var _k = null;
            // 设置数据集属性
            for (k in opt['attr'])
            {
                _k = k.replace(p , '');
                _k = p + k;
                div.attr(_k , opt['attr'][k]);
            }
            var html = [];
            html.push();
            html.push('         <div class="ico"><img src="' + opt.ico + '" class="image"></div>');
            html.push('         <div class="text">' + opt.text + '</div>');
            html.push('         <div class="close">');
            html.push('             <div class="positive"></div>');
            html.push('             <div class="negative"></div>');
            html.push('         </div>');
            div.html(html.join(''));
            // 添加标签
            this._tabs.append(div.get(0));
            // 参数重新初始化
            this._initDynamicHTML();
            this._initDynamicArgs();
            this._initDynamic();
            // 初始化样式
            div.css({
                width: 0 ,
                opacity: 0
            });
            // 高亮显示当前项
            this.highlightTab(div.get(0));
            var endW = this._tabW + 'px';
            var endOpacity = 1;

            // 动画展示
            div.animate({
                width: endW ,
                opacity: endOpacity
            } , function(){
                self._loginEvent(div.get(0));

                if (G.isFunction(self._created)) {
                    self._created.call(self , id);
                }
            } , this._time);
        } ,

        /**
         * 项点击事件
         */
        focus: function(tab){
            tab = G(tab);

            this.highlightTab(tab.get(0));

            var id = tab.data('id');

            if (G.type(this._click) === 'Function') {
                this._click.call(this , id);
            }
        } ,

        /**
         * 项点击事件（切换当前标签）
         */
        _tabClickEvent: function(e){
            var tar = G(e.currentTarget);

            this.focus(tar.get(0));
        } ,

        // 获取 tab 可移动范围
        getTabMoveRange: function(tab){
            tab = G(tab);

            var clientW = document.documentElement.clientWidth;
            var extraL  = this._tabs.getDocOffsetVal('left');
            var extraR  = clientW - extraL - this._tabsW;
            var w       = tab.width('border-box');
            var l       = tab.getDocOffsetVal('left');
            var minL    = -(l - extraL);
            var maxL    = clientW - extraL - extraR - w;

            return {
                minL: minL ,
                maxL: maxL
            }
        } ,

        // 鼠标点击后触发事件（拖动标签时）
        _tabMousedownEvent: function(e){
            var tab = G(e.currentTarget);

            this._canDrag = true;

            this._tabSX = e.clientX;
            this._tabSY = e.clientY;
            this._tabSL = tab.getCoordVal('left');
            this._tabST = tab.getCoordVal('top');
            this._range   = this.getTabMoveRange(tab.get(0));
            this._moveDOM = tab;

            // 选中
            this.focus(tab.get(0));

            // 设置最高层级
            tab.css({
                zIndex: '100000000'
            });
        } ,

        // 鼠标移动后触发事件（拖动标签时）
        _tabMouseMoveEvent: function(e){
            if (this._canDrag) {
                var tab = this._moveDOM;

                // 拖动标签
                this._tabEX = e.clientX;
                this._tabEY = e.clientY;

                var ox = this._tabEX - this._tabSX;
                var oy = this._tabEY - this._tabSY;

                var el = this._tabSL + ox;
                var et = this._tabST + oy;

                el = Math.max(this._range['minL'] , Math.min(this._range['maxL'] , el));

                /**
                 * 只进行左右移动，不允许上下移动
                 */
                tab.css({
                    left: el + 'px' ,
                    // top: et + 'px'
                });

                // 监听
                this._prevListen(tab.get(0));
                this._nextListen(tab.get(0));
            }
        } ,

        // 位置还原
        _originPosition: function(tab){
            tab = G(tab);
            var endLeft = '0px';
            var endTop  = '0px';
            tab.animate({
                left: endLeft ,
                top: endTop
            } , null , this._time);
        } ,

        // 位置判定
        _judgePosition: function(tab , index){
            tab = G(tab);
            index = Math.max(0 , index - 1);

            var curLeft = tab.getCoordVal('left');
            // var tabW = tab.width('border-box');
            var prev = -(index * this._tabW + this._tabW / 3);
            var next = -prev;

            if (curLeft <= prev) {
                return 'prev';
            }

            if (curLeft >= next) {
                return 'next';
            }

            return 'origin';
        } ,

        // 给定集合，设定 left = 0，top = 0
        _originRelative: function(doms){
            doms.forEach(function(dom){
                dom = G(dom);
                dom.css({
                    left: 0 ,
                    top: 0
                });
            });
        } ,

        // 切换到上一个
        _prevPosition: function(tab){
            tab = G(tab);

            var prevSiblings = tab.prevSiblings({
                tagName: 'div' ,
                class: 'tab'
            });

            if (prevSiblings.length === 0) {
                this._originPosition(tab.get(0));
                return ;
            }

            var minLeft = -(prevSiblings.length * this._tabW);
            var curLeft = Math.max(minLeft , tab.getCoordVal('left'));
            var count   = Math.ceil(Math.abs(curLeft / this._tabW));
            var range   = -((count - 1) * this._tabW + this._tabW * 1 / 3);
            var index   = curLeft < range ? count - 1 : count - 2;

            if (index < 0) {
                this._originPosition(tab.get(0));
                return ;
            }

            var prev = prevSiblings.jump(index , true);

            // 移动 DOM 元素
            G.insertBefore(tab.get(0) , prev.get(0));

            tab.css({
                left: 0 ,
                top: 0
            });

            this._originRelative(prevSiblings.get());
        } ,

        // 切换到下一个
        _nextPosition: function(tab){
            tab = G(tab);

            var nextSiblings = tab.nextSiblings({
                tagName: 'div' ,
                class: 'tab'
            });

            if (nextSiblings.length === 0) {
                this._originPosition(tab.get(0));
                return ;
            }

            var maxLeft = nextSiblings.length * this._tabW;
            var curLeft = tab.getCoordVal('left');
                curLeft = Math.min(maxLeft , curLeft);
            var count   = Math.ceil(Math.abs(curLeft / this._tabW));
            var range   = (count - 1) * this._tabW + this._tabW * 1 / 3;
            var index   = curLeft > range ? count - 1 : count - 2;

            // 这种情况是为了避免误操作导致调用该方法产生错误而设计的
            if (index < 0) {
                this._originPosition(tab.get(0));
                return ;
            }

            var next = nextSiblings.jump(index , true);

            // 交换 DOM 元素
            G.insertAfter(tab.get(0) , next.get(0));

            tab.css({
                left: '0px' ,
                top: '0px'
            });

            // 还原相关元素位置
            this._originRelative(nextSiblings.get());
        } ,

        /**
         * 前置元素移动监听
         */
        _prevListen: function(tab) {
            tab = G(tab);

            var prevSiblings = tab.prevSiblings({
                tagName: 'div',
                class: 'tab'
            });

            if (prevSiblings.length === 0) {
                return ;
            }

            prevSiblings.each(function(dom , k){
                dom = G(dom);
                var type = this._judgePosition(tab.get(0) , k + 1);
                var endVal = 0;

                if (type === 'prev') {
                    endVal = this._tabW;
                }

                dom.css({
                    left: endVal + 'px'
                });
            }.bind(this));
        } ,

        /**
         * 下一个元素移动
         */
        _nextListen: function(tab){
            tab = G(tab);

            var nextSiblings = tab.nextSiblings({
                tagName: 'div',
                class: 'tab'
            });

            if (nextSiblings.length === 0) {
                return ;
            }

            nextSiblings.each(function(dom , k){
                dom = G(dom);
                var type = this._judgePosition(tab.get(0) , k + 1);
                var endVal = 0;

                if (type === 'next') {
                    endVal = -this._tabW;
                }

                dom.css({
                    left: endVal + 'px'
                });
            }.bind(this));
        } ,

        /**
         * 确定位置
         */
        _determinePosition: function(){
            // 最终确定位置
            var type = this._judgePosition(this._moveDOM.get(0) , 1);

            if (type === 'next') {
                this._nextPosition(this._moveDOM.get(0));
            }

            if (type === 'prev') {
                this._prevPosition(this._moveDOM.get(0));
            }

            if (type === 'origin') {
                this._originPosition(this._moveDOM.get(0));
            }

            this._moveDOM.css({
                zIndex: 'auto'
            });
        } ,

        // 鼠标松开后触发事件（拖动标签时）
        _tabMouseupEvent: function(e){
            // 如果未发生拖拽行为，不做任何处理
            if (!this._canDrag) {
                return ;
            }

            this._canDrag = false;

            this._determinePosition();
        } ,

        title: function(id , title){
            var tab = G(this.tab(id));
            var text = G('.text' , tab.get(0));
            // 设置标签
            text.text(title === '' ? this._default.title : title);
        } ,

        // 通过 id 查找 tab
        tab: function(id){
            var i   = 0;
            var cur = null;
            for (; i < this._tabs_.length; ++i)
            {
                cur = this._tabs_.jump(i , true);
                if (cur.data('id') === id) {
                    return cur.get(0);
                }
            }
            throw new Error('未找到当前 id 对应的 tab');
        } ,

        // 重新调整
        resize: function(){
            this._initDynamicHTML();
            this._initDynamicArgs();
            this._initDynamic();
        } ,

        _defineEvent: function(){
            var win = G(window);
            win.on('mousemove'  , this._tabMouseMoveEvent.bind(this) , true , false);
            win.on('mouseup'    , this._tabMouseupEvent.bind(this) , true , false);
            win.on('resize'     , this.resize.bind(this) , true , false);
        } ,

        // 获取 tab 上新增的额外属性
        attr: function(id , key){
            var tab = G(this.tab(id));
            var value = tab.data(key);
            return value;
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
        window.MultipleTab = MultipleTab;
    }

    return MultipleTab;
});