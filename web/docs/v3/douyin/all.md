# 抖音更多方便的插件

得益于 yansongda/pay 的基础架构和良好的插件机制，
您可以自由的使用任何内置插件和自定义插件调用微信的任何 API。

诸如签名、API调用、解密、验签、解包等基础插件已经内置在 Pay 中，
您可以使用 `Pay::douyin()->mergeCommonPlugins(array $plugins)` 来获取调用 API 所必须的常用插件

首先，查找你想使用的插件，然后

```php
Pay::config($config);

$params = [
    'out_trade_no' => '202408040747147327',
];

$allPlugins = Pay::douyin()->mergeCommonPlugins([QueryPlugin::class]);

$result = Pay::douyin()->pay($allPlugins, $params);
```

关于插件的详细介绍，如果您感兴趣，可以参考 [yansongda/artful](https://artful.yansongda.cn/)

## 支付产品

### 小程序支付

- 小程序下单

  `\Yansongda\Pay\Plugin\Douyin\V1\Pay\Mini\PayPlugin`

- 商户订单号查询订单

  `\Yansongda\Pay\Plugin\Douyin\V1\Pay\Mini\QueryPlugin`

- 退款申请

  `\Yansongda\Pay\Plugin\Douyin\V1\Pay\Mini\RefundPlugin`

- 查询单笔退款（通过商户退款单号）

  `\Yansongda\Pay\Plugin\Douyin\V1\Pay\Mini\QueryRefundPlugin`
