# front


前端插件库。

# 导航菜单

- [Boundary，滚动检查](#Boundary)
- [css，通用型样式风格](#css)
- [FunctionNav，导航菜单切换-适用于 pc/mobile](#FunctionNav)
- [InfiniteClassification，无限极分类菜单](#InfiniteClassification)
- [Load，下拉刷新/上拉加载](#Load)
- [MenuSwitch，导航菜单切换](#MenuSwitch)
- [PicPlay_Touch，图片轮播](#PicPlay_Touch)
- [PicShow，图片展示，图片索引，会根据索引位置自动滚动到可视位置](#PicShow)
- [Prompt，提示框](#Prompt)
- [Slidebar，侧边栏滑动](#Slidebar)
- [Switch，内容切换，参考移动端手指左右滑动内容切换](#Switch)
- [UploadImage，图片上传](#UploadImage)
- [Zoom，图片缩放，仅适用于移动端，存在尚未修复的 `bug`，请勿使用！](#Zoom)
- [MultipleTab，多标签。](#MultipleTab)
- [TouchFeedback](#TouchFeedback)


# 插件列表

## Boundary

滚动检查。

- 滚动到顶部
- 滚动到底部
- 滚动中

```
<!-- 加载样式 -->
<link rel='stylesheet' href='../base/base.css'>
<link rel='stylesheet' href='css/Boundary.css'>

<!-- 测试范例 start -->
<style>
    .container {
        width: 200px;
        height: 300px;
        border: 1px solid red;
        margin: 20px;
        overflow: hidden;
        overflow-y: auto;
    }

    .container .con {
        height: 600px;
        background-color: green;
    }
</style>
<div class="container">
    <div class="con"></div>
</div>
<!-- 测试范例 end -->

<!-- 加载依赖 -->
<script src="../SmallJs/SmallJs.js"></script>
<script src="js/Boundary.js"></script>

<!-- 使用 -->
<script>
    // 支持 dom 元素
    var selector = document.getElementById('test');
    // 支持 id
    var selector = '#id';
    var boundary = new Boundary(selector , {
        // 是否进行初次检查
        once: true ,
        // 滚动条滚动到顶部时触发
        top: function(){
            console.log('滚动到顶部');
        } ,
        // 滚动条滚动到底部时触发
        bottom: function(){
            console.log('滚动到底部');
        } ,
        // 滚动条滚动时触发
        scroll: function(){
            console.log('滚动中');
        }
    });
</script>
```

## css

通用型样式风格。使用前务必载入：

```
<link rel='stylesheet' href='base.css'>
<link rel='stylesheet' href='ui/ui.css'>
```

#### form

表单输入。


`form/filter.css`，表单筛选，载入：

```
<link rel='stylesheet' href='form/filter.css'>
```

使用：

```
<div class='filter-option'>
    <div class='option'>
        <div class='field'>筛选字段1：</div>
        <div class='value'>
            <input type='text' class='form-text'>
        </div>
    </div>
    <div class='option'>
            <div class='field'>筛选字段2：</div>
            <div class='value'>
                <input type='text' class='form-text'>
            </div>
        </div>
</div>
```

`input.css`，输入表单，载入：

```
<link rel='stylesheet' href='form/input.css'>
```

使用：

```
<table class='input-tb'>
    <tbody>
        <tr>
            <td>字段1</td>
            <td>
                <input type='text'>
            </td>
        </tr>
        
        <tr>
            <td>字段2</td>
            <td>
                <input type='text'>
            </td>
        </tr>
    </tbody>
</table>
```

#### table

列表展示。

`line.css`，载入：

```
<link rel='stylesheet' href='line.css'>
```

使用：

```
<table>
    <thead>
        <tr>
            <th class='th-id'></th>
            <th class='th-name'></th>
            <th class='th-phone'></th>
            <th class='th-number'></th>
            <th class='th-status'></th>
            <th class='th-time'></th>
            <th class='th-sex'></th>
            <th class='th-mobile'></th>
            <th class='th-desc'></th>
            <th class='th-addr'></th>
            <th class='th-note'></th>
            <th class='th-opr'></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>普通行</td>
            <!-- 多行 -->
            <td class='multiple-rows'>
                <div class='row'><img src='one.jpg' class='image'></div>
            </td>
            <td>普通行</td>
            <td>普通行</td>
            <td>普通行</td>
            <td>普通行</td>
            <td>普通行</td>
            <td>普通行</td>
            <td>普通行</td>
            <td>普通行</td>
            <td>普通行</td>
            <td>普通行</td>
        </tr>
    </tbody>
</table>
```

`column.css`，载入：

```
<link rel='stylesheet' href='table/column.css'>
```

使用：

```
<table class='column-tb'>
    <tbody>
        <tr>
            <td>字段：字段值</td>
        </tr>
        <tr>
            <td>字段：字段值</td>
        </tr>
    </tbody>
</table>
```

#### ui

`ui.css` 中主要提供了一些常用的样式结构：

按钮：

```
btn-1
btn-2
btn-3
...
btn-11
```

标题：

```
component-title
component-title-1
component-title-2
component-title-3

<!-- html 结构 -->
<div class='标题样式'>
    <div class='left'></div>
    <div class='right'></div>
</div>
```

字体：

```
f-12
f-13
f-14
f-15
f-16
```

颜色：

```
red 红色
green 绿色
gray 灰色
```

强调：

```
weight 加粗
```

## FunctionNav

导航菜单切换-适用于 `pc/mobile`。

```
<!-- 加载样式 -->
<link rel='stylesheet' href='../base/base.css'>
<link rel='stylesheet' href='css/FunctionNav.css'>

<!-- 测试范例 start -->
<style>
    #container {
        width: 200px;
    }
</style>
<div id="container">

    <!-- 加载结构 -->
    <div class="function-nav">
        <div class="functions clear-float">
            <div class="function" data-id="diary">日常11</div>
            <div class="function cur" data-id="image">图片1234</div>
            <div class="function" data-id="video">视频</div>
            <div class="function" data-id="article">文章123456</div>
            <div class="function" id="circle" data-id="circle">圈子</div>
        </div>
        <div class="slider"></div>
    </div>
    
</div>
<!-- 测试范例 end -->

<!-- 加载依赖 -->
<script src="../SmallJs/SmallJs.js"></script>
<script src="js/FunctionNav.js"></script>

<!-- 使用 -->
<script>
    var selector = document.getElementById('container');
    var selector = '#container';
    var fn = new FunctionNav(selector , {

    });
</script>
```

## InfiniteClassification

无限极分类。

```
<!-- 加载样式 -->
<link rel='stylesheet' href='../base/base.css'>
<link rel='stylesheet' href='css/InfiniteClassification.css'>

<!-- 测试范例 start -->
<div id='container'>

    <!-- 加载结构 -->
    <div class='infinite-classification'>
        <div class="list">
            <div class="item" data-id="1">
                <div class="function">
                    <div class="icon"><img src="image/default/test.png" class="image"></div>
                    <div class="explain">
                        <div class="in">
                            <div class="ico"><img src="image/default/ring.png" class="image"></div>
                            <div class="text">车辆列表</div>
                        </div>
                    </div>
                    <div class="flag hide">
                        <div class="new">新的</div>
                        <div class="number">10</div>
                        <div class="switch"><img src="image/default/spread.png" class="image"></div>
                    </div>
                </div>
                <div class="child">
                    <div class="list">
                        <div class="item" data-id="2">
                            <div class="function">
                                <div class="icon"><img src="image/default/test.png" class="image"></div>
                                <div class="explain">
                                    <div class="in">
                                        <div class="ico"><img src="image/default/ring.png" class="image"></div>
                                        <div class="text">车辆列表</div>
                                    </div>
                                </div>
                                <div class="flag hide">
                                    <div class="new">新的</div>
                                    <div class="number">10</div>
                                    <div class="switch"><img src="image/default/spread.png" class="image"></div>
                                </div>
                            </div>
                            <div class="child">
                                <div class="list">
                                    <div class="item" data-id="4">
                                        <div class="function">
                                            <div class="icon"><img src="image/default/test.png" class="image"></div>
                                            <div class="explain">
                                                <div class="in">
                                                    <div class="ico"><img src="image/default/ring.png" class="image"></div>
                                                    <div class="text">车辆列表</div>
                                                </div>
                                            </div>
                                            <div class="flag hide">
                                                <div class="new">新的</div>
                                                <div class="number">10</div>
                                                <div class="switch"><img src="image/default/spread.png" class="image"></div>
                                            </div>
                                        </div>

                                        <div class="child">
                                            <div class="list"></div>
                                        </div>
                                    </div>
                                    <div class="item" data-id="5">
                                        <div class="function">
                                            <div class="icon"><img src="image/default/test.png" class="image"></div>
                                            <div class="explain">
                                                <div class="in">
                                                    <div class="ico"><img src="image/default/ring.png" class="image"></div>
                                                    <div class="text">车辆列表</div>
                                                </div>
                                            </div>
                                            <div class="flag hide">
                                                <div class="new">新的</div>
                                                <div class="number">10</div>
                                                <div class="switch"><img src="image/default/spread.png" class="image"></div>
                                            </div>
                                        </div>
                                        <div class="child">
                                            <div class="list"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>
<!-- 测试范例 end -->

<!-- 加载依赖 -->
<script src="../SmallJs/SmallJs.js"></script>
<script src="js/InfiniteClassification.js"></script>

<!-- 使用 -->
<script>
    var selector = document.getElementById('container');
    var selector = '#container';
    var ic = new InfiniteClassification(selector , {
        // 菜单展开动画过渡时间
        time: 300 ,
        // 次要的图标类型，new || number || switch
        icon: 'switch' ,
        // 标识符数组，初始化展开的项
        id: [13] ,
        // 初始状态，spread || shrink
        status: 'shrink' ,
        // 层级视觉显示效果
        amount: 12 ,
        // 同层级是否互斥
        exclution: false ,
        // 展开回调
        spread: function(id){
            console.log('spread:' + id);
        } ,
        // 收缩回调
        shrink: function(id){
            console.log('shrink:' + id);
        } ,
        // 项点击回调函数
        click: function(id){
            console.log('click:' + id);
        } ,
        // 是否菜单也可被选中
        menuFocus: true
    });
    
    // 展开所有
    ic.spreadAll();
    // 收缩所有
    ic.shrinkAll();
    // 展开指定项
    ic.spread(id , callback);
    // 收缩指定项
    ic.shrink(id , callback);
</script>
```

## Load

下拉刷新/上拉加载；适用于 `pc/mobile`。

```
<!-- 加载样式 -->
<link rel='stylesheet' href='../base/base.css'>
<link rel='stylesheet' href='css/LoadTop.css'>
<link rel='stylesheet' href='css/LoadBtm.css'>

<!-- 测试范例 start -->
<style>
    #container {
        width: 400px;
        height: 600px;
        border: 1px solid red;
        margin: 0;
        margin-left: 20px;
        position: relative;
    }
</style>

<div id="container">
    
    <!-- 加载结构：下拉刷新 -->
    <div class="Load-Top">
        <img src="image/load.gif" class="image">
        <span class="text"></span>
    </div>
    
    <!-- 加载结构：上拉加载 -->
    <div class="Load-Btm hide">
        <img src="image/load.gif" class="image">
        <span class="text">加载中</span>
    </div>
    
</div>
<!-- 测试范例 end -->

<!-- 加载依赖 -->
<script src="../SmallJs/SmallJs.js"></script>
<script src="js/LoadTop.js"></script>
<script src="js/LoadBtm.js"></script>

<!-- 使用 -->
<script>
    var selector = document.getElementById('container');
    var selector = '#container';
    
    // 下拉刷新
    var lt = new LoadTop(selector , {
        // 动画时常
        time: 300 ,
        // 允许的值：show | hide
        status: 'show' ,
        // 文本描述
        text: '正在加载' ,
        // 加载回调函数
        load: function(){
            window.setTimeout(this.hide.bind(this) , 3000);
        } ,
    });

    // 模拟请求
    window.setTimeout(function(){
        lt.hide();
    } , 3000);
    
    // 上拉加载
    var lb = new LoadBtm(selector , {
        // 动画时常
        time: 300 ,
        // 允许的值：show | hide
        status: 'show' ,
        // 文本描述
        text: '正在加载' ,
    });
    
    // 显示
    lt.show();
    lb.show();
    // 隐藏
    lt.hide();
    lb.hide();
</script>
```

## MenuSwitch

导航菜单切换。

```
<!-- 加载样式 -->
<link rel='stylesheet' href='../base/base.css'>
<link rel='stylesheet' href='css/MenuSwitch.css'>

<!-- 测试范例 start -->
<div id="container">

    <!-- 加载结构 -->
    <div class='menu-switch'>
        <div class='item cur'>测试 1</div>
        <div class='item'>测试 2</div>
    </div>
    
</div>
<!-- 测试范例 end -->

<!-- 加载依赖 -->
<script src="../SmallJs/SmallJs.js"></script>
<script src="js/MenuSwitch.js"></script>

<!-- 使用 -->
<script>
    var selector = document.getElementById('container');
    var selector = '#container';
    var m = MenuSwitch(selector , {
        // 默认选中的项
        id: 'one' ,
        // 样式类型
        type: 1 , 
        // 项点击后回调
        click: null ,
        // 切换后回调
        switch: null
    });
</script>
```

## PicPlay_Touch

图片轮播。

```
<!-- 加载样式 -->
<link rel='stylesheet' href='../base/base.css'>
<link rel='stylesheet' href='css/PicPlay_Touch.css'>

<!-- 测试范例 start -->
<style>

	.container {
		max-width:900px;
		min-width:320px;
		height:600px;
		margin-top:15px;
	}

</style>
<div id='container'>
   
   <!-- 加载结构 -->
   <div class='pic-play-touch'>
		<div class='images'>
			<div class='_images'>
				<a href='https://www.baidu.com/' class='link'><img src='image/test/01.jpg' class='image'></a>
				<a href='https://www.baidu.com/' class='link'><img src='image/test/02.jpg' class='image'></a>
				<a href='https://www.baidu.com/' class='link'><img src='image/test/03.jpg' class='image'></a>
				<a href='https://www.baidu.com/' class='link'><img src='image/test/04.jpg' class='image'></a>
				<a href='https://www.baidu.com/' class='link'><img src='image/test/05.jpg' class='image'></a>
			</div>

			<div class='prev'><img src='image/prev.png' class='image'></div>
			<div class='next'><img src='image/next.png' class='image'></div>
		</div>

	   	<div class="index">
			<div class='_index hide'></div>
			<div class='_image hide'></div>
		</div>
	</div>
	
</div>
<!-- 测试范例 end -->

<!-- 加载依赖 -->
<script src="../SmallJs/SmallJs.js"></script>
<script src="js/PicPlay_Touch.js"></script>

<!-- 使用 -->
<script>
    var selector = document.getElementById('container');
    var selector = '#container';
    var p = PicPlay_Touch(G('#pic_turn').get() , {
        // 动画过度时间
        time: 300,
        // 定时器时间
        duration: 2000 ,
        // 索引类型, index-普通索引 image-图片索引 none-无索引
        indexType: 'index',
        // 索引容器位置 (inset | outset)
        indexPos: 'outside',
        // 索引摆放类型（horizontal|vertical）
        placementType: 'horizontal',
        // 索引摆放位置（top|right|bottom|left）
        // placementType = horizontal，则允许的值有 top|bottom；placementType = vertical，则允许的值有 left|right
        placementPos: 'bottom',
        // placementType=horizontal时，请勿设置 placementPos=top！因为存在尚未修复的 bug
        // 默认点击图片会进行跳转
        linkTo: true,
        // 是否启用 上一张 | 下一张 功能
        enableOpr: true,
        // 是否启用滚动功能
        enableScroll: true,
        // 是否开启拖拽功能
        enableDrag: true,
        // 是否开启定时轮播功能
        enableTimer: false ,
        // 初始化显示的图片索引
        index: 1
    });
</script>
```

## PicShow

图片展示，图片索引，会根据索引位置自动滚动到可视位置。

```
<!-- 加载样式 -->
<link rel='stylesheet' href='../base/base.css'>
<link rel='stylesheet' href='css/PicShow.css'>

<!-- 测试范例 start -->
<style>
	#container {
		min-width:400px;
		max-width: 800px;
		height:400px;
	}
</style>

<div id='container'>

    <!-- 图片切换容器 -->
    <div class='pic-show'>

        <!-- 大图预览容器 -->
        <div class='preview'>
            <div class="content">
                <div class="list">
                    <a href="https://www.baidu.com/" class="link"><img src='image/Test/01.jpg' class='image'></a>
                    <a href="https://www.baidu.com/" class="link"><img src='image/Test/02.jpg' class='image'></a>
                    <a href="https://www.baidu.com/" class="link"><img src='image/Test/03.jpg' class='image'></a>
                    <a href="https://www.baidu.com/" class="link"><img src='image/Test/04.jpg' class='image'></a>
                    <a href="https://www.baidu.com/" class="link"><img src='image/Test/05.jpg' class='image'></a>
                    <a href="https://www.baidu.com/" class="link"><img src='image/Test/06.jpg' class='image'></a>
                </div>
            </div>
            <div class="btn prev"><img src="image/wPrev.png" class="image"></div>
            <div class="btn next"><img src="image/wNext.png" class="image"></div>
        </div>

        <!-- 索引容器 -->
        <div class='index'>
            <div class='btn prev'><img src='image/prev.png' class='image'></div>
            <div class='small'>
                <div class='list'>
                    <!-- 开发时保留！ -->
                    <div class='item cur'><img src='image/Test/01.jpg' class='pic'></div>
                    <div class='item'><img src='image/Test/02.jpg' class='pic'></div>
                </div>
            </div>
            <div class='btn next'><img src='image/next.png' class='image'></div>
        </div>
    </div>

</div>
<!-- 测试范例 end -->

<!-- 加载依赖 -->
<script src="../SmallJs/SmallJs.js"></script>
<script src="js/PicShow.js"></script>

<!-- 使用 -->
<script>
    var selector = document.getElementById('container');
    var selector = '#container';
    var s = new PicShow(selector , {
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
        // 点击下一个时回调
        next: null ,
        // 点击上一个时回调
        prev: null ,
        // 大图点击时回调
        click: null ,
        // 滚动功能
        enableWheel: true ,
        // 上一页、下一页功能
        enableOpr: true
    });
</script>
```

## Prompt

提示框。

```
<!-- 加载样式 -->
<link rel='stylesheet' href='../base/base.css'>
<link rel='stylesheet' href='css/Prompt.css'>

<!-- 加载依赖 -->
<script src="../SmallJs/SmallJs.js"></script>
<script src="js/Prompt.js"></script>

<!-- 使用 -->
<script>
     new Prompt('fuck you now !! i love this');
    // or
    Prompt.alert('你好');
</script>
```

## Slidebar

侧边栏滑动。

```
<!-- 加载样式 -->
<link rel='stylesheet' href='../base/base.css'>
<link rel='stylesheet' href='css/Slidebar.css'>

<!-- 测试范例 start -->
<style>
    .container {
        width: 400px;
        height: 600px;
        border: 1px solid red;
        margin: 0;
        margin-left: 20px;
    }
</style>

<div class="container">
    <!-- 加载结构 -->
    <div class="slidebar hide">
        <div class="mask"></div>
        <div class="con"></div>
    </div>
    
</div>
<div class="btns">
    <button onclick="showSlidebar()">切换</button>
</div>
<!-- 测试范例 end -->

<!-- 加载依赖 -->
<script src="../SmallJs/SmallJs.js"></script>
<script src="js/Slidebar.js"></script>

<!-- 使用 -->
<script>
    var selector = document.getElementById('container');
    var selector = '#container';
    var s = new Slidebar(selector , {
         // 动画时间
        time: 300 ,
        // 滑块宽度
        width: '75%' ,
        // 滑块遮罩层透明度
        opacity: 0.3 ,
        // 状态：show | hide
        status: 'show' ,
        // 点击时回调
        click: null ,
        // 侧边栏方向: left , right
        dir: 'left' ,
        // 侧边栏展示时回调
        open: null ,
        // 侧边栏隐藏时回调
        close: null
    });

    function showSlidebar(){
        if (s.status == 'show') {
            s.hide();
        } else {
            s.show();
        }
    }
</script>
```

## Switch

内容切换，参考移动端手指左右滑动内容切换。

```
<!-- 加载样式 -->
<link rel='stylesheet' href='../base/base.css'>
<link rel='stylesheet' href='css/Switch.css'>

<!-- 测试范例 start -->
<style>
    #container {
        width: 300px;
        height: 300px;
        border: 1px solid red;
    }
</style>
<div class="container">

    <!-- 加载结构 -->
    <div class="switch">
        <div class="items">
            <div class="item" data-id="one">内容1</div>
            <div class="item" data-id="two">内容2</div>
            <div class="item" data-id="thr">内容3</div>
            <div class="item" data-id="four">内容4</div>
        </div>
    </div>

</div>
<!-- 测试范例 end -->

<!-- 加载依赖 -->
<script src="../SmallJs/SmallJs.js"></script>
<script src="js/Switch.js"></script>

<!-- 使用 -->
<script>
    var selector = document.getElementById('container');
    var selector = '#container';
    // 强烈声明！不要嵌套！！
    var s = new Switch(selector , {
        id: 'two' ,
        // 动画过度时间
        time: 300 ,
        // 切换事件
        switch: function(id){
            console.log('内容切换成功' , id);
        }
    });
</script>
```

## UploadImage

图片上传。

```
<link rel='stylesheet' href='../base/base.css'>
<link rel='stylesheet' href='css/UploadImage.css'>

<!-- 测试范例 start -->
<div class="container">

    <!-- 加载结构 -->
    <div class='upload-image'>

        <div class='select-images'>
            <div class="upload-show">
                <div class="image-line"><img src="" class="image upload-image-btn" /><span class="selected-count hide">10</span></div>
                <div class="text-line">请选择要上传的图片</div>
                <div class="clear-selected" title="清空已选择的图片"><img src="" class="image" /></div>
                <input type='file' name='upload_images' multiple="multiple" class='upload-images-input'  />
            </div>
            <div class="tip">这边是提示内容</div>
        </div>

        <!-- 预置显示图片 -->
        <div class="init-show-image-list">
            <!-- 开发时保留 -->
            <img src="http://qp333com.oss-cn-hangzhou.aliyuncs.com/7peishang.com/avatar/2017-11-10/bf07531ad5dd288afc93bab47ee8d258.jpg" class="init-show-image" />
            <img src="http://qp333com.oss-cn-hangzhou.aliyuncs.com/7peishang.com/avatar/2017-11-10/bf07531ad5dd288afc93bab47ee8d258.jpg" class="init-show-image" />
            <img src="http://qp333com.oss-cn-hangzhou.aliyuncs.com/7peishang.com/avatar/2017-11-10/bf07531ad5dd288afc93bab47ee8d258.jpg" class="init-show-image" />
        </div>

        <div class='preview-images hide'>
            <!-- 图片上传项目：旧 -->
            <div class="image-item" data-filename="sama-96.jpg">
                <div class="img"><img src="http://qp333com.oss-cn-hangzhou.aliyuncs.com/7peishang.com/avatar/2017-11-10/bf07531ad5dd288afc93bab47ee8d258.jpg" class="image"></div>

                <div class="close"><img src="/UploadImages/Images/delete_unfocus.png" data-focus="/UploadImages/Images/delete_focus.png" data-unfocus="/UploadImages/Images/delete_unfocus.png" class="image"></div>

                <div class="progress hide">
                    <div class="p-total">
                        <div class="p-cur"></div>
                    </div>
                </div>

                <div class="msg hide">
                    <div class="msg-in">成功</div>
                </div>
            </div>

        </div>

        <!-- 待上传列表 -->
        <div class="upload-image-list hide">
            <div class="upload-title">待上传列表</div>

            <div class="image-list">
                <div class="list-content list-title">
                    <div class="item div-preview">图片预览</div>
                    <div class="item div-type">类型</div>
                    <div class="item div-size">大小</div>
                    <div class="item div-speed">速度</div>
                    <div class="item div-status">状态</div>
                    <div class="item div-opr">操作</div>
                </div>

                <div class="list-content list-body">

                    <!-- 开发时保留！项 -->
                    <div class="line total-progress">
                        <div class="line-in">
                            <!-- 上传进度 -->
                            <div class="cur-progress"></div>

                            <!-- 状态 -->
                            <div class="msg hide">
                                <div class="msg-in">...</div>
                            </div>
                            <div class="item div-preview multiple-rows">
                                <div class="row">sama-01.jpg</div>
                                <div class="row"><img src="http://qp333com.oss-cn-hangzhou.aliyuncs.com/7peishang.com/avatar/2017-11-10/bf07531ad5dd288afc93bab47ee8d258.jpg" class="image" /></div>
                            </div>
                            <div class="item div-type">image/jpeg</div>
                            <div class="item div-size">2.4M</div>
                            <div class="item div-speed">50kb/s</div>
                            <div class="item div-status">上传中...</div>
                            <div class="item div-opr multiple-rows">
                                <div class="row"><button type="button" class="btn-1 cancel">取消上传</button></div>
                                <div class="row"><button type="button" class="btn-1 delete">删除图片</button></div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>

</div>

<br />
<br />
<br />

<button id="upload" class="btn-2">上传</button>
<!-- 测试范例 end -->

<!-- 加载依赖 -->
<script src="../SmallJs/SmallJs.js"></script>
<script src="js/UploadImage.js"></script>

<!-- 使用 -->
<script>
    var upload = document.getElementById('upload');
    var selector = document.getElementById('container');
    var selector = '#container';
    var uploadObj = new UploadImage(selector , {
        // 图片上传链接
        url: '' ,
        // 待上传图片的表单字段名称：默认是 images
        field: 'images' ,
        // 插件 url
        pluginUrl: '' ,
        // 默认模式是追加 append | override
        mode: 'append' ,
        // 单张上传成功，每次上传成功后回调
        success: null ,
        // 单张上传失败，每次上传失败后回调
        error: null ,
        // 所有图片上传完成后的回调函数
        callback: null ,
        // 自动切换上传方式的图片数量，例如这边默认：5，表示 <=5 则采用并行上传（每张图片发起一个请求），>5 则采取串行，一张张上传.
        split: 1 ,
    });
    upload.onclick = function(){
        // 开始上传
        up.upload();
    } , false ,false);
</script>
```

## Zoom

图片缩放，仅适用于移动端，存在尚未修复的 `bug`，请勿使用！

```
<link rel='stylesheet' href='../base/base.css'>
<link rel='stylesheet' href='css/Zoom.css'>

<!-- 测试范例 start -->
<img src='test.jpg' id='container'>
<!-- 测试范例 end -->

<!-- 加载依赖 -->
<script src="../SmallJs/SmallJs.js"></script>
<script src="js/Zoom.js"></script>

<!-- 使用 -->
<script>
    var selector = document.getElementById('container');
    var selector = '#container';
    var zoom = new Zoom(selector , {
        // 尚无选项
    });
</script>
```

## MultipleTab

多标签。

```
<link rel='stylesheet' href='../base/base.css'>
<link rel='stylesheet' href='css/MultipleTab.css'>

<!-- 测试范例 start -->
<style>
    #add-tabs {
        background-color:red;
        color:white;
        border-radius:3px;
        padding:5px;
        font-size:14px;
        margin:12px;
        cursor:pointer;
    }
</style>
<div class="container">
    
    <!-- 加载结构 -->
    <div class="multiple-tab">
        <!-- 项列表 -->
        <div class="tabs">

            <!-- 开发时保留！ -->
            <div class="tab">
                <div class="ico"><img src="image/icon.ico" class="image" /></div>
                <div class="text">控制台</div>
                <div class="close">
                    <div class="positive"></div>
                    <div class="negative"></div>
                </div>
            </div>

        </div>
    </div>

</div>、
<div class="btn-list">
    <button id="add-tabs">添加标签</button>
</div>
<!-- 测试范例 end -->

<!-- 加载依赖 -->
<script src="../SmallJs/SmallJs.js"></script>
<script src="js/MultipleTab.js"></script>

<!-- 使用 -->
<script>
    var selector = document.getElementById('container');
    var selector = '#container';
    var tab = new MultipleTab(con.get(0) , {
        // 新建标签后回调
        created: null ,
        // 标签页删除后回调函数
        deleted: null ,
        // 标签点击后回调
        click: null ,
        // 默认：标签图标
        ico: '../image/default/default.png' ,
        // 默认：标签内容
        title: '新标签页' ,
        // 动画时间
        time: 300
    });
    var i = 0;
    btn.on('click' , function(){
        tab.create({
            // 标签标题
            text: '测试标签标题-' + ++i ,
            // 标签图标
            // ico: '' ,
            // 标签附加属性
            // attr: {} 
        });
    } , true , false);
</script>
```

## TouchFeedback

动作反馈。