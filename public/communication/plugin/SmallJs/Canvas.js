(function(){
    "use strict";

    function Canvas(selector)
    {
        var typeRange = [window , null , undefined];
        if (G.contain(this , typeRange) || this.constructor !== Canvas) {
            return new Canvas(selector);
        }
        this.container = G(selector);
        if (!this.container.isDom()) {
            throw new Error('参数 1 错误');
        }
        this.canvas = this.container.origin('getContext' , '2d');
        this.run();
    }

    Canvas.prototype = {
        constructor: Canvas ,

        width: function(val){
            if (G.isUndefined(val)) {
                return this.container.attr('width');
            }
            this.container.attr('width' , val);
            return this;
        } ,

        height: function(val){
            if (G.isUndefined(val)) {
                return this.container.attr('height');
            }
            this.container.attr('height' , val);
            return this;
        } ,

        begin: function(){
            this.canvas.beginPath();
            return this;
        } ,

        close: function(){
            this.canvas.closePath();
            return this;
        } ,

        save: function(){
            this.canvas.save();
            return this;
        } ,

        restore: function(){
            this.canvas.restore();
            return this;
        } ,

        drawImage: function(){
            this.canvas.apply(this.canvas , arguments);
            return this;
        } ,

        translate: function(x , y){
            this.canvas.translate(x,y);
            return this;
        } ,

        rotate: function(val , type){
            var typeRange = ['angle' , 'rad'];
            type = G.isUndefined(type) ? 'rad' : type;
            if (!G.contain(type , typeRange)) {
                throw new Error('参数 2 类型错误');
            }
            if (type == 'angle') {
                val = G.getRad(val);
            }
            this.canvas.rotate(val);
            return this;
        } ,

        scale: function(x , y){
            this.canvas.scale(x , y);
            return this;
        } ,

        moveTo: function(x,y){
            this.canvas.moveTo(x,y);
            return this;
        } ,

        lineTo: function(x,y){
            this.canvas.lineTo(x,y);
            return this;
        } ,

        lineCap: function(val){
            // butt | round | square
            this.canvas.lineCap = val;
            return this;
        } ,

        lineWidth: function(val){
            this.canvas.lineWidth = val;
            return this;
        } ,

        lineJoin: function(val){
            // round | miter | bevel
            this.canvas.lineJoin = val;
            return this;
        } ,

        fillStyle: function(val){
            this.canvas.fillStyle = val;
            return this;
        } ,

        strokeStyle: function(val){
            this.canvas.strokeStyle = val;
            return this;
        } ,

        globalAlpha: function(val){
            this.canvas.globalAlpha = val;
            return this;
        } ,

        clearRect: function(x , y , w , h){
            this.canvas.clearRect(x , y , w , h);
            return this;
        } ,

        clear: function(){
            this.clearRect(0 , 0 , this.width() , this.height());
            return this;
        } ,

        fill: function(){
            this.canvas.fill();
            return this;
        } ,

        stroke: function(){
            this.canvas.stroke();
            return this;
        } ,

        rect: function(x , y , w , h){
            this.canvas.rect(x , y , w , h);
            return this;
        } ,

        fillRect: function(x , y , w , h){
            this.canvas.fillRect(x , y , w , h);
            return this;
        } ,

        arc: function(){
            this.canvas.arc.apply(this.canvas , arguments);
            return this;
        } ,


        run: function () {

        }
    };

    window.Canvas = Canvas;

    return Canvas;
})();