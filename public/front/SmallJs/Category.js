var c = {
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