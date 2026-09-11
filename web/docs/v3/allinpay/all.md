# 通联支付更多方便的插件

得益于 yansongda/pay 的基础架构和良好的插件机制，
您可以自由的使用任何内置插件和自定义插件调用通联支付的任何 API。

首先，查找你想使用的插件，然后

```php
Pay::config($config);

$params = [
    'reqsn' => 'order-1001',
];

$allPlugins = [
    \Yansongda\Pay\Plugin\Allinpay\StartPlugin::class,
    \Yansongda\Pay\Plugin\Allinpay\Tranx\QueryPlugin::class,
    \Yansongda\Pay\Plugin\Allinpay\AddPayloadSignPlugin::class,
    \Yansongda\Pay\Plugin\Allinpay\AddRadarPlugin::class,
    \Yansongda\Pay\Plugin\Allinpay\VerifySignaturePlugin::class,
    \Yansongda\Pay\Plugin\Allinpay\ResponsePlugin::class,
    \Yansongda\Artful\Plugin\ParserPlugin::class,
];

$result = Pay::allinpay()->pay($allPlugins, $params);
```

关于插件的详细介绍，如果您感兴趣，可以参考 [yansongda/artful](https://artful.yansongda.cn/)

## 支付

- 统一支付（/unitorder/pay）

  `\Yansongda\Pay\Plugin\Allinpay\Pay\PayPlugin`

- 被扫支付（/unitorder/scanqrpay）

  `\Yansongda\Pay\Plugin\Allinpay\Pay\ScanPlugin`

- 主扫支付（/unitorder/nativepay）

  `\Yansongda\Pay\Plugin\Allinpay\Pay\NativePlugin`

## 交易

- 交易查询（/tranx/query）

  `\Yansongda\Pay\Plugin\Allinpay\Tranx\QueryPlugin`

- 交易确认查询（/tranx/queryconfirm）

  `\Yansongda\Pay\Plugin\Allinpay\Tranx\QueryConfirmPlugin`

- 退款（/tranx/refund）

  `\Yansongda\Pay\Plugin\Allinpay\Tranx\RefundPlugin`

- 撤销（/tranx/cancel）

  `\Yansongda\Pay\Plugin\Allinpay\Tranx\CancelPlugin`

- 交易关单（/tranx/close）

  `\Yansongda\Pay\Plugin\Allinpay\Tranx\ClosePlugin`

- 主扫关单（/unitorder/closenative）

  `\Yansongda\Pay\Plugin\Allinpay\UnitOrder\NativeClosePlugin`

## 通用

- 组装公共参数（cusid/appid/orgid/signtype/randomstr）

  `\Yansongda\Pay\Plugin\Allinpay\StartPlugin`

- RSA-SHA1 请求签名

  `\Yansongda\Pay\Plugin\Allinpay\AddPayloadSignPlugin`

- 构造 form 请求

  `\Yansongda\Pay\Plugin\Allinpay\AddRadarPlugin`

- 响应验签

  `\Yansongda\Pay\Plugin\Allinpay\VerifySignaturePlugin`

- 响应业务码校验（retcode）

  `\Yansongda\Pay\Plugin\Allinpay\ResponsePlugin`

- 回调处理与验签

  `\Yansongda\Pay\Plugin\Allinpay\CallbackPlugin`
