/*
 * author 陈学龙 2017-09-18 14:55:00
 * 仅支持本地图片上传
 * 进度条显示 + 上传速度 + 单张串行上传 + 可终止上传中的图片
 * 少量图片并行上传（<= 5）
 * 多张图片串行上传（> 5）
 *
 * 由于文件可以是追加的方式添加，所以可能会碰到不同目录下的重名文件
 * 但是由于文件内容不一致，所以，不能单纯的根据文件名重名来过滤文件
 *
 * 并行添加：
 * 1. 添加图片项
 * 2. 定义图片项事件
 * 	2.1 定义删除事件
 * 3. 可选设置上传选项
 * 3. 上传事件
 *
 * 串行添加：
 * 1. 添加图片项
 * 2. 定义图片项事件
 * 	2.1 删除事件
 * 	2.2 取消事件（在上传开始后显示，结束后移除）
 * 3. 可选设置上传选项（同并行）
 * 4. 上传事件
 *
 * 模式切换的两种途径（单项）：
 * 并行添加=》并行 => 串行
 * 串行删除=》串行 => 并行
 */
(function(global , factory){
	'use strict';

	// 通过以下代码判断代码是否运行在 nodejs 环境下
    // 这尤其适用于通过 nodejs 进行打包的情况！
    // 兼容浏览器端 js 和 服务器端 js
	if (typeof module !== 'undefined' && typeof module.exports == 'object') {
		module.exports = factory(global , true);
	} else {
		factory(global);
	}
})(typeof window !== undefined ? window : this , function(window , noGlobal){
	"use strict";

    function UploadImage(selector , opt){
        var thisRange = [undefined , null , window];

        if (G.contain(this , thisRange) || !G.contain(this , thisRange) && this.constructor !== UploadImage) {
            return new UploadImage(selector , opt);
        }

        // 图片初始化，直接在图片列表中，填充 img 标签即可
        this._default = {
            // 图片上传链接
            url: '' ,
            // 待上传图片的表单字段名称：默认是 images
            field: 'images' ,
            pluginUrl: '' ,
            // 默认模式是追加 append | override
            mode: 'append' ,
            // 单选 | 多选，默认多选
            multiple: true ,
            // 图片上传开始之前回调函数
            before: null ,
            // 单张上传成功，每次上传成功后回调
            success: null ,
            // 单张上传失败，每次上传失败后回调
            error: null ,
            // 所有图片上传完成后的回调函数
            callback: null ,
            // 上传开始时调用的回调函数
            uLoadStart: null ,
            // 上传结束时调用的回调函数
            uProgress: null ,
            // 上传结束时调用的回调函数
            uLoadEnd: null ,
            // 自动切换上传方式的图片数量，例如这边默认：5，表示 <=5 则采用并行上传（每张图片发起一个请求），>5 则采取串行，一张张上传.
            split: 1 ,
            // 初始选择的展示图片数量
            initImageList: [] ,
        };

        // 设置项
        if (G.type(opt) === 'Undefined') {
            opt = this._default;
        }

        // 支持的模式
        // override 覆盖
        // append 追加
        this.modeRange = ['override' , 'append'];

        // 相关元素
        this._con = G(selector);
        this._uploadImage = G('.upload-image' , this._con.get(0));

        // 设置项
        this._url 					= G.type(opt.url) === 'String' ? opt.url : this._default.url;
        this._pluginUrl 			= G.type(opt.pluginUrl) === 'String' ? opt.pluginUrl : this._default.pluginUrl;
        this._field 				= G.type(opt.field) === 'String' ? opt.field : this._default.field;
        this._multiple 				= G.type(opt.multiple) === 'Boolean' ? opt.multiple : this._default.multiple;
        this._initImageList 			= G.type(opt.initImageList) === 'Array' ? opt.initImageList : this._default.initImageList;
        this._callback 				= opt.callback;
        this._before 				= opt.before;
        this._success 				= opt.success;
        this._error 				= opt.error;
        this._uLoadStart 			= opt.uLoadStart;
        this._uLoadEnd 				= opt.uLoadEnd;
        this._mode 					= G.contain(opt.mode , this.modeRange) ? opt.mode : this._default.mode;
        this._split 				= G.type(opt.split) === 'Number' ? opt.split : this._default.split;

        this.run();
    };

    UploadImage.prototype = {
        constructor: UploadImage ,

        _initStaticHTML: function(){} ,

        _initStaticArgs: function(){
            // 元素集合
            this._initShowImageList   = G('.init-show-image-list'	, this._uploadImage.get(0));
            this._previewImages		= G('.preview-images'			, this._uploadImage.get(0));
            this._uploadImageList		= G('.upload-image-list'		, this._uploadImage.get(0));
            this._uploadInput		= G('.upload-images-input'	, this._uploadImage.get(0));
            this._selectedCount		= G('.selected-count'		, this._uploadImage.get(0));
            this._clearSelected		= G('.clear-selected'		, this._uploadImage.get(0));
            this.uploadImageBtn		= G('.upload-image-btn' 		, this._uploadImage.get(0));

            this._uploadTitle		= G('.upload-title'			, this._uploadImageList.get(0));
            this._imageList			= G('.image-list'				, this._uploadImageList.get(0));
            this._listBody			= G('.list-body'			, this._imageList.get(0));
            this._clearSelectedImage   = G('.image' 					, this._clearSelected.get(0));

            // 完整添加的图片列表
            this._imageFileList				= [];
            // 完整上传成功的图片列表
            this._succUploadImageFileList		= [];
            // 完整上传失败的图片列表
            this._failedUploadImageFileList 	= [];
            // 完整取消上传的图片列表
            this._cancelUploadImageFileList 	= [];
            // 单次上传成功的图片数量
            this._tempSuccUploadImageFileList = [];
            // 单次上传失败的图片数量
            this._tempFailedUploadImageFileList = [];
            // 单次上传取消上传的图片数量
            this._tempCancelUploadImageFileList = [];
            // 之前上传失败的图片
            this._prevFailedUploadImageFileList = [];
            // 单次上传待上传的图片列表
            this._uploadedList			  	= [];

            // 支持的图片类型
            this._imageType			= ['image/jpeg' , 'image/png' , 'image/gif'];

            // 图片源
            this._uploadImageBtnImageSrc 	= this._pluginUrl + 'image/upload_images.png';
            this._clearSelectedImageSrc 	= this._pluginUrl + 'image/clear_selected.png';
        } ,

        _initStatic: function(){
            // 初始化设置 input 的图片按钮
            this.uploadImageBtn.attr('src' , this._uploadImageBtnImageSrc);

            // 清除按钮
            this._clearSelectedImage.attr('src' , this._clearSelectedImageSrc);
        } ,

        _initDynamicHTML: function(){

        } ,

        _initDynamicArgs: function(){
            // 根据每次上传的图片数量确定上传的方式：parallel（并行）、serial（串行）
            this._type = this._uploadedList.length > this._split ? 'serial' : 'parallel';
        } ,

        _initDynamic: function(){

        } ,

        _initialize: function(){

        } ,

        // 通过文件ID找到图片列表中对应的项 index
        findSelectedImageItemIndexByIdentifier: function(identifier){
            var i = 0;

            for (; i < this._imageFileList.length; ++i)
            {
                if (this._imageFileList[i]['identifier'] === identifier) {
                    return i;
                }
            }

            return false;
        } ,

        // 通过文件找到图片列表中对应的项 index
        findSelectedImageItemIndexByFile: function(file){
            var i = 0;

            for (; i < this._imageFileList.length; ++i)
            {
                if (this._imageFileList[i]['file'] === file) {
                    return i;
                }
            }

            return false;
        } ,

        // 从完整的图片集合中删除指定索引的单元
        deleteFromImageFileList: function(index){
            return this._imageFileList.splice(index , 1);
        } ,

        // 实际从数组中删除
        deleteFromList: function(list , unit){
            var i 	= 0;
            var cur = null;

            for (; i < list.length; ++i)
            {
                cur = list[i];

                if (cur === unit) {
                    list.splice(i , 1);
                    return true;
                }
            }

            return false;
        } ,

        // 从待上传图片列表中删除指定的图片（可能不存在也不奇怪）
        // 假设从已上传列表中删除一张图片项，那么就会不存在，很正常.
        deleteUnitByIdentifier: function(identifier){
            var index = this.findSelectedImageItemIndexByIdentifier(identifier);
            var file  = this._imageFileList[index]['file'];

            this.deleteFromList(this._succUploadImageFileList 	, file);
            this.deleteFromList(this._failedUploadImageFileList 	, file);
            this.deleteFromList(this._cancelUploadImageFileList 	, file);

            this.deleteFromList(this._uploadedList , file);
            this.deleteFromList(this._tempSuccUploadImageFileList 	, file);
            this.deleteFromList(this._tempFailedUploadImageFileList 	, file);
            this.deleteFromList(this._tempCancelUploadImageFileList 	, file);

            this.deleteFromImageFileList(index);
        } ,

        deleteUnitByFile: function(file){
            var index = this.findSelectedImageItemIndexByFile(file);

            this.deleteFromList(this._succUploadImageFileList 	, file);
            this.deleteFromList(this._failedUploadImageFileList 	, file);
            this.deleteFromList(this._cancelUploadImageFileList 	, file);

            this.deleteFromList(this._uploadedList , file);
            this.deleteFromList(this._tempSuccUploadImageFileList 	, file);
            this.deleteFromList(this._tempFailedUploadImageFileList 	, file);
            this.deleteFromList(this._tempCancelUploadImageFileList 	, file);

            this.deleteFromImageFileList(index);
        } ,

        // 生成唯一标识
        genID: function(){
            return G.randomArr(100 , 'mixed').join('');
        } ,

        // 并行上传：删除上传的 item
        _parallelDeleteImageItemEvent: function(event){
            var tar  		= G(event.currentTarget);
            var identifier 	= tar.data('identifier');

            // 删除改文件（相关的所有数据）
            this.deleteUnitByIdentifier(identifier);

            this._selectedCount.text(this._uploadedList.length);

            if (this._uploadedList.length !== 0) {
                this._selectedCount.removeClass('hide');
            } else {
                this._selectedCount.addClass('hide');
            }

            // 重新初始化参数
            this._initDynamicHTML();
            this._initDynamicArgs();
            this._initDynamic();

            // 删除自身
            tar.get(0).parentNode.removeChild(tar.get(0));
        } ,

        // 串行上传：删除上传的 item
        _serialDeleteImageItemEvent: function(event){
            var self		= this;
            var tar  		= G(event.currentTarget);
            var canDelete   = tar.data('canDelete');

            // 不允许删除
            if (canDelete === 'n') {
                return ;
            }

            // 删除自身
            var imageItem = tar.parents({
                tagName: 'div' ,
                className: 'line total-progress'
            } , this._listBody.get(0));

            var identifier = imageItem.data('identifier');

            // 删除文件
            this.deleteUnitByIdentifier(identifier);

            this._selectedCount.text(this._uploadedList.length);

            if (this._uploadedList.length !== 0) {
                this._selectedCount.removeClass('hide');
            } else {
                this.hideSerial();
                this._selectedCount.addClass('hide');
            }

            // 重新初始化参数
            self._initDynamicHTML();
            self._initDynamicArgs();
            self._initDynamic();

            // 显著的从视野下被删除
            imageItem.addClass('focus-line');

            var sW = imageItem.width('border-box');
            var eW = 0;

            imageItem.animate({
                width: '0px'
            } , function(){
                imageItem.get(0).parentNode.removeChild(imageItem.get(0));

                // 切换上传模式
                if (self._uploadedList.length <= self._split) {
                    self.addImageForFile(self._succUploadImageFileList);
                    self.addImageForFile(self._failedUploadImageFileList);
                    self.addImageForFile(self._cancelUploadImageFileList);
                    self.addImageForFile(self._uploadedList);
                }
            });
        } ,

        // 并行添加的图片项事件
        _parallelDefineImageItemEvent: function(imageItem){
            var imageItem		= G(imageItem);
            var close		= G('.close'	, imageItem.get(0));
            var closeImage	= G('.image'		, close.get(0));

            // 删除事件
            imageItem.on('click' , this._parallelDeleteImageItemEvent.bind(this) , false , false);

            // 关闭按钮鼠标悬浮事件
            close.on('mouseover' , function(){
                closeImage.get(0).src = closeImage.data('focus');
            } , false , false);

            // 关闭按钮鼠标悬浮事件
            close.on('mouseout' , function(){
                closeImage.get(0).src = closeImage.data('unfocus');
            } , false , false);
        } ,


        // 串行添加的图片项事件
        _serialDefineImageItemEvent: function(imageItem){
            imageItem = G(imageItem);

            var _delete = G('.delete' , imageItem.get(0));

            _delete.on('click' , this._serialDeleteImageItemEvent.bind(this) , true , false);
        } ,

        // 图片项 事件
        _defineImageItemEvent: function(imageItem){
            if (this._type === 'parallel') {
                this._parallelDefineImageItemEvent(imageItem);
            } else {
                this._serialDefineImageItemEvent(imageItem);
            }
        } ,

        // 过滤图片集合中重复的图片
        // 过滤规则：文件名 + 文件类型 + 文件大小（解决不同目录重名文件的情况下为误过滤掉）
        filter: function(files){
            var i 		= 0;
            var n       = 0;
            var cur     = null;
            var cCur    = null;
            var self    = this;
            var exists  = false;
            var srcList = [];

            for (; i < files.length; ++i)
            {
                cur 	= files[i];
                exists 	= false;

                // 重名文件过滤
                for (n = 0; n < this._imageFileList.length; ++n)
                {
                    cCur = this._imageFileList[n];

                    if (
                        (cur['name'] === cCur['name']) &&
                        (cur['size'] === cCur['size']) &&
                        (cur['type'] === cCur['type'])
                    ) {
                        exists = true;
                        break;
                    }
                }

                // 文件类型过滤
                if (!G.contain(cur['type'] , this._imageType)) {
                    continue ;
                }

                if (!exists) {
                    srcList.push(cur);
                }
            }

            return srcList;
        } ,

        // 隐藏初始化展示图片列表
        hideInitShowImageList: function(){
            this._initShowImageList.addClass('hide');
        } ,

        // 隐藏并行上传
        hideParallel: function(){
            this._previewImages.addClass('hide');
        } ,

        // 显示并行上传
        showParallel: function(){
            this._previewImages.removeClass('hide');
        } ,

        // 隐藏串行上传
        hideSerial: function(){
            this._uploadImageList.addClass('hide');
        } ,

        // 显示列表行上传
        showSerial: function(){
            this._uploadImageList.removeClass('hide');
        } ,

        // 并行方式添加图片文件
        parallelAddImageForFile: function(files , index){
            var self = this;

            // 没有文件直接返回
            if (files.length === 0) {
                return ;
            }

            if (G.type(index) === 'Undefined') {
                index = files.length - 1;

                // 隐藏 + 清空 serial 相关内容
                this.hideSerial();
                this.serialClearSelected();
                this.showParallel();
            }

            var file 		= files[index];
            var identifier 	= null;

            // 判断是否重复（这会发生在上传方式改变的情况下！必须）
            if (!this.isRepeatImage(file)) {
                identifier = this.genID();

                this._imageFileList.push({
                    identifier: identifier ,
                    file: file
                });
            } else {
                identifier = this.findFromImageFileList(file)['identifier'];
            }

            var div = G(document.createElement('div'));

            div.addClass('image-item');
            div.data('identifier' , identifier);

            // 获取图片的本地预览 blob url
            G.getBlobUrl(file , function(blobUrl){
                var isUploaded = self.isUploaded(file);

                var assign = file;
                assign['src'] = blobUrl;

                div.html(self.templateOne(assign));

                // 添加节点
                self._previewImages.get(0).appendChild(div.get(0));

                var msg = G('.msg' , div.get(0));

                if (isUploaded !== false) {
                    self.showStatus(msg.get(0) , isUploaded);
                }

                // 定义图片项事件
                self._defineImageItemEvent(div.get(0));

                index--;

                if (index >= 0) {
                    // 如果 files 队列没有被消费干净，继续调用，知道他消费完为止
                    self.parallelAddImageForFile(files , index);
                } else {
                    // 添加完成之后
                    self.afterAddSetRelativeArgs();
                }
            });
        } ,

        // 上传结果描述
        uploadStatusExplain: function(status){
            return status === 'success' ? '成功' : (status === 'failed' ? '失败' : '取消');
        } ,

        // 串行方式添加图片文件
        serialAddImageForFile: function(files , index){
            // 无文件返回
            if (files.length === 0) {
                return ;
            }

            var self = this;

            if (G.type(index) === 'Undefined') {
                index = files.length - 1;

                // 隐藏 + 清空 paral 相关内容
                this.hideParallel();
                this.parallelClearSelected();
                this.showSerial();
            }

            var file 		= files[index];
            var identifier 	= null;

            // 判断是否重复（这会发生在上传方式改变的情况下！必须）
            if (!this.isRepeatImage(file)) {
                identifier = this.genID();

                this._imageFileList.push({
                    identifier: identifier ,
                    file: file
                });
            } else {
                identifier = this.findFromImageFileList(file)['identifier'];
            }

            var div = G(document.createElement('div'));
            div.addClass('line total-progress image-item');
            div.data('identifier' , identifier);

            // 获取图片的本地预览 blob url
            G.getBlobUrl(file , function(blobUrl){
                var assign 			= file;
                var isUploaded		= self.isUploaded(file);

                // console.log(isUploaded);

                if (isUploaded) {
                    assign['speed'] 			= '已上传';
                    assign['status'] 			= isUploaded;
                    assign['statusExplain'] 	= self.uploadStatusExplain(isUploaded);
                } else {
                    assign['speed'] 			= '未上传';
                    // 等待上传
                    assign['status'] 			= 'wait';
                    assign['statusExplain'] 	= '未上传';
                }

                assign['sizeExplain'] 	= G.getStorage(assign['size'] , 'b');
                assign['src'] 			= blobUrl;

                div.html(self.templateTwo(assign));

                self._listBody.get(0).appendChild(div.get(0));

                var msg = G('.msg' , div.get(0));

                if (isUploaded !== false) {
                    self.showStatus(msg.get(0) , isUploaded);
                }

                // 定义事件
                self._serialDefineImageItemEvent(div.get(0));

                index--;

                if (index >= 0) {
                    self.serialAddImageForFile(files , index);
                } else {
                    // 添加完成之后
                    self.afterAddSetRelativeArgs();
                }
            });
        } ,

        // 模板1类型：文件方式添加图片项
        addImageForFile: function(files){
            if (this._type === 'parallel') {
                this.parallelAddImageForFile(files);
            } else {
                this.serialAddImageForFile(files);
            }
        } ,

        // 获取图片类型
        getType: function(suffix){
            switch (suffix)
            {
                case 'jpg':
                    return 'image/jpeg';
                case 'png':
                    return 'image/png';
                case 'gif':
                    return 'image/gif';
                default:
                    return false;
            }
        } ,

        // 获取文件名
        getFilename: function(path){
            var filename = G.getFilename(path);
            var suffix   = G.getFileSuffix(path);

            return filename + '.' + suffix;
        } ,

        // 判断图片是否已经存在
        exists: function(name){
            var i = 0;
            var cur = null;

            for (; i < this._imageFileList.length; ++i)
            {
                cur = this._imageFileList[i];

                if (cur['name'] === name) {
                    return true;
                }
            }

            return false;
        } ,

        // 判断是否是重复的图片文件（这可能会发生在上传方式改变的情况下）
        isRepeatImage: function(file){
            var i 	= 0;
            var cur = null;

            for (; i < this._imageFileList.length; ++i)
            {
                cur = this._imageFileList[i];

                if (cur['file'] === file) {
                    return true;
                }
            }

            return false;
        } ,

        // 通过文件对象从 imageFileList 中找到完整的 单元
        findFromImageFileList: function(file){
            var i 	= 0;
            var cur = null;

            for (; i < this._imageFileList.length; ++i)
            {
                cur = this._imageFileList[i];

                if (cur['file'] === file) {
                    return cur;
                }
            }

            throw new Error('未找到文件对象对应的单元');
        } ,

        // 直接图片源的方式添加（不允许这种方式添加图片！）
        addImageForSrc: function(srcList){
            var i 		= 0;
            var cur 	= null;
            var div 	= null;
            var html 	= null;
            var file    = null;
            var type	= null;
            var filename = null;

            if (this._mode === 'override') {
                this._imageFileList 					= [];
                this._previewImages.html('');
            }

            for (; i < srcList.length; ++i)
            {
                cur = srcList[i];

                filename	= this.getFilename(cur);

                // 不会重复添加重名的文件
                if (this.exists(filename)) {
                    continue ;
                }

                div = document.createElement('div');
                div.className = 'image-item';
                div.setAttribute('data-fileName' , filename);

                type = this.getType(cur);

                file = {
                    name: filename ,
                    type: type
                };

                // 添加进图片列表
                this._imageFileList.push(file);

                // 填充 DOM 节点内容

                div.innerHTML = html;

                // 添加节点
                this._previewImages.get(0).appendChild(div);

                // 定义图片项事件
                this._defineImageItemEvent(div);
            }

            // 添加完成之后
            this.afterAddSetRelativeArgs();
        } ,

        // 设置相关显示参数
        afterAddSetRelativeArgs: function(){
            this._selectedCount.text(this._uploadedList.length);

            if (this._uploadedList.length !== 0) {
                this._clearSelected.addClass('clear-selected-hover');
                this._selectedCount.removeClass('hide');
            } else {
                this._clearSelected.removeClass('clear-selected-hover');
                this._selectedCount.addClass('hide');
            }
        } ,

        // 添加待上传图片项
        pushUploadedList: function(files){
            var i = 0;

            for (; i < files.length; ++i)
            {
                this._uploadedList.push(files[i]);
            }
        } ,

        // 初始化操作类型对应的环境
        initOprEnv: function(){
            if (this._mode === 'override') {
                // 完整记录
                this._imageFileList 					= [];
                this._succUploadImageFileList			= [];
                this._cancelUploadImageFileList		= [];
                this._failedUploadImageFileList		= [];

                // 临时记录
                this._uploadedList					= [];
                this._tempFailedUploadImageFileList	= [];
                this._tempCancelUploadImageFileList	= [];
                this._tempFailedUploadImageFileList	= [];
            }

            // 内容展示
            this._previewImages.html('');
            this._listBody.html('');
        } ,

        // 文件初始化
        _initDoms: function(files){
            if (files.length === 0) {
                if (this._imageFileList.length === 0) {
                    // 无图片上传，隐藏所有
                    this.hideParallel();
                    this.hideSerial();
                } else if (this._imageFileList.length > this._split) {
                    // 串行上传，隐藏并行
                    this.hideParallel();
                } else {
                    // 并行上传，隐藏串行
                    this.hideSerial();
                }

                return ;
            }

            // 根据操作类型（追加|覆盖），进行相应的初始化操作
            this.initOprEnv();

            // 隐藏初始化展示图片
            this.hideInitShowImageList();

            // 添加到待上传图片列表
            this.pushUploadedList(files);

            // 暂时记住上一次添加模式
            var type = this._type;

            // 重新初始化参数
            this._initDynamicHTML();
            this._initDynamicArgs();
            this._initDynamic();

            this.addImageForFile(G.copy(this._uploadedList).reverse());

            // 添加图片项（如果上传方式发生了变化，则使用 uploadedList 继续添加）
            if (type === this._type) {
                // this.addImageForFile(this._uploadedList);
            } else {
                // 添加之前处理过的图片：成功的
                // this.addImageForFile(this._succUploadImageFileList);
                // 添加之前处理过的图片：失败的
                // this.addImageForFile(this._failedUploadImageFileList);
                // 添加之前处理过的图片：取消的
                // this.addImageForFile(this._cancelUploadImageFileList);
                // 添加待处理的图片
                // this.addImageForFile(this._uploadedList);
            }
        } ,

        // 图片上传表单变化事件
        _uploadInputChangeEvent: function(event){
            var tar		= G(event.currentTarget);
            var files	= tar.files();

            // 过滤掉不支持的文件
            files = this.filter(files);
            this._initDoms(files);
        } ,

        // 检查是否有图片
        empty: function(){
            // if (this._imageFileList.length === 0) {
            if (this._uploadedList.length === 0) {
                return true;
            }

            return false;
        } ,

        // 通过 identifier 找到对应的元素
        findImageItem: function(context , identifier){
            var imageItemList = G('.image-item' , context);
            var i			= 0;
            var cur			= null;

            for (; i < imageItemList.length; ++i)
            {
                cur = imageItemList.jump(i , true);

                if (cur.data('identifier') === identifier) {
                    return cur.get(0);
                }
            }

            throw new Error('找不到标识符对应的DOM元素');
        } ,

        // 设置项
        setOpt: function(opt){
            if (G.type(opt) !== 'Object') {
                throw new Error('参数 1 类型错误');
            }

            // this.opt = opt;
            var key = null;

            for (key in opt)
            {
                this['_' + key] = opt[key];
            }
        } ,

        // 判断上传是否已经结束
        checkUploadOver: function(){
            var count = this._tempSuccUploadImageFileList.length + this._tempFailedUploadImageFileList.length + this._tempCancelUploadImageFileList.length;

            // console.log(this._tempSuccUploadImageFileList.length , this._tempFailedUploadImageFileList.length , this._tempCancelUploadImageFileList.length , this._uploadedList.length);

            if (count === this._uploadedList.length) {
                return true;
            }

            return false;
        } ,

        // 调用回调函数
        callback: function(type , msg){
            if (this.checkUploadOver()) {
                // 保留此次上传失败的图片
                // 用于再次上传的时候
                this._prevFailedUploadImageFileList = this._tempFailedUploadImageFileList;

                this.resetUploadedList();

                if (G.type(this._callback) === 'Function') {
                    this._callback();
                } else {
                    if (type == 'error') {
                        console.error(msg);
                    } else {
                        console.log(msg);
                    }
                }
            }
        } ,

        // 重置待上传列表（准备下一次上传）
        resetUploadedList: function(){
            // 检查图片是上传成功了还是失败了
            this._uploadedList 					= [];
            this._tempSuccUploadImageFileList 	= [];
            this._tempFailedUploadImageFileList 	= [];

            this._selectedCount.addClass('hide');
        } ,

        // 显示上传状态
        showStatus: function(msg , type){
            msg = G(msg);
            var typeRange 	= ['success' , 'failed' , 'cancel'];
            var msgIn 		= G('.msg-in' , msg.get(0));
            if (!G.contain(type , typeRange)) {
                throw new Error('参数 2 错误');
            }
            var explain = this.uploadStatusExplain(type);
            msgIn.text(explain);
            msg.removeClass('hide');
            msg.animate({
                opacity: 1
            });

        } ,

        // 是否上传过
        isUploaded: function(file){
            var i 	= 0;
            var cur = null;

            // 上传成功的记录中查找
            for (i = 0; i < this._succUploadImageFileList.length; ++i)
            {
                cur = this._succUploadImageFileList[i];

                if (cur === file) {
                    return 'success';
                }
            }

            // 上传失败的记录中查找
            for (i = 0; i < this._failedUploadImageFileList.length; ++i)
            {
                cur = this._tempFailedUploadImageFileList[i];

                if (cur === file) {
                    return 'failed';
                }
            }

            // 取消上传的记录中查找
            for (i = 0; i < this._tempCancelUploadImageFileList.length; ++i)
            {
                cur = this._tempCancelUploadImageFileList[i];

                if (cur === file) {
                    return 'cancel';
                }
            }

            return false;

        } ,

        // 记录上传日志
        log: function(file , status){
            var statusRange = ['success' , 'failed' , 'cancel'];

            if (!G.contain(status , statusRange)) {
                throw new Error('参数 2 错误');
            }

            if (status === 'failed') {
                // 完整记录上传失败的图片
                this._failedUploadImageFileList.push(file);
                // 记录单次上传失败的图片（用于确定是否全部上传完毕，不论成功 或 失败）
                this._tempFailedUploadImageFileList.push(file);
            } else if (status === 'cancel') {
                // 完整记录取消上传的图片
                this._cancelUploadImageFileList.push(file);
                // 记录单次取消上传的图片（用于确定是否全部上传完毕，不论成功 或 失败）
                this._tempCancelUploadImageFileList.push(file);
            } else {
                // 完整记录上传成功的图片
                this._succUploadImageFileList.push(file);
                // 记录单次取消上传的图片（用于确定是否全部上传完毕，不论成功 或 失败）
                this._tempSuccUploadImageFileList.push(file);
            }
        } ,

        // 并行上传图片
        parallelUploadImages: function(){
            var self	= this;
            if (this._uploadedList.length === 0) {
                if (this._prevFailedUploadImageFileList.length === 0) {
                    console.log('无待上传图片');
                    return ;
                }

                this._previewImages.html('');
                // 如果失败图片要重新上传，需要清空列表重新添加
                this._initDoms(this._prevFailedUploadImageFileList.reverse());
                // 清空
                this._prevFailedUploadImageFileList = [];
                // 重新上传
                this.upload();
                return ;
            }

            var i = this._uploadedList.length - 1;

            var uploadEvent = function(){
                var imageFile		= self._uploadedList[i];
                var identifier  = self.findFromImageFileList(imageFile)['identifier'];
                var imageItem		= null;
                var msg			= null;
                var msgIn		= null;
                var progress	= null;
                var pTotal		= null;
                var pCur		= null;
                var index		= i;
                var formData = G.formData(self._field	, imageFile);
                imageItem		= self.findImageItem(self._previewImages.get(0) , identifier);
                progress	= G('.progress' , imageItem);
                pTotal		= G('.p-total'	, progress.get(0));
                pCur		= G('.p-cur'	, pTotal.get(0));
                msg			= G('.msg' 		, imageItem);

                var failedHandle = function(){
                    // 记录上传日志
                    self.log(imageFile , 'failed');

                    // 把图片标识下上传状态
                    self.showStatus(msg.get(0) , 'failed');

                    if (G.type(self._error) === 'Function') {
                        self._error.apply(this , arguments);
                    }

                    // 全部上传成功时调用的回调函数
                    self.callback('error' , '图片序号 ' + index + '上传发生错误!');
                };

                var succHandle = function(){
                    // 记录上传日志
                    self.log(imageFile , 'success');

                    // 把图片标识下上传状态
                    self.showStatus(msg.get(0) , 'success');

                    // 调用
                    if (G.type(self._success) === 'Function') {
                        self._success.apply(this , arguments);
                    }

                    // 全部上传成功时调用的回调函数
                    self.callback('success' , '图片序号 ' + self._uploadedList.length + '上传成功!');
                };

                //
                // console.log(formData);

                G.ajax({
                    url: self._url ,
                    method: 'post' ,
                    data: formData ,
                    success: succHandle ,
                    // 上传开始
                    uLoadstart: function(){
                        progress.removeClass('hide');

                        if (G.type(self._uLoadStart) === 'Function') {
                            self._uLoadStart();
                        }
                    } ,
                    // 上传进行中
                    uProgress: function(event){
                        if (event.lengthComputable) {
                            var total	= event.total;
                            var loaded	= event.loaded;
                            var ratio	= loaded / total;
                            ratio   = Math.min(1 , Math.max(ratio , 0));
                            ratio   *= 100;

                            // 设置比例
                            pCur.css({
                                width: ratio + '%'
                            });
                        } else {
                            // console.log('当前上传文件大小未知');
                        }

                        if (G.type(self._uProgress) === 'Function') {
                            self._uProgress();
                        }
                    } ,
                    // 上传结束
                    uLoadend: function(){
                        if (G.type(self._uLoadEnd) === 'Function') {
                            self._uLoadEnd();
                        }
                    } ,
                    // 一旦发生错误立即终止传输
                    erorr: failedHandle ,
                    netError: failedHandle ,
                    uError: failedHandle ,
                    timeout: failedHandle ,
                });

                i--;

                if (i >= 0) {
                    uploadEvent();
                }
            };

            uploadEvent();
        } ,

        // 设置加载上传状态
        setUploadStatus: function(status , type){
            status.textContent = this.uploadStatusExplain(type);
        } ,

        // 串行上传图片
        serialUploadImages: function(){
            var self	= this;

            if (this._uploadedList.length === 0) {
                if (this._prevFailedUploadImageFileList.length === 0) {
                    console.log('无待上传图片');
                    return ;
                }

                this._listBody.html('');
                // 如果失败图片要重新上传，需要清空列表重新添加
                this._initDoms(this._prevFailedUploadImageFileList.reverse());
                // 清空
                this._prevFailedUploadImageFileList = [];
                // 重新上传
                this.upload();
                return ;
            }

            this._uploadedList.reverse();
            var i = this._uploadedList.length - 1;
            var curUploadIndex = 1;

            // 错误处理
            var consume = function(){
                i--;

                if (i >= 0) {
                    uploadEvent();
                }
            };

            // 禁止删除


            this._uploadTitle.html(this.titleTemplate('ing'));

            var curUpload 	= G('.cur' 	 , this._uploadTitle.get(0));
            var totalUpload = G('.total' , this._uploadTitle.get(0));

            totalUpload.text(this._uploadedList.length);

            var uploadSTime = 0;
            var uploadETime = 0;

            var uploadEvent = function() {
                var imageFile = self._uploadedList[i];
                var formData = G.formData(self._field, imageFile);
                var identifier = self.findFromImageFileList(imageFile)['identifier'];
                var index = i;
                var imageItem = G(self.findImageItem(self._listBody.get(0), identifier));
                var msg     = G('.msg' , imageItem.get(0));
                var pCur	= G('.cur-progress' , imageItem.get(0));
                var speed   = G('.div-speed' , imageItem.get(0));
                var status  = G('.div-status' , imageItem.get(0));
                var cancelBtn = G('.cancel' , imageItem.get(0));
                var deleteBtn = G('.delete' , imageItem.get(0));

                // 错误处理
                var failedHandle = function(){
                    // 记录上传日志
                    self.log(imageFile , 'failed');

                    // 标识当前记录上传状态
                    self.setUploadStatus(status.get(0) , 'failed');

                    // 把图片标识下上传状态
                    self.showStatus(msg.get(0) , 'failed');

                    if (G.type(self._error) === 'Function') {
                        self._error.apply(this , arguments);
                    }

                    // 全部上传成功时调用的回调函数
                    self.callback('error' , '图片序号 ' + index + '上传失败!');

                    // 不论成功失败，继续消费队列
                    consume();
                };

                // succHandle
                var succHandle = function(){
                    // 记录上传日志
                    self.log(imageFile , 'success');

                    // 标识当前记录上传状态
                    self.setUploadStatus(status.get(0) , 'success');

                    // 把图片标识下上传状态
                    self.showStatus(msg.get(0) , 'success');

                    // 调用
                    if (G.type(self._success) === 'Function') {
                        self._success.apply(this , arguments);
                    }

                    // 全部上传成功时调用的回调函数
                    self.callback('success' , '图片序号 ' + self._uploadedList.length + '上传成功!');

                    // 不论成功失败，继续消费队列
                    consume();
                };


                curUpload.text(curUploadIndex++);
                status.text('上传中...');

                cancelBtn.removeClass('hide');
                deleteBtn.addClass('hide');

                cancelBtn.on('click' , function(){
                    ajax.get().abort();
                } , true , false);

                var ajax = G.ajax({
                    url: self._url ,
                    method: 'post' ,
                    data: formData ,
                    success: succHandle ,
                    // 上传开始
                    uLoadstart: function(){
                        // 标记开始时间
                        uploadSTime = new Date().getTime();

                        imageItem.addClass('focus-line');

                        if (G.type(self._uLoadStart) === 'Function') {
                            self._uLoadStart();
                        }
                    } ,
                    // 上传进行中
                    uProgress: function(event){
                        var uploadETime = new Date().getTime();

                        if (event.lengthComputable) {
                            var total	= event.total;
                            var loaded	= event.loaded;
                            var ratio	= loaded / total;
                            ratio   = Math.min(1 , Math.max(ratio , 0));
                            ratio   *= 100;

                            // 设置上传进度比例
                            pCur.css({
                                width: ratio + '%'
                            });

                            // 单位换算：ms => s
                            var duration = (uploadETime - uploadSTime) / 1000;

                            // 设置上传速度
                            var uploadSpeed 		= event.loaded / duration;
                            var uploadSpeedExplain 	= G.getStorage(uploadSpeed , 'b') + '/s';

                            speed.text(uploadSpeedExplain);
                        } else {
                            // console.log('当前上传文件大小未知');
                        }

                        if (G.type(self._uProgress) === 'Function') {
                            self._uProgress();
                        }
                    } ,
                    // 上传结束
                    uLoadend: function(){
                        imageItem.removeClass('focus-line');

                        if (G.type(self._uLoadEnd) === 'Function') {
                            self._uLoadEnd();
                        }
                    } ,
                    // 一旦发生错误立即终止传输
                    erorr: failedHandle ,
                    netError: failedHandle ,
                    uError: failedHandle ,
                    timeout: failedHandle ,
                    abort: failedHandle
                });
            };

            uploadEvent();

        } ,

        // 图片上传（根据类型智能选择上传方式）
        upload: function(){
            if (G.isFunction(this._before)) {
                this._before.call(this);
            }
            if (this._type === 'parallel') {
                this.parallelUploadImages();
            } else {
                this.serialUploadImages();
            }
        } ,

        // 清空并行上传添加的内容
        parallelClearSelected: function(){
            this.hideParallel();

            this._previewImages.html('');
            this._selectedCount.addClass('hide');
            this._clearSelected.removeClass('clear-selected-hover');
        } ,

        // 清空串行上传添加的内容
        serialClearSelected: function(){
            this.hideSerial();

            this._uploadTitle.html('待上传列表');
            this._listBody.html('');
            this._selectedCount.addClass('hide');
            this._clearSelected.removeClass('clear-selected-hover');
        } ,


        _clearSelectedEvent:function(){
            // 完整记录
            this._imageFileList 	= [];
            this._succUploadImageFileList = [];
            this._failedUploadImageFileList = [];
            this._cancelUploadImageFileList = [];

            // 临时记录
            this._uploadedList 	= [];
            this._tempCancelUploadImageFileList = [];
            this._tempFailedUploadImageFileList = [];
            this._tempSuccUploadImageFileList = [];

            if (this._type === 'parallel') {
                this.parallelClearSelected();
            } else {
                this.serialClearSelected();
            }
        } ,

        // 定义图片相关事件
        _defineEvent: function(){
            this._uploadInput.on('change' , this._uploadInputChangeEvent.bind(this) , false , false);
            this._clearSelected.on('click' , this._clearSelectedEvent.bind(this) , false , false);
        } ,

        // 初始化展示图片（不允许删除）
        showInitImages: function(){
            this.addImageForSrc(this._initImageList);
            this._imageFileList = [];
            this.afterAddSetRelativeArgs();
        } ,

        // 第一种模板风格
        templateOne: function(assign){
            var html = '';
            html += "			<div class='img'><img src='" + assign['src'] + "' class='image' /></div>	";
            html += "			<div class='close'><img src='" + this._pluginUrl + "image/delete_unfocus.png' data-focus='" + this._pluginUrl + "image/delete_focus.png' data-unfocus='" + this._pluginUrl + "image/delete_unfocus.png' class='image' /></div>	";
            html += "			<div class='progress hide'>	";
            html += "				<div class='p-total'>	";
            html += "					<div class='p-cur'></div>	";
            html += "				</div>	";
            html += "			</div>	";
            html += ' 			<div class="msg hide">	';
            html += '				<div class="msg-in">...</div> ';
            html += '			</div> ';

            return html;
        } ,

        // 第二种模板风格
        templateTwo: function(assign){
            var html = '';
            html += '	<div class="line-in">	';
            html += '	    <!-- 上传进度 -->	';
            html += '	    <div class="cur-progress"></div>	';
            html += '	    <!-- 状态 -->	';
            html += '	    <div class="msg hide">	';
            html += '	    	<div class="msg-in">...</div>	';
            html += '	    </div>	';
            html += '		<div class="item div-preview multiple-rows">	';
            html += '	    	<div class="row">' + assign['name'] + '</div>	';
            html += '			<div class="row"><img src="' + assign['src'] + '" class="image" /></div>	';
            html += '	    </div>	';
            html += '	    <div class="item div-type">' + assign['type'] +'</div>	';
            html += '	    <div class="item div-size">' + assign['sizeExplain'] + '</div>	';
            html += '		<div class="item div-speed">' + assign['speed'] + '</div>	';
            html += '		<div class="item div-status">' + assign['statusExplain'] + '</div>	';
            html += '	    <div class="item div-opr multiple-rows">	';
            html += '	    	<div class="row"><button type="button" class="run-button run-button-orange hide">取消</button></div>	';
            html += '	    	<div class="row"><button type="button" class="run-button run-button-orange delete">删除</button></div>	';
            html += '	    </div>	';
            html += '	</div>';

            return html;
        } ,

        // 获取标题模板
        titleTemplate: function(type){
            var html = '';

            if (type === 'ing') {
                html += '	正在上传（<span class="cur">...</span>/<span class="total">...</span>）	';
            } else {
                html += '待上传列表';
            }

            return html;
        } ,

        run: function(){
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
    	window.UploadImage = UploadImage;
	}

	return UploadImage;
});