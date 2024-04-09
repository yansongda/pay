# 核心思想

`yansongda/pay` 的底层设计思想，是基于 `yansongda/artful` 的，所以，
如果你想要更好的理解 `yansongda/pay`，那么你需要先了解 `yansongda/artful`。

建议先阅读 `yansongda/artful` 的文档，再来阅读 `yansongda/pay` 的文档。

`yansongda/artful` 的文档地址：[https://artful.yansongda.cn](https://artful.yansongda.cn)

下面，我们主要介绍 `yansongda/pay` 的一些不同点。

## 插件

### 通用插件

Pay 内部已经集成了很多通用插件，如 加密，签名，调用支付宝/微信接口等。

只需要简单的使用以下代码即可获取通用插件

```php
$allPlugins = Pay::alipay()->mergeCommonPlugins([QueryPlugin::class]);
```

### 最终调用

在拿到所有的插件之后，就可以愉快的进行调用获取最后的数据了。

```php
$result = Pay::alipay()->pay($allPlugins, $params);
```

代码中的 `$params` 为调用 API 所需要的其它参数。

## _参数

除了 `yansongda/artful` 中的参数外，`yansongda/pay` 还有以下特有参数：

### _content_type

指定请求的 `Content-Type`，默认为 `application/json`。

### _accept

指定请求的 `Accept`，默认为 `application/json`。

### _action

指定快捷方式的 `action`，用于区分不同的场景，比如查询订单里有查询退款、转账等订单。

例如，支付宝的 `query` 快捷方式 `action` 有 `app`，`h5`，`scan`，`transfer` 等。

### _type

目前只用于微信支付，区分不同 app_id 的类型。

例如对 小程序，公众号，APP 等不同的 app_id 指定时， `_type` 为 `mini`, `mp`, `app` 等。

一般情况下默认为 `mp`。

### _service_url

目前只用于微信支付，指定服务商模式下的请求 url。

### _serial_no

目前只用于微信支付，指定请求证书序列号。
