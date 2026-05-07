# Airwallex 更多方便的插件

> 官方文档：
> [API Introduction](https://www.airwallex.com/docs/api/introduction)
> [Using PaymentIntents API](https://www.airwallex.com/docs/api/payments/payment_intents/create)
> [Listen for webhook events](https://www.airwallex.com/docs/developer-tools/webhooks/listen-for-webhook-events)


得益于 `yansongda/pay` 的基础架构和插件机制，
你可以自由组合内置插件或自定义插件来调用 Airwallex API。

如果你需要自行拼装调用链，可以使用：

```php
$allPlugins = Pay::airwallex()->mergeCommonPlugins([
    \Yansongda\Pay\Plugin\Airwallex\V1\Pay\QueryPlugin::class,
]);

$result = Pay::airwallex()->pay($allPlugins, [
    'payment_intent_id' => 'int_xxx',
]);
```

## 已内置插件

### 基础插件

- `\Yansongda\Pay\Plugin\Airwallex\V1\GetAccessTokenPlugin`
- `\Yansongda\Pay\Plugin\Airwallex\V1\ObtainAccessTokenPlugin`
- `\Yansongda\Pay\Plugin\Airwallex\V1\AddRadarPlugin`
- `\Yansongda\Pay\Plugin\Airwallex\V1\ResponsePlugin`
- `\Yansongda\Pay\Plugin\Airwallex\V1\VerifyWebhookSignPlugin`

### 支付产品

- 创建 Payment Intent
  `\Yansongda\Pay\Plugin\Airwallex\V1\Pay\PayPlugin`

- 查询 Payment Intent
  `\Yansongda\Pay\Plugin\Airwallex\V1\Pay\QueryPlugin`

- 查询退款
  `\Yansongda\Pay\Plugin\Airwallex\V1\Pay\QueryRefundPlugin`

- 取消 Payment Intent
  `\Yansongda\Pay\Plugin\Airwallex\V1\Pay\CancelPlugin`

- 发起退款
  `\Yansongda\Pay\Plugin\Airwallex\V1\Pay\RefundPlugin`

- 接收回调
  `\Yansongda\Pay\Plugin\Airwallex\V1\Pay\CallbackPlugin`
