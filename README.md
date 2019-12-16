# rtc

[API 接口地址](https://apizza.net/pro/#/project/60bdbb56abdf4a8cd847183801dcbef1/browse)

## 数据结构

#### 私聊 发送方提供的数据结构

```
[
    // 路由
    'router'    => 'user/test' ,
    // 项目标识符
    'identifier' => 'abcd' ,
    // 除非 action = test，否则都需要携带 token
    'token'     => '123456789' ,
    // 登录的客户端
    'platform'  => 'pc' ,
    // 客户端生成的每次请求的标识符
    'request'  => '123' ,
    // 用户自定义传输的数据（也是要符合一定格式的数据）
    'data' => [
        // 消息接收方 id
        'user_id' => 1 , 
        // 类型
        'type' => 'text' ,
        // 消息内容
        'message' => '你好啊' ,
        // 额外内容
        'extra' => '' ,
    ] ,
];
```

#### 群聊 发送方提供的数据结构

```
[
    // 路由
    'router'    => 'user/test' ,
    // 项目标识符
    'identifier' => 'abcd' ,
    // 除非 action = test，否则都需要携带 token
    'token'     => '123456789' ,
    // 登录的客户端
    'platform'  => 'pc' ,
    // 客户端生成的每次请求的标识符
    'request'  => '123' ,
    // 用户自定义传输的数据（也是要符合一定格式的数据）
    'data' => [
        // 接收方（群聊）
        'group_id' => 1 ,
        // 类型
        'type' => 'text' ,
        // 消息内容
        'message' => '你好啊' ,
        // 额外内容
        'extra' => '' ,
    ] ,
];
```

# 私聊/群聊 发送方接收的响应数据

```
[
    // 类型
    'type'  => 'response' ,
    // 项目标识符
    'code' => 'abcd' ,
    // 用户自定义传输的数据（也是要符合一定格式的数据）
    'data' => [] , 
    // 客户端生成的每次请求的标识符
    'request'  => '123' ,
];
```

# 接收到的推送数据格式

```
[
    // 类型
    'type'  => '推送类型' ,
    'data' => [] , 
];
```

### 群聊格式要求

```
[
    // 群聊消息
    'type'  => 'group_message' ,
    'data'  => [
        // 群
        'group' => [] ,
        // 发送者
        'user' => [] ,
        // 发送的消息
        'message' => [] ,
    ]
]
```

### 私聊格式要求

```

```