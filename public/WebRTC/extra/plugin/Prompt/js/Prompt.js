(function(){
    "use strict";

    (function(global , factory){
        if (typeof module !== 'undefined' && typeof module.exports === 'object') {
            module.exports = factory(global , true);
        } else {
            factory(global);
        }
    })(typeof window === 'undefined' ? this : window , function(window , noGlobal){
        function Prompt(html , opt){
            var thisRange = [null , undefined , window];

            if (G.contain(this , thisRange) || this.constructor !== Prompt) {
                return new Prompt(html , opt);
            }

            var self = this;

            this._default = {
                time: 300 ,
                // 状态：show hide
                status: 'show' ,
                opacity: 0.2 ,
                maxWidth: '' ,
                title: '信息' ,
                margin: '10px' ,
                // 弹层打开后毁掉
                success: null ,
                // 点击确认按钮回调
                confirm: null ,
                // 点击取消按钮回调
                cancel: null ,
                // 点击关闭按钮回调
                close: null ,
                // 是否显示关闭按钮
                closeBtn: false ,
                // 容器元素
                container: document.body ,
                // 按钮列表
                btn: [
                    {
                        name: '确认' ,
                        callback: function(){
                            // 关闭当前弹窗
                            self.hide(self._confirm);
                        }
                    }
                ]
            };

            if (!G.isObject(opt)) {
                opt = this._default;
            }

            this._statusRange = ['show' , 'hide'];
            this._html = G.isString(html) ? html : '';
            this._time = G.isNumber(opt.time) ? opt.time : this._default.time;
            this._status = G.contain(opt.status , this._statusRange) ? opt.status : this._default.status;
            this._opacity = G.isNumber(opt.opacity) ? opt.opacity : this._default.opacity;
            this._margin = G.isString(opt.margin) ? opt.margin : this._default.margin;
            this._title = G.isString(opt.title) ? opt.title : this._default.title;
            this._maxWidth = G.isValid(opt.maxWidth) ? opt.maxWidth : this._default.maxWidth;
            this._btn = G.isArray(opt.btn) ? opt.btn : this._default.btn;
            this._container = G.isDom(opt.container) ? opt.container : this._default.container;
            this._success = G.isFunction(opt.success) ? opt.success : this._default.success;
            this._confirm = G.isFunction(opt.confirm) ? opt.confirm : this._default.confirm;
            this._cancel = G.isFunction(opt.cancel) ? opt.cancel : this._default.cancel;
            this._close = G.isFunction(opt.close) ? opt.close : this._default.close;
            this._closeBtn = G.isBoolean(opt.closeBtn) ? opt.closeBtn : this._default.closeBtn;

            this._run();
        }

        var p = Prompt;

        // 静态属性

        // 弹窗数量
        p.count = 0;
        // 实例
        p.instance = [];

        // 简易方法
        p.alert = function(msg , opt){
            return new Prompt(msg , opt);
        };

        p.del = function(instance){
            var i   = 0;
            var cur = null;
            for (; i < this.instance.length; ++i)
            {
                cur = this.instance[i];
                if (cur === instance) {
                    this.instance.splice(i , 1);
                    i--;
                }
            }
        };

        p.hide = function(){
            console.log(this.instance);
            this.instance.forEach(function(v){
                v.hide();
            });

            p.instance = [];
        };

        p.prototype = {
            constructor: p ,

            _initStaticHTML: function(){
                var self = this;
                this._con = G(this._container);
                var div = document.createElement('div');
                    div = G(div);
                    div.addClass(['prompt' , 'hide']);
                var html = '';
                    html += '   <!-- 背景颜色 -->';
                    html += '    <div class="bg"></div>';
                    html += '    <!-- 内容 -->';
                    html += '    <div class="con">';
                    html += '       <div class="header">';
                    html += '           <div class="left title">信息</div>';
                    html += '           <div class="right btns">';
                    html += '                <button class="btn close">X</button>';
                    html += '            </div>';
                    html += '        </div>';
                    html += '       <div class="msg">提示内容+++++++</div>';
                    html += '       <div class="btns">';
                    // html += '           <button class="btn btn-1">确认</button>';
                    // html += '           <button class="btn btn-1">你好</button>';
                    // html += '           <button class="btn btn-1">取消</button>';
                    html += '        </div>';
                    html += '    </div>';

                div.html(html);
                this._con.append(div.get(0));
                this._prompt    = div;
                this._con_      = G('.con' , this._prompt.get(0));
                this._btnsForCon = G('.btns' , this._con_.get(0));

                // 设置按钮
                this._btn.forEach(function(v){
                    var btn = document.createElement('button');
                        btn = G(btn);
                        btn.addClass(['btn']);
                        btn.text(v.name);
                        btn.on('click' , v.callback.bind(self) , true , false);
                    self._btnsForCon.append(btn.get(0));
                });
            } ,

            _initStaticArgs: function(){
                this._bg        = G('.bg' , this._prompt.get(0));
                this._header    = G('.header' , this._con_.get(0));
                this._title_     = G('.title' , this._header.get(0));
                this._btnsForHeader = G('.btns' , this._header.get(0));
                this._close_     = G('.close' , this._btnsForHeader.get(0));
                this._msg       = G('.msg' , this._con_.get(0));

                // 参数
                this._startOpacity      = 0;
                this._endOpacity        = this._opacity;
                this._startMarginTop    = this._margin;
                this._endMarginTop      = '0px';

                // 弹层层级
                this._zIndex = parseInt(this._prompt.getStyleVal('zIndex'));
                this._zIndex += p.count++;

            } ,

            _initStatic: function(){
                // 保存实例
                p.instance.push(this);

                // 设置层级
                this._prompt.css({
                    zIndex: this._zIndex
                });

                // 弹层可拖动
                this._con_.move(this._prompt.get(0) , true);

                // 错落感
                this._con_.css({
                    marginTop: this._margin
                });

                // 标题
                this._title_.text(this._title);

                // 内容
                this._msg.html(this._html);

                // 是否隐藏关闭按钮
                if (this._closeBtn) {
                    this._close_.addClass('hide');
                }

                // 显示隐藏
                if (this._status == 'show') {
                    this.show();
                } else {
                    this.hide();
                }
            } ,

            _initDynamicHTML: function(){

            } ,

            _initDynamicArgs: function(){

            } ,

            _initDynamic: function(){
                // 内容居中
                this._con_.center(this._prompt.get(0) , 'all');
            } ,

            // 显示对话框
            show: function(fn){
                var self = this;
                this._prompt.removeClass('hide');

                this._bg.animate({
                    opacity: this._endOpacity
                } , null , this._time);

                this._con_.animate({
                    marginTop: this._endMarginTop ,
                    opacity: 1
                } , function(){
                    self._initDynamic();

                    if (G.isFunction(self._success)) {
                        self._success();
                    }

                    if (G.isFunction(fn)) {
                        fn.call(this);
                    }
                } , this._time);
            } ,

            // 隐藏对话框
            hide: function(fn){
                var self = this;

                this._bg.animate({
                    opacity: this._startOpacity
                } , null , this._time);

                this._con_.animate({
                    marginTop: this._startMarginTop ,
                    opacity: 0
                } , function(){
                    // self._prompt.addClass('hide');

                    self._con.remove(self._prompt.get(0));
                    if (G.isFunction(self._close)) {
                        self._close();
                    }

                    if (G.isFunction(fn)) {
                        fn.call(self);
                    }
                } , this._time);
            } ,

            _defineEvent: function(){
                this._msg.on('mousedown' , G.stop , true , false);
                this._btnsForCon.on('mousedown' , G.stop , true , false);
                this._close_.on('click' , this.hide.bind(this) , true , false);
            } ,

            _run: function(){
                this._initStaticHTML();
                this._initStaticArgs();
                this._initStatic();
                this._initDynamicHTML();
                this._initDynamicArgs();
                this._initDynamic();
                this._defineEvent();
            }
        };

        if (!noGlobal) {
            window.Prompt = p;
        }

        return p;
    });
})();