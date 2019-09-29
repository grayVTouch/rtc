/*
 * author grayVTouch 2017-04-20
 * 图片展示插件
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

    function PicShow(selector , opt){
        var thisRange = [undefined , null , window];

        if (G.contain(this , thisRange) || !G.contain(this , thisRange) && this.constructor !== PicShow) {
            return new PicShow(selector , opt);
        }

        // 默认设置
        this._defaultOpt = {
            // 动画过度时间
            time: 300 ,
            // h/w 图片高宽比
            HWRatio: 0.8 ,
            // 大图部分占据高度占容器高度的比率
            bigRatio: 0.8 ,
            // 展示小图数量
            indexCount: 5 ,
            // 初始索引
            index: 1 ,
            // 大图与小图部分的间隔
            interval: 10 ,
            // 插件 url
            pluginUrl: '' ,
            // 下一个回调
            next: null ,
            prev: null ,
            click: null ,
            // 滚动功能
            enableWheel: true ,
            // 上一页、下一页功能
            enableOpr: true

        };

        // 如果没有设置项，则启用默认项
        if (G.type(opt) === 'Undefined') {
            opt = this._defaultOpt;
        }

        // 相关元素
        this._con = G(selector);

        // 设置项
        this._time 		= G.type(opt['time']) === 'Number' ? opt['time'] : this._defaultOpt['time'];
        this._HWRatio 		= G.type(opt['HWRatio']) === 'Number' ? opt['HWRatio'] : this._defaultOpt['HWRatio'];
        this._bigRatio 		= G.type(opt['bigRatio']) === 'Number' ? opt['bigRatio'] : this._defaultOpt['bigRatio'];
        this._indexCount 	= G.type(opt['indexCount']) === 'Number' ? opt['indexCount'] : this._defaultOpt['indexCount'];
        this._index	  		= G.type(opt['index']) === 'Number' ? opt['index'] : this._defaultOpt['index'];
        this._interval	  	= G.type(opt['interval']) === 'Number' ? opt['interval'] : this._defaultOpt['interval'];
        this._pluginUrl	  	= G.type(opt['pluginUrl']) === 'String' ? opt['pluginUrl'] : this._defaultOpt['pluginUrl'];
        this._next	  	= G.type(opt['next']) === 'Function' ? opt['next'] : this._defaultOpt['next'];
        this._prev	  	= G.type(opt['prev']) === 'Function' ? opt['prev'] : this._defaultOpt['prev'];
        this._click	  	= G.type(opt['click']) === 'Function' ? opt['click'] : this._defaultOpt['click'];
        this._enableWheel	  	= G.type(opt['enableWheel']) === 'Boolean' ? opt['enableWheel'] : this._defaultOpt['enableWheel'];
        this._enableOpr	  	= G.type(opt['enableOpr']) === 'Boolean' ? opt['enableOpr'] : this._defaultOpt['enableOpr'];

        this._run();
    }

    PicShow.prototype = {
        constructor: PicShow ,
        author: '陈学龙' ,
        cTime: '2017-04-20' ,

        _initStaticHTML: function(){
            this._picShow 		= G('.pic-show' , this._con.get(0));
            this._preview		= G('.preview'  , this._picShow.get(0));
            this._content		= G('.content'  , this._picShow.get(0));
            this._listForBig	= G('.list'  	, this._content.get(0));
            this._links                 = G('.link' , this._listForBig.get(0));
            this._index_		= G('.index' 	, this._picShow.get(0));
            this._small			= G('.small'   	, this._index_.get(0));
            this._listForSmall	= G('.list'	   	, this._small.get(0));

            // 初始化设置索引
            var html = [];
            this._links.each(function(dom , k){
                dom = G(dom);
                var image = G('.image' , dom.get(0)).first();
                var index = k + 1;
                html.push('<div class="item" data-index="' + index + '" data-id="' + k + '"><img src="' + image.attr('src') + '" class="image" /></div>')
            });

            this._listForSmall.html(html.join(''));

            // 大图新增头尾元素
            var first = this._links.first(true);
            var last  = this._links.last(true);

            G.insertBefore(last.clone().get(0) , first.get(0));
            this._listForBig.append(first.clone().get(0));
        } ,

        // 静态参数
        _initStaticArgs: function(){
            this._prevForBig			= G('.prev'		, this._preview.get(0));
            this._nextForBig			= G('.next'		, this._preview.get(0));
            this._links                 = G('.link' , this._listForBig.get(0));
            this._items					= G('.item'	   , this._listForSmall.get(0));
            this._prevForSmall			= G('.prev'	   , this._index_.get(0));
            this._imageForPrevWithSmall	= G('.image'	   , this._prevForSmall.get(0));
            this._nextForSmall			= G('.next'	   , this._index_.get(0));
            this._imageForNextWithSmall	= G('.image'	   , this._nextForSmall.get(0));

            // 范围值
            this._minID 	= 0;
            this._maxID 	= this._links.length - 1;
            this._minIndex 	= 1;
            this._maxIndex 	= this._items.length;

            // 图片显示限制
            this._lenForBefore 	= G.oddEven(this._indexCount - 1) == 'even' ? (this._indexCount - 1) / 2 : this._indexCount / 2;
            this._lenForAfter 	= this._indexCount - 1 - this._lenForBefore;
        } ,

        // 一次性设置
        _initStatic: function(){
            var i 	= 0;
            var cur = null;

            // 设置大图
            for (i = 0; i < this._links.length; ++i)
            {
                cur = this._links.jump(i , true);

                cur.data('id' , i);

                if (i === 0) {
                    cur.data('index' , this._links.length - 2);
                } else if (i === this._links.length - 1) {
                    cur.data('index' , 1);
                } else {
                    cur.data('index' , i);
                }

                // 设置当前项
                if (cur.data('id') == this._index) {
                    cur.addClass('cur');
                } else {
                    cur.removeClass('cur');
                }
            }

            // 设置小图
            this._items.each(function(dom , k){
                dom = G(dom);

                if (dom.data('index') == this._index) {
                    dom.addClass('cur');
                } else {
                    dom.removeClass('cur');
                }
            }.bind(this));

            // 如果启用
            if (!this._enableOpr) {
                this._prevForBig.addClass('hide');
                this._nextForBig.addClass('hide');
            }
        } ,

        _initDynamicHTML: function(){

        } ,

        // 动态参数
        _initDynamicArgs: function(){
            // 容器的宽高
            this._picShowW = this._picShow.width('border-box');
            this._picShowH = this._picShow.height('border-box');

            // 内容的宽高
            this._previewW  = this._picShowW;
            this._previewH  = Math.floor((this._picShowH - this._interval) * this._bigRatio);

            // 图片容器的宽度
            this._listForBigW = this._previewW * this._links.length;

            this._index_W    = this._picShowW;
            this._index_H	= this._picShowH - this._interval - this._previewH;

            // 按钮的宽度
            this._btnW = this._prevForSmall.width('border-box');

            // 小图列表项顶级容器宽度
            this._smallW = this._index_W - this._btnW * 2;

            // 小图的宽度
            this._itemW  		= Math.floor(this._smallW / this._indexCount);
            this._smallW 		= this._indexCount * this._itemW;
            this._listForSmallW = this._items.length * this._itemW;
        } ,

        // 动态设置
        _initDynamic: function(){
            // 设置大图容器高度
            this._preview.css({
                height: this._previewH + 'px' ,
                marginBottom: this._interval + 'px'
            });

            // 设置大图内容容器的宽度 + left 值！
            this._listForBig.css({
                width: this._listForBigW + 'px' ,
                left: -this.leftForBig(this._index) + 'px'
            });

            // 设置小图的宽！
            this._links.css({
                width: this._previewW + 'px'
            });

            // 设置小图容器高度 + 行高（子元素会继承，要的就是继承效果）
            this._index_.css({
                height: this._index_H + 'px'
            });

            // 设置图片列表顶级容器元素宽度
            this._small.css({
                width: this._smallW + 'px'
            });

            // 设置小图列表容器宽度
            this._listForSmall.css({
                width: this._listForSmallW + 'px'
            });

            var i 	= 0;
            var cur = null;

            // 设置小图的宽
            for (i = 0; i < this._items.length; ++i)
            {
                cur = this._items.jump(i , true);
                cur.css({
                    width: this._itemW + 'px'
                });
            }
        } ,

        _initialize: function(){

        } ,

        // 通过 获取 left 值
        leftForBig: function(id){
            return id * this._previewW;
        } ,

        leftForSmall: function(index){
            return (index - 1) * this._itemW;
        } ,

        // 通过 id 找到大图（因为大图会有重复的 index，所以 id 和 大图一一对应）
        big: function(id){
            var i 	= 0;
            var cur = null;

            for (; i < this._links.length; ++i)
            {
                cur = this._links.jump(i , true);

                if (cur.data('id') == id) {
                    return cur.get(0);
                }
            }

            throw new Error("未找到对应项");
        } ,

        // 通过 index 找到小图
        small: function(index){
            var i 	= 0;
            var cur = null;

            for (; i < this._items.length; ++i)
            {
                cur = this._items.jump(i , true);

                if (cur.data('index') == index) {
                    return cur.get(0);
                }
            }

            throw new Error("未找到对应项");
        } ,

        // 大图切换到指定位置
        switchForBig: function(id){
            var self  = this;
            var endLeft = -this.leftForBig(id) + 'px';

            this._listForBig.animate({
                left: endLeft
            } , function(){
                if (id == self._minID) {
                    id = self._maxID - 1;

                    self._listForBig.css({
                        left: -self.leftForBig(id) + 'px'
                    });
                }

                if (id == self._maxID) {
                    id = self._minID + 1;

                    self._listForBig.css({
                        left: -self.leftForBig(id) + 'px'
                    });
                }

                var image = self.big(id);
                image = G(image);

                image.highlight('cur' , self._links.get(0));
            } , this._time);
        } ,

        // 小图切换到指定位置
        switchForSmall: function(index){
            var endLeft = null;

            if (index < this._indexCount) {
                endLeft = -this.leftForSmall(this._minIndex);
            } else {
                if (index + this._lenForAfter >= this._maxIndex) {
                    endLeft = -this.leftForSmall(this._maxIndex - this._indexCount + 1);
                } else {
                    endLeft = -this.leftForSmall(index - this._lenForBefore);
                }
            }

            endLeft += 'px';

            var item = this.small(index);
            item = G(item);
            // 切换样式
            item.highlight('cur' , this._items.get());

            this._listForSmall.animate({
                left: endLeft
            } , null , this._time);
        } ,

        // 图片点击事件
        _clickEventForSmall: function(event){
            var tar 	= G(event.currentTarget);
            var index 	= parseInt(tar.data('index'));

            // 大图切换
            this.switchForBig(index);

            // 小图切换
            this.switchForSmall(index);

            if (G.type(this._click) === 'Function') {
                this._click.call(this , index);
            }
        } ,

        // 获取小图当前选中项 index
        index: function(){
            var i = 0;
            var cur = null;

            for (; i < this._items.length; ++i)
            {
                cur = this._items.jump(i , true);

                if (cur.hasClass('cur')) {
                    return parseInt(cur.data('index'));
                }
            }

            throw new Error("未找到对应项");
        } ,

        // 上一个
        prev: function(){
            var index = this.index();
            var curIndex = index - 1;
            curIndex = curIndex  < this._minIndex ? this._maxIndex : curIndex;
            var id = index == this._minIndex ? this._minID : index - 1;

            // 大图切换
            this.switchForBig(id);

            // 小图切换
            this.switchForSmall(curIndex);

            if (G.type(this._prev) === 'Function') {
                this._prev.call(this , curIndex);
            }
        } ,

        // 下一个
        next: function(){
            var index = this.index();
            var curIndex = index + 1;
            curIndex = curIndex  > this._maxIndex ? this._minIndex : curIndex;
            var id = index == this._maxIndex ? this._maxID : index + 1;

            // 大图切换
            this.switchForBig(id);

            // 小图切换
            this.switchForSmall(curIndex);

            if (G.isFunction(this._next)) {
                this._next.call(this , curIndex);
            }
        } ,

        // 鼠标滚轮事件
        _wheelEvent: function(event){
            event.preventDefault();
            if (event.deltaY >= 0) {
                this.next();
            } else {
                this.prev();
            }
        } ,

        _resizeEvent: function(){
            this._initDynamicHTML();
            this._initDynamicArgs();
            this._initDynamic();
        } ,

        _defineEvent: function(){
            var win = G(window);

            this._items.on('click'			, this._clickEventForSmall.bind(this) , true , false);
            this._prevForBig.on('click'		, this.prev.bind(this) , true , false);
            this._nextForBig.on('click'		, this.next.bind(this) , true , false);
            this._prevForSmall.on('click'	, this.prev.bind(this) , true , false);
            this._nextForSmall.on('click'	, this.next.bind(this) , true , false);

            if (this._enableWheel) {
                this._index_.on('wheel'		, this._wheelEvent.bind(this) , true , false);
            }

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
    	window.PicShow = PicShow;
	}

    return PicShow;
});