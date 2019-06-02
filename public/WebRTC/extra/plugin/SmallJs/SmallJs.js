/*
 * author 陈学龙 2016/10/23 18:09:00
 * SmallJS 框架！ version 1.0
 * 运行环境：IE 10+（包括 IE10，绝大部分支持 IE 9，但是涉及到最新 HTML5技术的全部不支持） 或同等级别的其他浏览器
 * 更新时间：
 1. 2016/10/23 18:09:00
 2. 2017/02/03 10:22:00
 3. 2017/02/03 13:40:00
 4. 2017/02/06 12:34:00
 5. 2017/02/06 23:25:00
 6. 2017/02/13 16:59:00
 7. 2017/02/14 23:19:00
 8. 2017/03/18 14:05:00
 9. 2017/03/18 14:05:00
 内容：Ajax 对象新增方法 get，返回 XMLHttpRequest 对象
 10. 2017/03/24 16:40:00
 内容：新增阻止事件冒泡|阻止默认事件发生
 11. 2017/03/24 20:05:00
 内容：修改 Ajax 请求的 url 模式（6个）
 12. 2017/03/25 14:34:00
 内容：parseTime（返回时间信息） + timestampDiff（获取两个时间点相差的毫秒数）
 13. 2017/04/01 12:49:00
 内容：元素选择器 bug ，无法选取带有 除了字母、下划线、数字意外字符的元素，修正正则
 14. 2017/04/09 16:36:00
 内容：完善 timeCount 函数
 15. 2017/05/12 15:14:00
 内容：添加基础函数库功能：根据生日计算出年龄（周岁）
 16. 2017/05/14 21:44:00
 内容：修改 Ajax 对象，新增 get 方法，获取 XMLHttpRequest 对象
 17. 2017/05/14 21:58:00
 内容：新增 formBlur 静态方法，使表单失去焦点（防止重复提交）
 18. 2017/05/14 21:58:00
 内容：选择器类型判定 bug 修复
 19. 2017/07/03 18:14:00
 内容：rem 布局需要用到的设置根元素字体
 20. 2017/07/19 14:15:00
 内容：
 1. 获取 data-attribute 属性的快捷方法 getAttr
 2. 设置 data-attribute 属性的快捷方法 setAttr
 21. 2017/07/22 10:07:00
 内容：
 1. 新增错误处理基础函数 throw
 22. 2107/07/29 16:44:00
 内容：
 1. 类数组对象转换成数组
 23. 2107/07/29 16:44:00
 内容：
 1. 增加滚动到底部 或 滚动到顶部（scroll）
 24. 2017/09/11 09:18:00
 内容：
 1. 升级功能 parentFind、childFind
 2. 新增功能 parentFindAll、children 、not、find
 3. 修复功能 bug： isDom
 25. 2017/10/10 13:34:00
 1. Ajax 请求都会添加一个 AJAX_REQUEST 的请求头
 26. 2017/11/17 23:53:00
 1. getDomList，获取一个对象，DOM 元素组成的数组
 2. domSwitch，交换 DOM 元素位置
 3. insertBefore，简化版的 insertBefore
 4. insertAfter，自定义节点操作函数
 27. 2017/11/18 17:19:00
 1. fromUnixtime  格式化 unix 时间戳
 2. unixTimestamp 返回 unix 时间戳
 3. 新增 parents，替代 parentFindAll
 28. 2017/11/19 08:51:00
 1. 新增 data 元素方法获取数据集属性
 2. withCredentials 允许跨域
 3. 优化 getDocOffsetVal 性能
 29. 2017-11-21 16:23:00
 1. XHR 新增错误处理
 30. 2017-11-22 15:46:00
 1. 新增容量显示(b、byte、kb、mb、gb、tb、pb)
 2. 新增 getFormData | appendFormData
 31. 2017-12-13 10:50:00
    1. 新增基础函数 getRadioVal
32. 2017-12-27 13:37:00
    1. g.getMonthDyas
    2. g.isLeapYear
33. 2018-01-07 15:04:00
    1. addClass 允许一次性添加多类名
34. 2018-01-13 09:58:00
    1. highlight 新增第三个参数
    2. focus 获取一个集合中具有指定属性名的元素 current
35 2018-01-15 14:53:00
    1. 新增 scrollLoad 滚动加载函数
36 2018-01-27 09:11:00
    1. css 支持集合元素批量设置样式！
37 2018-01-29 16:59:00
    1. 修改 getEleindex 为 index
38 2018-02-01 16:14:00
    1. 修改 g.scroll 函数，新增 g.top、g.bottom 函数等页面滚动处理函数
39. 2018-05-14 09:26:00
    1. 新增光标控制函数： getCursorPoint、setCursorPoint、getCursorPointForContentEditableElement、getCursorPointForInput、setCursorPointForInput、setCursorPointForInput
    2. 新增 isDomm、isDoms 等函数
    3. 修复元素查找属性查找不到的 bug
40. 2018-07-22 22:38:00
    完善 Ajax 生命周期钩子
41. 2019-02-16 21:06
    1. 完善 Ajax，data 类型允许 Blob 类型的数据
    2. 优化 Ajax 设置请求头部分代码
	3. Http 动词全部统一大写！且不做限制
 命名规则：
 1. 变量：首字母小写 + 驼峰法
 2. 函数：首字母小写 + 驼峰法
 2.1 函数参数：1. 无扩展性的采取 function(args1 , args2 ..){....}
 2. 考虑到将来有可能会进行扩展的采取
 2.1 function(option){...}
 2.1.1 option = {key:val}
 2.2 function (){....}
 2.2.1 内部使用 arguments 对象来操作参数
 3. 搜索型：function search(val , range){..}					  // 待搜索的值   ， 搜索的范围
 4. 追加型：function add(obj , val){...}						  // 待追加的对象 ， 追加的值
 5. 替换型：function replace(obj , originalVal , endVal)		  // 待操作的对象 ， 原值		     ，最终值
 6. 删除型：function remove(obj , val){...}					  // 待删除的对象 ， 待删除的值
 7. 切换型：function switchCn(focusCn , focusEle , list){...}  // 命中元素的类名 ，命中元素 ， 元素所在的集合
 8. 取值型：function getDocOffsetVal(doc , type){...}
 3. 类：  首字母大小 + 驼峰法
 3.1 类参数： 采取 {key:val} 格式（无扩展：直接参数 args1 , args2 ....）
 4. 类属性： 私有属性|受保护属性 _ + 首字母小写 + 驼峰法
 5. 类方法： 私有方法|受保护方法 _ + 首字母小写 + 驼峰法
 * 使用时注意： 前面带有 _ 的属性或方法，全都是不允许调用的（虽然实际上是可执行的，但是不赞成调用！因为调用后得到的结果未知....）！
 */

