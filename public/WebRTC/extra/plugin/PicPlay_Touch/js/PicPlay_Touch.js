/*
 * author cxl 2016-09-16
 * 图片轮播
 * 支持 多开 拖拽 滚动 下标点击 前后按键点击 定时轮播 切换 ， 普通下标 或者 图片缩略图下标
 * 前提： 必须设置容器元素宽度！
 */
(function(global , factory){
    "use strict";

    if (typeof module !== 'undefined' && typeof module.exports === 'object') {
        module.exports = factory(global , true);
    } else {
        factory(global);
    }
})(typeof window === 'undefined' ? this : window , function(window , noGlobal){
    "use strict";

    function PicPlay_Touch(selector , opt){
        var thisRange = [window , null , undefined];

        if (G.contain(this , thisRange) || (!G.contain(this , thisRange) && this.constructor !== PicPlay_Touch)) {
            return new PicPlay_Touch(selector , opt);
        }

        this._default = {
            // 动画过度时间
            time: 200 ,
            // 定时器时间
            duration: 5000 ,
            // 索引类型, index-普通索引 image-图片索引 none-无索引
            indexType: 'index' ,
            // 索引容器位置 (inside | outside)
            indexPos: 'inside' ,
            // 索引摆放类型（horizontal|vertical）
            placementType: 'horizontal' ,
            // 索引摆放位置（top|right|bottom|left）
            // placementType = horizontal，则允许的值有 top|bottom；placementType = vertical，则允许的值有 left|right
            // todo placementType=horizontal 时，placementPos=top，存在问题，请勿使用此种布局！
            placementPos: '' ,
            // 链接点击后回调
            link: null ,
            // 是否启用 上一张 | 下一张 功能
            enableOpr: true ,
            // 是否启用滚动功能
            enableScroll: true ,
            // 是否开启拖拽功能
            enableDrag: true ,
            // 是否开启定时轮播功能
            enableTimer: true ,
            // 初始索引
            index: 1 ,
            // 高宽比
            hwRatio: 0.5 ,
            // 宽高比
            whRatio: 2
        };

        if (G.isUndefined(opt)) {
            opt = this._default;
        }

        this._indexTypeRange				= ['index' , 'image' , 'none'];
        this._placementTypeRange			= ['horizontal' , 'vertical'];
        this._horizontalPlacementPosRange	= ['top' , 'bottom'];
        this._verticalPlacementPosRange		= ['left' , 'right'];
        this._defaultHorizontalPlacementPos = 'bottom';
        this._defaultVerticalPlacementPos 	= 'right';
        this._indexPosRange					= ['outside' , 'inside'];

        // 容器元素
        this._con				= G(selector);
        this._time			= G.type(opt['time']) !== 'Number'			? this._default['time']		: opt['time'];
        this._duration		= G.type(opt['duration']) !== 'Number'		? this._default['duration']	: opt['duration'];
        this._indexType			= !G.contain(opt['indexType'] , this._indexTypeRange)   ? this._default['indexType']		: opt['indexType'];
        this._indexPos			= !G.contain(opt['indexPos'] , this._indexPosRange)     ? this._default['indexPos']		: opt['indexPos'];
        this._placementType		= !G.contain(opt['placementType'] , this._placementTypeRange)     ? this._default['placementType']		: opt['placementType'];
        this._placementPos		= this._placementType === 'horizontal' ? (G.contain(opt['placementPos'] , this._horizontalPlacementPosRange) ? opt['placementPos'] : this._defaultHorizontalPlacementPos) : (G.contain(opt['placementPos'] , this._verticalPlacementPosRange) ? opt['placementPos'] : this._defaultVerticalPlacementPos);
        this._enableOpr		= G.type(opt['enableOpr']) !== 'Boolean'  ? this._default['enableOpr'] : opt['enableOpr'];
        this._enableScroll  = G.type(opt['enableScroll']) !== 'Boolean'  ? this._default['enableScroll'] : opt['enableScroll'];
        this._enableDrag	= G.type(opt['enableDrag']) !== 'Boolean'  ? this._default['enableDrag'] : opt['enableDrag'];
        this._enableTimer	= G.type(opt['enableTimer']) !== 'Boolean'  ? this._default['enableTimer'] : opt['enableTimer'];
        this._link			= G.isFunction(opt['link'])			? opt['link'] : this._default['link'];
        // 后期 _index 会被覆盖，这是正常现象
        // 实际 this.index 才是内部指针
        this._index			= G.type(opt['index']) !== 'Number'			? this._default['index'] : parseInt(opt['index']);
        this._hwRatio = G.isNumber(opt.hwRatio) ? this._hwRatio : this._default.hwRatio;
        this._whRatio = G.isNumber(opt.whRatio) ? this.whRatio : this._default.whRatio;

        this._run();
    }

    PicPlay_Touch.prototype = {
        version: '1.0' ,

        cTime: '2016-09-16 10:20:00' ,

        constructor: PicPlay_Touch ,

        timerGroup: 'PicPlay_Touch' ,

        _initStaticHTML: function(){
            // 图片一开始选中索引
            this.index = this._index;
            this._focus = 'cur';
            this._picPlayTouch   = G('.pic-play-touch' , this._con.get(0));
            this._images    = G('.images' , this._picPlayTouch.get(0));
            this.__images   = G('._images' , this._images.get(0));

            // 索引
            this._index    = G('.index' , this._picPlayTouch.get(0));
            this.__index   = G('._index' , this._index.get(0));
            this.__image     = G('._image' , this._index.get(0));
            // 连接
            this._links      = G('.link' , this.__images.get(0));

            // 大图处理
            var first   = this._links.first(true);
            var last    = this._links.last(true).clone();

            first.insertBefore(last.get(0));
            this.__images.append(first.clone().get(0));

            this._s_images   = G('.link .image' , this.__images.get(0));
            this._imageCount = this._s_images.length - 2;

            var indexHtml   = '';
            var imageHtml   = '';
            var i = 0;
            var index = null;
            var className = null;

            for (; i < this._imageCount; ++i)
            {
                index = i + 1;
                indexHtml += " <div class='_index_' data-index='" + index + "'></div> ";
                imageHtml += " <div class='_index_' data-index='" + index + "'>  ";
                imageHtml += "   <div class='bg'></div>  ";
                imageHtml += "   <img src='" + this._s_images.jump(index , true).attr('src') + "' class='image'>  ";
                imageHtml += " </div> ";
            }

            this.__index.html(indexHtml);
            this.__image.html(imageHtml);
        } ,

        // 定义静态变量
        _initStaticArgs: function(){
            this._links      = G('.link' , this.__images.get(0));
            this._prevBtn    = G('.prev' , this._images.get(0));
            this._nextBtn    = G('.next' , this._images.get(0));
            this._indexs     = G('._index_' , this.__index.get(0));
            this._indexsForImage  = G('._index_' , this.__image.get(0));
            this._unit = 'px';

            // 是否能够拖拽移动
            this._canMove = false;

            // 拖拽超过多少时间执行范围判断: ms
            this._checkTime = 200;

            // 定时器
            this._con.timer = null;
            // 是否已经停止动画（动画过度是否完成）
            this._con.isCarStop = false;
        } ,

        _initStatic: function(){
            this._index.removeClass('hide');

            if (this._indexType === 'index') {
                this.__index.removeClass('hide');
                this.__image.addClass('hide');
                this.__index.addClass([this._indexType + '-' + this._placementType]);
            } else if (this._indexType === 'image') {
                this.__index.addClass('hide');
                this.__image.removeClass('hide');
                this.__image.addClass([this._indexType + '-' + this._placementType]);
            } else if (this._indexType === 'none') {
                this.__index.addClass('hide');
                this.__image.addClass('hide');
                this._indexType = 'index';
            } else {
                // 可继续扩展
            }

            // 设置索引样式

            if (this._enableOpr) {
                this._prevBtn.removeClass('hide');
                this._nextBtn.removeClass('hide');
            }

            if (this._enableTimer) {
                this.clearTime();
                this.setTime();
            }

            this._minIndex = 1;
            this._maxIndex = this._imageCount;
        } ,

        _initDynamicHTML: function(){

        } ,

        _initDynamicArgs: function(){
            this._maxW = Math.max(0 , this._picPlayTouch.width('content-box'));
            this._maxH = Math.max(0 , this._picPlayTouch.height('content-box'));

            // 索引 与 图片分离时，间隔
            this._marginForImage = 20;
            this._marginForIndex = 0;

            // 索引容器 + 索引宽高
            // 大图容器 + 大图宽高
            if (this._indexType === 'image') {
                // 当前索引容器
                this._curIndex = this.__image;

                if (this._placementType === 'horizontal') {
                    // 水平摆放索引
                    this._imageMarginRight  = this.__images.getCoordVal('marginRight');
                    this._extraImageW       = this._imageMarginRight * (this._s_images.length - 3);

                    // 索引 h / w 比例
                    this._picSHRatio = this._hwRatio;

                    this._picSW = Math.max(0 , Math.floor((this._maxW - this._extraImageW) / this._imageCount));
                    this._picSH = Math.max(0 , Math.floor(this._picSW * this._picSHRatio));

                    // 索引容器宽高
                    this._curIndexW = this._maxW;
                    this._curIndexH = this._picSH;

                    // 单个索引宽高
                    this._indexW = this._maxW;
                    this._indexH = this._picSH;

                    if (this._indexPos === 'inside') {
                        this._imagesW = this._maxW;
                        this._imagesH = this._maxH - this._curIndexH;
                    } else {
                        this._imagesW = this._maxW;
                        this._imagesH = this._maxH - this._marginForImage - this._curIndexH;
                    }
                } else {
                    // 垂直摆放索引
                    this._imageMarginBottom  = this.__images.getCoordVal('marginBottom');
                    this._extraImageW        = this._imageMarginBottom * (this._s_images.length - 3);

                    // 索引 w / h 比率
                    this._picSWRatio = this._whRatio;

                    this._picSH = Math.max(0 , Math.floor((this._maxH - this._extraImageW) / this._imageCount));
                    this._picSW = Math.max(0 , Math.floor(this._picSH * this._picSWRatio));

                    this._curIndexW = this._picSW;
                    this._curIndexH = this._maxH;

                    this._indexW = this._picSH;
                    this._indexH = this._picSW;

                    if (this._indexPos === 'inside') {
                        this._imagesW = this._maxW - this._curIndexW;
                        this._imagesH = this._maxH;
                    } else {
                        this._imagesW = this._maxW - this._marginForImage - this._curIndexW;
                        this._imagesH = this._maxH;
                    }
                }
            } else {
                // indexType = index or other
                // 当前索引容器
                this._curIndex = this.__index;

                // 非图片索引无需理会单个索引宽、高
                this._indexW = 0;
                this._indexH = 0;

                if (this._placementType === 'horizontal') {
                    this._curIndexW = this._maxW;
                    this._curIndexH = this._curIndex.height('border-box');

                    if (this._indexPos === 'inside') {
                        this._imagesW = this._maxW;
                        this._imagesH = this._maxH;
                    } else {
                        this._imagesW = this._maxW;
                        this._imagesH = this._maxH - this._marginForIndex - this._curIndexH;
                    }
                } else {
                    this._curIndexW = this._curIndex.width('border-box');
                    this._curIndexH = this._maxH;

                    if (this._indexPos === 'inside') {
                        this._imagesW = this._maxW;
                        this._imagesH = this._maxH;
                    } else {
                        this._imagesW = this._maxW - this._marginForIndex - this._curIndexW;
                        this._imagesH = this._maxH;
                    }
                }
            }


            // 计算大图容器的相关参数
            this.__imagesW = this._placementType === 'horizontal' ? this._imagesW * this._s_images.length : this._imagesW;
            this.__imagesH = this._placementType === 'horizontal' ? this._imagesH : this._imagesH * this._s_images.length;

            // 水平切换的时候
            this._minLeftVal		 = -((this._s_images.length - 1) * this._imagesW);
            this._maxLeftVal		 = 0;

            /**
             * 垂直切换的时候
             */
            this._minTopVal			= -((this._s_images.length - 1) * this._imagesH);
            this._maxTopVal			= 0;

            this._horizontalPos 	= {};
            this._verticalPos 		= {};
            this._fullHorizontalPos = [];
            this._fullVerticalPos   = [];

            // 大图顶级容器样式
            var imagesCssJson = {
                width:  this._imagesW + this._unit ,
                height: this._imagesH + this._unit
            };

            // 大图主容器样式
            var _imagesCssJson = {
                width: this.__imagesW + this._unit
            };

            // 索引样式
            var indexCssJson = {};

            if (this._placementType === 'horizontal') {
                _imagesCssJson['left'] = -this._imagesW + this._unit;
                indexCssJson['left'] = 0;
                indexCssJson['width'] = '100%';

                if (this._placementPos === 'top') {
                    indexCssJson['top'] = 0;

                    if (this._indexType === 'image') {
                        if (this._indexPos === 'inside') {
                            imagesCssJson['marginTop'] = this._curIndexH + this._unit;
                        } else {
                            imagesCssJson['marginTop'] = this._curIndexH + this._marginForImage + this._unit;
                        }
                    } else {
                        if (this._indexPos === 'inside') {
                            imagesCssJson['marginTop'] = 0;
                        } else {
                            imagesCssJson['marginTop'] = this._curIndexH + this._marginForIndex + this._unit;
                        }
                    }
                } else {
                    indexCssJson['bottom'] = 0;

                    if (this._indexType === 'image') {
                        if (this._indexPos === 'inside') {
                            imagesCssJson['marginBottom'] = this._curIndexH + this._unit;
                        } else {
                            imagesCssJson['marginBottom'] = this._curIndexH + this._marginForIndex + this._unit;
                        }
                    } else {
                        if (this._indexPos === 'inside') {
                            imagesCssJson['marginBottom'] = 0;
                        } else {
                            imagesCssJson['marginBottom'] = this._curIndexH + this._marginForIndex + this._unit;
                        }
                    }
                }

                this._attr      = 'left';
                this._amount    = this._imagesW;
                this._minVal 	  = this._minLeftVal;
                this._maxVal	  = this._maxLeftVal;
                this._pos         = this._horizontalPos;
                this._fullPos     = this._fullHorizontalPos;
            } else {
                _imagesCssJson['top'] = -this._imagesH + this._unit;
                indexCssJson['top'] = 0;
                indexCssJson['height'] = '100%';

                if (this._placementPos === 'left') {
                    indexCssJson['left'] = 0;

                    if (this._indexType === 'image') {
                        if (this._indexPos === 'inside') {
                            imagesCssJson['marginLeft'] = this._curIndexW + this._unit;
                        } else {
                            imagesCssJson['marginLeft'] = this._curIndexW + this._marginForImage + this._unit;
                        }
                    } else {
                        if (this._indexPos === 'inside') {
                            imagesCssJson['marginLeft'] = 0;
                        } else {
                            imagesCssJson['marginLeft'] = this._curIndexW + this._marginForIndex + this._unit;
                        }
                    }
                } else {
                    indexCssJson['right'] = 0;

                    if (this._indexType === 'image') {
                        if (this._indexPos === 'inside') {
                            imagesCssJson['marginRight'] = this._curIndexW + this._unit;
                        } else {
                            console.log(this._curIndexW , this._marginForImage);
                            imagesCssJson['marginRight'] = this._curIndexW + this._marginForImage + this._unit;
                        }
                    } else {
                        if (this._indexPos === 'inside') {
                            imagesCssJson['marginRight'] = 0;
                        } else {
                            imagesCssJson['marginRight'] = this._curIndexW + this._marginForIndex + this._unit;
                        }
                    }
                }

                this._attr = 'top';
                this._amount   = this._imagesH;
                this._minVal 	  = this._minTopVal;
                this._maxVal	  = this._maxTopVal;
                this._pos         = this._verticalPos;
                this._fullPos     = this._fullVerticalPos;
            }

            this._imagesCssJson     = imagesCssJson;
            this.__imagesCssJson    = _imagesCssJson;
            this._indexCssJson      = indexCssJson;

            // horizontal 拖拽时，拖拽超过长度允许切换
            this._horizontalMRange 	= this._imagesW * 0.4;

            // vertical 拖拽时，拖拽超过长度允许切换
            this._verticalMRange 	= this._imagesW * 0.4;

            this._btnW = Math.max(0 , this._prevBtn.width('border-box'));
            this._btnH = this._imagesH;



        } ,

        _initDynamic: function(){
            // 显示正确的索引
            if (this._indexType === 'image') {
                this.__index.addClass('hide');
                this.__image.removeClass('hide');
            } else if (this._indexType === 'index') {
                this.__index.removeClass('hide');
                this.__image.addClass('hide');
            } else {
                this.__index.addClass('hide');
                this.__image.addClass('hide');
            }

            // 容器样式
            this._images.css(this._imagesCssJson);
            this.__images.css(this.__imagesCssJson);
            this._index.css(this._indexCssJson);

            // 跳转链接集合
            this._linkHrefs = [];

            var i = 0;
            // 保存图片链接
            for (i = 1; i < this._links.length - 2; ++i)
            {
                this._linkHrefs.push(this._links.get(i).href);
            }

            /**
             * 移动端 或 enableOpr = false
             * 是否展示 上一页 | 下一页功能
             */
            var browser = G.browser();

            if (browser === 'mobile' || !this._enableOpr) {
                this._prevBtn.addClass('hide');
                this._nextBtn.addClass('hide');
            }

            // 设置图片索引
            for (i = 0; i < this._indexsForImage.length; ++i)
            {
                this._indexsForImage.jump(i , true).css({
                    width: this._picSW + this._unit ,
                    height: this._picSH + this._unit
                });
            }

            // 初始大图图片直属父系容器（link）
            for (i = 0; i < this._links.length; ++i)
            {
                this._links.jump(i , true).css({
                    width: this._imagesW + this._unit
                });
            }

            // 设置按钮
            this._prevBtn.css({
                height: this._btnH + this._unit ,
                lineHeight: this._btnH + this._unit
            });

            this._nextBtn.css({
                height: this._btnH + this._unit ,
                lineHeight: this._btnH + this._unit
            });

            // 初始化位置信息 和 下标对应关系
            // 水平时的位置对应关系
            for (i = 1; i <= this._imageCount; ++i)
            {
                this._horizontalPos[i] = (-this._imagesW) * i;
            }

            // 初始化位置信息 和 下标对应关系
            // 垂直时的位置对应关系
            for (i = 1; i <= this._imageCount; ++i)
            {
                this._verticalPos[i] = (-this._imagesH) * i;
            }

            // 完整的 pos 列表
            this._fullHorizontalPos.push(0);
            this._fullVerticalPos.push(0);

            for (i = 1; i <= this._s_images.length - 1; ++i)
            {
                this._fullHorizontalPos.push(-(this._imagesW * i));
                this._fullVerticalPos.push(-(this._imagesH * i));
            }
        } ,

        _init: function(){
            this.toIndex(this.index);
        } ,

        _getLinkHref: function(){
            return this._linkHrefs[this._getCurIndex()];
        } ,

        // 根据位置 获取对应的 下标
        _getIndex: function(){
            var curVal = Math.floor(this.__images.getCoordVal(this._attr));

            for (var key in this._pos)
            {
                if (this._pos[key] === curVal) {
                    return parseFloat(key);
                }
            }

            throw new Error('超出受支持的范围');
        } ,
        // 根据类名 获取当前显示的 下标
        _getCurIndex: function(){
            return this.index;
        } ,

        _getPos: function(index){
            return this._pos[index];
        } ,

        _animate: function(SmallJsObj , val){
            // 动画过度尚未完成
            if (SmallJsObj.get(0).__smalljs_animating__) {
                return ;
            }

            var self = this;
            var json = {};
                json[this._attr] = val;

            SmallJsObj.animate(json , function(){
                var curVal  = Math.floor(self.__images.getCoordVal(self._attr));
                var index   = false;
                var imageCssJson 	= {};

                if (curVal === self._minVal) {
                    index = 1;
                    imageCssJson[self._attr] = self._pos[index] + self._unit;
                    self.__images.css(imageCssJson);
                } else if (curVal === self._maxVal) {
                    index = self._indexs.length;
                    imageCssJson[self._attr] = self._pos[index] + self._unit;
                    self.__images.css(imageCssJson);
                } else {
                    index = self._getIndex();
                }

                self.index = index;

                index -= 1;

                if (self._indexType === 'image') {
                    self._indexsForImage.jump(index , true).highlight(self._focus , self._indexsForImage.get());
                } else if (self._indexType === 'index') {
                    self._indexs.jump(index , true).highlight(self._focus , self._indexs.get());
                } else {
                    throw new RangeError('不支持的索引类型！');
                }
                /**
                 * 是否支持自动轮播
                 */
                if (self._enableTimer) {
                    self.setTime();
                }
            } , this._time);
        } ,

        _prevEvent: function(){
            this.clearTime();
            var index   = this.index;
                index  -= 1;
                index   = index < this._minIndex ? this._minIndex - 1 : index;
            var val     = this._fullPos[index] + this._unit;
            this._animate(this.__images , val);
        } ,

        _nextEvent: function(){
            this.clearTime();
            var index   = this.index;
                index  += 1;
                index   = index < this._minIndex ? this._minIndex + 1 : index;
            var val     = this._fullPos[index]  + this._unit;
            this._animate(this.__images , val);
        } ,

        _wheelEvent: function(e){
            e.preventDefault();
            if (e.deltaY >= 0) {
                this._nextEvent();
            } else {
                this._prevEvent();
            }
        } ,

        // 跳转到指定索引
        toIndex: function(index){
            var endVal = this._getPos(index) + this._unit;
            this._animate(this.__images , endVal);
        } ,

        _indexEvent: function(e){
            this.clearTime();
            var tar		= G(e.currentTarget);
            var index   = tar.data('index');

            this.toIndex(index);
        } ,

        _mouseDownEvent: function(e){
            this.clearTime();

            var browser		= G.browser();
            this._sTime		= new Date().getTime();
            this._canMove	= true;

            if (this._placementType === 'horizontal') {
                this._sox		= browser === 'mobile' ? e.touches[0].clientX : e.clientX;
            } else {
                this._soy		= browser === 'mobile' ? e.touches[0].clientY : e.clientY;
            }

            this._sVal		= this.__images.getCoordVal(this._attr);
        } ,

        _mouseMoveEvent: function(event){
            if (this._canMove) {
                var e			= event || window.event;
                // G.prevent(e);
                G.stop(e , true);
                var browser		 = G.browser();
                if (this._placementType === 'horizontal') {
                    this._eox		= browser === 'mobile' ? e.touches[0].clientX : e.clientX;
                    this._ox		 = this._eox - this._sox;
                    this._endVal 	= Math.max(this._minVal , Math.min(0 , this._sVal + this._ox));
                } else {
                    this._eoy		= browser === 'mobile' ? e.touches[0].clientY : e.clientY;
                    this._oy		= this._eoy - this._soy;
                    this._endVal 	= Math.max(this._minVal , Math.min(0 , this._sVal + this._oy));
                }

                var imageCssJson = {};
                    imageCssJson[this._attr] = this._endVal + this._unit;
                this.__images.css(imageCssJson);
            }
        } ,

        _mouseUpEvent: function(){
            var self = this;

            if (this._canMove === false) {
                return false;
            }

            this._canMove	= false;
            this._eTime		= new Date().getTime();
            var curVal  	= this.__images.getCoordVal(this._attr);
            var duration	= this._eTime - this._sTime;
            var initVal 	= this._getPos(this._getCurIndex());
            var endVal  	= false;

            if (duration >= this._checkTime) {
                // 超过时长，还原
                if (this._placementType === 'horizontal') {
                    var ox = Math.abs(this._ox);

                    if (ox > this._horizontalMRange) {
                        if (this._ox > 0) {
                            endVal = initVal + this._imagesW + this._unit;
                            this._animate(this.__images , endVal);
                        } else if (this._ox < 0) {
                            endVal = initVal - this._imagesW + this._unit;
                            this._animate(this.__images , endVal);
                        } else {
                            if (this.linkTo) {
                                window.location.href = this._getLinkHref();
                            }
                        }
                    } else {
                        endVal = initVal + this._unit;
                        this._animate(this.__images , endVal);
                    }
                } else {
                    var oy = Math.abs(this._oy);

                    if (oy > this._verticalMRange) {
                        if (this._oy > 0) {
                            endVal = initVal + this._imagesH + this._unit;
                            this._animate(this.__images , endVal);
                        } else if (this._ox < 0) {
                            endVal = initVal - this._imagesH + this._unit;
                            this._animate(this.__images , endVal);

                        } else {
                            if (this.linkTo) {
                                window.location.href = this._getLinkHref();
                            }
                        }
                    } else {
                        endVal = initVal;
                        this._animate(this.__images , endVal);
                    }
                }
            } else {
                // 未超过时长，进行计算
                if (this._placementType === 'horizontal') {
                    if (curVal > initVal) {
                        endVal = initVal + this._imagesW + this._unit;
                        this._animate(this.__images , endVal);
                    } else if (curVal < initVal) {
                        endVal = initVal - this._imagesW + this._unit;
                        this._animate(this.__images , endVal);
                    } else {
                        if (this.linkTo) {
                            window.location.href = this._getLinkHref();
                        }
                    }
                } else {
                    if (curVal > initVal) {
                        endVal = initVal + this._imagesH + this._unit;
                        this._animate(this.__images , endVal);
                    } else if (curVal < initVal) {
                        endVal = initVal - this._imagesH + this._unit;
                        this._animate(this.__images , endVal);
                    } else {
                        if (this.linkTo) {
                            window.location.href = this._getLinkHref();
                        }
                    }
                }
            }
        } ,

        /**
         * 清除定时器
         */
        clearTime: function(){
            this._con.isCarStop = true;
            G.timer.clearGroup(this.timerGroup);
        } ,

        /**
         * 定义定时器
         */
        setTime: function(){
            var self = this;
            var fn   = this.setTime.bind(this);

            this._con.isCarStop = false;
            this._con.timer     = G.timer.time(function(){
                self._nextEvent();

                if (self._con.isCarStop === false) {
                    self._con.timer = G.timer.time(fn , self._duration , self.timerGroup);
                }
            } , this._duration , this.timerGroup);
        } ,

        // 调整事件
        _resizeEvent: function(){
            this.clearTime();
            this._initDynamicHTML();
            this._initDynamicArgs();
            this._initDynamic();

            if (this._enableTimer) {
                this.setTime();
            }
        } ,

        // 链接点击事件
        _linkEvent: function(e){
            var tar = G(e.currentTarget);

            if (G.isFunction(this._link)) {
                G.prevent(e);
                this._link.call(this , tar.get(0));
            }
        } ,

        _defineEvent: function(){
            var win = G(window);
            var browser = G.browser();
            // PC端事件（由于移动端若是增加下面这些事件，反而使用户体验急剧下降，故而自动移除）
            if (browser !== 'mobile') {
                // 上一张 | 下一张功能
                if (this._enableOpr) {
                    this._prevBtn.on('click' , this._prevEvent.bind(this) , true , false);
                    this._nextBtn.on('click' , this._nextEvent.bind(this) , true , false);
                }

                // 鼠标滚动 上一张 | 下一张 事件
                if (this._enableScroll) {
                    this._images.on('wheel' , this._wheelEvent.bind(this) , true , false);
                }
            }

            // 拖拽功能
            if (browser === 'mobile' || this._enableDrag) {
                var mousedown = browser === 'mobile' ? 'touchstart' : 'mousedown';
                var mousemove = browser === 'mobile' ? 'touchmove'  : 'mousemove';
                var mouseup   = browser === 'mobile' ? 'touchend'   : 'mouseup';

                // this._s_images.on('click' , function(e){
                //     // G.prevent(e);
                //     // G.stop(e , true);
                // } , false , false);

                this.__images.on(mousedown , this._mouseDownEvent.bind(this) , true , false);

                win.on(mousemove , this._mouseMoveEvent.bind(this) , true , false);
                win.on(mouseup , this._mouseUpEvent.bind(this) , true , false);

                // 阻止图片拖拽
                this._s_images.on('dragstart' , function(event){
                    var e = event || window.event;

                    if (!e.defaultPrevented) {
                        e.preventDefault();
                    }

                    if (e.cancelable) {
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                    }

                    return false;
                } , false , false);

                // 阻止链接拖拽
                this._links.on('dragstart' , function(event){
                    var e = event || window.event;

                    if (!e.defaultPrevented) {
                        e.preventDefault();
                    }

                    if (e.cancelable) {
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                    }

                    return false;
                } , false , false);
            }

            // 索引事件
            this._indexsForImage.on('click' , this._indexEvent.bind(this) , true , false);
            this._indexs.on('click' , this._indexEvent.bind(this) , true , false);
            this._links.on('click' , this._linkEvent.bind(this) , true , false);

            // 动态调整
            win.on('resize' , this._resizeEvent.bind(this) , true , false);
        } ,

        _run: function(){
            this._initStaticHTML();
            this._initStaticArgs();
            this._initStatic();
            this._initDynamicHTML();
            this._initDynamicArgs();
            this._initDynamic();
            this._init();
            this._defineEvent();
        }
    };

    if (!noGlobal) {
        window.PicPlay_Touch = PicPlay_Touch;
    }

    return PicPlay_Touch;
});