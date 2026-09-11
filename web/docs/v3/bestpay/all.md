# 翼支付更多方便的插件

得益于 yansongda/pay 的基础架构和良好的插件机制，
您可以自由的使用任何内置插件和自定义插件调用翼支付的任何 API。

首先，查找你想使用的插件，然后

```php
Pay::config($config);

$params = [
    'outTradeNo' => 'order-1001',
];

$allPlugins = [
    \Yansongda\Pay\Plugin\Bestpay\V1\StartPlugin::class,
    \Yansongda\Pay\Plugin\Bestpay\V1\Pay\Web\PayPlugin::class,
    \Yansongda\Pay\Plugin\Bestpay\V1\AddPayloadSignPlugin::class,
    \Yansongda\Pay\Plugin\Bestpay\V1\AddRadarPlugin::class,
    \Yansongda\Pay\Plugin\Bestpay\V1\VerifySignaturePlugin::class,
    \Yansongda\Pay\Plugin\Bestpay\V1\ResponsePlugin::class,
    \Yansongda\Artful\Plugin\ParserPlugin::class,
];

$result = Pay::bestpay()->pay($allPlugins, $params);
```

关于插件的详细介绍，如果您感兴趣，可以参考 [yansongda/artful](https://artful.yansongda.cn/)

## 支付

- PC 收银台（/pay/tradeCreate，tradeChannel=WEBCASHIER）

  `\Yansongda\Pay\Plugin\Bestpay\V1\Pay\Web\PayPlugin`

- 手机收银台（/pay/tradeCreate，tradeChannel=MOBILECASHIER）

  `\Yansongda\Pay\Plugin\Bestpay\V1\Pay\H5\PayPlugin`

- 聚合收款码（/aggregate/aggregatepay/offline/c2b/payOrder）

  `\Yansongda\Pay\Plugin\Bestpay\V1\Pay\Scan\PayPlugin`

## 查询

- 超级收银台订单查询（/integrate/orderQuery）

  `\Yansongda\Pay\Plugin\Bestpay\V1\Pay\QueryPlugin`

- 线下聚合订单查询（/aggregate/aggregatepay/tradeQuery）

  `\Yansongda\Pay\Plugin\Bestpay\V1\Pay\AggregateQueryPlugin`

## 其他

- 退款（/integrate/refund）

  `\Yansongda\Pay\Plugin\Bestpay\V1\Pay\RefundPlugin`

- 关单（/pay/closeOrder）

  `\Yansongda\Pay\Plugin\Bestpay\V1\Pay\ClosePlugin`

- 回调验签

  `\Yansongda\Pay\Plugin\Bestpay\V1\CallbackPlugin`

## 公共插件

- 签名组装（sdkRequest 信封 + 加签）

  `\Yansongda\Pay\Plugin\Bestpay\V1\AddPayloadSignPlugin`

- 构建请求

  `\Yansongda\Pay\Plugin\Bestpay\V1\AddRadarPlugin`

- 响应验签

  `\Yansongda\Pay\Plugin\Bestpay\V1\VerifySignaturePlugin`

- 响应业务校验

  `\Yansongda\Pay\Plugin\Bestpay\V1\ResponsePlugin`

- 配置注入

  `\Yansongda\Pay\Plugin\Bestpay\V1\StartPlugin`

:::tip
各插件命名空间均带 `V1`（对齐 MAPI 接口版本 `BESTPAY_MAPI_VERSION=1.0.3`），
后续接口版本变更时按 `V{n}` 组织。
:::
