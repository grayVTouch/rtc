/**
 * ******************************
 * author 陈学龙 2018-05-12
 * 富文本消息格式转换器
 * ******************************
 */
(function(){
    "use strict";

    function Editor(){
        var thisRange = [window , null , undefined];

        if (G.contain(this , thisRange) || (!G.contain(this , thisRange) && this.constructor !== Editor)) {
            return new Editor();
        }

        this._run();
    }

    Editor.prototype = {
        constructor: Editor ,

        _initStaticHTML: function(){

        } ,

        _initStaticArgs: function(){
            // 文本正则：表情正则
            this._regForFaceOfText = /\!\[face\]\[([A-z0-9\-_\u4e00-\u9fa5]*)\]\(([\u4e00-\u9fa5A-z0-9\:\/\?%\-_\.#\=&, ]*)\)/g;

            // 文本正则：换行正则
            this._regForNewlineOfText = /\n/g;

            // 文本正则：链接
            this._regForLinkOfText = /\!\[link\]\(((https?\:\/\/)?\/?[\u4e00-\u9fa5A-z0-9\:\/\?%\-_\.#\=& ]*)\)/g;

            // html正则：表情正则
            this._regForFaceHtml = /<img\s*src="([\u4e00-\u9fa5A-z0-9\:\/\?%\-_\.#\=&, ]*)"\s*class="image image-for-chat-face"\s*title="([A-z0-9\-_\u4e00-\u9fa5]*)">/g;

            // html正则：换行正则
            this._regForNewlineOfHtml = /(<br\s*\/?>)/g;

            // html正则：链接
            this._regForLinkOfHtml = /<a\s*href="((https?\:\/\/)?\/?[\u4e00-\u9fa5A-z0-9\:\/\?%\-_\.#\=& ]*)"\s*class="link link-for-chat">((https?\:\/\/)?\/?[\u4e00-\u9fa5A-z0-9\:\/\?%\-_\.#\=& ]*)<\/a>/g;

            // 过滤掉所有正则
            this._regForTag = /<[^>]*>/g;
        } ,

        _initStatic: function(){

        } ,

        _initDynamicHTML: function(){

        } ,

        _initDynamicArgs: function(){

        } ,

        _initDynamic: function(){

        } ,

        // text => html
        html: function(text){
            // 表情 text 转 html
            // var html = text.replace(this._regForNewlineOfText , '<br />');
            var html = text.replace(this._regForNewlineOfText , '');
                html = html.replace(this._regForFaceOfText , '<img src="$2" class="image image-for-chat-face" title="$1">');
                html = html.replace(this._regForLinkOfText , '<a href="$1" class="link link-for-chat">$1</a>');

            return html;
        } ,

        // html => text
        text: function(html){
            // var text = html.replace(this._regForNewlineOfHtml , "\n");
            var text = html;
                text = text.replace(this._regForFaceHtml , '![face][$2]($1)');
                text = text.replace(this._regForLinkOfHtml , '![link]($1)');
                text = text.replace(this._regForTag , '');

            return text;
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
        }
    };

    // 提供冲突解决办法
    Editor.noConflict = function(apiName){
        if (apiName === 'Editor') {
            return ;
        }

        window[apiName] = Editor;
        window.Editor = window.__editor__;
    };

    // Socket
    if (G.getValType(Editor) !== 'Undefined') {
        window.__editor__ = window.Editor;
    }

    window.Editor = Editor;
})();