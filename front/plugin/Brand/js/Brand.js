(function(){
    "use strict";

    function Brand(container , option)
    {
        if (!G.isDom(container)) {
            throw new Error('参数 1 类型错误');
        }
        if (G.isUndefined(option)) {
            option = this.default;
        }
        // 用户传递参数
        this.container = container;
        this.option = option;
        this.run();
    }

    Brand.prototype = {
        option: null ,
        // 容器元素
        container: null ,
        // 默认参数
        default: {
            // 插件地址
            pluginUrl: '' ,
            // 是否允许单选/多选切换
            multiple: true ,
            // 选中时回调
            checked: null ,
            // 取消选中时回调函数
            unchecked: null ,
            confirm: null ,
        } ,
        // 页面上要用到的 dom 元素
        dom: {} ,
        // 需要用到的值
        value: {} ,
        // 静态数据初始化
        initStatic: function(){
            /**
             * ***********************************
             * 值
             * ***********************************
             */
            // dom 元素
            this.dom.container = G(this.container);
            this.dom.brandContainer = G('.brand-container' , this.dom.container.get(0));
            this.dom.left = this.dom.brandContainer.children({
                tagName: 'div' ,
                className: 'left'
            } , false , true).first();
            this.dom.right = this.dom.brandContainer.children({
                tagName: 'div' ,
                className: 'right'
            } , false , true).first();
            this.dom.letter = G('.letter' , this.dom.left.get(0)).first();
            this.dom.brand = G('.brand' , this.dom.left.get(0)).first();
            this.dom.selected = G('.selected' , this.dom.left.get(0));
            this.dom.brandForSelected = G('.selected .brand' , this.dom.left.get(0)).first();
            this.dom.function = G('.function' , this.dom.left.get(0)).first();
            this.dom.confirm = G('.confirm' , this.dom.function.get(0)).first();
            this.dom.cancel = G('.cancel' , this.dom.function.get(0)).first();
            this.dom.ctrl = G('.ctrl' , this.dom.right.get(0)).first();
            this.dom.mode = G('.mode' , this.dom.right.get(0)).first();
            this.dom.all = G('.all' , this.dom.letter.get(0)).first();
            this.dom.hot = G('.hot' , this.dom.letter.get(0)).first();
            this.dom.letters = G('span' , this.dom.letter.get(0));
            this.dom.brands = G('.item' , this.dom.brand.get(0));

            // 单选模式还是多选模式
            this.option.multiple = G.isBoolean(this.option.multiple) ? this.option.multiple : this.default.multiple;

            // 展开按钮
            this.value.expand = '+展开';
            this.value.shrink = '-收缩';
            this.value.single = '单选';
            this.value.multiple = '多选';
            this.value.mode = 'single';
            this.value.brandH = this.dom.brand.height('border-box');
            // 缓存的 idList
            this.value.idList = [];

            /**
             * ***********************************
             * 样式初始化
             * ***********************************
             */
            if (!this.option.multiple) {
                this.dom.mode.addClass('hide');
            }
        } ,
        // 动态数据初始化
        initDynamic: function(){

        } ,

        // 字母悬浮事件
        letterMouseOverEvent: function(e){
            let tar = G(e.currentTarget);
            let type = tar.data('type');
            tar.highlight('cur' , this.dom.letters.get());
            if (type == 'all') {
                this.switchAll();
            } else if (type == 'hot') {
                this.switchHot();
            } else {
                // 字母
                this.switchBrand(tar.data('letter'));
            }

        } ,

        // 切换显示品牌
        switchBrand: function(letter){
            var i   = 0;
            var cur = null;
            for (; i < this.dom.brands.length; ++i)
            {
                cur = this.dom.brands.jump(i , true);
                if (cur.data('letter') == letter) {
                    cur.removeClass('hide');
                } else {
                    cur.addClass('hide');
                }
            }
        } ,

        // 切换热门品牌
        switchHot: function(){
            var i   = 0;
            var cur = null;
            for (; i < this.dom.brands.length; ++i)
            {
                cur = this.dom.brands.jump(i , true);
                if (cur.data('hot') == 'y') {
                    cur.removeClass('hide');
                } else {
                    cur.addClass('hide');
                }
            }
        } ,

        // 切换展示所有品牌
        switchAll: function(){
            this.dom.brands.removeClass('hide');
        } ,

        // 展开
        expand: function(){
            this.dom.ctrl.data('status' , 'expand');
            this.dom.ctrl.text(this.value.shrink);
            this.dom.brand.css({
                maxHeight: 'none'
            });
        } ,

        // 收缩
        shrink: function(){
            this.dom.ctrl.data('status' , 'shrink');
            this.dom.ctrl.text(this.value.expand);
            this.dom.brand.css({
                maxHeight: this.value.brandH + 'px'
            });
        } ,

        // 展开/收缩 控制
        ctrlClickEvent: function(e){
            var tar = G(e.currentTarget);
            var status = tar.data('status');
            if (status != 'expand') {
                this.expand();
            } else {
                this.shrink();
            }
        } ,

        // 通过 id 获取项
        findById: function(id){
            var i   = 0;
            var cur = null;
            for (; i < this.dom.brands.length; ++i)
            {
                cur = this.dom.brands.jump(i , true);
                if (cur.data('id') == id) {
                    return cur.get(0);
                }
            }
            return null;
        } ,

        // 获取选中项
        selected: function(){
            var i   = 0;
            var cur = null;
            let res = [];
            for (; i < this.dom.brands.length; ++i)
            {
                cur = this.dom.brands.jump(i , true);
                if (cur.data('checked') == 'y') {
                    res.push(cur.get(0));
                }
            }
            return res;
        } ,

        // 选中的 id 列表
        idList: function(){
            var i   = 0;
            var cur = null;
            let res = [];
            for (; i < this.dom.brands.length; ++i)
            {
                cur = this.dom.brands.jump(i , true);
                if (cur.data('checked') == 'y') {
                    res.push(cur.data('id'));
                }
            }
            return res;
        } ,

        // 选中品牌
        checked: function(id){
            var brand   = this.findById(id);
            if (G.isNull(brand)) {
                console.log('未找到 id = ' + id + '对应品牌，忽略');
                return ;
            }
            brand = G(brand);
            var status  = G('.status' , brand.get(0)).first();
            if (this.value.mode == 'single') {
                this.uncheckedAll();
            }
            brand.data('checked' , 'y');
            status.removeClass('hide');
            this.addSelected(id);
            if (this.value.mode == 'single') {
                if (G.isFunction(this.option.checked)) {
                    this.option.checked(id , this.idList());
                }
            }
        } ,

        // 取消选中品牌
        unchecked: function(id){
            var brand   = this.findById(id);
            if (G.isNull(brand)) {
                console.log('未找到 id = ' + id + '对应品牌，忽略');
                return ;
            }
            brand = G(brand);
            var status  = G('.status' , brand.get(0)).first();
            brand.data('checked' , 'n');
            status.addClass('hide');
            this.delSelected(id);
            if (this.value.mode == 'single') {
                if (G.isFunction(this.option.unchecked)) {
                    this.option.unchecked(id , this.idList());
                }
            }
        } ,

        // 添加记录到已选中项列表
        addSelected: function(id){
            var brand = G(this.findById(id));
            var name  = brand.data('name');
            var span = document.createElement('span');
            var self = this;
            span = G(span);
            span.data('id' , id);
            span.text(name);
            span.on('click' , function(){
                self.unchecked(id);
            } , true , false);

            this.dom.brandForSelected.append(span.get(0));
        } ,

        // 删除记录到已选择中项列表
        delSelected: function(id){
            var span    = G('span' , this.dom.brandForSelected.get(0));
            var i       = 0;
            var cur     = null;
            for (; i < span.length; ++i)
            {
                cur = span.jump(i , true);
                if (cur.data('id') == id) {
                    this.dom.brandForSelected.remove(cur.get(0));
                }
            }
        } ,

        // 品牌点击事件
        brandClickEvent: function(e){
            var tar = G(e.currentTarget);
            var checked = tar.data('checked');
            var id      = tar.data('id');
            if (checked != 'y') {
                this.checked(id);
            } else {
                this.unchecked(id);
            }
        } ,

        uncheckedAll: function(){
            var i   = 0;
            var cur = null;
            var id  = null;
            for (; i < this.dom.brands.length; ++i)
            {
                cur = this.dom.brands.jump(i , true);
                id  = cur.data('id');
                this.unchecked(id);
            }
        } ,

        modeForSingle: function(origin){
            origin = G.isBoolean(origin) ? origin : true;
            this.value.mode = 'single';
            this.dom.mode.text(this.value.multiple);
            this.dom.function.addClass('hide');
            this.dom.brandContainer.removeClass('focus');
            // 还原
            if (origin) {
                this.uncheckedAll();
                this.value.mode = 'multiple';
                for (var i = 0; i < this.value.idList.length; ++i)
                {
                    this.checked(this.value.idList[i]);
                }
                this.value.mode = 'single';
            }
        } ,

        modeForMultiple: function(){
            this.value.mode=  'multiple';
            this.dom.mode.text(this.value.single);
            this.dom.function.removeClass('hide');
            this.dom.brandContainer.addClass('focus');
            this.value.idList = this.idList();
        } ,

        modeClickEvent: function(){
            if (this.value.mode == 'single') {
                this.modeForMultiple();
            } else {
                this.modeForSingle();
            }
        } ,

        confirmClickEvent: function(){
            this.modeForSingle(false);
            if (G.isFunction(this.option.confirm)) {
                this.option.confirm(this.idList());
            }
        } ,

        // 多选初始化
        multiple: function(idList){
            this.value.mode = 'multiple';
            idList.forEach(function(v){
                this.checked(v);
            }.bind(this));
            this.value.mode = 'single';
        } ,

        // 定义相关事件
        defineEvent: function(){
            this.dom.letters.on('mouseover' , this.letterMouseOverEvent.bind(this) , true , false);
            this.dom.ctrl.on('click'        , this.ctrlClickEvent.bind(this) , true , false);
            this.dom.brands.on('click'      , this.brandClickEvent.bind(this) , true , false);
            this.dom.mode.on('click'        , this.modeClickEvent.bind(this) , true , false);
            this.dom.confirm.on('click'        , this.confirmClickEvent.bind(this) , true , false);
            this.dom.cancel.on('click'        , this.modeForSingle.bind(this) , true , false);
        } ,
        // 运行程序
        run: function(){
            this.initStatic();
            this.initDynamic();
            this.defineEvent();
        } ,
    };

    window.Brand = Brand;
})();