(function(window){

    "use strict";

    /*
     * ********
     构造函数
     * ********
     */
    function SmallJs(selector , context){
        var thisRange = [window , undefined , null];

        if (g.contain(this , thisRange) || this.constructor !== SmallJs) {
            return new SmallJs(selector , context);
        }
        // 使用时注意
        this._cur = this._get(selector , context);

        this.length = this._cur.length;

        this._run();
    }

    // 简略缩写
    var g = SmallJs;

    /*
     * ************
     构造函数原型
     * ************
     */
    g.pro = g.prototype = {
        // author
        author: '陈学龙' ,

        // 版本
        version: '1.0' ,

        // 构造函数
        constructor: SmallJs ,

        length: 0 ,

        // 当前引用的DOM对象
        _cur: null ,

        // 队列
        _queue: null ,

        _initialize: function(){
            this.loop(function(dom){
                if (g.isUndefined(dom.__smalljs_queue__)) {
                    // 为每一个 dom 元素设置一个队列
                    dom.__smalljs_queue__ = new Queue();
                }
            });
        } ,

        _run: function(){
            this._initialize();
        } ,

        // 获取 or 设置 transform
        transform: function(k , v){
            var self = this;
            var prefix  = ['' , 'webkit' , 'moz' , 'ms' , 'o'];
            if ((g.isUndefined(v) && !g.isObject(k))) {
                var i       = 0;
                var cur     = null;
                var transK  = null;
                var transV  = null;
                for (; i < prefix.length; ++i)
                {
                    cur = prefix[i];
                    transK   = cur === '' ? 'transform' : cur + 'Transform';
                    transV = this.css(transK);
                    if (G.isValid(transV)) {
                        break;
                    }
                }
                var returnObj = G.isBoolean(k) ? k : true;
                return returnObj ? g.parseTransform(transV) : transV;
            }
            // 设置样式
            this.each(function(dom){
                dom = g(dom);
                var val = '';
                if (G.isValid(v)) {
                    val = k + '(' + v + ')'; //+ ' translate3d(0px , 0px , 0px)';
                } else {
                    var cur = null;
                    var k1  = null;
                    for (k1 in k)
                    {
                        cur = k[k1];
                        val += k1 + '(' + cur + ') ';
                    }
                    val = val.replace(/ ^/ , '');
                    // val += ' translate3d(0px , 0px , 0px)';
                }
                prefix.forEach(function(p){
                    var key = p === '' ? 'transform' : p + 'Transform';
                    dom.css(key , val);
                });
            });
        } ,

        // 是否 dom
        isDom: function(){
            return g.isDom(this.get(0));
        } ,

        isDoms: function(){
            return this._cur.every(function(dom){
                return g.isDom(dom);
            });
        } ,

        // 队列操作
        _consume: function(queue , context , operation , merge , before , callback , after){
            merge = g.isBoolean(merge) ? merge : false;
            before = g.isArray(before) ? before : [];
            after = g.isArray(after) ? after : [];

            queue.push({
                context: context ,
                operation: operation ,
                merge: merge ,
                before: before ,
                callback: callback  ,
                after: after
            });

            queue.consume(function(task , resolve){
                // 合并操作
                var callback = function(){
                    if (g.isFunction(task.callback.callback)) {
                        task.callback.callback();
                    }

                    // 必须调用此钩子才能继续消费队列!!
                    resolve();
                };

                if (task.merge) {
                    task.before[0][task.callback.name] = callback;
                } else {
                    task.before.push(callback);
                }

                var args = task.before;

                task.after.forEach(function(v){
                    args.push(v);
                });

                // 判断是否合并参数
                task.operation.apply(task.context , args);
            });
        } ,

        _selector: function(selector , context){
            return !context ? document.querySelectorAll(selector) : context.querySelectorAll(selector);
        } ,

        // 针对表单的特殊事件（仅表单有效）
        selected: function(val){
            if (g.isUndefined(val)) {
                return this.get(0).selected;
            }

            val = g.isBoolean(val) ? val : true;

            this.loop(function(dom){
                dom.selected = val;
            });
        } ,

        checked: function(val){
            if (g.isUndefined(val)) {
                return this.get(0).checked;
            }

            val = g.isBoolean(val) ? val : true;

            this.loop(function(dom){
                dom.checked = val;
            });
        } ,

        // 触发函数原生的事件
        trigger: function(event){
            var self = this;
            var args = arguments;
            args = g.array(args);
            args = args.slice(1);

            this.loop(function(dom){
                dom[event].apply(dom , args);
            });
        } ,

        // 调用对象原生方法
        origin: function(event){
            var ctx = this.get(0);
            var args = arguments;
            args = g.array(args);
            args = args.slice(1);
            return ctx[event].apply(ctx , args);
        } ,

        /*
         * 1. 获取 DOM 元素
         * 2. 转换为 SmallJs 对象，并将 _cur 属性设置为当前元素
         */
        _get: function(selector , context){
            var res = [];

            if (!g.contain(g.type(selector) , ['Null' , 'Undefined'])) {
                if (g.isDom(selector)) {
                    res = [selector];
                } else if (g.isDoms(selector)) {
                    res = g.array(selector);
                } else if (g.isObj(selector)) {
                    res = [selector];
                } else if (g.isString(selector)) {
                    res = this._selector(selector , context);
                    res = g.array(res);
                } else {
                    throw new Error('当前提供的选择器格式错误：' + selector);
                }
            }

            return res;
        } ,

        // 向当前元素集合增加元素
        push: function(dom , isCopy){
            isCopy = g.isBoolean(isCopy) ? isCopy : true;

            if (isCopy) {
                var res = g.copy(this._cur);
                res.push(dom);
                return g(res);
            }

            this._cur.push(dom);
            return this;
        } ,

        unshift: function(dom , isCopy){
            isCopy = g.isBoolean(isCopy) ? isCopy : true;

            if (isCopy) {
                var res = g.copy(this._cur);
                res.unshift(dom);
                return g(res);
            }

            this._cur.unshift(dom);
            return this;
        } ,

        // 获取 DOM 元素|集合，可直接使用 Javascript 原生语法
        get: function(index){
            return g.isInt(index) ? this._cur[index] : this._cur;
        } ,

        files: function(){
            return this.get(0).files;
        } ,

        // loop 的别名
        each: function(fn){
            this.loop(fn);
        } ,

        // 循环给定元素
        // 统一 api 是为了以后升级用
        loop: function(fn){
            var self = this;

            this._cur.forEach(function(v , k , o){
                // 提供给回调函数三个参数
                // v 键值
                // k 键名
                // o 原数组
                fn.call(self , v , k , o);
            });
        } ,

        // 检查元素内滚动条是否在顶部
        isTop: function(){
            return this.scrollTop() === 0;
        } ,

        // 检查元素滚动条是否在底部
        isBottom: function(){
            var clientH = this.clientHeight();
            var maxH    = this.scrollHeight();
            var maxT    = Math.floor(maxH - clientH);
            var curT    = this.scrollTop();
            curT    = Math.ceil(curT);

            return curT >= maxT;
        } ,

        // 检查元素是否在最左边
        isLeft: function(){
            return this.scrollLeft() === 0;
        } ,

        // 检查元素是否在最左边
        isRight: function(){
            var clientW = this.clientWidth();
            var maxW    = this.scrollWidth();
            var maxL    = maxW - clientW;
            var curL    = this.scrollLeft();
            curL    = Math.ceil(curL);

            return curL >= maxL;
        } ,

        // 文本
        text: function(text){
            if (g.isUndefined(text)) {
                return this.get(0).textContent;
            }

            this.loop(function(dom){
                dom.textContent = text;
            });

            return this;
        } ,

        // 值
        val: function(v){
            if (g.isUndefined(v)) {
                return this.get(0).value;
            }

            this.loop(function(dom){
                dom.value = v;
            });

            return this;
        } ,

        // html
        html: function(html , type){
            var range = ['inner' , 'outer'];
            type = g.contain(type , range) ? type : 'inner';

            if (g.isNull(html) || g.isUndefined(html)) {
                return type === 'inner' ? this._cur.innerHTML : this._cur.outerHTML;
            }

            this.loop(function (dom) {
                if (type === 'inner') {
                    dom.innerHTML = html;
                } else {
                    dom.outerHTML = html;
                }
            });

            return this;
        } ,

        // 添加节点
        append: function(node){
            this.loop(function (dom) {
                dom.appendChild(node);
            });

            return this;
        } ,

        // 移除节点
        remove: function(dom){
            return g(this.get(0).removeChild(dom));
        } ,

        // 插入节点
        insertBefore: function(node){
            this.loop(function(dom){
                dom.parentNode.insertBefore(node , dom);
            });

            return this;
        } ,

        // 克隆节点
        clone: function(deep){
            return g(g.clone(this.get(0) , true));
        } ,

        // 滚动到顶部
        top: function(time , fn){
            this.loop(function(dom){
                // g.top(dom , time , fn);
                this._consume(dom.__smalljs_queue__ , g , g.top , false , [dom , time] , {
                    name: '' ,
                    callback: fn
                });
            });

            return this;
        } ,

        // 滚动到底部
        bottom: function(time , fn){
            this.loop(function(dom){
                this._consume(dom.__smalljs_queue__ , g , g.bottom , false , [dom , time] , {
                    name: '' ,
                    callback: fn
                });
            });

            return this;
        } ,

        // 滚动到最左边
        left: function(time , fn){
            var self = this;

            this.loop(function(dom){
                this._consume(dom.__smalljs_queue__ , g , g.left , false , [dom , time] , {
                    name: '' ,
                    callback: fn
                });
            });

            return this;
        } ,

        // 滚动到最右边
        right: function(time , fn){
            this.loop(function(dom){
                this._consume(dom.__smalljs_queue__ , g , g.right , false , [dom , time] , {
                    name: '' ,
                    callback: fn
                });
            });

            return this;
        } ,

        // 水平滚动
        hScroll: function(time , v , fn){
            this.loop(function(dom){
                this._consume(dom.__smalljs_queue__ , g , g.hScroll , false , [dom , time , v] , {
                    name: '' ,
                    callback: fn
                });
            });

            return this;
        } ,

        // 垂直滚动
        vScroll: function(time , v , fn){
            this.loop(function(dom){
                this._consume(dom.__smalljs_queue__ , g , g.vScroll , false , [dom , time , v] , {
                    name: '' ,
                    callback: fn
                });
            });

            return this;
        } ,

        // 滚动到指定位置
        scroll: function(time , pos , x , y , fn) {
            this.loop(function(dom){
                // queue , context , operation , merge , before , callback , after
                this._consume(dom.__smalljs_queue__ , g , g.scroll , false , [dom , time , pos , x , y] , {
                    name: '' ,
                    callback: fn
                });
            });

            return this;
        } ,

        // 元素滚动到可见位置
        // extra 额外要减去的高度
        scrollIntoView: function(selector , time , pos , extra , fn){
            var container = g(selector);

            container = container.isDom() ? container : this.scrollParent();

            var containerSW = container.scrollWidth();
            var containerSH = container.scrollHeight();
            var containerCW = container.width('padding-box');
            var containerCH = container.height('padding-box');
            var maxSW       = containerSW - containerCW;
            var maxSH       = containerSH - containerCH;

            maxSW = Math.max(0 , Math.min(maxSW , containerSW));
            maxSH = Math.max(0 , Math.min(maxSH , containerSH));

            extra = !g.isValid(extra) ? {x: 0 , y: 0} : extra;

            switch (pos)
            {
                case 'x':
                    extra = {
                        x: extra ,
                        y: 0
                    };
                    break;
                case 'y':
                    extra = {
                        x: 0 ,
                        y: extra
                    };
                    break;
                case 'all':
                    break;
                default:
                    break;
            }

            var dom     = this.first(true);
            var domOT   = dom.getDocOffsetVal('top' , container.get(0));
            var domOL   = dom.getDocOffsetVal('left' , container.get(0));
            domOT   = domOT - extra.y;
            domOL   = domOL - extra.x;
            var x = Math.min(domOL ,  maxSW);
            var y = Math.min(domOT , maxSH);

            if (container.eq(document.body)) {
                g.scrollTo(time , pos , x , y , fn);
            } else {
                container.scroll(time , pos , x , y , fn);
            }
        } ,

        // 获取父元素中第一个带有滚动条
        scrollParent: function(){
            var parents = this.parents();
            var i   = 0;
            var cur = null;

            for (; i < parents.length; ++i)
            {
                cur = parents.jump(i , true);

                if (cur.width('padding-box') > cur.scrollWidth() || cur.height('padding-box') > cur.scrollHeight()) {
                    return cur;
                }
            }

            return g(document.body);
        } ,

        getAttr: function(attr){
            console.warn('请使用 attr 代替');
            return this.attr(attr);
        } ,

        setAttr: function(attr , val) {
            console.warn('请使用 attr 代替');
            return this.attr(attr , val);
        } ,

        // 获取属性
        attr: function(attr , val){
            if (g.isUndefined(val)) {
                return this.get(0).getAttribute(attr);
            }

            this.loop(function (dom) {
                dom.setAttribute(attr , val);
            });

            return this;
        } ,

        // 获取原生属性
        native: function(attr , val){
            if (g.isUndefined(val)) {
                return this.get(0)[attr];
            }

            this.loop(function(dom){
                dom[attr] = val;
            });

            return this;
        } ,

        // 获取|设置数据集属性
        data: function(attr , val){
            attr = 'data-' + attr;

            return this.attr(attr , val);
        } ,

        /*
         * 指针移动：第一个元素
         * @param  Boolean  isReturnNewObj 是否返回导航后副本
         * @return Object
         */
        first: function(isCopy){
            isCopy = g.isBoolean(isCopy) ? isCopy : false;

            if (!isCopy) {
                this._cur = [this.get(0)];
                this.length = this._cur.length;

                return this;
            }

            return g(this.get(0));
        } ,

        /*
         * 指针移动：最后一个元素
         * @param  Boolean  isReturnNewObj 是否返回导航后副本
         * @return Object
         */
        last: function(isCopy){
            isCopy = g.isBoolean(isCopy) ? isCopy : false;

            var lastIndex = this._cur.length - 1;

            if (!isCopy) {
                this._cur = [this.get(lastIndex)];
                this.length = this._cur.length;

                return this;
            }

            return g(this.get(lastIndex));
        } ,

        /*
         * 指针移动：指定元素
         * 1. index 类型不是 number 时，返回 第一个元素
         * 2. index 的大小 大于等于 总长度时 ，返回最后一个元素
         */
        jump: function(index , isCopy){
            var min = 0;
            var max = this.length;
            index = !g.isInt(index) ? min : (index > max ? max : (index < min ? min : index));
            isCopy = g.isBoolean(isCopy) ? isCopy : false;

            if (!isCopy) {
                this._cur   = [this.get(index)];
                this.length = this._cur.length;

                return this;
            }

            return g(this.get(index));
        } ,

        /*
         * 对象合并（继承），不建议使用。未经测试！
         * @param1 HTMLElement src
         * @param2 Object      copy
         * ....
         * @return 继承 SmallJs 后的 HTMLElement
         */
        _merge: function(){
            if (g.isDoms(this._cur)) {
                throw new Error('当前指针所指向的是一个 DOM 元素集合！不是单一的元素！请设置单一元素后在进行合并！');
            }

            var src       = arguments[0];
            var list      = g.mergeObj.apply(null , g.array(arguments , 1));
            for (var key in list)
            {
                if (g.type(src[key]) === 'Undefined') {
                    if (g.type(list[key]) === 'Array') {
                        src[key] = [];
                        this._merge(src[key] , list[key]);
                    } else if (g.type(list[key]) === 'Object') {
                        src[key] = {};
                        this._merge(src[key] , list[key]);
                    } else {
                        src[key] = list[key];
                    }
                }
            }
            return src;

        } ,

        // 获取样式值
        getStyleVal: function(attr , pseudo){
            pseudo = g.isUndefined(pseudo) ? null : pseudo;
            return window.getComputedStyle(this.get(0) , pseudo)[attr];
        } ,

        // 设置元素 Css 样式
        css: function(key , val){
            if (!g.isValid(val) && g.isString(key)) {
                return this.getStyleVal(key);
            }

            var json = {};
            if (g.isObject(key)) {
                json = key;
            } else {
                json[key] = val;
            }

            this.loop(function(dom){
                var key  = null;

                for (key in json)
                {
                    dom.style[key] = json[key];
                }
            });

            return this;
        } ,
        // 第一个设置了除 position: static 以外的父元素
        offsetParent: function(){
            return g(this.get(0).offsetParent);
        } ,
        // 只读属性
        offsetLeft: function(){
            return this.get(0).offsetLeft;
        } ,
        // 只读属性: 相对于 offsetParent
        offsetTop: function(){
            return this.get(0).offsetTop;
        } ,
        // 只读属性: border + padding +content
        offsetWidth: function(){
            return this.get(0).offsetWidth;
        } ,
        // 只读属性: border + padding +content
        offsetHeight: function(){
            return this.get(0).offsetHeight;
        } ,
        // 只读属性
        clientWidth: function(){
            return this.get(0).clientWidth;
        } ,
        // 只读属性
        clientHeight: function(){
            return this.get(0).clientHeight;
        } ,
        // 只读属性
        scrollWidth: function(){
            return this.get(0).scrollWidth;
        } ,
        // 只读属性
        scrollHeight: function(){
            return this.get(0).scrollHeight;
        } ,
        scrollTop: function(val){
            if (!g.isNumber(val)) {
                return this.get(0).scrollTop;
            }

            this.get(0).scrollTop = val;

            return this;
        } ,
        scrollLeft: function(val){
            if (!g.isNumber(val)) {
                return this.get(0).scrollLeft;
            }

            this.get(0).scrollLeft = val;

            return this;
        } ,
        /*
         * 获取盒子 宽度
         * @param   Element  dom       待获取的元素
         * @param   String   boxType   待获取元素的盒子类型，支持 content-box | padding-box | border-box | true <=> content-box
         * @return  Number
         */
        width: function(boxType){
            // 盒子类型
            var boxRange = ['border-box' , 'padding-box' , 'content-box'];
            boxType =  g.contain(boxType , boxRange) ? boxType : 'content-box';
            // 宽度
            var w = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('width'))));
            // padding
            var pl = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('paddingLeft'))));
            var pr = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('paddingRight'))));
            // 边框
            var bl = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('borderLeftWidth'))));
            var br = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('borderRightWidth'))));
            // 当前的元素 box-sizing 值
            var boxSizing;
            var bReg;

            w  = isNaN(w)  ? 0 : w;
            pl = isNaN(pl) ? 0 : pl;
            pr = isNaN(pr) ? 0 : pr;
            bl = isNaN(bl) ? 0 : bl;
            br = isNaN(br) ? 0 : br;

            // box-sizing
            boxSizing = this.getStyleVal('boxSizing');

            /*
             * 浏览器检测
             * 比较奇葩的是：
             * 例如， dom => box-sizing:border-box;
             * 在 chrome 内核浏览器 或 firefox 内核浏览器中， width = width + padding + border ; height = height + padding + border
             * 但是！在 ie 内核浏览器中， width = width ; height = height
             * 所以，在计算高度的时候，需要区别对待
             */
            bReg = /ie|edge/i;

            if (boxType === 'border-box') {
                if (boxSizing == 'content-box') {
                    return w + pl + pr + bl + br;
                }

                if (boxSizing == 'border-box') {
                    return bReg.test(browser) ? w + pl + pr + bl + br : w;
                }
            }

            if (boxType === 'padding-box') {
                if (boxSizing == 'content-box') {
                    return w + pl + pr;
                }

                if (boxSizing == 'border-box') {
                    return bReg.test(browser) ? w + pl + pr : w - bl - br;
                }
            }

            if (boxType === 'content-box') {
                if (boxSizing == 'content-box') {
                    return w;
                }

                if (boxSizing == 'border-box') {
                    return bReg.test(browser) ? w : w - bl - br - pl - pr;
                }
            }

            return false;
        } ,

        /*
         * 获取盒子 高度
         * @param   Element  dom       待获取的元素
         * @param   String   boxType   待获取元素的盒子类型，支持 content-box | padding-box | border-box | true <=> content-box
         * @return  Number
         */
        height: function(boxType){
            // 盒子类型
            var boxRange = ['border-box' , 'padding-box' , 'content-box'];
            boxType =  g.contain(boxType , boxRange) ? boxType : 'content-box';

            // 高度
            var h = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('height'))));
            // padding
            var pt = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('paddingTop'))));
            var pb = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('paddingBottom'))));
            // 边框
            var bt = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('borderTopWidth'))));
            var bb = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('borderBottomWidth'))));
            // 盒子类型范围
            var boxRange = ['border-box' , 'padding-box' , 'content-box'];
            // 当前的元素 box-sizing 值
            var boxSizing;
            var bReg;

            h  = isNaN(h)  ? 0 : h;
            pt = isNaN(pt) ? 0 : pt;
            pb = isNaN(pb) ? 0 : pb;
            bt = isNaN(bt) ? 0 : bt;
            bb = isNaN(bb) ? 0 : bb;

            bReg      = /ie|edge/i;
            // box-sizing
            boxSizing = this.getStyleVal('boxSizing');

            if (boxType === 'border-box') {
                if (boxSizing === 'content-box') {
                    return h + pt + pb + bt + bb;
                }

                if (boxSizing === 'border-box') {
                    return bReg.test(browser) ? h + pt + pb + bt + bb : h;
                }
            }

            if (boxType === 'padding-box') {
                if (boxSizing === 'content-box') {
                    return h + pt + pb;
                }

                if (boxSizing === 'border-box') {
                    return bReg.test(browser) ? h + pt + pb : h - bt - bb;
                }
            }

            if (boxType === 'content-box') {
                if (boxSizing == 'content-box') {
                    return h;
                }

                if (boxSizing == 'border-box') {
                    return bReg.test(browser) ? h : h - bt - bb - pt - pb;
                }
            }

            return false;
        } ,

        /*
         * 获取盒子 总宽度(margin + border + padding + width)
         * @return  Number
         */
        getTW: function(){
            // 宽度
            var w = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('width'))));
            // padding
            var pl = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('paddingLeft'))));
            var pr = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('paddingRight'))));
            // 边框
            var bl = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('borderLeftWidth'))));
            var br = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('borderRightWidth'))));
            // margin
            var ml = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('marginLeft'))));
            var mr = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('marginRight'))));
            var boxSizing;
            var bReg = /ie|edge/i;

            w  = isNaN(w)  ? 0 : w;
            pl = isNaN(pl) ? 0 : pl;
            pr = isNaN(pr) ? 0 : pr;
            bl = isNaN(bl) ? 0 : bl;
            br = isNaN(br) ? 0 : br;
            ml = isNaN(ml) ? 0 : ml;
            mr = isNaN(mr) ? 0 : mr;

            // box-sizing
            boxSizing = this.getStyleVal('boxSizing');

            if (boxSizing == 'content-box') {
                return  w + pl + pr + bl + br + ml + mr;
            }

            if (boxSizing == 'border-box') {
                return bReg.test(browser) ? w + pl + pr + bl + br + ml + mr : w + ml + mr;
            }

            return false;
        } ,


        /*
         * 获取盒子 总高度(margin + border + padding + height)
         * @return  Number
         */
        getTH: function(){
            // 高度
            var h = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('height'))));
            // padding
            var pt = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('paddingTop'))));
            var pb = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('paddingBottom'))));
            // 边框
            var bt = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('borderTopWidth'))));
            var bb = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('borderBottomWidth'))));
            // margin
            var mt = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('marginTop'))));
            var mb = Math.max(0 , Math.floor(parseFloat(this.getStyleVal('marginBottom'))));
            var boxSizing;
            var bReg = /ie|edge/i;

            h  = isNaN(h) ? 0  : h;
            pt = isNaN(pt) ? 0 : pt;
            pb = isNaN(pb) ? 0 : pb;
            bt = isNaN(bt) ? 0 : bt;
            bb = isNaN(bb) ? 0 : bb;
            mt = isNaN(mt) ? 0 : mt;
            mb = isNaN(mb) ? 0 : mb;

            // box-sizing
            boxSizing = this.getStyleVal('boxSizing');

            if (boxSizing == 'content-box') {
                return h + pt + pb + bt + bb + mt + mb;
            }

            if (boxSizing == 'border-box') {
                return bReg.test(browser) ? h + pt + pb + bt + bb + mt + mb : h + mt + mb;
            }

            return false;
        } ,

        // 获取样式位置值
        getCoordVal: function(type){
            var typeRange = ['left' , 'top' , 'right' , 'bottom' , 'marginLeft' , 'marginRight' , 'marginTop' , 'marginBottom'];

            if (!g.contain(type , typeRange)) {
                throw new RangeError('不支持的位置，受支持的位置有：' + typeRange.join(' '));
            }

            return parseFloat(this.getStyleVal(type));
        } ,

        /*
         * 视口坐标
         * 获取相对于浏览器窗口 可见区域左上角的 left top
         * 有滚动条时，结果依旧正确！放心使用
         * @param  Element dom   待获取元素
         * @param  String  type  获取的类型
         * @return Number
         */
        getWindowOffsetVal: function(type){
            var typeRange = ['left' , 'right' , 'top' , 'bottom' , 'all'];
            var dom	      = this._cur;
            type = g.contain(type , typeRange) ? type : 'all';

            if (type === 'left') {
                return this.get(0).getBoundingClientRect().left;
            }

            if (type === 'right') {
                return this.get(0).getBoundingClientRect().right;
            }

            if (type === 'top') {
                return this.get(0).getBoundingClientRect().top;
            }

            if (type === 'bottom') {
                return this.get(0).getBoundingClientRect().bottom;
            }

            var rect = this.get(0).getBoundingClientRect();

            return {
                left: rect.left  ,
                right: rect.right  ,
                top:  rect.top ,
                bottom:  rect.bottom
            }
        } ,

        // 元素比较
        eq: function(dom){
            return this.get(0) === dom;
        } ,

        /*
         * 文档坐标
         * 获取相对于文档左上角的 left top
         * 有滚动条时，结果依旧正确！放心使用
         * @param  Element dom   待获取元素
         * @param  String  type  获取的类型
         * @return Number
         */
        getDocOffsetVal: function(type , until){
            var typeRange = ['left' , 'top' , 'all'];

            until   = g.isDom(until) ? until : document.body;
            until   = g(until);
            type    = g.contain(type , typeRange) ? type : 'all';

            var dom		    = this.first(true);
            var parent      = null;
            var oPosition   = null;
            var posTypeRange = ['relative' , 'absolute' , 'fixed'];
            var isCssDefine= true;
            var leftVal = 0;
            var topVal  = 0;

            while (dom.isDom() && !dom.eq(until.get(0)))
            {
                // 以下代码会导致页面回流，严重影响性能！
                // 故而不能使用！
                // parent = dom.parent();
                // oPosition = parent.getStyleVal('position');
                // // 父元素设置为 position
                // // 这样才有 offsetTop or offsetleft 值
                // isCssDefine = g.contain(oPosition , posTypeRange);
                // if (!isCssDefine) {
                //     parent.css({
                //         position: 'relative'
                //     });
                // }
                leftVal += dom.offsetLeft();
                topVal  += dom.offsetTop();
                // 会导致页面回流，故而不能使用
                // 重置回原始值
                // if (!isCssDefine) {
                //     parent.css({
                //         position: oPosition
                //     });
                // }
                dom = dom.offsetParent();
            }

            switch (type)
            {
                case 'left': return leftVal;
                case 'top': return topVal;
                case 'all': return {
                    left: leftVal ,
                    top:  topVal
                };
            }
        } ,

        offset: function(){

        } ,

        // on 的别名
        loginEvent: function(){
            console.warn('loginEvent 已经不推荐使用，请使用 on 代替');
            this.on.apply(this , arguments);
        } ,

        /*
         * 事件注册，支持批量操作
         * @param String      type
         * @param Function    fn
         * @param Boolean     isRepeat
         * @param Boolean     isCaptureCatch | option
         * @return undefined
         */
        on: function(type , fn , isRepeat , mixed){
            this.loop(function(dom){
                g.$e.bind(dom , type , fn , isRepeat , mixed);
            });
            return this;
        } ,

        // 元素方式检查是否存在某父元素
        existsParent: function(parent){
            var pNode = this.get(0).parentNode;

            while (pNode)
            {
                if (pNode === parent) {
                    return true;
                }

                if (pNode === document.documentElement) {
                    break;
                }

                pNode = pNode.parentNode;
            }

            return false;
        } ,

        // 元素方式检查是否存在某子元素
        existsChild: function(child){
            var parent  = this.get(0);
            var res     = false;
            var find = function(parent){
                if (res) {
                    return ;
                }

                var cNodes  = parent.children;
                var i       = 0;
                var cur     = null;

                for (; i < cNodes.length; ++i)
                {
                    cur = cNodes[i];
                    if (cur === child) {
                        res = true;
                        return ;
                    }

                    if (cur.childElementCount !== 0) {
                        find(cur);
                    }
                }
            };

            find(parent);

            return res;
        } ,

        // 元素方式检查存在某兄弟元素
        existsSibling: function(sibling){

            if (this.existsTopSibling(sibling)) {
                return true;
            }

            if (this.existsBtmSibling(sibling)) {
                return true;
            }

            return false;
        } ,

        // 水平向上查找检查是否存在某元素的某兄弟元素
        existsTopSibling: function(sibling){
            var prev = this.get(0).previousElementSibling;

            while (prev)
            {
                if (prev === sibling) {
                    return true;
                }

                prev = prev.previousElementSibling;
            }

            return false;
        } ,

        // 水平向下查找检查是否存在某元素的某兄弟元素
        existsBtmSibling: function(sibling){
            var next = this.get(0).nextElementSibling;

            while (next)
            {
                if (next === sibling) {
                    return true;
                }

                next = next.nextElementSibling;
            }

            return false;
        } ,

        /*
         * 返回当前元素的直系子元素集中具有指定特征的子集
         * @param Object  json      属性
         * @param Boolean isStrict  是否严格查询
         * @param Boolean isCopy    是否生成副本
         */
        children: function(json , isStrict , isCopy){
            json        = g.isObj(json) && !g.isNull(json) ? json : {};
            isStrict    = g.isBoolean(isStrict) ? isStrict : false;
            isCopy      = g.isBoolean(isCopy) ? isCopy : true;

            var res 			= [];
            var cur 			= null;
            var attrVal 		= '';
            var checkVal 		= '';
            var i				= 0;
            var key				= null;
            var children		= this.get(0).children;
            var isSatisfy		= true;

            for (i = 0; i < children.length; ++i)
            {
                cur 		= children[i];
                isSatisfy	= true;
                for (key in json)
                {
                    attrVal 	 = g.isValid(cur[key]) ? cur[key] : cur.getAttribute(key);
                    checkVal 	 = json[key];

                    if (isStrict) {
                        isSatisfy = attrVal === checkVal;
                    } else {
                        attrVal     = g.isString(attrVal) ? attrVal.toLowerCase() : attrVal;
                        checkVal    = g.isString(checkVal) ? checkVal.toLowerCase() : checkVal;
                        isSatisfy   = g.isString(attrVal) ? attrVal.search(checkVal) !== -1 : attrVal == checkVal;
                    }

                    if (!isSatisfy) {
                        break;
                    }
                }

                if (isSatisfy) {
                    res.push(cur);
                }
            }

            if (!isCopy) {
                this._cur   = res;
                this.length = res.length;
                return this;
            } else {
                return g(res);
            }
        } ,

        /*
         * 向上查找符合条件的所有祖先元素，直到遇到给定的祖先元素为止
         * @param  Element dom
         * @param  Object  json
         * @param  bool    isStrict    是否开启严格模式搜索
         * @param  Boolean isSaveSelf  结果集中是否保留起始元素
         * @param  Boolean isSaveUntil 结果集中是否保留中断搜索的祖先元素
         * @res 满足条件的所有父元素集合
         * @return this
         */
        parents: function(json , until , isStrict ,  isCopy){
            json        = g.isObj(json) ? json : {};
            until       = g.isDom(until) ? until : document.body;
            isStrict    = g.isBoolean(isStrict) ? isStrict : false;
            isCopy      = g.isBoolean(isCopy) ? isCopy : true;

            var res		= [];
            var parent 	= this.first(true).parent();
            var attrVal = '';
            var key     = null;
            var checkVal    = '';
            var isSatisfy   = true;

            while (!parent.eq(until))
            {
                isSatisfy = true;

                for (key in json)
                {
                    attrVal 	    = g.isValid(parent.native(key)) ? parent.native(key) : parent.attr(key);
                    checkVal 		= json[key];

                    if (isStrict) {
                        isSatisfy = attrVal === checkVal;
                    } else {
                        attrVal     = g.isString(attrVal) ? attrVal.toLowerCase() : attrVal;
                        checkVal    = g.isString(checkVal) ? checkVal.toLowerCase() : checkVal;
                        isSatisfy   = g.isString(attrVal) ? attrVal.search(checkVal) !== -1 : attrVal == checkVal;
                    }

                    if (!isSatisfy) {
                        break;
                    }
                }

                if (isSatisfy) {
                    res.push(parent.get(0));
                }

                parent = parent.parent();
            }

            if (!isCopy) {
                this._cur   = res;
                this.length = res.length;
                return this;
            } else {
                return g(res);
            }
        } ,

        /**
         * 返回当前元素的直系父元素
         */
        parent: function(){
            return g(this.get(0).parentNode);
        } ,

        /*
         * 向下查找所有符合条件的子元素
         * @param Element dom
         * @param Object  json
         * @param bool    isStrict 是否开启严格模式搜索 a === b 而不是 a.search(b) !==-1
         * @param Boolean isCopy   是否复制一份，而不是替换当前 context
         * @return Element | false
         */
        childrens: function(json , isStrict , isCopy){
            json        = g.isObj(json) ? json : {};
            isStrict    = g.isBoolean(isStrict) ? isStrict : false;
            isCopy      = g.isBoolean(isCopy) ? isCopy : true;

            var self  			= this;
            var res   			= [];
            var cNode 			= false;
            var isSatisfy 		= true;
            var attrVal  		= null;
            var checkVal 		= null;
            var attrValType		= null;
            var checkValType 	= null;
            var i               = 0;
            var key             = null;

            var find = function(dom){
                var cNodes = dom.children;

                for (i = 0; i < cNodes.length; ++i)
                {
                    isSatisfy = true;
                    cNode 	  = cNodes[i];

                    for (key in json)
                    {
                        attrVal 	    = g.isValid(cNode[key]) ? cNode[key] : cNode.getAttribute(key);
                        checkVal 		= json[key];

                        if (isStrict) {
                            if (attrVal === checkVal) {
                                isSatisfy = true;
                            } else {
                                isSatisfy = false;
                                break;
                            }
                        } else {
                            // 字符串值
                            attrValType 	= g.type(attrVal);
                            checkValType 	= g.type(checkVal);

                            if (attrValType === 'String') {
                                attrVal = attrVal.toLowerCase();
                            }

                            if (checkValType === 'String') {
                                checkVal = checkVal.toLowerCase();
                            }

                            if (attrValType === 'String') {
                                if (attrVal.search(checkVal) !== -1) {
                                    isSatisfy = true;
                                } else {
                                    isSatisfy = false;
                                    break;
                                }
                            } else {
                                // 非字符串值（我想着应该不可能出现....）
                                if (attrVal == checkVal) {
                                    isSatisfy = true;
                                } else {
                                    isSatisfy = false;
                                    break;
                                }
                            }
                        }
                    }

                    if (isSatisfy) {
                        res.push(cNode);
                    }

                    if (cNode.childElementCount !== 0) {
                        find(cNode);
                    }
                }
            };

            find(this.get(0));

            if (!isCopy) {
                this._cur = res;
                this.length  = res.length;
                return this;
            } else {
                return g(res);
            }
        } ,


        /**
         * 从当前元素集中排除具有指定特征的元素
         */
        not: function(json , isStrict , isCopy){
            json        = g.isObj(json) ? json : {};
            isStrict    = g.isBoolean(isStrict) ? isStrict : false;
            isCopy      = g.isBoolean(isCopy) ? isCopy : true;

            var doms        = g(this.get());
            var cur  	 	= null;
            var attrVal  	= '';
            var checkVal 	= '';
            var res      	= [];
            var isSatisfy 	= true;
            var i 			= 0;
            var key			= null;

            for (i = 0; i < doms.length; ++i)
            {
                isSatisfy = true;
                cur = doms.jump(i , true);
                for (key in json)
                {
                    attrVal 	 = g.isValid(cur.native(key)) ? cur.native(key) : cur.attr(key);
                    checkVal 	 = json[key];

                    if (isStrict) {
                        isSatisfy = attrVal !== checkVal;
                    } else {
                        attrVal     = g.isString(attrVal) ? attrVal.toLowerCase() : attrVal;
                        checkVal    = g.isString(checkVal) ? checkVal.toLowerCase() : checkVal;
                        isSatisfy   = g.isString(attrVal) ? attrVal.search(checkVal) === -1 : attrVal != checkVal;
                    }

                    if (!isSatisfy) {
                        break;
                    }
                }

                if (isSatisfy) {
                    res.push(cur.get(0));
                }
            }

            if (!isCopy) {
                this._cur   = res;
                this.length = res.length;
                return this;
            } else {
                return g(res);
            }
        } ,

        /**
         * 从当前元素集中获取具有指定特征的元素
         */
        filter: function(json , isStrict , isCopy){
            json        = g.isObj(json) ? json : {};
            isStrict    = g.isBoolean(isStrict) ? isStrict : false;
            isCopy      = g.isBoolean(isCopy) ? isCopy : true;

            var doms        = g(this.get());
            var cur  	 	= null;
            var attrVal  	= '';
            var checkVal 	= '';
            var res      	= [];
            var isSatisfy 	= true;
            var i 			= 0;
            var key			= null;

            for (i = 0; i < doms.length; ++i)
            {
                isSatisfy = true;
                cur = doms.jump(i , true);
                for (key in json)
                {
                    attrVal 	 = g.isValid(cur.native(key)) ? cur.native(key) : cur.attr(key);
                    checkVal 	 = json[key];

                    if (isStrict) {
                        isSatisfy = attrVal === checkVal;
                    } else {
                        attrVal     = g.isString(attrVal) ? attrVal.toLowerCase() : attrVal;
                        checkVal    = g.isString(checkVal) ? checkVal.toLowerCase() : checkVal;
                        isSatisfy   = g.isString(attrVal) ? attrVal.search(checkVal) !== -1 : attrVal == checkVal;
                    }

                    if (!isSatisfy) {
                        break;
                    }
                }

                if (isSatisfy) {
                    res.push(cur.get(0));
                }
            }

            if (!isCopy) {
                this._cur   = res;
                this.length = res.length;
                return this;
            } else {
                return g(res);
            }
        } ,

        nextSibling: function(){
            return g(this.get(0).nextElementSibling);
        } ,

        prevSibling: function(){
            return g(this.get(0).previousElementSibling);
        } ,

        /*
         * 水平查找符合条件的兄弟元素
         * @param Element dom
         * @param Object json
         * @return Element | false
         */
        siblings: function(json , isStrict , isCopy){
            var res = [];

            var prevSiblings = this.prevSiblings(json , isStrict , isCopy);
            var nextSiblings = this.nextSiblings(json , isStrict , isCopy);

            prevSiblings.loop(function(dom){
                res.push(dom);
            });

            nextSiblings.loop(function(dom){
                res.push(dom);
            });

            if (!isCopy) {
                this._cur = res;
                this.length = res.length;
                return this;
            } else {
                return g(res);
            }
        } ,

        // 兄弟元素 水平向上查找指定特征的兄弟元素
        prevSiblings: function(json , isStrict , isCopy){
            json        = g.isObj(json) ? json : {};
            isStrict    = g.isBoolean(isStrict) ? isStrict : false;
            isCopy      = g.isBoolean(isCopy) ? isCopy : true;

            var prevSibling = this.prevSibling();
            var res			= [];
            var isSatisfy	= true;
            var attrVal		= '';
            var checkVal	= '';
            var key			 = null;

            while (prevSibling.isDom())
            {
                isSatisfy = true;
                for (key in json)
                {
                    attrVal  		= g.isValid(prevSibling.native(key)) ? prevSibling.native(key) : prevSibling.attr(key);
                    checkVal 		= json[key];

                    if (isStrict) {
                        isSatisfy = attrVal === checkVal;
                    } else {
                        attrVal     = g.isString(attrVal)   ? attrVal.toLowerCase() : attrVal;
                        checkVal    = g.isString(checkVal)  ? checkVal.toLowerCase() : checkVal;
                        isSatisfy   = g.isString(attrVal)   ? attrVal.search(checkVal) !== -1 : attrVal == checkVal;
                    }

                    if (!isSatisfy) {
                        break;
                    }
                }

                if (isSatisfy) {
                    res.push(prevSibling.get(0));
                }

                prevSibling = prevSibling.prevSibling();
            }

            if (!isCopy) {
                this._cur   = res;
                this.length = res.length;
                return this;
            } else {
                return g(res);
            }
        } ,

        // 兄弟元素 水平向下查找
        nextSiblings: function(json ,isStrict , isCopy){
            json        = g.isObj(json) ? json : {};
            isStrict    = g.isBoolean(isStrict) ? isStrict : false;
            isCopy      = g.isBoolean(isCopy) ? isCopy : true;

            var nextSibling = this.nextSibling();
            var res			= [];
            var isSatisfy	= true;
            var attrVal		= '';
            var checkVal	= '';
            var key			 = null;

            while (nextSibling.isDom())
            {
                isSatisfy = true;
                for (key in json)
                {
                    attrVal  		= g.isValid(nextSibling.native(key)) ? nextSibling.native(key) : nextSibling.attr(key);
                    checkVal 		= json[key];

                    if (isStrict) {
                        isSatisfy = attrVal === checkVal;
                    } else {
                        attrVal     = g.isString(attrVal) ? attrVal.toLowerCase() : attrVal;
                        checkVal    = g.isString(checkVal) ? checkVal.toLowerCase() : checkVal;
                        isSatisfy   = g.isString(attrVal) ? attrVal.search(checkVal) !== -1 : attrVal == checkVal;
                    }

                    if (!isSatisfy) {
                        break;
                    }
                }

                if (isSatisfy) {
                    res.push(nextSibling.get(0));
                }

                nextSibling = nextSibling.nextSibling();
            }

            if (!isCopy) {
                this._cur   = res;
                this.length = res.length;
                return this;
            } else {
                return g(res);
            }
        } ,

        // 添加类名
        addClass: function(cn){
            var self = this;
            // 移除原有的类名
            this.removeClass(cn);

            if (g.isString(cn)) {
                this.loop(function(dom){
                    var origin  = dom.className;
                    var res     = origin === '' ? cn : origin + ' ' + cn;
                    res     = g.trim(res);
                    dom.className = res;
                });
            } else if (g.isArray(cn)) {
                cn = cn.join(' ');
                this.loop(function(dom){
                    var origin  = dom.className;
                    var res     = origin === '' ? cn : origin + ' ' + cn;
                    res     = g.trim(res);
                    dom.className = res;
                });
            } else {
                throw new Error("参数 1 类型错误");
            }
        } ,

        // 移除类名
        removeClass: function(cn){
            var self = this;
            if (g.isString(cn)) {
                this.loop(function(dom){
                    var origin  = dom.className;
                    var reg     = new RegExp(cn);
                    var res     = origin.replace(reg , '');
                    res     = g.trim(res);

                    dom.className = res;
                });
            } else if (g.isArray(cn)) {
                cn.forEach(function(v){
                    self.loop(function(dom){
                        var origin = dom.className;
                        var reg = new RegExp(v , 'ig');
                        var res = origin.replace(reg , '');
                        res = g.trim(res);

                        dom.className = res;
                    });
                });
            } else {
                throw new Error('参数 1 类型错误');
            }
        } ,

        // 替换类名
        replaceClass: function(oldCn , newCn){
            this.removeClass(oldCn);
            this.addClass(newCn);
        } ,

        /*
         * 判断类名是否存在！
         * @param String   cn
         * @param Boolean  isStrict
         */
        hasClass: function(className , isStrict){
            var cn = this.attr('class');
            if (!g.isValid(cn)) {
                return false;
            }
            isStrict = g.isBoolean(isStrict) ? isStrict : false;
            var cnList  = cn.split(' ');
            var i   = 0;
            var cur = null;

            for (; i < cnList.length; ++i)
            {
                cur = cnList[i];
                if (isStrict ? cur === className : cur.toLowerCase() == className) {
                    return true;
                }
            }

            return false;
        } ,

        // 切换内容
        switch: function(doms){
            doms = g(doms);

            var isExists = g.scn('hide');

            if (!isExists) {
                document.styleSheets[0].addRule('.hide','display:none;' , 0);
            }

            this.removeClass('hide');

            var i   = 0;
            var cur = null;

            for (; i < doms.length; ++i)
            {
                cur = doms.jump(i , true);
                if (cur.get(0) !== this.get(0)){
                    cur.addClass('hide');
                }
            }
        } ,

        /*
         * 突出显示被选中的元素
         * @param String cn 类名
         * @param HTMLCollect list  元素集合
         * @param reverse 相反操作
         * @return undefined
         */
        highlight: function(cn , list , reverse){
            list = g(list);
            reverse = g.type(reverse) === 'Boolean' ? reverse : false;

            var i = 0;
            var cur = null;

            for (; i < list.length; ++i)
            {
                cur = list.jump(i , true);

                if (!reverse) {
                    if (this.get(0) !== cur.get(0)) {
                        cur.removeClass(cn);
                    } else {
                        cur.addClass(cn);
                    }
                } else {
                    if (this.get(0) !== cur.get(0)) {
                        cur.addClass(cn);
                    } else {
                        cur.removeClass(cn);
                    }
                }
            }
        } ,

        // 获取元素索引
        index: function(list){
            list    = g(list);
            var i   = 0;
            var cur = null;

            for (; i < list.length; ++i)
            {
                cur = list.jump(i , true);
                if (cur.get(0) === this.get(0)){
                    return i;
                }
            }

            return false;
        } ,

        /*
         * 初始化时间选择器函数
         * @param Element dom    待填充的元素
         * @param Number sn      开始的数字
         * @param Number en      结束的数字
         * @param Number focusN  被命中的数字
         * @return HTML String
         */
        timeSelector: function(min , max , cur){
            if (!g.isInt(min)) {
                throw new Error('参数 1 错误');
            }

            if (!g.isInt(max)) {
                throw new Error('参数 2 错误');
            }

            if (min > max) {
                throw new RangeError('最小值大于最大值');
            }

            var option     = '';
            var option  = null;
            var i = 0;
            var _cur = null;

            for (i = min; i <= max; ++i)
            {
                option = document.createElement('option');
                option.value = i;
                option.textContent = i;

                if (g.isInt(cur) && cur === i) {
                    option.selected = true;
                }

                this.append(option);
            }
        } ,

        /*
         * 全屏
         * 一次只能允许一个元素全屏
         */
        requestFullScreen: function(){
            var dom				   = this.get(0);
            var fullScreenEnabled  = document.fullScreenEnabled || document.webkitFullScreenEnabled || document.mozFullScreenEnabled || document.msFullScreenEnabled;
            var isFullScreen	   = document.fullScreenElement || document.webkitFullScreenElement || document.mozFullScreenElement || document.msFullScreenElement;

            if (g.type(fullScreenEnabled) === 'Undefined' || fullScreenEnabled) {
                if (g.type(isFullScreen) === 'Undefined') {
                    if (dom.requestFullScreen) {
                        dom.requestFullScreen();
                    } else if (dom.webkitRequestFullScreen) {
                        dom.webkitRequestFullScreen();
                    } else if (dom.mozRequestFullScreen) {
                        dom.mozRequestFullScreen();
                    } else if (dom.requestFullscreen) {
                        dom.requestFullscreen();
                    } else if (dom.msRequestFullscreen) {
                        dom.msRequestFullscreen();
                    } else {
                        console.log('不存在进入全屏的方法！ => undefined');
                    }
                } else if (isFullScreen === null) {
                    if (dom.requestFullScreen) {
                        dom.requestFullScreen();
                    } else if (dom.webkitRequestFullScreen) {
                        dom.webkitRequestFullScreen();
                    } else if (dom.mozRequestFullScreen) {
                        dom.mozRequestFullScreen();
                    } else if (dom.requestFullscreen) {
                        dom.requestFullscreen();
                    } else if (dom.msRequestFullscreen) {
                        dom.msRequestFullscreen();
                    } else {
                        console.log('不存在进入全屏的方法！ => null');
                    }
                } else {
                    console.log('元素已经是全屏状态了！');
                    return true;
                }
            } else {
                console.log('不支持全屏模式！');
            }
        } ,

        /*
         * 退出全屏
         */
        exitFullScreen: function(){
            var fullScreenEnabled  = document.fullScreenEnabled || document.webkitFullScreenEnabled || document.mozFullScreenEnabled || document.msFullScreenEnabled;
            var isFullScreen	   = document.fullScreenElement || document.webkitFullScreenElement || document.mozFullScreenElement || document.msFullScreenElement;

            if (g.type(fullScreenEnabled) === 'Undefined' || fullScreenEnabled) {
                if (g.type(isFullScreen) === 'Undefined') {
                    if (document.exitFullScreen) {
                        document.exitFullScreen();
                    } else if (document.webkitExitFullScreen) {
                        document.webkitExitFullScreen();
                    } else if (document.webkitCancelFullScreen) {
                        document.webkitCancelFullScreen();
                    } else if (document.mozCancelFullScreen) {
                        document.mozCancelFullScreen();
                    } else if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.msExitFullscreen) {
                        document.msExitFullscreen();
                    } else {
                        console.log('不存在退出全屏的方法！ => undefined');
                    }
                } else if (isFullScreen !== null) {
                    if (document.exitFullScreen) {
                        document.exitFullScreen();
                    } else if (document.webkitExitFullScreen) {
                        document.webkitExitFullScreen();
                    } else if (document.webkitCancelFullScreen) {
                        document.webkitCancelFullScreen();
                    } else if (document.mozCancelFullScreen) {
                        document.mozCancelFullScreen();
                    } else if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.msExitFullscreen) {
                        document.msExitFullscreen();
                    } else {
                        console.log('不存在退出全屏的方法！ => null');
                    }
                } else {
                    console.log('元素已经是非全屏状态了！');
                    return true;
                }
            } else {
                console.log('不支持全屏模式！');
            }
        } ,

        // 结合队列的动画方法
        animateQueue: function(json , callback , time , wait , delay){
            this.loop(function(dom){
                this._consume(dom.__smalljs_queue__ , g , g.animate , false , [dom , json] , {
                    name: '' ,
                    callback: callback
                } , [time , wait , delay]);
            });

            return this;
        } ,

        // 未结合队列的动画方法
        animate: function(json , callback , time , wait , delay){
            this.loop(function(dom){
                dom.__smalljs_queue__.clear();

                g.animate(dom , json , callback , time , wait , delay);
            });

            return this;
        } ,

        // 动画扩展
        move: function(con , isLimit){
            this.loop(function(dom){
                new Move(dom , con , isLimit);
            });

            return this;
        } ,

        /*
         * 使元素居中
         * @param Element   dom
         * @param Element   container
         * @param String    pos            水平居中（horizontal） | 垂直居中(vertical) | 水平垂直居中（all）
         */
        center: function(container , pos){
            var self    = this;
            var posRange = ['horizontal' , 'vertical' , 'all'];
            var lv		= null;
            var tv		= null;
            var unit    = 'px';

            pos = g.contain(pos , posRange) ? pos : 'all';

            this.loop(function(dom){
                dom = g(dom);

                var con	   = g(container);
                var conW   = con.width('padding-box');
                var conH   = con.height('padding-box');
                var myW    = dom.width('border-box');
                var myH    = dom.height('border-box');

                switch (pos)
                {
                    case 'horizontal':
                        lv = Math.max(0 , Math.floor((conW - myW) / 2));

                        self.css({
                            left: lv + unit
                        });

                        break;
                    case 'vertical':
                        tv = Math.max(0 , Math.floor((conH - myH) / 2));

                        self.css({
                            top: tv + unit
                        });

                        break;
                    case 'all':
                        lv = Math.max(0 , Math.floor((conW - myW) / 2));
                        tv = Math.max(0 , Math.floor((conH - myH) / 2));

                        self.css({
                            left: lv + unit ,
                            top: tv + unit
                        });
                        break;

                    default:
                        throw new RangeError('不支持的位置类型！当前受支持的位置类型有：' + posRange.join(' '));
                }
            });

            return this;
        }
    };

    /**
     * 静态属性
     */

    // 页面所有定时器集合
    g.timer = g.t = {
        // 定时器 id 集合
        id: [] ,

        _group: {} ,

        // 添加定時器 id
        add: function(id){
            this.id.push(id);
        } ,

        // dingshiqi
        del: function(id){
            var index = this.id.indexOf(id);
            // 清除总合集中的 id
            if (index !== -1) {
                this.id.splice(index , 1);
            }

            var k = null;
            var cur = null;
            // 清除分组集合中的 id
            for (k in this._group)
            {
                cur = this._group[k];
                if (g.isArray(cur) && (index = cur.indexOf(id)) !== -1) {
                    cur.splice(index , 1);
                }
            }
        } ,

        // 清除定时器
        clear: function(id){
            this.del(id);

            // 实际上调用以下任意一个方法都可以清除
            window.clearTimeout(id);
            window.clearInterval(id);
        } ,

        // 清除所有定时器
        clearAll: function(){
            var id = g.copy(this.id);
            var self = this;
            id.forEach(function(v){
                self.clear(v);
            });
        } ,

        // 分组添加定时器 id
        group: function(name , id){
            if (!g.isArray(this._group[name])) {
                this._group[name] = [];
            }
            var group = this._group[name];
            var res = group.indexOf(id);

            // 添加到总合集
            this.add(id);

            if (res !== -1) {
                return ;
            }
            group.push(id);
        } ,

        // 清除分组定时器
        clearGroup: function(name){
            var group = g.isArray(this._group[name]) ? this._group[name] : [];
            var copy  = g.copy(group);
            var self = this;
            copy.forEach(function(v){
                self.clear(v);
            });
        } ,
        // 添加定时器
        time: function(fn , time , group){
            time = g.isInt(time) ? time : 0;
            var self = this;
            var id = window.setTimeout(function(){
                self.del(id);
                if (g.isFunction(fn)) {
                    fn();
                }
            } , time);
            if (g.isValid(group) && g.isString(group)) {
                this.group(group , id);
            } else {
                this.add(id);
            }

            return id;
        } ,

        // 清除定时器
        clearTime: function(id){
            this.clear(id);
        } ,

        // 清除定时器
        interval: function(fn , time , group){
            time = g.isInt(time) ? time : 0;
            var id = window.setInterval(function(){
                if (g.isFunction(fn)) {
                    fn();
                }
            } , time);
            if (g.isValid(group) && g.isString(group)) {
                this.group(group , id);
            } else {
                this.add(id);
            }

            return id;
        } ,

        // 清除定时器
        clearInterval: function(id){
            this.clear(id);
        }
    };

    /*
     * ******************************************
     以下是以 SmallJs 作为命名空间的 基础函数库
     * ******************************************
     */
    // 获取对象的首个属性
    g.firstKey = function(obj){
        return Object.keys(obj)[0];
    };

    // 获取对象首个单元值
    g.firstValue = function(obj){
        return Object.values(obj)[0];
    };

    g.regexpCheck = function(reg , str){
        var regexp = new RegExp(reg);
        return regexp.test(str);
    };

    // 字符串检查
    g.strSearch = function(needle , str){
        return str.search(needle) != -1;
    };

    // 事件定时器轮询
    g.loop = {
        timer: null ,
        // 每 20 毫秒轮询一次
        freq: 20 ,
        event: [
            // {
            //     check: null ,
            //     callback: null
            // }
        ] ,
        isStop: false ,
        // 注册事件
        register (check , callback) {
            this.event.push({
                check: check ,
                callback: callback
            });
            return this;
        } ,
        // 停止事件轮询
        stop () {
            this.isStop = true;
        } ,
        // 轮询
        loop () {
            window.clearTimeout(this.timer);
            var i = 0;
            var e = null;
            for (i = 0; i < this.event.length; ++i)
            {
                e = this.event[i];
                if (e.check()) {
                    e.callback();
                    this.event.splice(i , 1);
                    i--;
                }
            }
            if (!this.isStop) {
                window.setTimeout(this.loop.bind(this) , this.freq);
            } else {
                this.isStop = false;
            }
        } ,
    };

    g.gesture = function(ox , oy , isSimple){
        ox = g.isNumber(ox) ? ox : 0;
        oy = g.isNumber(oy) ? oy : 0;
        isSimple = g.isBoolean(isSimple) ? isSimple : false;

        // 误触范围
        var positiveAmount = 15;
        var negativeAmount = -positiveAmount;
        var dir = '';

        if (ox > positiveAmount && Math.abs(ox) > Math.abs(oy)) {
            dir = 'right';
        } else if (ox < negativeAmount && Math.abs(ox) > Math.abs(oy)) {
            dir = 'left';
        } else if (oy > positiveAmount && Math.abs(oy) > Math.abs(ox)) {
            dir = 'bottom';
        } else if (oy < negativeAmount && Math.abs(oy) > Math.abs(ox)) {
            dir = 'top';
        } else {
            dir = 'none';
        }

        if (isSimple) {
            if (G.contain(dir , ['left' , 'right'])) {
                return 'horizontal';
            }

            if (G.contain(dir , ['top' , 'bottom'])) {
                return 'vertical';
            }
        }

        return dir;
    };

    // 解析 transform 属性
    g.parseTransform = function(val){
        var reg     = /^matrix3d/;
        var is3d    = reg.test(val);
        var origin  = null;
        var res     = null;
        var unit   = 180 / Math.PI;

        if (is3d) {
            // 3d 变换
            val = val.replace(/matrix3d|\(|\)| /g , '');
            val = val.split(',');

            val.forEach(function(v , i , a){
                a[i] = parseFloat(v);
            });

            res = {
                translateX: val[12] ,
                translateY: val[13] ,
                translateZ: val[14] ,
                scaleX: val[0] ,
                scaleY: val[5] ,
                scaleZ: val[10] ,
                // todo 暂不支持 3d 旋转的解析
                rotateX: 0 ,
                rotateY: 0 ,
                rotateZ: 0 ,
                skewX: 0 ,
                skewY: 0 ,
                skewZ: 0
            };
        } else {
            // 2d 变换
            val = val.replace(/matrix|\(|\)| /g , '');
            val = val.split(',');

            val.forEach(function(v , i , a){
                a[i] = parseFloat(v);
            });

            res = {
                translateX: val[4] ,
                translateY: val[5] ,
                translateZ: 0 ,
                // 以下两个参数会受到旋转和拉伸的影响，不可认为是 css 样式设置值！！
                scaleX: val[0] ,
                scaleY: val[3] ,
                scaleZ: 0 ,
                rotateX: 0 ,
                rotateY: 0 ,
                rotateZ: Math.round(Math.acos(val[0]) * unit) ,
                skewX: Math.round(Math.atan(val[1]) * unit) ,
                skewY: Math.round(Math.atan(val[3]) * unit) ,
                skewZ: 0
            };
        }
        return res;
    };

    g.unit = function(attr){
        return attr === 'opacity' ? '' : 'px';
    };

    g.id = function(selector){
        return g(selector);
    };

    g.class = function(selector , context){
        var res = !this.isDom(context) ? document.getElementsByClassName(selector) : context.getElementsByClassName(selector);

        return g(res);
    };

    g.tag = function(selector , context){
        var res = !this.isDom(context) ? document.getElementsByClassTagName(selector) : context.getElementsByClassName(selector);

        return g(res);
    };

    g.tagNS = function(selector , context){
        var res = !this.isDom(context) ? document.getElementsByClassTagNameNS(selector) : context.getElementsByClassNameNS(selector);

        return g(res);
    };

    // 创建标签
    g.createDom = function(tagName , attr){
        var dom = this(document.createElement(tagName));
        if (this.isObject(attr)) {
            var k = null;
            for (k in attr)
            {
                dom.attr(k , attr[k]);
            }
        }
        return dom;
    };

    // 由于无法使用 name 进行命名
    // 所以采用折中的办法
    g.getName = function(selector){
        return g(document.getElementsByName(selector));
    };

    g.querySelector = function(selector , context){
        var res = !this.isDom(context) ? document.querySelector(selector) : context.querySelector(selector);

        return g(res);
    };

    g.querySelectorAll = function(selector , context){
        var res = !this.isDom(context) ? document.querySelectorAll(selector) : context.querySelectorAll(selector);

        return g(res);
    };

    // 输入域：获取选区范围
    g.getSelectionForInput = function(dom){
        return {
            startOffset: dom.selectionStart ,
            endOffset: dom.selectionEnd
        };
    };

    // 可编辑的 html：获取选区范围
    g.getSelectionForContentEditableElement = function(dom){
        var selection = window.getSelection();

        if (selection.rangeCount === 0) {
            return false;
        }

        var range = selection.getRangeAt(0);

        var cRange = range.cloneRange();

        cRange.selectNodeContents(dom);
        cRange.setStart(range.startContainer , range.startOffset);
        cRange.setEnd(range.endContainer , range.endOffset);

        return {
            startOffset: range.startOffset ,
            endOffset: range.endOffset
        };
    };

    // 获取选区
    g.getSelection = function(dom){
        if (dom.contentEditable !== 'true') {
            return this.getSelectionForInput(dom);
        }

        return this.getSelectionForContentEditableElement(dom);
    };

    // 检查是否存在选区
    g.isCollapse = function(dom){
        dom.focus();

        var selection = window.getSelection();
        var range = selection.getRangeAt(0);
        var cRange = range.cloneRange();

        cRange.selectNodeContents(dom);
        cRange.setStart(range.startContainer , range.startOffset);
        cRange.setEnd(range.endContainer , range.endOffset);

        return cRange.collapsed;
    };

    // 获取可编辑元素的光标位置
    g.getCursorPointForContentEditableElement = function(dom){
        var selection = window.getSelection();

        if (selection.rangeCount === 0) {
            return false;
        }

        var range = selection.getRangeAt(0);
        var cRange = range.cloneRange();

        cRange.selectNodeContents(dom);
        cRange.setEnd(range.endContainer , range.endOffset);

        return cRange.toString().length;
    };

    // 获取 textarea/input 等带有输入域的文本框的光标位置
    g.getCursorPointForInput = function(dom){
        return dom.selectionEnd;
    };

    // 获取光标当前位置
    g.getCursorPoint = function(dom){
        if (dom.contentEditable !== 'true') {
            return this.getCursorPointForInput(dom);
        }

        return this.getCursorPointForContentEditableElement(dom);
    };

    // 可编辑 html: 设置光标位置
    g.setCursorPointForContentEditableElement = function(dom , pos){
        dom.focus();

        // 快捷方式
        var posRange = ['first' , 'last'];

        if (this.contain(pos , posRange)) {
            if (pos === 'first') {
                pos = 0;
            } else {
                pos = dom.childNodes.length;
            }
        }

        var range = document.createRange();

        range.selectNodeContents(dom);

        range.setStart(dom , pos);

        range.collapse(true);

        var selection = window.getSelection();

        selection.removeAllRanges();
        selection.addRange(range);
    };

    // 文本域：设置光标位置
    g.setCursorPointForInput = function(dom , pos){
        dom.focus();

        dom.setSelectionRange(pos , pos , 'none');
    };

    // 设置光标位置
    g.setCursorPoint = function(dom , pos){
        if (dom.contentEditable !== 'true') {
            this.setCursorPointForInput(dom , pos);
            return ;
        }

        this.setCursorPointForContentEditableElement(dom , pos);
    };

    // 获取某个元素距离给定父元素的位置
    // 滚动到顶部
    g.top = function (dom , time , fn){
        this.scroll(dom , time , 'y' , null , 0 , fn);
    };

    // 滚动到底部
    g.bottom = function(dom , time , fn){
        dom = this(dom);

        var clientH = dom.clientHeight();
        var domH    = dom.scrollHeight();
        var endY    = domH - clientH;

        this.scroll(dom.get(0) , time , 'y' , null , endY , fn);
    };

    g.left = function (dom , time , fn){
        this.scroll(dom , time , 'x' , 0 , null , fn);
    };

    g.right = function(dom , time , fn){
        dom = this(dom);

        var clientW = dom.clientWidth();
        var domW    = dom.scrollWidth();
        var endX    = domW - clientW;

        this.scroll(dom.get(0) , time , 'x' , endX , null , fn);
    };

    // 垂直滚动
    g.hScroll = function(dom , time , v , fn){
        this.scroll(dom , time , 'x' , v , null , fn);
    };

    // 水平滚动
    g.vScroll = function(dom , time , v , fn){
        this.scroll(dom , time , 'y' , null , v , fn);
    };

    /**
     * 平滑滚动顶部或者顶部
     * @param String type top bottom
     * @param Number time
     * @param fn 回调函数
     */
    g.scroll = function(dom , time , pos , x , y , fn){
        dom = this(dom);

        var self = this;
        var posRange    = ['x' , 'y' , 'all'];

        time = g.isInt(time) ? time : 300;
        time = Math.max(0 , time);
        pos  = g.contain(pos , posRange) ? pos : 'all';

        // 初始值
        var startX  = dom.scrollLeft();
        var startY  = dom.scrollTop();
        var sTime = new Date().getTime();

        // 变化量
        var ratio = 0;
        var minRatio = 0;
        var maxRatio = 1;
        var xRange = x - startX;
        var yRange = y - startY;

        // 结束值
        var endX  = 0;
        var endY  = 0;
        var eTime = 0;

        var start = function(){
            self.CAF(dom.get(0).__smalljs_scroll_timer__);

            eTime   = new Date().getTime();
            ratio   = (eTime - sTime) / time;
            ratio   = Math.max(minRatio , Math.min(maxRatio , ratio));

            endX = startX + xRange * ratio;
            endY = startY + yRange * ratio;

            if (pos === 'x') {
                dom.scrollLeft(endX);
            }

            if (pos === 'y') {
                dom.scrollTop(endY);
            }

            if (pos === 'all') {
                dom.scrollLeft(endX);
                dom.scrollTop(endY);
            }

            if (ratio === maxRatio) {
                if (g.isFunction(fn)) {
                    fn();
                }
            } else {
                dom.get(0).__smalljs_scroll_timer__ = self.RAF(start);
            }
        };

        // 开始执行
        start();
    };

    // 滚动条滚动到指定位置
    g.scrollTo = function(time , pos , x , y , fn){
        var self  = this;
        var posRange = ['x' , 'y' , 'all'];
        var curX = window.pageXOffset;
        var curY = window.pageYOffset;

        time = this.isInt(time) ? time : 300;
        pos = this.contain(pos , posRange) ? pos : 'all';
        x   = this.contain(pos , ['x' , 'all']) ? x : curX;
        y   = this.contain(pos , ['y' , 'all']) ? y : curY;

        var sTime = new Date().getTime();
        var eTime = 0;
        var ratio = 0;
        var xRange = x - curX;
        var yRange = y - curY;
        var endX;
        var endY;

        // 开始动画
        var start = function(){
            self.CAF(window.__scrollTimer__);

            eTime = new Date().getTime();
            ratio = Math.max(0 , Math.min(1 , (eTime - sTime) / time));
            endX = curX + xRange * ratio;
            endY = curY + yRange * ratio;

            window.scrollTo(endX , endY);

            if (ratio !== 1) {
                return window.__scrollTimer__ = self.RAF(start);
            }

            delete window.__scrollTimer__;
            if (self.isFunction(fn)) {
                fn();
            }
        };
        start();
    };

    /**
     * 滚动加载（到了可视区域时候）
     * 此函数待测试
     * @param element  dom
     * @param function callback 每次浏览器滚动，都会触发
     * @param function callback
     * @return void
     */
    g.scrollLoad = function(dom , callback , completed){
        dom = this(dom);

        var self = this;

        // 判断是否已经在可视区域
        var inVisible = function(){
            if (self.inVisible(dom.get(0) , 'all')) {
                if (g.isFunction(completed)) {
                    completed(dom.get(0));
                } else {
                    console.log('滚动加载未设置回调函数');
                }
            }
        };

        // 定义滚动事件
        this(window).on('scroll' , function(){
            if (self.isFunction(callback)) {
                callback();
            }

            inVisible();
        } , true , false);

        // 初始化定义的时候调用一次，进行初始化加载
        inVisible();
    };

    // 判断一个值是否在浏览器窗口范围内，能够被浏览器看到
    g.inVisible = function(dom , pos){
        var posRange = ['x' , 'y' , 'all'];
        pos         = this.contain(pos , posRange) ? pos : 'all';
        dom         = this(dom);

        var minL = dom.getDocOffsetVal('left');
        var minT = dom.getDocOffsetVal('top');
        var domW = dom.width('border-box');
        var domH = dom.height('border-box');
        var maxL = minL +domW;
        var maxT = minT + domH;

        var container = this(document.documentElement);
        var minLRange = Math.floor(container.scrollLeft());
        var maxLRange = Math.ceil(minLRange + container.clientWidth());
        var minTRange = Math.floor(container.scrollTop());
        var maxTRange = Math.ceil(minTRange + container.clientHeight());

        if (pos === 'x') {
            return minL >= minLRange && minL <= maxLRange || maxL >= minLRange && maxL <= maxLRange;
        }

        if (pos === 'y') {
            return minT >= minTRange && minT <= maxTRange || maxT >= minTRange && maxT <= maxTRange;
        }

        if (pos === 'all') {
            return (minL >= minLRange && minL <= maxLRange || maxL >= minLRange && maxL <= maxLRange) || (minT >= minTRange && minT <= maxTRange || maxT >= minTRange && maxT <= maxTRange);
        }
    };

    /*
     * 获取某个月份的天数
     * 公历：获取某年某月的总天数
     * 1 3 5 7 8 10 12  都是31天
     * 4 6 9 11         都是30天
     * 2 月 平年28天 ， 闰年29天
     * 平年 365 天	 ， 闰年 366天
     */
    g.getMonthDays = function(year , month){
        year    = parseInt(year);
        month   = parseInt(month);

        var to = [1 , 3 ,5 , 7 , 8 , 10 , 12];
        var tt = [4 , 6 , 9 , 11];

        if (this.contain(month , to)) {
            return 31;
        }

        if (this.contain(month , tt)) {
            return 30;
        }

        /*
         * 闰年判断规则
         * 1. 普通年能被4整除且不能被100整除的为闰年.
         * 2. 世纪年能被400整除的是闰年
         * 满足以上任一规则都是闰年
         */
        if (this.isLeapYear(year)) {
            return 29;
        }

        return 28;
    };

    // 指定 select 和 值，选中
    g.select = function(select , value){
        var options = this('option' , select);
        var i       = 0;
        var cur     = null;

        for (; i < options.length; ++i)
        {
            cur = options.jump(i , true);

            if (cur.val() != value) {
                cur.selected(true);
            } else {
                cur.selected(false);
            }
        }
    };

    /*
     * 判断是否为闰年
     * 闰年判断规则
     * 1. 普通年能被4整除且不能被100整除的为闰年.
     * 2. 世纪年能被400整除的是闰年
     * 满足以上任一规则都是闰年
     */
    g.isLeapYear = function(year){
        return ((year % 4 === 0) && (year % 100 !== 0)) || year % 400 === 0;
    };

    /*
     * 阻止事件冒泡
     */
    g.stop = function(e , once){
        once = g.isBoolean(once) ? once : false;
        if (once) {
            g.stopImmediate(e);
        } else {
            e.stopPropagation();
        }
    };

    /*
     * 阻止默认事件
     */
    g.prevent = function(e){
        if (e.cancelable && !e.defaultPrevented) {
            // e.cancelable = true，表明事件可以取消默认行为
            // e.defaultPrevented，表明事件尚未调用 e.preventDefault 方法阻止默认行为
            // 以上两个都满足的情况，调用 preventDefault 阻止默认行为！
            e.preventDefault();
        }
    };

    // 阻止事件冒泡，阻止其他同意元素的相同事件触发
    g.stopImmediate = function(e){
        e.stopImmediatePropagation();
    };

    // 获取单选框的值
    g.radio = function (doms){
        var i   = 0;
        var cur = null;

        for (; i < doms.length; ++i)
        {
            cur = doms[i];

            if (cur.checked) {
                return cur.value;
            }
        }

        return false;
    };

    // 获取复选款选择的内容
    g.checkbox = function(doms){
        var i       = 0;
        var cur     = null;
        var values  = [];

        for (; i < doms.length; ++i)
        {
            cur = doms[i];

            if (cur.checked) {
                values.push(cur.value);
            }
        }

        return values;
    };

    /**
     * 设置定时器
     */
    g.setTimeout = function(fn , time){
        var self = this;

        var id = window.setTimeout(function(){
            self.timer.del(id);

            if (g.isFunction(fn)) {
                fn();
            }
        } , time);

        this.timer.add(id);

        return id;
    };

    // 清除定时器
    g.clearTimeout = function(id){
        if (!g.isInt(id)) {
            return ;
        }

        this.timer.clear(id);
    };

    /**
     * 设置定时器
     */
    g.setInterval = function(fn , time){
        var self = this;

        var id = window.setInterval(function(){
            if (g.isFunction(fn)) {
                fn();
            }
        } , time);

        this.timer.add(id);

        return id;
    };

    // 清除定时器
    g.clearInterval = function(id){
        if (!g.isInt(id)) {
            return ;
        }

        this.timer.clear(id);
    };

    /**
     * 获取 FormData 对象，可添加初始化键值对
     */
    g.formData = function(){
        var formData = new FormData();

        if (arguments.length === 2) {
            formData.append(arguments[0] , arguments[1]);
        }

        if (arguments.length === 1) {
            var data = arguments[0];

            for (var key in data)
            {
                formData.append(key , data[key]);
            }
        }

        return formData;
    };

    g.buildQuery = function(obj){
        var str = '';
        var k = null;
        var v = null;
        for (k in obj)
        {
            v = obj[k];
            v = encodeURIComponent(v);
            str += k + '=' + v + '&';
        }
        return str.slice(0 , -1);
    };

    /**
     * 向一个 formData 对象添加键值对
     */
    g.append = function(){
        var formData = arguments[0];

        if (arguments.length === 3) {
            formData.append(arguments[1] , arguments[2]);
        }

        if (arguments.length === 2) {
            for (var key in arguments[1])
            {
                formData.append(key , arguments[1][key]);
            }
        }

        return formData;
    };

    /**
     * 获取容量
     * @param Number size 容量
     * @param String type 单位
     * @return String，最大支持显示 PB
     */
    g.getStorage = function(size , type){
        size = parseInt(size);
        type = type.toLowerCase();

        var typeRange = ['b' , 'byte' , 'kb' , 'mb' , 'gb' , 'tb' , 'pb'];

        if (!this.contain(type , typeRange)) {
            throw new Error('参数 2 错误');
        }

        // 进率
        var b  = 1;
        var kb = 1024;
        var mb = kb * 1024;
        var gb = mb * 1024;
        var tb = gb * 1024;
        var pb = tb * 1024;

        var explain = ['B' , 'KB' , 'MB' , 'GB' , 'TB' , 'PB'];

        // 获取值
        var get = function(val){
            var index 	= 0;
            var cur 	= null;

            while (index < explain.length)
            {
                cur = val / 1024;

                if (cur < 1 || index === explain.length - 1) {
                    return val.toFixed(2) + explain[index];
                }

                val = cur;

                index++;
            }

            return false;
        };

        switch (type)
        {
            case 'kb':
                size *= kb;
                break;
            case 'mb':
                size *= mb;
                break;
            case 'gb':
                size *= gb;
                break;
            case 'tb':
                size *= tb;
                break;
            case 'pb':
                size *= pb;
                break;
        }

        return get(size);
    };

    /**
     * 节点交换，要求：必须是同一个元素的子元素之间进行交换
     */
    g.switch = function(a , b){
        // 只有一个元素
        if (!this.isDom(a) || !this.isDom(b)) {
            return ;
        }

        a = this(a);
        b = this(b);

        var aN = a.nextSibling().get(0);
        var bN = b.nextSibling().get(0);

        // 相邻的兄弟元素
        if (aN === b || bN === a) {
            if (aN === b) {
                return b.parent().get(0).insertBefore(b , a);
            }

            if (bN === a) {
                return a.parent().get(0).insertBefore(a , b);
            }
        }

        // 其中一个元素没有兄弟元素
        if (!this.isDom(aN) || !this.isDom(bN)) {
            if (!this.isDom(aN)) {
                a.parent().get(0).insertBefore(a , b);
                b.parent().get(0).appendChild(a);
            }

            if (!this.isDom(bN)) {
                b.parent().get(0).insertBefore(b , a);
                a.parent().get(0).appendChild(a);
            }

            return ;
        }

        // 都有兄弟元素
        a.parent().get(0).insertBefore(a , bN);
        b.parent().get(0).insertBefore(b , aN);
    };

    /**
     * 在某个元素的前面添加（移动）元素
     */
    g.insertBefore = function(newDOM , existsDOM){
        return existsDOM.parentNode.insertBefore(newDOM , existsDOM);
    };

    // 克隆节点
    g.clone = function(dom , deep){
        deep = this.isBoolean(deep) ? deep : true;
        return dom.cloneNode(deep);
    };

    // 屏蔽浏览器右键功能
    g.contextmenu = function(){
        if (!this.isUndefined(window._disabledContextMenu__)) {
            return ;
        }

        window.oncontextmenu = function(){
            return false;
        };

        window._disabledContextMenu_ = true;
    };

    /**
     * 在某个元素的后面添加（移动）元素
     */
    g.insertAfter = function(newDOM , existsDOM){
        var nextS = existsDOM.nextElementSibling;

        if (this.type(nextS) === 'Null') {
            return existsDOM.parentNode.appendChild(newDOM);
        }

        return this.insertBefore(newDOM , nextS);
    };

    /**
     * 获取DOM元素集合
     */
    g.doms = function(obj){
        var k 	= null;
        var cur = null;
        var doms = [];

        for (k in obj)
        {
            cur = obj[k];

            if (this.isDom(cur)) {
                doms.push(cur);
            }
        }

        return doms;
    };

    /*
     * 错误处理
     * @param error 错误对象
     */
    g.throw = function(error){
        var str  = "错误文件：" + error.fileName + "\n";
        str += "错误行数：" + error.lineNumber + "\n";
        str += "错误信息：" + error.message;

        throw new Error(str);
    };

    /*
     * 阻止默认事件发生
     */
    g.preventDefault = function(){
        console.warn('请使用 prevent 代替');
        g.prevent();
    };

    /*
     * 普通对象继承 （不支持元素）
     * 第一种模式
     * @param  isOverExistsKey   是否覆盖已有键名的键值
     * @param  isExtends         是否直接在原对象上进行更改
     * @param  isDeep            是否进行深层次递归拷贝
     * @param Object  args3      继承对象
     * @param Object  args4      被继承对象
     * @param Object  args5      被继承对象
     ....
     * 第二种模式
     * @param Object args1 继承对象
     * @param Object args2 被继承对象
     * @param Object args3 被继承对象
     ....
     * @return object
     */
    g.merge = function(){
        var args		    = arguments;
        var isOverExistsKey = false;
        var isExtends		= false;
        var isDeep			= true;
        var list			= null;
        var typeRange		= ['Array' , 'Object'];
        var oneArgs         = args[0];
        var twoArgs         = args[1];
        var threeArgs       = args[2];
        var oneArgsDesc     = g.type(oneArgs);
        var twoArgsDesc     = g.type(twoArgs);
        var threeArgsDesc   = g.type(threeArgs);
        var copy;
        var oSindex;
        var i;
        var n;
        var curDesc;
        var curCDesc;
        var cur;

        if (args.length === 0) {
            return false;
        }

        // 初始化参数
        if (oneArgsDesc === 'Boolean') {
            isOverExistsKey = oneArgs;

            if (twoArgsDesc === 'Boolean') {
                isExtends = twoArgs;

                if (threeArgsDesc === 'Boolean') {
                    isDeep = threeArgs;
                    oSindex  = 3;
                } else {
                    oSindex  = 2;
                }
            } else {
                oSindex = 1;
            }
        } else {
            oSindex = 0;
        }

        // 参数长度检测
        if (oSindex === 0 && args.length === 1) {
            return oneArgs;
        }

        if (oSindex === 1 && args.length < oSindex + 1) {
            return false;
        }

        if (oSindex === 2 && args.length < oSindex + 1) {
            return false;
        }

        if (oSindex === 3 && args.length < oSindex + 1) {
            return false;
        }

        // 检查类型检测
        for ( i = oSindex ; i < args.length; ++i)
        {
            if (!g.contain(g.type(args[i]) , typeRange)) {
                throw new TypeError('参数 ' + (i + 1) + ' 类型错误');
            }
        }

        // 参数类型是否一致检测
        for (i = oSindex ; i < args.length; ++i)
        {
            curDesc = g.type(args[i]);

            for (n = i + 1; n < args.length; ++n)
            {
                curCDesc = g.type(args[n]);

                if (curDesc !== curCDesc) {
                    throw new TypeError('除特殊参数外，其他所有参数类型不一致');
                }
            }

            break;
        }

        /*
         * 数据拷贝
         * 拷贝 B 到 A 中
         */
        copy = function(A , B){
            var aDesc = g.type(A);
            var bDesc = g.type(B);
            var k;
            var i;
            var cur;
            var curDesc;
            var curC;
            var curCDesc;

            if (aDesc === 'Object') {
                for (k in B)
                {
                    cur       = B[k];
                    curDesc = g.type(cur);

                    if (g.type(A[k]) !== 'Undefined') {
                        // 是否覆盖原值
                        if (isOverExistsKey) {
                            if (g.contain(curDesc , typeRange)) {
                                A[k] = curDesc === 'Object' ? {} : [];

                                // 是否递归拷贝
                                if (isDeep) {
                                    copy(A[k] , cur);
                                } else {
                                    A[k] = cur;
                                }
                            } else {
                                A[k] = cur;
                            }
                        }
                    } else {
                        if (g.contain(curDesc , typeRange)) {
                            A[k] = curDesc === 'Object' ? {} : [];

                            // 是否递归拷贝
                            if (isDeep) {
                                copy(A[k] , cur);
                            } else {
                                A[k] = cur;
                            }
                        } else {
                            A[k] = cur;
                        }
                    }
                }
            }

            if (aDesc === 'Array') {
                for (i = 0; i < B.length; ++i)
                {
                    cur     = B[i];
                    curDesc = g.type(cur);

                    if (g.contain(curDesc , typeRange)) {
                        A[i] = curDesc === 'Object' ? {} : [];

                        // 是否递归拷贝
                        if (isDeep) {
                            copy(A[i] , cur);
                        } else {
                            A.push(cur);
                        }
                    } else {
                        A.push(cur);
                    }
                }
            }

        };

        for (i = oSindex ; i < args.length; ++i)
        {
            if (list === null) {
                cur     = args[i];
                curDesc = g.type(cur);
                list    = isExtends ? args[i] : curDesc === 'Object' ? {} : [];
            } else {
                copy(list , args[i]);
            }
        }

        return isExtends ? undefined : list;
    };

    // 安全设置对外暴露借口名称
    g.noConflict = function(name){
        if (!this.isUndefined(window[name]) && this.isNull(window[back])) {
            back = window[name];
        }

        this._set(name);
    };

    // 重命名对外暴露的 API 接口名称
    g.rename = function(name){
        // 清除原来的命名
        this._destroy(name);

        // 设置新名称
        this._set(name);
    };

    // 销毁原有 api 名称
    g._destroy = function(name){
        window[name] = undefined;
    };

    // 设置 api 对外爆乳名称
    g._set = function(name) {
        window[name] = SmallJs;
    };

    // 获取冲突的 api 变量，如果存在的话
    g.back = function(){
        return back;
    };

    /*
     * 返回随机数组
     * @param  Interger len    长度
     * @param  String   type   类型
     * @return Array
     */
    g.randomArr = function(len , type , isReturnString){
        var typeRange = ['number' , 'letter' , 'mixed'];
        type      = !g.contain(type , typeRange) ? 'number' : type;
        isReturnString = this.isBoolean(isReturnString) ? isReturnString : false;
        var rel		  = [];
        var min;
        var max;
        var letterList;
        var letterListLen;
        var i;
        var curNum;
        var curLetter;
        var tmpRel;
        var lMin;
        var lMax;
        var index;

        letterList    = ['a' , 'b' , 'c' , 'd' , 'e' , 'f' , 'g' , 'h' , 'i' , 'j' , 'k' , 'l' , 'm' , 'n' , 'o' , 'p' , 'q' , 'r' , 's' , 't' , 'u' , 'v' , 'w' , 'x' , 'y' , 'z'];
        letterListLen = letterList.length;

        // 生成 A-z 5 个字母
        for (i = 0; i < letterListLen; ++i)
        {
            letterList.push(letterList[i].toUpperCase());
        }

        if (type === 'letter') {
            min = 0;
            max = letterList.length - 1;

            for (i = 1; i <= len; ++i)
            {
                index = g.random(min , max);
                rel.push(letterList[index]);
            }
        }

        if (type === 'number') {
            min = 0;
            max = len;
            for (i = 1; i <= len; ++i)
            {
                rel.push(g.random(min , max));
            }
        }

        if (type === 'mixed') {
            min		  = 0;
            max		  = len;
            lMin	  = 0;
            lMax	  = letterList.length - 1;

            for (i = 1; i <= len; ++i)
            {
                tmpRel    = [];
                index		  = g.random(lMin , lMax);
                // 随机数字
                curNum    = g.random(min , max);
                // 随机字母
                curLetter = letterList[index];
                // 随机字母 数字 或者数组
                tmpRel.push(curNum , curLetter);
                // 从随机数组中随机取一个（字母：50%，数字：50%概率）值
                rel.push(tmpRel[g.random(0 , 1)]);
            }
        }

        return isReturnString ? rel.join('') : rel;
    };

    /*
     * 根据生日计算得出年龄（周岁）
     * 时间格式：'YYYY-mm-dd HH:II:SS' or 'YYYY-mm-dd'
     * @param  String birthday 生日
     * @return 年龄 or 在生日时间大于当前时间的时候，返回 false
     */
    g.getAge = function(birthday){
        var timeJson    = g.parseTime(birthday , 'date');
        var curD		= new Date();
        var curYear		= curD.getFullYear();
        var curMonth	= curD.getMonth() + 1;
        var curDate		= curD.getDate();
        var yearDiff	= curYear  - timeJson['year'];
        var monthDiff	= curMonth - timeJson['month'];
        var dateDiff	= curDate  - timeJson['date'];

        // 出生日期 大于 当前日期的时候返回 false
        if (yearDiff < 0 || yearDiff === 0 && monthDiff < 0  || yearDiff === 0 && monthDiff === 0 && dateDiff < 0) {
            return false;
        }

        // 未到月份
        if (monthDiff < 0) {
            yearDiff -= 1;
        }

        // 已到月份，未到日期
        if (monthDiff === 0 && dateDiff < 0) {
            yearDiff -= 1;
        }

        return yearDiff;
    };

    /*
     * 返回某个范围内的随机数字
     * @param  Interger min       最小值
     * @param  Interger max       最大值
     * @param  Boolean  isInt     是否返回整数
     * @param  Interger fixedNum  isInt = false 时（返回浮点数，即带小数的数），保留多少位小数点，默认保留：3位小数
     * @return Mixed
     */
    g.random = function(min , max , isInt , fixedNum){
        var isInt	 = g.type(isInt)    !== 'Boolean' ? true : isInt;
        var fixedNum = g.type(fixedNum) !== 'Number'  ? 3    : fixedNum;
        var range	 = max - min;
        var rel		 = Math.max(min , Math.min(max , range * Math.random()));

        if (isInt) {
            // 四舍五入
            return Math.round(rel);
        }

        return parseFloat(rel).toFixed(fixedNum);
    };

    /*
     * 判断一个值是否是一个元素
     * @param  Mixed val
     * @return Boolean
     */
    g.isDom = function(val){
        if (!this.isObj(val)) {
            return false;
        }

        if (!this.isValid(val)) {
            return false;
        }

        if (this.type(val.nodeType) === 'Undefined') {
            return false;
        }

        if (val.nodeType !== 1) {
            return false;
        }

        return true;
    };

    /*
     * 判断一个值是否时元素集合
     * @param  Mixed val
     * @return Boolean
     */
    g.isDoms = function(val){
        if (!this.isObj(val)) {
            return false;
        }

        var type = this.type(val);

        if (type === 'HTMLCollection') {
            return true;
        }

        if (type === 'NodeList') {
            return true;
        }

        if (type === 'Array') {
            var i = 0;
            for (; i < val.length; ++i)
            {
                if (!this.isDom(val[i])) {
                    return false;
                }
            }

            return true;
        }

        return false;
    };

    /*
     * 判断是否为对象
     * @param  Mixed val
     * @return Boolean
     */
    g.isObj = function(val){
        return typeof val === 'object';
    };

    /**
     * 检查是否是函数
     */
    g.isFunction = function(data){
        return this.type(data) === 'Function';
    };

    /**
     * 检查是否是字符串
     */
    g.isString = function(data){
        return this.type(data) === 'String';
    };

    /**
     * 检查是否是数字
     */
    g.isNumber = function(data){
        return this.type(data) === 'Number';
    };

    /**
     * 检查是否是数字（字符串数字）
     */
    g.isNumeric = function(data){
        return G.isNumber(data) || /^[0-9]+(\.[0-9]{1,})?$/.test(data);
    };

    /**
     * 检查是否是整形
     */
    g.isInt = function(data){
        return this.isNumber(data) && /^\d+$/.test(String(data));
    };

    /**
     * 检查是否是浮点型
     */
    g.isFloat = function(data){
        return this.isNumber(data) && !this.isInt(data);
    };

    /**
     * 检查是否是布尔值
     */
    g.isBoolean = function(data){
        return this.type(data) === 'Boolean';
    };

    /**
     * 检查是否是 Null
     */
    g.isNull = function(data){
        return this.type(data) === 'Null';
    };

    /**
     * 检查是否是 Undefined
     */
    g.isUndefined = function(data){
        return this.type(data) === 'Undefined';
    };

    /**
     * 检查是否是 数组
     */
    g.isArray = function(data){
        return this.type(data) === 'Array';
    };

    /**
     * 检查是否是 对象
     * isObj 的别名
     */
    g.isObject = g.isObj;

    /*
     * 正确获取值的类型
     * @param Mixed val 待判断的值
     * @return string number Boolean null undefined regexp date function array object
     */
    g.type = function(val){
        var prefix = '[object ';
        var sIndex = prefix.length;
        var eIndex = -1;

        return Object.prototype.toString.call(val).slice(sIndex , eIndex);
    };

    // getValType 的别名
    g.getValType = function(val){
        console.warn('请使用 type 代替 getValType');
        return g.type(val);
    };

    /*
     * 判断一个值是否是有效值
     */
    g.isValid = function(val){
        if (val === '') {
            return false;
        }

        if (g.type(val) === 'Undefined') {
            return false;
        }

        if (val === null) {
            return false;
        }

        if (val === false) {
            return false;
        }

        return true;
    };

    g.isValidVal = function(val){
        console.warn('请使用 isValid 代替 isValidVal');
        return this.isValid(val);
    };

    /*
     * 数组 | 对象 判断是否存在值！
     * @param  Mixed           unit
     * @param  Object | Array  obj
     * @param  Boolean         isDeep
     * @return Boolean         true | false
     */
    g.contain = function(unit , obj , isDeep){
        isDeep		= g.type(isDeep) !== 'Boolean' ? false : isDeep;
        var typeRange	= ['Array' , 'Object'];
        var type		= g.type(obj);

        // 参数 2 类型检测
        var checkRange = function(type){
            var i = 0;
            for (i = 0; i < typeRange.length; ++i)
            {
                if (type === typeRange[i]) {
                    return true;
                }
            }

            return false;
        };

        if (!checkRange(type)) {
            throw new TypeError('参数 2 类型错误');
        }

        // 检索函数：顺序检索
        var check = function(obj){
            var k           = null;
            var isExists    = null;
            var cur         = null;

            for (k in obj)
            {
                cur = obj[k];
                if (unit === cur) {
                    return true;
                }

                type = g.type(cur);

                if (isDeep && checkRange(type)) {
                    isExists = check(cur);

                    if (isExists) {
                        return true;
                    }
                }
            }

            return false;
        };

        return check(obj);
    };

    /*
     * 将对象 转换为 json 字符串 json_encode 的增强版
     * @param Object | Array  obj
     * @return String
     * 支持中文的转译
     */
    g.jsonEncode = function(obj){
        return JSON.stringify(obj);
    };

    /*
     * 解码 json 字符串
     * @param String jsonStr
     * @return Object
     */
    g.jsonDecode = function(jsonStr){
        return JSON.parse(jsonStr);
    };

    /*
     * 过滤 非单词字符边界处 空格 \r \n \r\n
     * @param String str 将要过滤的字符串
     * todo 待测试
     */
    g.trimAll = function(str){
        str = str.replace(/^( |\r|\n)+/g , '');
        str = str.replace(/( |\r|\n)+$/g , '');
        str = str.replace(/(\W)( |\r|\n)+/g , '$1');
        str = str.replace(/( |\r|\n)+(\W)/g , '$2');
        return str;
    };

    /*
     * 过滤 左右两边 空格 \r \n \r\n
     * @param String str 将要过滤的字符串
     */
    g.trim = function(str){
        str = this.lTrim(str);
        str = this.rTrim(str);
        return str;
    };

    /*
     * 过滤 左边开头的 空格 \r \n \r\n
     * @param String str 将要过滤的字符串
     */
    g.lTrim = function(str){
        return str.replace(/^( |\r|\n)*/ , '');
    };

    /*
     * 过滤 右边开头的 空格 \r \n \r\n
     * @param String str 将要过滤的字符串
     */
    g.rTrim = function(str){
        return str.replace(/( |\r|\n)*$/ , '');
    };

    /*
     * 过滤： 对象|数组 中 重复 无效 的单元
     * 不支持 DOM 元素的过滤
     * 过滤无效值 null undefined ''
     * @param Object|Array						    obj 对象|数组
     * @param Boolean isRemoveRepeatUnit			是否移除重复的单元
     * @param Boolean isDeep					    是否执行递归过滤
     * @return Object|Array
     * todo 待测试 + 完善
     */
    g.filterObj = function(obj , isRemoveRepeatUnit , isDeep){
        var typeRange = ['Array' , 'Object'];
        var type      = null;
        var objCopy   = null;
        var remove    = null;

        if (g.type(isRemoveRepeatUnit) !== 'Boolean') {
            isRemoveRepeatUnit = true;
        }

        if (g.type(isDeep) !== 'Boolean') {
            isDeep = true;
        }

        objCopy = g.copyObj(obj);
        remove = function(objCopy){
            var removeKey = [];
            var tempObj = {};

            type = g.type(objCopy);

            if (!g.contain(type , typeRange)) {
                throw new TypeError('type 参数错误');
            }

            if (type === 'Array') {

                // 过滤无效值
                for (var i = 0; i < objCopy.length; ++i)
                {
                    type = g.type(objCopy[i]);

                    if (g.contain(type , typeRange)) {
                        continue;
                    }

                    if (!g.isValidVal(objCopy[i])) {
                        objCopy.splice(i , 1);
                        i--;
                    }
                }

                // 删除重复值
                if (isRemoveRepeatUnit) {
                    for (var i = 0; i < objCopy.length; ++i)
                    {
                        for (var n = i + 1; n < objCopy.length; ++n)
                        {
                            if (objCopy[i] === objCopy[n]) {
                                objCopy.splice(n , 1);
                                i--;
                            }
                        }
                    }
                }

                // 是否递归过滤
                if (isDeep) {
                    for (var i = 0; i < objCopy.length; ++i)
                    {
                        type = g.type(objCopy[i]);

                        if (g.contain(type , typeRange)) {
                            remove(objCopy[i]);
                        }
                    }
                }
            } else {
                // 过滤无效值
                for (var key in objCopy)
                {
                    type = g.type(objCopy[key]);

                    if (g.contain(type , typeRange)) {
                        continue;
                    }

                    if (!g.isValidVal(objCopy[key])) {
                        delete objCopy[key];
                    }
                }

                // 删除重复值
                if (isRemoveRepeatUnit) {
                    for (var key in objCopy)
                    {
                        if (!g.contain(objCopy[key] , tempObj)) {
                            tempObj[key] = objCopy[key];
                        }
                    }
                    objCopy = tempObj;
                }

                // 是否递归过滤
                if (isDeep) {
                    for (var key in objCopy)
                    {
                        type = g.type(objCopy[key]);

                        if (g.contain(type , typeRange)) {
                            remove(objCopy[key]);
                        }
                    }
                }
            }
        };

        remove(objCopy);

        return objCopy;
    };


    /*
     * 递归 对象|数组 拷贝
     * @param Object obj 数组 | 对象
     * @return Object
     */
    g.copyObj = function(obj , isDeep){
        var typeRange = ['Array' , 'Object'];
        var rel		  = g.type(obj)    === 'Object'  ? {}     : [];
        var isDeep    = g.type(isDeep) === 'Boolean' ? isDeep : true;
        var copy;

        // 核心函数
        copy = function(A , B){
            var aDesc = g.type(A);
            var bDesc = g.type(B);
            var cur;
            var curDesc;
            var k;

            if (!g.contain(aDesc , typeRange)) {
                throw new TypeError('参数 1 类型错误');
            }

            if (!g.contain(bDesc , typeRange)) {
                throw new TypeError('参数 2 类型错误');
            }

            if (aDesc !== bDesc) {
                throw new TypeError('所有参数类型不一致');
            }

            for (k in B)
            {
                cur = B[k];
                curDesc = g.type(cur);

                if (isDeep && g.contain(curDesc , typeRange)) {
                    A[k] = curDesc === 'Object' ? {} : [];
                    copy(A[k] , cur);
                } else {
                    A[k] = cur;
                }
            }
        };

        copy(rel , obj);

        return rel;
    };

    // 复制对象
    g.copy = g.copyObj;

    // 合成对象（该版本）
    g.assign = function(){
        var args = this.array(arguments);
        var check = args.every(function(v){
            return g.isObject(v);
        });
        if (!check) {
            throw new TypeError('所有参数必须为对象');
        }
        var complex = ['Object' , 'Array'];
        var res = args[0];
        var other = args.slice(1);
        other.forEach(function(copy){
            var k = null;
            var v = null;
            var type = null;
            for (k in copy)
            {
                v = copy[k];
                type = g.type(v);
                res[k] = g.contain(type , complex) ? g.copy(v) : v;
            }
        });
        return res;
    };

    // 创建新的 object
    g.createObject = function(){
        var arr = g.array(arguments);
        arr.unshift({});
        return Object.assign.apply(Object , arr);
    };

    /*
     * 将一个类数组对象转换为数组
     * @param  Object  obj
     * @param  Number  start
     * @param  Number  end
     * @return Array
     */
    g.array = function(obj , saveAll){
        saveAll = this.isBoolean(saveAll) ? saveAll : false;

        var arr = [];

        if (saveAll) {
            for (var key in obj)
            {
                arr.push(obj[key]);
            }
        } else {
            var i = 0;

            for (; i < obj.length; ++i)
            {
                arr.push(obj[i]);
            }
        }

        return arr;
    };

    // 一维数组：希尔排序（高级排序算法）
    g.shellSort = function(arr , sortType){
        var sortTypeRange = ['asc' , 'desc'];
        var sortType      = !g.contain(sortType , sortTypeRange) ? 'asc' : sortType;
        // 交换的中间值
        var tempVal		  = null;
        // 数组长度
        var arrLen		  = arr.length;
        // 基准值
        var divisor		  = 3;
        // 间隔
        var interval	  = 1;
        var i;
        var n;

        if (g.type(arr) !== 'Array') {
            throw new TypeError('参数 1 类型错误');
        }

        // 计算动态间隔序列
        while (interval < arrLen / divisor)
        {
            interval = interval * divisor + 1;
        }

        // 排序
        while (interval >= 1)
        {
            for (i = interval; i < arrLen; ++i)
            {
                for (n = i - interval; n >= 0; n -= interval)
                {
                    if (sortType === 'asc' ? arr[n + interval] < arr[n] : arr[n + interval] > arr[n]) {
                        tempVal    = arr[n];
                        arr[n]     = arr[n + interval];
                        arr[n + interval] = tempVal;
                    } else {
                        break;
                    }
                }
            }

            // 反向工程
            interval = (interval - 1) / divisor;
        }

        return arr;
    };

    /*
     * 一维数组：冒泡排序（基本排序算法）
     * 原理：每一次循环都会将最值移动到端电上
     * @param  Array   arr
     * @param  String  sortType
     * @return Array
     */
    g.bubbleSort = function(arr , sortType){
        var sortTypeRange = ['asc' , 'desc'];
        var sortType	  = !g.contain(sortType , sortTypeRange) ? 'asc' : sortType;
        // 作为中间值，进行交换
        var cur  = null;
        // 比较值的索引
        var compareindex = null;
        var i;
        var n;

        if (g.type(arr) !== 'Array') {
            throw new TypeError('参数 1 类型错误');
        }

        for (i = 0; i < arr.length; ++i)
        {
            for (n = 0; n < arr.length - i; ++n)
            {
                compareindex = n + 1;

                // 确保不会和无效值进行交换
                if (compareindex >= arr.length - i) {
                    break;
                }

                if (sortType === 'asc' ? arr[n] > arr[compareindex] : arr[n] < arr[compareindex]) {
                    cur    = arr[n];
                    arr[n] = arr[compareindex];
                    arr[compareindex]   = cur;
                }
            }
        }

        return arr;
    };

    /*
     * 一维数组：选择排序（基本排序算法）
     * 原理：每一次循环都会将最值移动到端电上
     * @param  Array   arr
     * @param  String  sortType
     * @return Array
     */
    g.selectionSort = function(arr , sortType){
        var sortTypeRange = ['asc' , 'desc'];
        sortType	  = !g.contain(sortType , sortTypeRange) ? 'asc' : sortType;
        // 作为中间值，进行交换
        var cur;
        var index;
        var i;
        var n;

        if (g.type(arr) !== 'Array') {
            throw new TypeError('参数 1 类型错误');
        }


        for (i = 0; i < arr.length; ++i)
        {
            index = i;

            for (n = i + 1; n < arr.length; ++n)
            {
                if (sortType === 'asc' ? arr[index] > arr[n] : arr[index] < arr[n]) {
                    index = n;
                }
            }

            if (i !== index) {
                cur       = arr[i];
                arr[i]    = arr[index];
                arr[index]  = cur;
            }
        }

        return arr;
    };

    // 判断奇数 偶数
    g.oddEven = function(num){
        num = parseInt(num);
        var b = 2;
        if (num % b !== 0) {
            return 'odd';
        } else {
            return 'even';
        }
    };

    /*
     * 查询字符串解析
     * @return Ojbect 解析后的 key => val 键值对 对象
     */
    g.queryString = function(){
        var str    = decodeURIComponent(window.location.search);
        var result = '';
        var obj    = {};
        var assoc  = null;

        if (str === '') {
            return false;
        }

        str = g.trim(str.substring(1));

        result = str.split('&');

        for (var i = 0; i < result.length; ++i)
        {
            assoc = result[i].split('=');

            if (assoc.length !== 2) {
                continue;
            }

            obj[assoc[0]] = assoc[1];
        }

        return obj;
    };

    // 解析 url 地址
    g.parse = function(url){
        url = this.isString(url) ? url : '';

        var path = url;

        // 获取 hash
        var hashReg = /#(.*)/;
        var qsReg = /\?(.*)/;

        var hash = path.match(hashReg);
        hash = this.isNull(hash) ? '' : (hash[1] ? hash[1] : '');

        path = path.replace(hash , '');

        var qs = path.match(qsReg);
        qs = this.isNull(qs) ? '' : (qs[1] ? qs[1] : '');

        path = path.replace(qs , '');
        path = path.replace(/\/?\??$/ , '');

        var _qs = qs.split('&').filter(function(v){
            return v.length > 0;
        });
        var qsRes = {};

        _qs.forEach(function(v){
            v = v.split('=');

            var k1 = v[0];
            var v1 = v[1] ? v[1] : '';

            qsRes[k1] = v1;
        });

        return {
            fullPath: url ,
            path: path ,
            query: qsRes ,
            hash: hash
        };
    };

    // 获取文件名
    g.getFilename = function(filePath){
        var filePath  = filePath.replace('\\' , '/');
        var sIndex      = filePath.lastIndexOf('/') + 1;
        var eIndex		= filePath.lastIndexOf('.');

        return filePath.substring(sIndex === -1 ? 0 : sIndex , eIndex);
    };

    // 获取文件后缀名（文件类型）
    g.getFileSuffix = function(filePath){
        return filePath.substring(filePath.lastIndexOf('.') + 1);
    };

    // 获取浏览器
    g.getBrowser = function(){
        console.warn('该方法已经不推荐使用，请使用 browser 替代');
        return this.browser();
    };


    /**
     * 判断浏览器
     * @return mobile | firefox | edge | ie8 ie9 ie10 ie_lower | chrome | opera
     */
    g.browser = function(){
        var b = window.navigator.userAgent.toLowerCase();

        if (b.search('mobile') !== -1) {
            return 'mobile';
        }

        if (b.search('firefox') !== -1) {
            return 'firefox';
        }

        if (b.search('trident') !== -1) {
            var pattern = /msie \d{1,2}\.0/i;
            var result = b.match(pattern);

            if (result === null) {
                return 'edge';
            }

            result = result[0].toLowerCase();

            switch(result)
            {
                case 'msie 7.0':
                    return 'ie_lower';
                case 'msie 8.0':
                    return 'ie8';
                case 'msie 9.0':
                    return 'ie9';
                case 'msie 10.0':
                    return 'ie10';
            }
        }

        if (b.search('opr') !== -1) {
            return 'opera';
        }

        return 'chrome';
    };

    /*
     * 检索样式表中是否存在某规则
     * @param String className
     * @return Boolean
     */
    g.scn = function(className){
        var sList	  = document.styleSheets;
        var cssRules = null;
        var selector = '';

        for (var i = 0; i < sList.length; ++i)
        {
            cssRules = sList[i].cssRules;

            for (var n = 0; n < cssRules.length; ++n)
            {
                selector = cssRules[n].selectorText;

                if (selector === className) {
                    return true;
                }
            }
        }

        return false;
    };

    /*
     * 倒计时函数
     * @param Number   time 	倒计时时长 单位：s
     * @param Number   step 	步进
     * @param Number   stepFn 	每次步进回调
     * @param function fn   	完成时回调
     * @return json
     */
    g.timeCount = function(time , step , stepFn , fn){
        var time = g.type(time) !== 'Number'   ? 5    : time;
        var step = g.type(step) !== 'Number'   ? 1    : step;
        var self = this;

        if (time >= 0){
            if (g.type(stepFn) === 'Function') {
                stepFn(time , self.timeCountTimer);
            }

            time -= step;

            self.timeCountTimer = window.setTimeout(function(){
                self.timeCount(time , step , stepFn , fn);
            } , 1000);
        } else {
            if (g.type(fn) === 'Function') {
                fn(self.timeCountTimer);
            }
        }
    };


    /*
     * 获取二进制文件的 间接 url
     * @param BlobFile   file |  二进制文件
     * @param Function   fn    处理生成的 blob url 的回调函数
     * @return undefined
     */
    g.getBlobUrl = function(file , fn){
        var URL     = window.URL || window.webkitURL;
        var reader  = null;

        if (g.type(URL) === 'Undefined') {
            reader = new FileReader();
            reader.readAsDataURL(file);	//  FileReader 支持的： readAsText | readAsArrayBuffer | readAsDataURL | readAsBinaryString

            g(reader).on('load' , function(){
                if (g.type(fn) === 'Function') {
                    fn(this.result);
                }
            } , false , false);

            return ;
        }

        if (g.type(fn) === 'Function') {
            fn(URL.createObjectURL(file));
        }
    };

    /**
     * ***************
     * ajax 请求简化
     * ***************
     */
    g.get = function(url , option){

    };

    /*
     * 销毁一个由 URL.createObjectURL 创建的 blob://URL
     */
    g.revokeBlobUrl = function(blobUrl){
        var URL = window.URL || window.webkitURL || false;

        if (!URL) {
            throw new Error('浏览器不支持URL对象！请升级浏览器至IE10+');
        }

        URL.revokeObjectURL(blobUrl);
    };

    /*
     * 将一个 base64编码后的字符串 转为二进制对象！
     */
    g.base64ToBlob = function(base64String){
        var reg  = /^data:(.+);base64,(.)+$/;
        var mime = '';
        var blob = null;
        var arr  = null;
        var b    = null;

        if (!reg.test(base64String)) {
            throw new TypeError('不是 base64 编码后的字符串！');
        }

        mime = base64String.match(/data:(.+);/)[1];
        blob = window.atob(base64String.split(',')[1]);
        arr = [];
        arr.push(blob);
        b = new Blob(arr , {type:mime});
        return b;
    };

    /*
     * rem布局时，设置 html 元素字体大小的函数
     */
    g.setRootFontSize = function(width , fontSize){
        var html = this(document.documentElement);
        var clientW = html.clientWidth();
        clientW = clientW < 0 ? 0.01 : clientW;
        var widthAmount     = Math.abs(width - clientW);
        var fontAmount      = fontSize * (widthAmount / width);
        var endForFontSize  = fontSize;

        if (width > clientW) {
            endForFontSize -= fontAmount;
        } else {
            endForFontSize += fontAmount;
        }

        endForFontSize = Math.max(0 , endForFontSize);

        html.css({
            fontSize: endForFontSize + 'px'
        });
    };

    // 动画定时器
    g.RAF = function(fn){
        var freq = Math.floor(1000 / 60);
        var requestAnimationFrame = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || false;

        if (requestAnimationFrame) {
            return requestAnimationFrame(fn);
        }

        return window.setTimeout(fn , freq);
    };

    // 清除动画定时器
    g.CAF = function(timerId){
        var cancelAnimationFrame = window.cancelAnimationFrame || window.webkitCancelAnimationFrame || window.mozCancelAnimationFrame || false;

        if (cancelAnimationFrame) {
            return cancelAnimationFrame(timerId);
        }

        window.clearTimeout(timerId);
    };

    // 角度转换为弧度
    g.getRad = function(deg){
        return deg * Math.PI / 180;
    };

    // 弧度转换为角度
    g.getDeg = function(rad){
        return rad * 180 / Math.PI;
    };

    /*
     * 获取图片的真实尺寸
     * @param String    url 图片的 URL
     * @param Function  fn  回调函数
     * @return Array
     */
    g.getImage = function(url , fn){
        var img = new Image();

        g(img).on('load' , function(){
            var info = {
                url:    img.src ,
                width:  img.width ,
                height: img.height
            };

            if (g.type(fn) === 'Function') {
                fn(info);
            } else {
                console.log('图片信息：' , info);
            }

        } , false , false);

        img.src = url;
    };

    /*
     * 随机获取颜色
     */
    g.generateRandomColor = function(option){
        if (g.type(option) === 'Undefined') {
            var option = {
                minR: 0 ,
                maxR: 255 ,
                minG: 0 ,
                maxG: 255 ,
                minB: 0 ,
                maxB: 255 ,
                minA: 0 ,
                maxA: 1000 ,
                endA: undefined ,
                alpha: undefined ,
                len: 1000
            };
        }

        var R = null;
        var G = null;
        var B = null;
        var A = null;
        var alphaRatio = null;
        var colorList = null;

        option['minR']	= g.type(option['minR']) === 'Undefined'  || g.type(option['minR']) !== 'Number'  || (option['minR'] < 0  || option['minR'] > 255) ? 0			: option['minR'];
        option['maxR']	= g.type(option['maxR']) === 'Undefined'  || g.type(option['maxR']) !== 'Number'  || (option['maxR'] < 0  || option['maxR'] > 255) ? 255		    : option['maxR'];

        if (option['minR'] > option['maxR']) {
            throw new RangeError('R通道数据错乱！');
        }

        option['minG']	= g.type(option['minG']) === 'Undefined'  || g.type(option['minG']) !== 'Number'  || (option['minG'] < 0  || option['minG'] > 255) ? 0			: option['minG'];
        option['maxG']	= g.type(option['maxG']) === 'Undefined'  || g.type(option['maxG']) !== 'Number'  || (option['maxG'] < 0  || option['maxG'] > 255) ? 255			: option['maxG'];

        if (option['minG'] > option['maxG']) {
            throw new RangeError('G通道数据错乱！');
        }

        option['minB']	= g.type(option['minB']) === 'Undefined'  || g.type(option['minB']) !== 'Number'  || (option['minB'] < 0  || option['minB'] > 255) ? 0			: option['minB'];
        option['maxB']	= g.type(option['maxB']) === 'Undefined'  || g.type(option['maxB']) !== 'Number'  || (option['maxB'] < 0  || option['maxB'] > 255) ? 255			: option['maxB'];

        if (option['minB'] > option['maxB']) {
            throw new RangeError('B通道数据错乱！');
        }

        option['minA']	= g.type(option['minA']) === 'Undefined'  || g.type(option['minA']) !== 'Number'  || (option['minA'] < 0  || option['minA'] > 1000) ? 0			: option['minA'];
        option['maxA']	= g.type(option['maxA']) === 'Undefined'  || g.type(option['maxA']) !== 'Number'  || (option['maxA'] < 0  || option['maxA'] > 1000) ? 1000		: option['maxA'];

        if (option['minA'] > option['maxA']) {
            throw new RangeError('A通道数据错乱！');
        }

        option['alpha'] = g.type(option['alpha']) === 'Undefined' || g.type(option['alpha']) !== 'Number' || (option['alpha'] < 0 || option['alpha'] > 1)    ? undefined	: option['alpha'];
        option['len']   = g.type(option['len']) === 'Undefined'   || g.type(option['len']) !== 'Number'   || (option['len'] < 0   || option['len'] > 600000) ? 1000		: option['len'];
        alphaRatio = 1000;
        colorList = [];

        for (var i = 0; i < option['len']; ++i)
        {
            R = random(option['minR'] , option['maxR']);
            G = random(option['minG'] , option['maxG']);
            B = random(option['minB'] , option['maxB']);
            A = g.type(option['alpha']) === 'Undefined' ? random(option['minA'] , option['maxA']) / alphaRatio : option['alpha'];

            colorList.push('RGBA(' + R + ' , ' + G + ' , ' + B + ' , ' + A + ')');
        }

        return colorList;
    };


    /*
     * 以指定长度将数组分割成更小的数组的集合
     * @param Array      arr        待分割的数组
     * @param Number     chunkLen   每隔多少个单元分一组
     * @param Array
     */
    g.arrChunk = function(arr , chunkLen){
        if (!(arr instanceof Array)) {
            throw new TypeError('arr 不是数组类型！');
        }

        if (g.type(chunkLen) !== 'Number') {
            throw new TypeError('chunkLen 不是数字');
        }

        var rel = [];
        var curRel = [];

        for (var i = 0; i < arr.length; ++i)
        {
            if ((i + 1) % chunkLen !== 0) {
                curRel.push(arr[i]);
            } else {
                curRel.push(arr[i]);
                rel.push(curRel);
                curRel = [];
            }
        }

        return rel;
    };

    /*
     * 将类型化数组 转换为 普通数组
     * @param   ArrayBuffer arr  带转换的类型化数组
     * @return  Array
     */
    g.abToa = function(ab){
        return Array.apply([] , ay);
    };

    g.cookie = {
        // 解析 cookie
        parse: function(cookie){
            var res = {};

            cookie = cookie.split(';');
            cookie.forEach(function(v , k , a){
                v = g.trim(v);
                var item = v.split('=');
                res[item[0]] = item[1];
            });

            return res;
        } ,

        // 获取 cookie
        get: function(key){
            var cookie = this.parse(document.cookie);

            if (g.isUndefined(key)) {
                return cookie;
            }

            return cookie[key];
        } ,

        // 设置 cookie
        set: function(k , v , time , path , domain , secure){
            var cookie = k + '=' + v;

            if (g.isInt(time)) {
                cookie += '; max-age=' + time;
            }

            if (g.isValid(path)) {
                cookie += '; path=' + path;
            }

            if (g.isValid(domain)) {
                cookie += '; domain=' + domain;
            }

            if (g.isValid(secure)) {
                cookie += '; secure=' + secure;
            }

            document.cookie = cookie;
        } ,

        exists: function(key){
            var cookies = this.get();

            for (var k in cookies)
            {
                if (k === key) {
                    return true;
                }
            }

            return false;
        } ,

        // 删除
        del: function(k , path , domain){
            if (!this.exists(k)) {
                return ;
            }

            this.set(k , '' , -1 , path , domain);
        } ,

        // 清空 cookie
        clear: function(){
            var cookies = this.get()
            var k;
            var v;

            for (k in cookies)
            {
                v = cookies[k];

                this.del(k);
            }
        }
    };

    // localStorage 操作
    g.storage = {
        get: function(k){
            return window.localStorage.getItem(k);
        } ,

        set: function(k , v){
            if (g.isUndefined(v) && g.isObject(k)) {
                var k1 = null;
                var v1 = null;
                for (k1 in k)
                {
                    v1 = k1[k1];
                    window.localStorage.setItem(k1 , v1);
                }
                return ;
            }

            window.localStorage.setItem(k , v);
        } ,

        exists: function(k){
            return !g.isNull(this.get(k));
        } ,

        del: function(k){
            if (g.isArray(k)) {
                k.forEach(function(v){
                    window.localStorage.removeItem(v);
                });
                return ;
            }

            window.localStorage.removeItem(k);
        } ,

        clear: function(){
            return window.localStorage.clear();
        } ,

        // json 操作
        json: function(k , v){
            if (g.isUndefined(v)) {
                return g.jsonDecode(this.get(k));
            }

            this.set(k , g.jsonEncode(v));
        }
    };

    // 树遍历
    g.tree = {
        // 默认字段
        _field: {
            id: 'id' ,
            p_id: 'p_id'
        } ,

        // 字段修正
        field: function(field){
            if (g.isUndefined(field)) {
                return this._field;
            }
            if (g.isUndefined(field.id)) {
                throw new Error('提供的字段对象不包含 id 属性');
            }
            if (g.isUndefined(field.p_id)) {
                throw new Error('提供的字段对象不包含 p_id 属性');
            }
            return field;
        } ,

        // 当前项
        current: function(id , data , field){
            field = this.field(field);
            var i = 0;
            var v = null;
            for (; i < data.length; ++i)
            {
                v = data[i];
                if (v[field['id']] == id) {
                    return v;
                }
            }
            return null;
        } ,

        // 单个：直系父级
        parent: function(id , data , field){
            field = this.field(field);
            var cur    = this.current(id , data , field);
            var i = 0;
            var v = null;
            for (i = 0; i < data.length; ++i)
            {
                v = data[i];
                if (v[field['id']] == cur[field['p_id']]) {
                    return v;
                }
            }
            return null;
        } ,

        // 全部：父级
        parents: function(id , data , field , self , struct){
            field   = this.field(field);
            self    = g.isBoolean(self) ? self : true;
            struct  = g.isBoolean(struct) ? struct : true;
            var cur = this.current(id , data  , field);
            var _self_ = this;

            var get = function(cur , res){
                var parent = _self_.parent(cur[field['id']] , data , field);
                if (g.isNull(parent)) {
                    return res;
                }
                res.push(parent);
                return get(parent , res);
            };

            var parents = get(cur , []);
            if (self) {
                // 保留自身
                parents.unshift(cur);
            }
            parents = parents.reverse();
            if (struct) {
                var get_struct = function(list , res){
                    if (list.length === 0) {
                        return res;
                    }
                    cur = list.shift();
                    res = cur;
                    res.children = get_struct(list);
                    return res;
                };
                parents = get_struct(parents);
            }
            return parents;
        } ,

        // 直系全部：子级
        children: function(id , data , field){
            field  = this.field(field);
            var res    = [];
            var i = 0;
            var v = null;
            for (; i < data.length; ++i)
            {
                v = data[i];
                if (v[field['p_id']] == id) {
                    res.push(v);
                }
            }
            return res;
        } ,

        // 所有子级
        childrens: function(id , data , field , self , struct){
            field   = this.field(field);
            self    = g.isBoolean(self) ? self : true;
            struct  = g.isBoolean(struct) ? struct : true;
            var cur = this.current(id , data , field);
            var _self_ = this;

            var get = function(id){
                var res    = _self_.children(id , data , field);
                var v           = null;
                var i           = 0;
                var other       = null;
                var len         = res.length;
                for (; i < len; ++i)
                {
                    v = res[i];
                    other = get(v[field['id']]);
                    other.unshift(0);
                    other.unshift(res.length);
                    res.splice.apply(res , other);
                }
                return res;
            };
            var res = get(id);
            if (self && cur !== false) {
                // 保存自身
                res.unshift(cur);
            }
            if (struct) {
                // 保存结构
                var get_struct = function(id){
                    var children = _self_.children(id , res , field);
                    var i = 0;
                    var v = null;
                    for (; i < children.length; ++i)
                    {
                        v = children[i];
                        v.children = get_struct(v[field['id']]);
                    }
                    return children;
                };
                res = get_struct(id);
                if (self && cur !== false) {
                    cur.children = res;
                    res = cur;
                }
            }
            return res;
        } ,
    };

    /*
     * SmallJs 自有属性速查表
     */
    g.guide = function(){
        var methodList = {};
        var keys       = Object.getOwnPropertyNames(SmallJs);

        for (var i = 0; i < keys.length; ++i)
        {
            methodList[keys[i]] = SmallJs[keys[i]];
        }

        return methodList;
    };

    /*
     * 根据给定的时间长度转化为预定义格式的时间
     * @param Number  duration		时长，单位是 s
     * @param String  format		返回的时间格式
     * @param Boolean isZeroAdd		分以上值为 0 是否需要严格按照格式进行返回，是 值为0的也会自动填充，否则不会
     * @param Boolean isReturnJson	是否返回 Json 数据
     * @return Mixed
     */
    g.formatTime = function(duration , format , isZeroAdd , isReturnJson){
        var formatRange = ['D天H时I分S秒' , 'HH:II:SS' , 'HH时II分SS秒'];
        var format		= !g.contain(format , formatRange) ? 'D天H时I分S秒' : format;
        var isZeroAdd	= g.type(isZeroAdd) !== 'Boolean' ? true : isZeroAdd;
        var isReturnJson = g.type(isReturnJson) !== 'Boolean' ? false : isReturnJson;

        var sRatio      = 1;
        var iRatio		= 60;
        var hRatio		= 60 * 60;
        var dRatio		= 24 * 60 * 60;
        var d			= null;
        var h			= null;
        var i			= null;
        var s			= null;

        // 获取换算后时间
        var getTime		= function(time , ratio , stepRatio){
            time = Math.floor(time / ratio);

            while (time >= stepRatio)
            {
                time %= stepRatio;
            }

            return time;
        };

        // 时间格式化处理
        var stringProcessing = function(){
            if (g.type(d) !== 'Null' && d < 10 && isZeroAdd) {
                d = '0' + d;
            }

            if (g.type(h) !== 'Null' && h < 10 && isZeroAdd) {
                h = '0' + h;
            }

            if (g.type(i) !== 'Null' && i < 10 && isZeroAdd) {
                i = '0' + i;
            }

            if (g.type(s) !== 'Null' && s < 10 && isZeroAdd) {
                s = '0' + s;
            }
        };

        if (format === 'D天H时I分S秒') {
            s = getTime(duration , sRatio , 60);
            i = getTime(duration , iRatio , 60);
            h = getTime(duration , hRatio , 24);
            d = Math.floor(duration / dRatio);

            stringProcessing();

            if (isReturnJson) {
                return {
                    day: d ,
                    hour: h ,
                    minute: i ,
                    second: s
                };
            }

            return d + '天' + h + '时' + i + '分' + s + '秒';
        }

        if (format === 'HH:II:SS') {
            s = getTime(duration , sRatio , 60);
            i = getTime(duration , iRatio , 60);
            h = Math.floor(duration / hRatio);

            stringProcessing();

            if (isReturnJson) {
                return {
                    hour: h ,
                    minute: i ,
                    second: s
                };
            }

            return h + ':' + i + ':' + s;
        }

        if (format === 'HH时II分SS秒') {
            s = getTime(duration , sRatio , 60);
            i = getTime(duration , iRatio , 60);
            h = Math.floor(duration / hRatio);

            stringProcessing();

            if (isReturnJson) {
                return {
                    hour: h ,
                    minute: i ,
                    second: s
                };
            }

            return h + '时' + i + '分' + s + '秒';
        }
    };

    /*
     * 获取一个时间的个部分值，以对象形式返回
     * 支持的时间格式：
     * date: 2016-10-10
     * datetime: 2016-10-10 23:25:58
     */
    g.parseTime = function(time , type){
        var typeRange = ['date' , 'datetime'];
        var result    = {};

        if (!g.contain(type , typeRange)) {
            throw new Error('时间格式错误！时间格式要求，类型 date：2016-01-10 或 datetime：2017-03-05 10:11:00');
        }

        if (type === 'date') {
            time	  = time.split('-');

            var year  = parseInt(time[0]);
            var month = parseInt(time[1]);
            var date  = parseInt(time[2]);

            return {
                year: year ,
                month:month ,
                date: date
            }
        }

        if (type === 'datetime') {
            time	  = time.split(' ');

            var ymd   = time[0].split('-');
            var his   = time[1].split(':');

            var year  = parseInt(ymd[0]);
            var month = parseInt(ymd[1]);
            var date  = parseInt(ymd[2]);
            var hour  = parseInt(his[0]);
            var min	  = parseInt(his[1]);
            var sec   = parseInt(his[2]);

            return {
                year: year ,
                month:month ,
                date: date ,
                hour: hour ,
                minute: min ,
                second: sec
            };
        }
    };

    /*
     * 获取当前时间点信息，格式化返回 返回格式是： 2017-05-27 22:54:00
     * @param  Boolean isReturnFormatTimeString 是否返回处理后字符串
     * @param  Boolean isZeroAdd				月、日、时、分、秒小于 10 是否前面添加 0
     * @return Object|String
     */

    g.getCurTimeData = function(isReturnFormatTimeString , isZeroAdd){
        var isReturnFormatTimeString = g.type(isReturnFormatTimeString) !== 'Boolean' ? false : isReturnFormatTimeString;
        var isZeroAdd				 = g.type(isZeroAdd) !== 'Boolean' ? true : isZeroAdd;

        var d	 = new Date();
        var y	 = d.getFullYear();
        var m	 = d.getMonth() + 1;
        var date = d.getDate();
        var h	 = d.getHours();
        var i	 = d.getMinutes();
        var s	 = d.getSeconds();

        if (!isReturnFormatTimeString) {
            return {
                year:   y ,
                month:  m ,
                date:   date ,
                hour:   h ,
                minute: i ,
                second: s
            };
        }

        var formatTimeString = '';

        if (isZeroAdd) {
            formatTimeString += y;

            if (m < 10) {
                formatTimeString += '-' + '0' + m;
            } else {
                formatTimeString += '-' + m;
            }

            if (date < 10) {
                formatTimeString += '-' + '0' + date;
            } else {
                formatTimeString += '-' + date;
            }

            if (h < 10) {
                formatTimeString += ' ' + '0' + h;
            } else {
                formatTimeString += ' ' + h;
            }

            if (i < 10) {
                formatTimeString += ':' + '0' + i;
            } else {
                formatTimeString += ':' + i;
            }

            if (s < 10) {
                formatTimeString += ':' + '0' + s;
            } else {
                formatTimeString += ':' + s;
            }

            return formatTimeString;
        }

        // 月、日、时、分、秒 小于 10 的时候，默认前面不加 0
        return y + '-' + m + '-' + date + ' ' + h + ':' + ':' + m + ':' + s;
    };

    /*
     * 根据给定的时间戳获取每个项的值
     * 格式 2017-05-10 10:49:00
     * @param  String timestamp 时间戳
     * @return String
     */
    g.getTimeData = function(timestamp){
        var timeReg = /^\d{4}-\d{1,2}-\d{1,2} \d{1,2}:\d{1,2}:\d{1,2}$/;

        if (!timeReg.test(timestamp)) {
            throw new Error('时间格式不正确');
        }

        var data = timestamp.split(' ');
        var ymd  = data[0].split('-');
        var his  = data[1].split(':');

        return {
            year:   parseInt(ymd[0]) ,
            month:  parseInt(ymd[1]) ,
            date:   parseInt(ymd[2]) ,
            hour:   parseInt(his[0]) ,
            minute: parseInt(his[1]) ,
            second: parseInt(his[2])
        };
    };

    /*
     * 返回两个时间戳的相差的毫秒数
     * 格式要求：2017-10-10 10:10:10
     */
    g.timestampDiff = function(sTime , eTime , type){
        var typeRange = ['date' , 'datetime'];
        var type	  = g.contain(type , typeRange) ? type : 'datetime';
        var t1 = g.parseTime(sTime , type);
        var t2 = g.parseTime(eTime , type);

        var d1 = new Date();
        var d2 = new Date();

        // 时间点1
        d1.setFullYear(t1['year']);
        d1.setMonth(t1['month'] - 1);
        d1.setDate(t1['date']);

        // 时间点2
        d2.setFullYear(t2['year']);
        d2.setMonth(t2['month'] - 1);
        d2.setDate(t2['date']);

        // 如果类型是 datetime 的时候，设置时分秒
        if (type === 'datetime') {
            d1.setHours(t1['hour']);
            d1.setMinutes(t1['minute']);
            d1.setSeconds(t1['second']);

            d2.setHours(t2['hour']);
            d2.setMinutes(t2['minute']);
            d2.setSeconds(t2['second']);
        }

        var d1Mill = d1.getTime();
        var d2Mill = d2.getTime();

        return d2Mill - d1Mill;
    };

    /**
     * unixtime 转换成 正常时间
     * @param  number  unixtime unix 时间戳
     * @param  String  type 返回的类型
     * @param  Boolean isReturnFormatTimeString 是否返回字符串（默认返回对象）
     * @param  Boolean isZeroAdd 是否对小于 10 的数加上前导0
     * @return Object|String
     */
    g.fromUnixtime = function(unixtime , type , isReturnFormatTimeString , isZeroAdd){
        var typeRange                = ['datetime' , 'date'];
        type                     = g.contain(type , typeRange) ? type : 'datetime';
        var isReturnFormatTimeString = g.type(isReturnFormatTimeString) !== 'Boolean' ? false : isReturnFormatTimeString;
        var isZeroAdd				 = g.type(isZeroAdd) !== 'Boolean' ? true : isZeroAdd;

        var d	 = new Date(unixtime);
        var y	 = d.getFullYear();
        var m	 = d.getMonth() + 1;
        var date = d.getDate();
        var h	 = d.getHours();
        var i	 = d.getMinutes();
        var s	 = d.getSeconds();

        if (!isReturnFormatTimeString) {
            if (type === 'date') {
                return {
                    year:   y ,
                    month:  m ,
                    date:   date
                };
            }

            return {
                year:   y ,
                month:  m ,
                date:   date ,
                hour:   h ,
                minute: i ,
                second: s
            };
        }

        var formatTimeString = '';

        if (isZeroAdd) {
            formatTimeString += y;

            if (m < 10) {
                formatTimeString += '-' + '0' + m;
            } else {
                formatTimeString += '-' + m;
            }

            if (date < 10) {
                formatTimeString += '-' + '0' + date;
            } else {
                formatTimeString += '-' + date;
            }

            if (h < 10) {
                formatTimeString += ' ' + '0' + h;
            } else {
                formatTimeString += ' ' + h;
            }

            if (i < 10) {
                formatTimeString += ':' + '0' + i;
            } else {
                formatTimeString += ':' + i;
            }

            if (s < 10) {
                formatTimeString += ':' + '0' + s;
            } else {
                formatTimeString += ':' + s;
            }

            return formatTimeString;
        }

        // 月、日、时、分、秒 小于 10 的时候，默认前面不加 0
        if (type === 'date') {
            return y + '-' + m + '-' + date;
        }

        return y + '-' + m + '-' + date + ' ' + h + ':' + ':' + m + ':' + s;
    };

    /**
     * 从格式化时间中返回 unixtime
     * @param formatTime 格式化时间
     * @param type 指明时间类型：datetime、date
     * @returns {Number|number}
     */
    g.unixTimestamp = function(formatTime , type){
        var typeRange = ['datetime' , 'date'];

        if (!g.contain(type , typeRange)) {
            throw new Error('参数 2 错误');
        }

        var info = g.parseTime(formatTime , type);

        var d = new Date();
        d.setFullYear(info['year']);
        d.setMonth(info['month'] - 1);
        d.setDate(info['date']);

        if (type === 'datetime') {
            d.setHours(info['hour']);
            d.setMinutes(info['minute']);
            d.setSeconds(info['second']);
        } else {
            d.setHours(0);
            d.setMinutes(0);
            d.setSeconds(0);
        }

        return d.getTime();
    };

    /*
     * 倒计时函数
     * @param  Number   sn
     * @param  Number   en
     * @param  Callback refreshFunc
     * @param  Callback timeOverFunc
     * @return Undefined
     */
    g.countTimeDown = function(sn , en , refreshFunc , timeOverFunc){
        if (g.type(sn) !== 'Number') {
            throw new Error('参数 1 类型错误');
        }

        if (g.type(en) !== 'Number') {
            throw new Error('参数 2 类型错误');
        }

        if (g.type(refreshFunc) !== 'Function') {
            throw new Error('参数 3 类型错误');
        }

        if (g.type(timeOverFunc) !== 'Function') {
            throw new Error('参数 4 类型错误');
        }

        if (sn - en > 0) {
            throw new Error('参数 1 必须小于参数 2');
        }

        var duration = en - sn;

        var go = function(){
            if (duration !== 0) {
                duration--;
                refreshFunc(duration);
                window.setTimeout(go , 1000);
            } else {
                timeOverFunc();
            }
        };

        go();
    };

    /*
     * 获取视频第一帧展示图
     * @param  String    url 视频 URL
     * @param  Function  fn  处理完后的回调，会将处理完后的链接传给第一个参数
     * @param  Object    option 视频截取的尺寸设置
     option = {
     width: 300 ,   // > 0
     height: 300 ,  // > 0
     type: 'jpg' ,  // jpg | png
     quality: 1     // 0-1
     };
     * @return Undefined
     */
    g.getVideoShowPic = function(url , fn , option){
        var v   = document.createElement('video');
        var cav = document.createElement('canvas');
        var ctx = cav.getContext('2d');

        v = G(v);

        v.loginEvent('loadeddata' , function(){
            var self  = this;

            this.currentTime = 1;

            v.loginEvent('seeked' , function(){
                var vW = 0;
                var vH = 0;
                var typeRange = ['jpg' , 'png'];
                var type      = 'jpg'; // 图片类型
                var src		  = '';	   // 截取后的图片源
                var quality   = 1;     // 截取质量

                // 未设置截取视频的尺寸时，自动设置为视频的尺寸大小
                if (g.type(option) === 'Undefined') {
                    vW = this.videoWidth;
                    vH = this.videoHeight;

                    cav.width  = vW;
                    cav.height = vH;

                    ctx.drawImage(this , 0 , 0 , vW , vH , 0 , 0 , vW , vH);
                } else {
                    vW   = option['width'];
                    vH   = option['height'];
                    type = g.contain(option['type'] , typeRange) ? option['type'] : type;
                    quality = g.type(option['quality']) !== 'Number' || g.type(option['quality']) === 'Number' && option['quality'] > 0 && option['quality'] < 1 ? quality : option['quality'];

                    cav.width  = vW;
                    cav.height = vH;

                    ctx.drawImage(this , 0 , 0 , vW , vH);
                }

                switch (type)
                {
                    case 'jpg':
                        src = cav.toDataURL('image/jpeg' , quality);
                        break;
                    case 'png':
                        src = cav.toDataURL('image/png'  , quality);
                        break;
                }

                if (g.type(fn) === 'Function') {
                    fn(src);
                } else {
                    console.log('没有注册回调函数，第一帧展示图src源：' + src);
                }
            } , false , false);
        } , false , false);

        v.get().src = url;
    };

    /*
     * 本地化显示当前时间
     */
    g.showClock = function(fn , isReturnJson){
        var isReturnJson = g.type(isReturnJson) !== 'Boolean' ? false : isReturnJson;

        var setTime = function(){
            var d	 = new Date();
            var y	 = d.getFullYear();
            var m	 = d.getMonth() + 1;
            var date = d.getDate();
            var day  = d.getDay() + 1;
            var h	 = d.getHours();
            var i	 = d.getMinutes();
            var s	 = d.getSeconds();

            m	= m < 10 ? '0' + m : m;
            date = date < 10 ? '0' + date : date;
            h	= h < 10 ? '0' + h : h;
            i	= i < 10 ? '0' + i : i;
            s	= s < 10 ? '0' + s : s;

            switch (day)
            {
                case 1:
                    day = '日';
                    break;

                case 2:
                    day = '一';
                    break;

                case 3:
                    day = '二';
                    break;

                case 4:
                    day = '三';
                    break;

                case 5:
                    day = '四';
                    break;

                case 6:
                    day = '五';
                    break;

                case 7:
                    day = '六';
                    break;
            }
            var data = null;

            data = y + '-' + m + '-' + date + ' 星期' + day  + ' ' + h + ':' + i + ':' + s;

            if (isReturnJson) {
                data = {
                    year: y ,
                    month: m ,
                    date: date ,
                    day: day ,
                    hour: h ,
                    minute: i ,
                    second: s
                };
            }

            if (g.type(fn) === 'Function') {
                fn(data);
            }
            window.setTimeout(setTime , 1000);
        };

        setTime();
    };

    /*
     * **************
     SmallJs 扩展库
     * **************
     */

    /*
     * author 陈学龙 2016/09/16 Version 1.0
     * 普通动画扩展
     * 速度恒定不变 | 时间恒定不变
     * 范围动画(时间)
     * 使用方法：
     *   todo
     */

    function Animate(dom , json , callback , time , wait , delay){
        var thisRange = [window , null , undefined];

        if (g.contain(this , thisRange) || (!g.contain(this , thisRange) && this.constructor !== Animate)) {
            return new Animate(dom , json , callback , time , wait , delay);
        }

        if (!g.isDom(dom)) {
            throw new Error('参数 1 类型错误');
        }

        if (!g.isObj(json)) {
            throw new TypeError('参数 1 类型错误');
        }

        this._default = {
            dom: null ,
            time: 300 ,
            json: {} ,
            callback: null ,
            delay: 0 ,
            wait: false
        };

        // 静态参数
        this._dom       = dom;
        this._g         = g(dom);
        this._time      = g.isInt(time) ? time : this._default['time'];
        // 动画未完成是否不允许再次调用
        this._wait      = g.isBoolean(wait) ? wait : this._default['wait'];
        this._json      = g.isObj(json) ? json : this._default['json'];
        this._callback  = g.isFunction(callback) ? callback : this._default['callback'];
        this._delay     = g.isInt(delay) ? delay : this._default['delay'];

        this._run();
    }

    Animate.prototype = {
        version: '7.0' ,
        cTime: '2016/09/16 10:20:00' ,
        constructor: Animate ,
        _initStaticArgs: function(){
            // 用户设置
            this._styles = {};
            this._css3Range = [
                'translateX' ,
                'translateY' ,
                'translateZ' ,
                'rotateX' ,
                'rotateY' ,
                'rotateZ' ,
                'scaleX' ,
                'scaleY' ,
                'scaleZ' ,
                'skewX' ,
                'skewY' ,
                'skewZ'
            ];
            var k;
            var v;
            var unit;
            var amount;
            var sVal;
            var eVal;
            var reg = /\-?[0-9\.]+/;

            for (k in this._json)
            {
                // 普通属性
                v       = String(this._json[k]);
                v       = g.trim(v);
                eVal    = v.match(reg)[0];
                unit    = v.replace(eVal , '');
                eVal    = parseFloat(eVal);
                sVal    = this._css3Range.indexOf(k) >= 0 ? this._g.transform(true)[k] : parseFloat(this._g.css(k));
                amount  = eVal - sVal;

                this._styles[k] = {
                    unit: unit ,
                    amount: amount ,
                    sVal: sVal ,
                    eVal: eVal ,
                    // 是否完成
                    completed: false
                };
            }
        } ,

        _initStatic: function(){

        } ,

        _initDynamicArgs: function(){

        } ,

        _initDynamic: function(){

        } ,

        // 初始化静态参数
        _initialize: function(){

        } ,

        // 设置最终效果
        _end: function(){
            var k;
            var v;
            var cssEnd = {};
            var transformEnd = {};
            var res = '';
            for (k in this._styles)
            {
                v   = this._styles[k];
                res = v.eVal + v['unit'];
                if (this._css3Range.indexOf(k) >= 0) {
                    // css3 属性
                    transformEnd[k] = res;
                } else {
                    cssEnd[k] = res;
                }
            }
            this._g.transform(transformEnd);
            this._g.css(cssEnd);
        } ,
        // 检查动画是否已经完成
        _isCompleted: function(){
            var k   = null;
            var cur = null;
            for (k in this._styles)
            {
                cur = this._styles[k];
                if (!cur.completed) {
                    return false;
                }
            }
            return true;
        } ,

        _animate: function(){
            var self		= this;
            this._dom.__smalljs_animating__ = true;
            var timestamp = new Date().getTime();
            var startTime = timestamp + this._delay;
            var endTime = timestamp + this._time;
            // 初始化
            var animate = function(){
                g.CAF(self._dom.__smalljs_animate_timer__);
                var curTime     = new Date().getTime();
                var ratio       = (curTime - startTime) / self._time;
                    ratio       = Math.min(ratio , 1);
                var k;
                var v;
                var cssEnd = {};
                var transformEnd = {};
                var res = '';
                for (k in self._styles)
                {
                    v   = self._styles[k];
                    res = v.sVal + ratio * v.amount;
                    if (v.sVal >= v.eVal) {
                        // 缩小
                        res = Math.max(v.eVal , Math.min(v.sVal , res));
                    } else {
                        // 放大
                        res = Math.max(v.sVal , Math.min(v.eVal , res));
                    }
                    if (self._css3Range.indexOf(k) >= 0) {
                        transformEnd[k] = res + v['unit'];
                    } else {
                        cssEnd[k] = res + v['unit'];
                    }
                }
                self._g.css(transformEnd);
                self._g.css(cssEnd);
                if (curTime <= endTime) {
                    return self._dom.__smalljs_animate_timer__ = g.RAF(animate);
                }
                self._dom.__smalljs_animating__ = false;
                self._end();
                if (g.isFunction(self._callback)) {
                    self._callback();
                }
            };
            g.timer.clearTime(this._dom.__smalljs_animate_delay_timer__);
            this._dom.__smalljs_animate_delay_timer__ = g.timer.time(function(){
                animate();
                delete self._dom.__smalljs_animate_delay_timer__;
            } , this._delay);
        } ,

        _run: function(){
            if (this._wait && this._dom.__smalljs_animating__) {
                // 如果要求动画完成后再此进行动画的话
                // 判断是否正在动画中，如果当前正在动画中，那么中止执行
                return ;
            }
            this._initStaticArgs();
            this._initStatic();
            this._initDynamicArgs();
            this._initDynamic();
            this._initialize();
            this._animate();
        }
    };

    /*
     * author 陈学龙 2016/09/16
     * 元素移动类
     * 条件： dom 需设置 position:absolute ， 且初始化设置了 left , top 值
     * @param Element dom  待移动元素
     * @param Element con  移动元素所在移动的容器
     * @return 'Function'
     */
    function Move(dom , container , isLimit){
        var thisRange = [window , null , undefined];

        if (g.contain(this , thisRange) || (!g.contain(this , thisRange) && this.constructor !== Move)) {
            return new Move(dom , container , isLimit);
        }
        this._isLimit   = g.type(isLimit) !== 'Boolean' ? false : isLimit;
        this._dom		= g(dom);
        this._con		= g(container);

        this._run();
    }

    Move.prototype = {
        version: '1.0' ,
        cTime: '2016/10/28 10:05:00' ,
        constructor: Move ,

        _initialize: function(){
            this._canMove	=  false ;
            this._minLV		=  0 ;
            this._minTV		=  0 ;
            this._sox		=  0 ;
            this._soy		=  0 ;
            this._eox		=  0 ;
            this._eoy		=  0 ;
            this._ox		=  0 ;
            this._oy		=  0 ;
            this._curLV		=  0 ;
            this._curTV		=  0 ;
            this._eLV		=  0 ;
            this._eTV		=  0 ;
        } ,

        // 计算相关值
        _calculate: function(){
            this._conW		= this._con.width();
            this._conH		= this._con.height();
            this._eleW		= this._dom.width();
            this._eleH		= this._dom.height();
            this._maxLV		= Math.floor(Math.max(0 , this._conW - this._eleW));
            this._maxTV		= Math.floor(Math.max(0 , this._conH - this._eleH));
        } ,

        mouseDown: function(event){
            var e = event || window.event;
            e.stopPropagation();

            this._calculate();

            this._sox	  = browser === 'mobile' ? e.touches[0].clientX : e.clientX;
            this._soy	  = browser === 'mobile' ? e.touches[0].clientY : e.clientY;
            this._sLV	  = this._dom.getCoordVal('left');
            this._sTV	  = this._dom.getCoordVal('top');

            // console.log(this._sLV , this._sTV , this._sox , this._soy);
            this._canMove = true;

            this._dom.css({
                cursor: 'move'
            });
        } ,

        mouseUp: function(event){
            this._canMove = false;

            this._dom.css({
                cursor: 'default'
            });
        } ,

        mouseMove: function(event){
            if (this._canMove) {
                var e = event || window.event;
                e.preventDefault();

                this._eox = browser === 'mobile' ? e.touches[0].clientX  : e.clientX;
                this._eoy = browser === 'mobile' ? e.touches[0].clientY  : e.clientY;
                this._ox  = this._eox - this._sox;
                this._oy  = this._eoy - this._soy;

                if (this._isLimit) {
                    this._eLV = Math.max(this._minLV , Math.min(this._maxLV , this._sLV + this._ox));
                    this._eTV = Math.max(this._minTV , Math.min(this._maxTV , this._sTV + this._oy));
                } else {
                    this._eLV = this._sLV + this._ox;
                    this._eTV = this._sTV + this._oy;
                }

                this._dom.css({
                    cursor: 'move' ,
                    left: this._eLV + 'px' ,
                    top:  this._eTV + 'px'
                });
            }
        } ,

        _defineEvent: function(){

            // 注册事件
            this._dom.on(mouseDown  , this.mouseDown.bind(this)  , true , false);
            win.on(mouseMove     , this.mouseMove.bind(this)  , true  , false);
            win.on(mouseUp       , this.mouseUp.bind(this)    , true  , false);
        } ,

        _run: function(){
            this._initialize();
            this._defineEvent();
        }
    };


    // 集合操作
    function Set(){}

    Set.prototype = {
        author: '陈学龙' ,
        cTime: '2016/12/20 01:08:00' ,
        version: '1.0' ,
        construct: Set ,
        /*
         * 集合间关系：子集
         * 要求：只能是 Object Array Element Null Undefined Number Boolean String 类型的集合，不允许出现 例如： Function RegExp Date Math
         * 判断 objA 是否是 objB 的子集
         * @param Object objA 待比较对象
         * @param Object objB 比较对象
         * @return Boolean
         */
        isSubSet: function(objA , objB , isStrict){
            var typeRange		= ['Object' , 'Array' , 'Element'];
            var isStrict		= g.type(isStrict) === 'Boolean' ? isStrict : true;
            var self			= this;
            var recTypeRange	= ['Object' , 'Array'];
            var isSame		    = false;
            var compare;

            if (!g.contain(g.type(objA) , typeRange)) {
                throw new TypeError('参数 1 类型错误');
            }

            if (!g.contain(g.type(objB) , typeRange)) {
                throw new TypeError('参数 2 类型错误');
            }

            // 核心：比较函数
            // 作用：判断 A 对象中的单元 在 B 对象中是否存在
            compare  = function(A , B){
                var aDesc = g.type(A);
                var bDesc = g.type(B);
                var cur;
                var curC;
                var curDesc;
                var curCDesc;
                var k;
                var k1;
                var i;
                var n;

                // 先判断描述是否一致
                if (aDesc !== bDesc) {
                    return false;
                }

                // DOM 元素时
                if (g.isDom(A) || g.isDom(B)) {
                    if (A === B) {
                        return isSame = true;
                    }

                    return false;
                }

                // 对象要考虑键名 和 键值
                if (aDesc === 'Object') {
                    for (k in A)
                    {
                        cur		= A[k];
                        curDesc = g.type(cur);

                        for (k1 in B)
                        {
                            curC	 = B[k1];
                            curCDesc = g.type(curC);

                            if (isStrict) { // 比较键名 + 键值
                                // 判断键值是否相等
                                if (k === k1) {
                                    // 判断类型是否相等
                                    if (curDesc === curCDesc) {
                                        // 是否是 DOM 元素
                                        if (g.isDom(cur) || g.isDom(curC)) {
                                            if (cur === curC) {
                                                isSame = true;
                                                break;
                                            } else {
                                                isSame = false;
                                            }
                                        } else {
                                            // Array || Object时，递归判断
                                            if (g.contain(curDesc , recTypeRange)) {
                                                compare(cur , curC);
                                                // 之所以要 break 的理由：首先键名相等，其次键值类型一致，再次符合当前判断条件，也就是说，执行到这儿，已经确定了 cur和curC 的值了
                                                break;
                                            } else {
                                                // 单纯的值则直接比较
                                                if (cur === curC) {
                                                    isSame = true;
                                                    break;
                                                } else {
                                                    isSame = false;
                                                }
                                            }
                                        }
                                    } else {
                                        isSame = false;
                                    }
                                } else {
                                    isSame = false;
                                }
                            } else {// 只比较键值，其他同上
                                if (curDesc === curCDesc) {
                                    if (g.isDom(cur) || g.isDom(curC)) {
                                        if (cur === curC) {
                                            isSame = true;
                                            break;
                                        } else {
                                            isSame = false;
                                        }
                                    } else {
                                        if (g.contain(curDesc , recTypeRange)) {
                                            compare(cur , curC);
                                            // 之所以要 break 的理由：首先键名相等，其次键值类型一致，再次符合当前判断条件，也就是说，执行到这儿，已经确定了 cur和curC 的值了
                                            break;
                                        } else {
                                            if (cur === curC) {
                                                isSame = true;
                                                break;
                                            } else {
                                                isSame = false;
                                            }
                                        }
                                    }
                                } else {
                                    isSame = false;
                                }
                            }
                        }

                        if (isSame === false) {
                            return isSame;
                        }
                    }
                }

                // 数组不要考虑键名，只要考虑键值
                if (aDesc === 'Array') {
                    for (i = 0; i < A.length; ++i)
                    {
                        cur		= A[i];
                        curDesc = g.type(cur);

                        for (n = 0; n < B.length; ++n)
                        {
                            curC	 = B[n];
                            curCDesc = g.type(curC);

                            if (curDesc === curCDesc) {
                                if (g.isDom(cur) || g.isDom(curC)) {
                                    if (cur === curC) {
                                        isSame = true;
                                        break;
                                    } else {
                                        isSame = false;
                                    }
                                } else {
                                    if (g.contain(curDesc , recTypeRange)) {
                                        compare(cur , curC);
                                        // 之所以要 break 的理由：首先键值类型一致，再次符合当前判断条件，也就是说，执行到这儿，已经确定了 cur和curC 的值了
                                        break;
                                    } else {
                                        if (cur === curC) {
                                            isSame = true;
                                            break;
                                        } else {
                                            isSame = false;
                                        }
                                    }
                                }
                            } else {
                                isSame = false;
                            }
                        }
                    }
                }
            };

            compare(objA , objB);

            return isSame;
        } ,

        /*
         * 集合间关系：真子集
         * 如果 A 含于 B，且 B 不含于 A，那么 A 是 B 的真子集（反过来也讲得通）
         * 判断 objA 是否是 objB 的真子集
         * @param  Mixed(Object|Array) objA
         * @param  Mixed(Object|Array) objB
         * @return Boolean
         */
        isProperSubSet: function(objA , objB){
            var AIsBSubSet = this.isSubSet(objA , objB) ,
                BIsASubSet = this.isSubSet(objB , objA);

            if (AIsBSubSet === false) {
                return false;
            }

            if (BIsASubSet) {
                return false;
            }

            return true;
        } ,

        /*
         * 集合间关系：相等
         * A 含于 B ，且 B 含于 A ，则 A = B
         * 判断 objA 是否等于 objB（倒过来描述也行）
         * @param  Mixed(Object|Array) objA
         * @param  Mixed(Object|Array) objB
         * @return Boolean
         */
        isSameSet: function(objA , objB){
            var AIsBSubSet = this.isSubSet(objA , objB);
            var BIsASubSet = this.isSubSet(objB , objA);

            if (AIsBSubSet && BIsASubSet) {
                return true;
            }

            return false;
        } ,

        /*
         * 集合间基本运算：并集
         * 条件：所有参数类型一致！且仅支持：Object Array 的并集运算
         * 特殊：参数 1 如果为 Boolean 的时候，那么表示是否覆盖已有键值的单元值（只在类型为对象的时候有效）
         * 第一种模式
         * @param  Boolean			   isOverOriginalVal 是否覆盖键名相同的值
         * @param  Mixed(Array|Object) args2
         * @param  Mixed(Array|Object) args3
         * @param  Mixed(Array|Object) args4
         ....
         * @return Mixed(Array|Object)
         * 第二种模式
         * @param  Mixed(Array|Object) args1
         * @param  Mixed(Array|Object) args2
         * @param  Mixed(Array|Object) args3
         ....
         * @return Mixed(Array|Object)
         */
        unionSet: function(){
            var args			= arguments;
            var typeRange		= ['Array' , 'Object'];
            var argsDescList	= [];
            var self			= this;
            var rel				= null;
            var argsDesc;
            var i;
            var n;
            var merge;
            var oneArgs;
            var oneArgsDesc;
            var isOverOriginalVal;

            // 参数长度为 0
            if (args.length === 0) {
                return false;
            }

            oneArgs			  = args[0];
            oneArgsDesc		  = g.type(oneArgs);
            isOverOriginalVal = oneArgsDesc !== 'Boolean' ? true : oneArgs;


            // 检查所有参数类型是否正确
            for (i = oneArgsDesc === 'Boolean' ? 1 : 0; i < args.length; ++i)
            {
                argsDesc = g.type(args[i]);

                argsDescList.push(argsDesc);

                if (g.contain(argsDesc , typeRange) === false) {
                    throw new TypeError('参数 ' + (i + 1) + ' 类型错误');
                }
            }

            // 检查所有参数类型是否一致
            for (i = oneArgsDesc === 'Boolean' ? 1 : 0; i < argsDescList.length; ++i)
            {
                for (n = i + 1; n < argsDescList.length; ++n)
                {
                    if (argsDescList[i] !== argsDescList[n]) {
                        throw new TypeError('所有参数类型不一致');
                    }
                }

                break;
            }

            // 参数长度为 1，且第一个参数为 Boolean
            if (args.length === 1 && oneArgsDesc === 'Boolean') {
                return false;
            }

            // 参数长度为 1，且第一个参数不为 Boolean
            if (args.length === 1 && oneArgsDesc !== 'Boolean') {
                return oneArgs;
            }

            // 参数长度至少 2 个
            /*
             * 拷贝对象 B 到 对象 A中
             * 核心：合并函数
             * 碰撞处理：不要复制
             * @param Object|Array A                 拷贝到的对象
             * @param Object|Array B                 被拷贝的对象
             * @param Boolean      isOverOriginalVal 如果是对象，是否覆盖已有键值的值
             * @return undefined
             */
            merge = function(A , B , isOverOriginalVal){
                var aDesc = g.type(A);
                var bDesc = g.type(B);
                var isOverOriginalVal = g.type(isOverOriginalVal) !== 'Boolean' ? false : isOverOriginalVal;
                var cur;
                var curDesc;
                var i;
                var k;

                if (g.contain(aDesc , typeRange) === false) {
                    throw new TypeError('参数 1 类型错误');
                }

                if (g.contain(bDesc , typeRange) === false) {
                    throw new TypeError('参数 2 类型错误');
                }

                if (aDesc !== bDesc) {
                    throw new TypeError('所有参数类型不一致');
                }

                if (aDesc === 'Object') {
                    for (k in B)
                    {
                        cur     = B[k];
                        curDesc = g.type(cur);

                        if (g.type(A[k]) !== 'Undefined' && !isOverOriginalVal) {
                            continue ;
                        }

                        if (g.contain(curDesc , typeRange)) {
                            A[k] = g.copyObj(cur);
                        } else {
                            A[k] = cur;
                        }
                    }
                }

                if (aDesc === 'Array') {
                    for (i = 0; i < B.length; ++i)
                    {
                        cur     = B[i];
                        curDesc = g.type(cur);

                        if (g.contain(curDesc , typeRange)) {
                            A.push(g.copyObj(cur));
                        } else {
                            A.push(cur);
                        }
                    }
                }
            };

            for (i = oneArgsDesc === 'Boolean' ? 1 : 0; i < args.length; ++i)
            {
                if (rel === null) {
                    argsDesc = g.type(args[i]);
                    rel		 = argsDesc === 'Object' ? {} : [];
                }

                merge(rel , args[i] , isOverOriginalVal);
            }

            return rel;
        } ,

        /*
         * 集合间基本运算：交集
         * @param  Boolean			   isStrict 是否严格比较，当比较的是对象时有效（比较键名和键值）
         * @param  Mixed(Array|Object) args2
         * @param  Mixed(Array|Object) args3
         * @param  Mixed(Array|Object) args4
         ....
         * @return Mixed(Array|Object)
         * 第二种模式
         * @param  Mixed(Array|Object) args1
         * @param  Mixed(Array|Object) args2
         * @param  Mixed(Array|Object) args3
         ....
         * @return Mixed(Array|Object)
         */
        intersectionSet: function(){
            var args		 = arguments;
            var typeRange	 = ['Array' , 'Object'];
            var argsDescList = [];
            var self		 = this;
            var rel			 = null;
            var isStrict     = true;
            var argsDesc;
            var relDesc;
            var getIntersect;
            var i;
            var n;
            var oneArgs;
            var oneArgsDesc;

            // 参数长度为 0
            if (args.length === 0) {
                return false;
            }

            oneArgs     = args[0];
            oneArgsDesc = g.type(oneArgs);

            // 检查所有参数类型是否正确
            for (i = oneArgsDesc === 'Boolean' ? 1 : 0; i < args.length; ++i)
            {
                argsDesc = g.type(args[i]);
                argsDescList.push(argsDesc);

                if (g.contain(argsDesc , typeRange) === false) {
                    throw new TypeError('参数 ' + (i + 1) + ' 类型错误');
                }
            }

            // 检查所有参数类型是否一致
            for (i = oneArgsDesc === 'Boolean' ? 1 : 0; i < argsDescList.length; ++i)
            {
                for (n = i + 1; n < argsDescList.length; ++n)
                {
                    if (argsDescList[i] !== argsDescList[n]) {
                        throw new TypeError('所有参数类型不一致');
                    }
                }

                break;
            }

            // 参数长度为 1
            if (args.length === 1 && oneArgsDesc === 'Boolean') {
                return false
            }

            if (args.length === 1 && oneArgsDesc !== 'Boolean') {
                return oneArgs;
            }

            isStrict = oneArgsDesc !== 'Boolean' ? true : oneArgs;

            // 核心：获取两个集合的交集
            getIntersect = function(A , B , isStrict){
                var aDesc = g.type(A);
                var bDesc = g.type(B);
                var rel;
                var k;
                var k1;
                var cur;
                var curC;
                var curDesc;
                var curCDesc;

                if (!g.contain(aDesc , typeRange)) {
                    throw new TypeError('参数 1 类型错误');
                }

                if (!g.contain(bDesc , typeRange)) {
                    throw new TypeError('参数 2 类型错误');
                }

                if (aDesc !== bDesc) {
                    throw new TypeError('所有参数类型不一致');
                }

                rel = aDesc === 'Object' ? {} : [];

                if (aDesc === 'Object') {
                    for (k in A)
                    {
                        cur     = A[k];
                        curDesc = g.type(cur);

                        for (k1 in B)
                        {
                            curC     = B[k1];
                            curCDesc = g.type(curC);

                            if (isStrict) {
                                if (k === k1) {
                                    if (curDesc === curC) {
                                        if (g.isDom(cur)) {
                                            if (cur === curC) {
                                                rel[k] = cur;
                                            }
                                        } else {
                                            if (g.contain(cur , typeRange)) {
                                                if (this.isSameSet(cur , curC)) {
                                                    rel.push(cur);
                                                }
                                            } else {
                                                if (cur === curC) {
                                                    rel.push(cur);
                                                }
                                            }
                                        }
                                    }
                                }
                            } else {
                                if (curDesc === curC) {
                                    if (g.isDom(cur)) {
                                        if (cur === curC) {
                                            rel[k] = cur;
                                        }
                                    } else {
                                        if (g.contain(cur , typeRange)) {
                                            if (this.isSameSet(cur , curC)) {
                                                rel.push(cur);
                                            }
                                        } else {
                                            if (cur === curC) {
                                                rel.push(cur);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (aDesc === 'Array') {
                    for (i = 0; i < A.length; ++i)
                    {
                        cur     = A[i];
                        curDesc = g.type(cur);

                        for (n = 0; n < B.length; ++n)
                        {
                            curC     = B[n];
                            curCDesc = g.type(curC);

                            if (curDesc === curCDesc) {
                                if (g.isDom(cur)) {
                                    if (cur === curC) {
                                        rel.push(cur);
                                    }
                                } else {
                                    if (g.contain(cur , typeRange)) {
                                        if (this.isSameSet(cur , curC)) {
                                            rel.push(cur);
                                        }
                                    } else {
                                        if (cur === curC) {
                                            rel.push(cur);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                return rel;
            };

            // 运算原理
            // 第一次：rel =  参数 1
            // 第二次：rel 和 参数 2 求交集    = rel
            // 第三次：rel 和 参数 3 求交集    = rel
            // 第四次：rel 和 参数 4 求交集    = rel
            // ...
            // 得出最终结果 rel
            for (i = oneArgsDesc === 'Boolean' ? 1 : 0; i < args.length; ++i)
            {
                if (rel === null) {
                    argsDesc = g.type(args[i]);
                    rel		 = args[i];
                    relDesc  = argsDesc;
                } else {
                    rel = getIntersect(rel , args[i] , isStrict);
                }
            }

            return rel;
        } ,

        /*
         * 集合间基本运算：补集
         * 参数 1 是全集
         * 参数 2，3，4...等是他的子集
         * 返回结果：参数 1 相对 其他参数的补集
         * @param  Boolean			   isStrict 是否严格比较，当比较的是对象时有效（比较键名和键值）
         * @param  Mixed(Array|Object) args2
         * @param  Mixed(Array|Object) args3
         * @param  Mixed(Array|Object) args4
         ....
         * @return Mixed(Array|Object)
         * 第二种模式
         * @param  Mixed(Array|Object) args1
         * @param  Mixed(Array|Object) args2
         * @param  Mixed(Array|Object) args3
         ....
         * @return Mixed(Array|Object)
         */
        complementSet: function(){
            var args		 = arguments;
            var typeRange	 = ['Array' , 'Object'];
            var argsDescList = [];
            var tipArr		 = [];
            var self		 = this;
            var rel			 = null;
            var isStrict     = true;
            var argsDesc;
            var i;
            var n;
            var k;
            var k1;
            var cur;
            var curC;
            var curDesc;
            var curCDesc;
            var isRel;
            var sets;
            var partSets;
            var oneArgs;
            var oneArgsDesc;

            // 参数长度为 0
            if (args.length === 0) {
                return false;
            }

            oneArgs     = args[0];
            oneArgsDesc = g.type(oneArgs);

            // 检查所有参数类型是否正确
            for (i = oneArgsDesc === 'Boolean' ? 1 : 0; i < args.length; ++i)
            {
                argsDesc = g.type(args[i]);
                argsDescList.push(argsDesc);

                if (g.contain(argsDesc , typeRange) === false) {
                    throw new TypeError('参数 ' + (i + 1) + ' 类型错误');
                }
            }

            // 检查所有参数类型是否一致
            for (i = oneArgsDesc === 'Boolean' ? 1 : 0; i < argsDescList.length; ++i)
            {
                for (n = i + 1; n < argsDescList.length; ++n)
                {
                    if (argsDescList[i] !== argsDescList[n]) {
                        throw new TypeError('所有参数类型不一致');
                    }
                }

                break;
            }

            // 参数长度为 1
            if (args.length === 1 && oneArgsDesc === 'Boolean') {
                return false
            }

            if (args.length === 1 && oneArgsDesc !== 'Boolean') {
                return oneArgs;
            }

            isStrict = oneArgsDesc !== 'Boolean' ? true : oneArgs;

            // 全集
            sets  = oneArgsDesc === 'Boolean' ? args[1] : oneArgs;
            // 部分集合
            partSets = g.array(args).slice(oneArgsDesc === 'Boolean' ? 2 : 1);
            partSets = this.unionSet.apply(this , partSets);

            rel = oneArgsDesc === 'Object' ? {} : [];

            if (this.isSameSet(partSets , sets)) {
                return rel;
            }

            if (!this.isSubSet(partSets , sets)) {
                throw new TypeError('参数 1 不是全集');
            }

            if (oneArgsDesc === 'Object') {
                for (k in sets)
                {
                    cur     = sets[k];
                    curDesc = g.type(cur);
                    // 子集中是否包含当前值，默认是包含的
                    isRel   = true;

                    for (k1 in partSets)
                    {
                        curC     = partSets[k1];
                        curCDesc = g.type(curC);

                        if (isStrict) {
                            if (k === k1) {
                                if (curDesc === curCDesc) {
                                    if (g.isDom(cur) || g.isDom(curC)) {
                                        if (cur === curC) {
                                            isRel = true;
                                            break;
                                        } else {
                                            isRel = false;
                                        }
                                    } else {
                                        if (g.contain(curDesc , typeRange)) {
                                            if (this.isSameSet(cur , curC)) {
                                                isRel = true;
                                                break;
                                            } else {
                                                isRel = false;
                                            }
                                        } else {
                                            if (cur === curC) {
                                                isRel = true;
                                                break;
                                            } else {
                                                isRel = false;
                                            }
                                        }
                                    }
                                } else {
                                    isRel = false;
                                }
                            } else {
                                isRel = false;
                            }
                        } else {
                            if (curDesc === curCDesc) {
                                if (g.isDom(cur) || g.isDom(curC)) {
                                    if (cur === curC) {
                                        isRel = true;
                                        break;
                                    } else {
                                        isRel = false;
                                    }
                                } else {
                                    if (g.contain(curDesc , typeRange)) {
                                        if (this.isSameSet(cur , curC)) {
                                            isRel = true;
                                            break;
                                        } else {
                                            isRel = false;
                                        }
                                    } else {
                                        if (cur === curC) {
                                            isRel = true;
                                            break;
                                        } else {
                                            isRel = false;
                                        }
                                    }
                                }
                            } else {
                                isRel = false;
                            }
                        }
                    }

                    // 如果子集中找不到当前值
                    if (isRel === false) {
                        rel[k] = cur;
                    }
                }
            }

            if (oneArgsDesc === 'Array') {
                for (i = 0; i < sets.length; ++i)
                {
                    cur     = sets[i];
                    curDesc = g.type(cur);

                    for (n = 0; n < partSets.length; ++n)
                    {
                        curC     = partSets[n];
                        curCDesc = g.type(curC);

                        if (curDesc === curCDesc) {
                            if (g.isDom(cur) || g.isDom(curC)) {
                                if (cur === curC) {
                                    isRel = true;
                                    break;
                                } else {
                                    isRel = false;
                                }
                            } else {
                                if (g.contain(curDesc , typeRange)) {
                                    if (this.isSameSet(cur , curC)) {
                                        isRel = true;
                                        break;
                                    } else {
                                        isRel = false;
                                    }
                                } else {
                                    if (cur === curC) {
                                        isRel = true;
                                        break;
                                    } else {
                                        isRel = false;
                                    }
                                }
                            }
                        } else {
                            isRel = false;
                        }
                    }

                    if (isRel === false) {
                        rel.push(cur);
                    }
                }
            }

            return rel;

        }
    };

    /*
     * ******************************************************************************************************************************************************
     SmallJs 实例对象继承的原型部分的成分之一，但是又由于拆开描述并不能清晰的表达该部分。所以，采取 构造函数 的方式进行描述，然后归并到 SmallJs 原型对象中
     * ******************************************************************************************************************************************************
     */

    /*
     * Event 事件操作类
     */
    function Event(){
        var thisRange = [window , null , undefined];

        if (g.contain(this , thisRange) || (!g.contain(this , thisRange) && this.constructor !== Event)) {
            return new Event();
        }
    }

    Event.clientX = function(e){
        return browser === 'mobile' ? e.touches[0].clientX : e.clientX;
    };

    Event.clientY = function(e){
        return browser === 'mobile' ? e.touches[0].clientY : e.clientY;
    };

    Event.prototype = {
        author: '陈学龙' ,
        version: '1.0' ,
        cTime: '2016/10/25 17:32:00' ,
        constructor: Event ,
        _events: [] ,

        /*
         * 检测某元素上是否已注册某方法
         */
        isBind: function(obj , type){
            var events = this.events(obj);

            if (g.isBoolean(events)) {
                return false;
            }

            var k = null;

            for (k in events)
            {
                if (k === type) {
                    return events[k];
                }
            }

            return false;
        } ,

        /*
         * 获取已注册事件的对象列表中当前提供元素所在下标
         */
        index: function(obj){
            var index = false;
            this._events.forEach(function(v , k){
                if (index === false && v['obj'] === obj) {
                    index = k;
                }
            });

            return index;
        } ,

        obj: function(obj){
            var res = false;
            this._events.forEach(function(v){
                if (res === false && v['obj'] === obj) {
                    res = v;
                }
            });

            return res;
        } ,

        /*
         * 获取当前提供元素已注册事件列表
         */
        events: function(obj){
            var index = this.index(obj);

            return g.isBoolean(index) ? index : this._events[index]['event'];
        } ,

        /**
         * 绑定事件
         */
        bind: function(obj , type , fn , isRepeat , mixed , context){
            if (!g.isString(type)) {
                throw new TypeError('参数 1 类型错误');
            }

            if (!g.isFunction(fn)) {
                throw new TypeError('参数 2 类型错误');
            }

            isRepeat    = g.isBoolean(isRepeat) ? isRepeat : true;
            if ( browser === 'opera' || browser.search('ie') !== -1) {
                // 由于 opera 或 ie 浏览器并未实现 option 参数
                // 所以降级
                mixed   = g.isBoolean(mixed) ? mixed : false;
            } else {
                // 其他浏览器则完整支持 option
                mixed   = g.isBoolean(mixed) ? mixed : G.isObject(mixed) ? mixed : false;
            }
            context     = g.isObj(context) ? context : obj;

            if (!isRepeat && this.isBind(obj , type)) {
                console.warn('当前提供元素 ' + obj.toString() + ' 不允许重复绑定 ' + type + " 事件");
                // throw new Error('当前提供元素 ' + obj.toString() + ' 不允许重复绑定 ' + type + " 事件");
                return ;
            }

            obj.addEventListener(type , fn , mixed);

            var _obj = this.obj(obj);

            if (g.isBoolean(_obj)) {
                var res = {};
                res[type] = true;

                // 如果未注册过事件，直接添加
                this._events.push({
                    obj: obj ,
                    event: res
                });
            } else {
                _obj['event'][type] = true;
            }
        } ,
    };

    // get 请求
    g.get = function(url , option){
        if (this.isString(url)) {
            option['method'] = 'get';
        } else {
            url['method'] = 'get';
        }

        this.ajax(url , option);
    };

    // post 请求
    g.post = function(url , option){
        if (this.isString(url)) {
            option['method'] = 'post';
        } else {
            url['method'] = 'post';
        }

        this.ajax(url , option);
    };

    /**
     * 字符串辅助函数
     */
    g.string = g.str = {
        // 字符串
        repeat: function(str , len){
            var i   = 0;
            var res = '';
            for (; i < len; ++i)
            {
                res += str;
            }
            return res;
        } ,
    };

    /*
     * ****************************************************************************************************************************************************
     * SmallJs 函数的组成部分之一，同属于基础函数库，但直接使用拆开描述并不能清晰的表达该部分，所以采取 构造函数 的方式进行描述，然后归并到 SmallJs 函数上
     * ****************************************************************************************************************************************************
     */

    /*
     * Ajax 操作类
     * ajax 请求都会带有一个 AJAX-REQUEST 请求头
     * todo
     */
    function Ajax(url , option){
        var thisRange = [window , null , undefined];

        if (g.contain(this , thisRange) || this.constructor !== Ajax) {
            return new Ajax(url , option);
        }

        this.default = {
            header:   {} ,                          // 发送的请求头部信息 格式： {'Content-Type' : 'text/html; charset=utf-8' , 'Cache-Control' : 'false'}
            method: 'get' ,                          // 请求方法 get | GET | post | POST
            url: '' ,                                // 请求路径
            async: true ,                          // 是否异步
            data: null ,                         // 发送的数据
            direct: false ,                      // 直接发出请求，这将绕过生命周期钩子的拦截
            responseType: '' ,                       // 相应类型
            additionalTimestamp: true , 			 // 是否在 url 末尾追加时间戳
            wait: 0 ,                         // 请求：设置超时时间，单位：ms，默认值：0
            withCredentials: false ,					 // 跨域请求是否允许携带 cookie

            // 下载事件
            before: null ,  // 发送请求之前（已经创建 xhr）
            after: null ,
            success: null ,                          // 请求：成功时回调
            netError: null ,                         // 请求：失败时回调
            error: null ,                            // 请求：失败时回调
            progress: null ,                         // 请求：加载时回调
            load: null ,                             // 请求：加载完成时回调
            timeout: null ,                          // 请求：超时回调
            abort: null ,                            // 请求：中断是回调
            loadstart: null ,						 // 请求：接收到响应的时候触发
            loadend: null , 						 // 请求：响应结束的时候触发（导致结束的原因：error , timeout , load，未知）

            // 上传事件
            uLoad: null ,							 // 上传：上传完成时回调
            uLoadstart: null ,						 // 上传：上传开始时回调
            uTimeout: null ,						 // 上传：上传开始超时回调
            uError: null ,							 // 上传：上传发生错误时回调
            uProgress: null ,						 // 上传：上传中回调
            uLoadend: null ,						 // 上传：上传终止时超时回调（有可能是发生错误而终止、有可能是超时终止...）
            uAbort: null ,							 // 上传：上传中断

            // 相关属性
            isReturnXHR: false ,					 // 是否返回 XHR 对象
            username: '' ,							 // http 验证的用户名
            password: '' ,							 // http 验证的密码
            // isUpload: false ,                        // 上传文件还是下载文件！ 决定了事件时定义在上传对象 还是 在下载对象上！

            // 是否允许携带用于区分普通请求 和 ajax 请求的请求头（标识）
            isAllowAjaxHeader: true
        };

        if (g.isString(url)) {
            option = g.isObject(option) ? option : {};
            option['url'] = url;
        } else {
            if (!g.isObject(url)) {
                throw new Error('未传入配置参数');
            }
            option = url
        }

        // this.methodRange		 = ['GET' , 'POST' , 'PUT' , 'DISPATCH' , 'DELETE'];
        this.dataType		     = ['Object' , 'FormData' , 'Blob'];
        // 会根据不同的 responseType 将响应数据做一些转换后在返回给用户
        this.responseTypeRange	 = ['' , 'text' , 'document' , 'json' , 'blob'];
        this.enctypeRange		 = ['text/plain' , 'application/x-www-form-urlencoded' , 'multipart/form-data'];
        this.header			 = g.type(option['header']) === 'Undefined'				? this.default['header']		: option['header'];

        // this.method			 = !g.contain(option['method'] , this.methodRange)				? this.default['method']		: option['method'];
        this.method			 = option.method.toUpperCase();
        this.url				 = !g.isValid(option['url'])									? this.default['url']			: option['url'];
        this.async			        = g.type(option['async']) !== 'Boolean'					? this.default['async']		: option['async'];
        this.additionalTimestamp = g.type(option['additionalTimestamp']) !== 'Boolean'					? this.default['additionalTimestamp']		: option['additionalTimestamp'];
        this.data                  = g.type(option.data) != 'FormData' ? (g.isObject(option.data) ? g.buildQuery(option.data) : g.buildQuery(this.default.data)) : option.data;
        this.responseType	 	    = !g.contain(option['responseType'] , this.responseTypeRange)  ? this.default['responseType']	: option['responseType'];
        this.wait		            = g.type(option['wait']) !== 'Number'				? this.default['wait']	: option['wait'];
        this.withCredentials		= g.type(option['withCredentials']) !== 'Boolean'				? this.default['withCredentials']	: option['withCredentials'];

        // 下载事件
        this.before			 = g.type(option['before']) !== 'Function'				? this.default['before']		: option['before'];
        this.success			 = g.type(option['success']) !== 'Function'				? this.default['success']		: option['success'];
        this.netError			 = g.type(option['netError']) !== 'Function'				? this.default['netError']		: option['netError'];
        this.error				 = g.type(option['error']) !== 'Function'					? this.default['error']			: option['error'];
        this.progress			 = g.type(option['progress']) !== 'Function'				? this.default['progress']		: option['progress'];
        this.loadstart			 = g.type(option['loadstart']) !== 'Function'				? this.default['loadstart']		: option['loadstart'];
        this.load				 = g.type(option['load']) !== 'Function'					? this.default['load']			: option['load'];
        this.loadend			 = g.type(option['loadend']) !== 'Function'				? this.default['loadend']	    : option['loadend'];
        this.timeout			 = g.type(option['timeout']) !== 'Function'				? this.default['timeout']		: option['timeout'];
        this.abort				 = g.type(option['abort']) !== 'Function'					? this.default['abort']			: option['abort'];

        // 上传事件
        this.uError			 = g.type(option['uError']) !== 'Function'					? this.default['uError']		: option['uError'];
        this.uProgress			 = g.type(option['uProgress']) !== 'Function'				? this.default['uProgress']		: option['uProgress'];
        this.uLoadstart		 = g.type(option['uLoadstart']) !== 'Function'				? this.default['uLoadstart']	: option['uLoadstart'];
        this.uLoad				 = g.type(option['uLoad']) !== 'Function'					? this.default['uLoad']			: option['uLoad'];
        this.uLoadend			 = g.type(option['uLoadend']) !== 'Function'				? this.default['uLoadend']	    : option['uLoadend'];
        this.uTimeout			 = g.type(option['uTimeout']) !== 'Function'				? this.default['uTimeout']		: option['uTimeout'];
        this.uAbort			 = g.type(option['uAbort']) !== 'Function'					? this.default['uAbort']		: option['uAbort'];

        this.isReturnXHR		 = g.type(option['isReturnXHR']) !== 'Boolean'				? this.default['isReturnXHR']   : option['isReturnXHR'];

        this.username			 = !g.isValid(option['username'])								? this.default['username']		: option['username'];
        this.password			 = !g.isValid(option['password'])								? this.default['password']		: option['password'];

        this.isAllowAjaxHeader = !g.isValid(option['isAllowAjaxHeader'])								? this.default['isAllowAjaxHeader']		: option['isAllowAjaxHeader'];
        this.direct = g.isBoolean(option.direct) ? option.direct : this.default.direct;

        this.run();
    }

    /**
     * *****************************************
     * 在各类钩子函数中都可以拦截请求的执行流程
     * *****************************************
     */

    // 创建 xhr 之前
    Ajax.before = null;
    // 创建 xhr 之后
    Ajax.created = null;
    // xhr open 之后
    Ajax.opened = null;
    // xhr 接收到响应之后
    Ajax.responded = null;
    // 发送请求之后
    Ajax.after = null;

    Ajax.prototype = {
        version: '1.0' ,
        cTime: '2016/10/25 17:32:00' ,
        author: '陈学龙' ,
        constructor: Ajax ,

        // 当前创建的 XMLHttpRequest
        xhr: null ,
        // 请求头
        url: null ,
        method: null ,
        async: null ,
        header: null ,
        additionalTimestamp: null ,
        data: null ,
        responseType: null ,
        wait: null ,
        withCredentials: null ,
        before: null ,
        success: null ,
        netError: null ,
        error: null ,
        progress: null ,
        loadstart: null ,
        load: null ,
        loadend: null ,
        timeout: null ,
        abort: null ,
        uError: null ,
        uProgress: null ,
        uLoadstart: null ,
        uLoad: null ,
        uLoadend: null ,
        uTimeout: null ,
        uAbort: null ,
        isReturnXHR: null ,
        username: null ,
        password: null ,
        isAllowAjaxHeader: null ,
        direct: null ,

        // 调用原生方法
        native: function(event){
            var args = arguments;
            args = g.array(args);
            args = args.slice(1);
            return this.xhr[event].apply(this.xhr , args);
        } ,

        // 获取或设置原生属性
        attr: function(key , val){
            if (G.isUndefined(val)) {
                return this.xhr[key];
            }
            this.xhr[key] = val;
        } ,

        // 获取请求头
        getHeader: function(key){
            return this.header[key];
        } ,

        // 设置请求头
        setHeader: function(key , val){
            this.header[key] = val;
        } ,

        // 从现有请求头集合中移除指定请求头
        removeHeader: function(key){
            delete this.header[key];
        } ,

        // 获取 XMLHttpRequest 对象
        get: function(){
            return this.xhr;
        } ,

        // 创建 ajax
        create: function(){
            var xhr = this.xhr = new XMLHttpRequest();
            if (!this.direct && g.isFunction(Ajax.created)) {
                if (Ajax.created.call(this) !== true) {
                    return false;
                }
            }
            if (g.isFunction(this.before)) {
                this.before.call(this);
            }
            return true;
        } ,

        // 获取要附加的链接符号
        sign: function(){
            if (this.url.lastIndexOf('?') === -1) {
                return '?';
            }
            return '&';
        } ,

        // 初始化 ajax
        initialize: function(){
            /**
             * ***********************
             * 初始化请求
             * ***********************
             */
            // 初始化要设置的请求头
            if (g.type(this.data) !== 'FormData') {
                // 表单提交默认的 content-type
                this.setHeader('Content-Type' , 'application/x-www-form-urlencoded');
            } else {
                // 如果是 FormData，请勿设置任何请求头
                this.removeHeader('Content-Type');
            }
            // 追加 AJAX 请求标识符头部
            // 这里请求设置有一个要求！不允许使用 _（下划线） ！！只能使用 - （中划线）
            if (this.isAllowAjaxHeader) {
                // 兼容使用 jQuery 库的项目
                this.setHeader('X-Request-With' , 'XMLHttpRequest');
                // SmallJs 独有
                this.setHeader('Ajax-Request' , 'yes');
            }
            // 设置请求头
            this.setRequestHeader();
            /**
             * **************************
             * 初始化必要属性
             * **************************
             */
            this.attr('timeout' , this.timeout);
            /**
             * ************************
             * 初始化响应
             * ************************
             */
            if (g.isValid(this.responseType)) {
                this.attr('responseType' , this.responseType);
            }
            /**
             * ******************
             * 初始化事件
             * ******************
             */
            this.defineEvent();
        } ,

        // 初始化上传对象
        initializeUpload: function(){

        } ,

        beforeOpen: function(){
            /*
             * *************************************************************
             * 数据测试：
             * 特殊：如果 method = get 情况下，data 必须是字符串
             * 特殊：如果 method = post 情况下，data 允许 字符串 或 FormData
             * 特殊：如果 method = other 情况下，data 必须是一个字符串
             * **************************************************************
             */
            if (this.method == 'GET') {
                if (g.isValid(this.data)) {
                    if (!g.isString(this.data)) {
                        throw new Error('Ajax data 字段类型错误');
                    }
                    // 附加查询字符串到 url
                    this.url += this.sign() + this.data;
                }
            }
            // 防止缓存
            if (this.additionalTimestamp) {
                // 先将原有的时间戳清除掉
                this.url = this.url.replace(/(\?|&)?__timestamp__.*/g , '');
                // 是否追加时间戳，防止请求被缓存
                var time = new Date().getTime();

                // 附加新的时间戳
                this.url += this.sign() + '__timestamp__=' + time;
            }
        } ,

        // 打开 ajax 请求
        open: function(){
            /**
             * 支持使用了验证的请求
             */
            this.xhr.open(this.method , this.url , this.async , this.username , this.password);
            if (!this.direct && g.isFunction(Ajax.opened)) {
                return Ajax.opened.call(this);
            }
            return true;
        } ,

        // 设置 ajax 请求头
        setRequestHeader: function(){
            for (var key in this.header)
            {
                this.native('setRequestHeader' , key , this.header[key]);
            }
        } ,

        // 响应

        // 定义 xhr 事件
        defineEvent: function(){
            var self    = this;
            var xhr     = g(this.xhr);
            var upload  = g(this.xhr.upload);

            // 响应
            xhr.on('readystatechange' , function(){
                /**
                 * 针对 readyState 代码含义
                 * 0 未 open，未 send
                 * 1 已 open，未 send
                 * 2 已 send
                 * 3 正在下载响应体
                 * 4 请求完成
                 *
                 * 针对 status 的代码的含义
                 * 如果 status !== 200 ，则表示发生了错误，否则表示传输完成
                 * 可能是 0 （canceld），500 服务器内部错误等.....
                 *
                 */
                if (this.readyState === 4) {
                    var response = this.response;
                    var status   = this.status;
                    var contentType = self.native('getResponseHeader' , 'Content-Type');
                    contentType = g.type(contentType) == 'String' ? contentType.toLowerCase() : '';
                    if (
                        contentType.search('application/json') != -1 &&
                        (
                            !g.isValid(self.responseType) ||
                            (g.isValid(self.responseType) && self.responseType != 'json'))
                    ) {
                        response = g.jsonDecode(this.response);
                    }
                    if (!self.direct && g.isFunction(Ajax.responded)) {
                        var next = Ajax.responded.call(self , response , status);
                        if (next === false) {
                            return ;
                        }
                    }
                    if (g.type(self.success) === 'Function') {
                        if (this.isReturnXHR) {
                            self.success(response , status , self.xhr);
                        } else {
                            // 可能是 responseText || responseXML
                            self.success(response , status);
                        }
                    }
                }
            } , true , false);

            /*** 下载事件 ***/

            // error
            if (g.type(this.error) === 'Function') {
                xhr.on('error' , self.error , true , false);
            }

            // timeout
            if (g.type(this.timeout) === 'Function') {
                xhr.on('timeout' , this.timeout , true , false);
            }

            // loadstart
            if (g.type(this.loadstart) === 'Function') {
                xhr.on('loadstart' , this.loadstart , true , false);
            }

            // progress
            if (g.type(this.progress) === 'Function') {
                xhr.on('timeout' , this.progress , true , false);
            }

            // load
            if (g.type(this.load) === 'Function') {
                xhr.on('load' , this.load , true , false);
            }

            // loadend
            if (g.type(this.loadend) === 'Function') {
                xhr.on('loadend' , this.loadend , true , false);
            }

            // abort
            if (g.type(this.abort) === 'Function') {
                xhr.on('abort' , this.abort , true , false);
            }

            /*
             * 上传事件:
             * onloadstart
             * onprogress
             * onabort
             * onerror
             * onload
             * ontimeout
             * onloadend
             */
            // error
            if (g.type(this.uError) === 'Function') {
                upload.on('error' , self.uError , true , false);
            }

            // timeout
            if (g.type(this.uTimeout) === 'Function') {
                upload.on('timeout' , this.uTimeout , true , false);
            }

            // loadstart
            if (g.type(this.uLoadstart) === 'Function') {
                // console.log('load start');
                upload.on('loadstart' , this.uLoadstart , true , false);
            }

            // progress
            if (g.type(this.uProgress) === 'Function') {
                // console.log('你正在定义上传进度事件！' , this.uProgress);
                upload.on('progress' , this.uProgress , true , false);
            }

            // load
            if (g.type(this.uLoad) === 'Function') {
                console.log('load start');
                upload.on('load' , this.uLoad , true , false);
            }


            // loadend
            if (g.type(this.uLoadend) === 'Function') {
                upload.on('loadend' , this.uLoadend , true , false);
            }

            // abort
            if (g.type(this.uAbort) === 'Function') {
                upload.on('abort' , this.uAbort , true , false);
            }
        } ,

        // 发送请求
        send: function(){
            if (this.withCredentials) {
                this.xhr.withCredentials = this.withCredentials;
            }
            if (this.method === 'GET') {
                // get 方法在 url 中发送数据
                this.xhr.send(null);
            } else {
                // post 方法在 send 方法参数中发送数据
                this.xhr.send(this.data);
            }
        } ,

        // 初始化前调用
        _before_: function() {
            if (!this.direct && g.isFunction(Ajax.before)) {
                return Ajax.before.call(this);
            }
            return true;
        } ,

        // 初始化后调用
        _after_: function(){
            if (!this.direct && g.isFunction(Ajax.after)) {
                Ajax.after.call(this);
            }
        } ,

        // 重新执行
        restart: function(){
            this.run();
        } ,

        // 开始运行程序
        run: function(){
            if (this._before_() != true) {
                // 被用户手动拦截
                return ;
            }
            if (this.create() != true) {
                // 被用户手动拦截
                return ;
            }
            // 打开之前做一些处理
            this.beforeOpen();
            if (this.open() != true) {
                // 被用户手动拦截
                return ;
            }
            // 该方法必须在请求 open 之后调用
            this.initialize();
            this.send();
            // 请求在调用之后
            this._after_();
        }
    };

    /*
     * 队列操作类
     * 只允许存放 原始值 + 数组 + 对象 + 元素
     * 不允许存放 函数，正则对象...等
     */
    function Queue(){
        var thisRange = [window , undefined , null];

        if (g.contain(this , thisRange) || (!g.contain(this , thisRange) && this.constructor !== Queue)) {
            return new Queue();
        }

        this._queue = [];
        this.length = this._queue.length;
    }

    Queue.prototype = {
        version: '1.0' ,

        constructor: Queue ,

        author: '陈学龙' ,

        cTime: '2016/10/27 00:46:00' ,

        length: 0 ,

        // 是否正在消费队列
        isConsuming: false ,

        get: function(index){
            return !g.isInt(index) ? this._queue : this._queue[index];
        } ,

        first: function(){
            return this._queue[0];
        } ,

        last: function(){
            return this._queue[this.length - 1];
        } ,

        jump: function(index){
            return this._queue[index];
        } ,

        // 队列尾部添加
        push: function(){
            for (var i = 0; i < arguments.length; ++i)
            {
                this._queue.push(arguments[i]);
            }

            this.length = this._queue.length;
        } ,

        // 队列首部添加
        unshift: function(){
            for (var i = 0; i < arguments.length; ++i)
            {
                this._queue.unshift(arguments[i]);
            }

            this.length = this._queue.length;
        } ,

        // 队列首部删除
        shift: function(){
            this.length--;
            return this._queue.shift();
        } ,

        // 队列尾部删除
        pop: function(){
            this.length--;
            return this._queue.pop();
        } ,

        // 队列操作
        splice: function(index , len){
            var args = g.array(arguments).slice(2);
            var res  = null;

            this._queue.splice(index , len);

            if (args.length === 0) {
                this.length = this._queue.length;
                return res;
            }

            args.unshift(index);
            args.unshift(0);

            res = this._queue.splice.apply(this._queue , args);

            this.length = this._queue.length;

            return res;
        } ,

        clear: function(){
            this._queue   = [];
            this.length = this._queue.length;
            this.isConsuming = false;
        } ,

        empty: function(){
            return this._queue.length === 0;
        } ,

        // 消费队列
        consume: function(fn) {
            // 表明队列正在作业中
            // 一次仅允许一个队列作业存在
            // 因为能够被消费的队列仅有一个
            // 如果允许多个队列作业同时进行
            // 则无法实现链式顺序调用
            if (this.isConsuming) {
                return ;
            }

            var self = this;
            // 消费队列
            var consume = function(){
                if (self._queue.length > 0) {
                    self.isConsuming = true;
                    fn.call(null , self._queue.shift() , consume);
                } else {
                    self.isConsuming = false;
                }
            };

            consume();
        } ,
    };

    /**
     * 对外暴露借口
     */
    g.event = g.e     = Event;
    g.animate   = Animate;
    g.ajax		= Ajax;
    g.queue = g.q		= Queue;
    g.set       = Set;
    g.$e	    = new Event();
    g.$s		= new Set();
    g.$q        = new Queue();
    g.c         = g.cookie;
    g.s         = g.storage;
    g.t         = g.tree;
    g.click		= g.browser() === 'mobile' ? 'touchstart' : 'click';
    g.mousedown	= g.browser() === 'mobile' ? 'touchstart' : 'mousedown';
    g.mousemove	= g.browser() === 'mobile' ? 'touchmove'  : 'mousemove';
    g.mouseup   = g.browser() === 'mobile' ? 'touchend'   : 'mouseup';

    /*
     * ********
     * 变量定义
     * ********
     */
    var back     = null;
    var apiName  = 'G';

    // 预定义事件类型：支持移动端
    var win       = g(window);
    var body      = g(body);
    var browser   = g.browser();
    var click     = browser === 'mobile' ? 'touchstart' : 'click';
    var mouseDown = browser === 'mobile' ? 'touchstart' : 'mousedown';
    var mouseMove = browser === 'mobile' ? 'touchmove'  : 'mousemove';
    var mouseUp   = browser === 'mobile' ? 'touchend'   : 'mouseup';

    /*
     * **********
     初始化框架
     * **********
     */

    // 整合 事件操作类到 SmallJs 原型中
    // g.merge(false , true , true , g.pro , new Event());

    // 暴露对外操作 API
    g.noConflict(apiName);
})(typeof window !== 'undefined' ? window : this);