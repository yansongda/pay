# Stripe 更多方便的插件

:::warning
Stripe 的实现由 AI 生成并经人工审核，但由于测试资源限制，尚未经过实际测试，请在充分验证后再用于生产环境。
:::

得益于 yansongda/pay 的基础架构和良好的插件机制，
您可以自由的使用任何内置插件和自定义插件调用 Stripe 的任何 API。

首先，查找你想使用的插件，然后

```php
Pay::config($config);

$params = [
    'payment_intent_id' => 'pi_3OxxxxxxxxxxxxxxxxxxxxY',
];

$allPlugins = [StartPlugin::class, QueryPlugin::class, AddRadarPlugin::class, ResponsePlugin::class, ParserPlugin::class];

$result = Pay::stripe()->pay($allPlugins, $params);
```

关于插件的详细介绍，如果您感兴趣，可以参考 [yansongda/artful](https://artful.yansongda.cn/)

## 支付产品

### PaymentIntent 支付

- 创建 PaymentIntent

  `\Yansongda\Pay\Plugin\Stripe\V1\Pay\PayPlugin`

- 查询 PaymentIntent

  `\Yansongda\Pay\Plugin\Stripe\V1\Pay\QueryPlugin`

- 取消 PaymentIntent

  `\Yansongda\Pay\Plugin\Stripe\V1\Pay\CancelPlugin`

- 退款申请

  `\Yansongda\Pay\Plugin\Stripe\V1\Pay\RefundPlugin`

- 查询退款

  `\Yansongda\Pay\Plugin\Stripe\V1\Pay\QueryRefundPlugin`

### Checkout Session 支付

- 创建 Checkout Session

  `\Yansongda\Pay\Plugin\Stripe\V1\Pay\WebPlugin`
