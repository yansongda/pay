# PayPal 更多方便的插件

得益于 yansongda/pay 的基础架构和良好的插件机制，
您可以自由的使用任何内置插件和自定义插件调用 PayPal 的任何 API。

诸如获取令牌、API调用、验签、解包等基础插件已经内置在 Pay 中，
您可以使用 `Pay::paypal()->mergeCommonPlugins(array $plugins)` 来获取调用 API 所必须的常用插件

首先，查找你想使用的插件，然后

```php
Pay::config($config);

$params = [
    'order_id' => '5O190127TN364715T',
];

$allPlugins = Pay::paypal()->mergeCommonPlugins([QueryPlugin::class]);

$result = Pay::paypal()->pay($allPlugins, $params);
```

关于插件的详细介绍，如果您感兴趣，可以参考 [yansongda/artful](https://artful.yansongda.cn/)

## 支付产品

### Web 支付

- 创建订单

  `\Yansongda\Pay\Plugin\Paypal\V1\Pay\PayPlugin`

- 捕获订单（完成扣款）

  `\Yansongda\Pay\Plugin\Paypal\V1\Pay\CapturePlugin`

- 查询订单

  `\Yansongda\Pay\Plugin\Paypal\V1\Pay\QueryPlugin`

- 退款申请

  `\Yansongda\Pay\Plugin\Paypal\V1\Pay\RefundPlugin`

- 查询退款

  `\Yansongda\Pay\Plugin\Paypal\V1\Pay\QueryRefundPlugin`
