# 抖音更多方便的插件

得益于 yansongda/pay 的基础架构和良好的插件机制，
您可以自由的使用任何内置插件和自定义插件调用抖音的任何 API。

首先，查找你想使用的插件，然后

```php
Pay::config($config);

$params = [
    'out_order_no' => '202408040747147327',
];

$allPlugins = [StartPlugin::class, ObtainClientTokenPlugin::class, QueryPlugin::class, AddPayloadBodyPlugin::class, AddRadarPlugin::class, ResponsePlugin::class, ParserPlugin::class];

$result = Pay::douyin()->pay($allPlugins, $params);
```

关于插件的详细介绍，如果您感兴趣，可以参考 [yansongda/artful](https://artful.yansongda.cn/)

## 基础插件

### client_token

- 获取 client_token（子调用管线，校验响应并解析）

  `\Yansongda\Pay\Plugin\Douyin\V1\GetClientTokenPlugin`

- 获取 client_token 响应校验

  `\Yansongda\Pay\Plugin\Douyin\V1\GetClientTokenResponsePlugin`

- 注入 client_token（优先使用 `params['_access_token']` 外部注入，否则自动获取并缓存）

  `\Yansongda\Pay\Plugin\Douyin\V1\ObtainClientTokenPlugin`

### 请求/响应

- 构建请求（`access-token` 请求头 + JSON body，`_body` 优先）

  `\Yansongda\Pay\Plugin\Douyin\V1\AddRadarPlugin`

- 校验响应（HTTP 2xx 且顶层 `err_no === 0`）

  `\Yansongda\Pay\Plugin\Douyin\V1\ResponsePlugin`

## 支付

### 小程序支付（JSAPI 下单签名）

- 小程序下单签名（不发 HTTP 请求，产出 `data` + `byteAuthorization`）

  `\Yansongda\Pay\Plugin\Douyin\V1\Pay\SignPlugin`

- 查询订单

  `\Yansongda\Pay\Plugin\Douyin\V1\Pay\QueryPlugin`

- 查询 CPS 信息

  `\Yansongda\Pay\Plugin\Douyin\V1\Pay\QueryCpsPlugin`

- 支付结果回调（RSA 验签 + 解析，`type=payment`）

  `\Yansongda\Pay\Plugin\Douyin\V1\Pay\CallbackPlugin`

## 退款

- 创建退款（自动注入配置的 `refund_notify_url`）

  `\Yansongda\Pay\Plugin\Douyin\V1\Refund\RefundPlugin`

- 查询退款

  `\Yansongda\Pay\Plugin\Douyin\V1\Refund\QueryRefundPlugin`

- 退款审核

  `\Yansongda\Pay\Plugin\Douyin\V1\Refund\AuditPlugin`

- 退款结果回调（RSA 验签 + 解析，`type=refund`）

  `\Yansongda\Pay\Plugin\Douyin\V1\Refund\CallbackPlugin`

- 退款申请回调（RSA 验签 + 解析，`type=pre_create_refund`，同步应答由业务方构造）

  `\Yansongda\Pay\Plugin\Douyin\V1\Refund\PreRefundCallbackPlugin`
