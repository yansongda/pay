# Apple 更多方便的插件

得益于 yansongda/pay 的基础架构和良好的插件机制，您可以自由的使用任何内置插件和自定义插件调用 Apple 的任何 API。

首先，查找你想使用的插件，然后

```php
Pay::config($config);

$params = [
    'transaction_id' => '1234567890',
    '_action' => 'history',
];

$allPlugins = [\Yansongda\Artful\Plugin\StartPlugin::class, \Yansongda\Pay\Plugin\Apple\Pay\QueryHistoryPlugin::class, \Yansongda\Pay\Plugin\Apple\AddRadarPlugin::class, \Yansongda\Pay\Plugin\Apple\ResponsePlugin::class, \Yansongda\Artful\Plugin\ParserPlugin::class];

$result = Pay::apple()->pay($allPlugins, $params);
```

关于插件的详细介绍，如果您感兴趣，可以参考 [yansongda/artful](https://artful.yansongda.cn/)

## 支付令牌验签解密

- `\Yansongda\Pay\Plugin\Apple\Pay\PayTokenPlugin`

## 网页商户验证

- `\Yansongda\Pay\Plugin\Apple\Pay\MerchantSessionPlugin`

## 查询

- `\Yansongda\Pay\Plugin\Apple\Pay\QueryPlugin`（单笔交易）
- `\Yansongda\Pay\Plugin\Apple\Pay\QueryHistoryPlugin`（交易历史）
- `\Yansongda\Pay\Plugin\Apple\Pay\QuerySubscriptionsPlugin`（订阅状态）

## 退款历史

- `\Yansongda\Pay\Plugin\Apple\Pay\RefundPlugin`

## 回调

- `\Yansongda\Pay\Plugin\Apple\Pay\CallbackPlugin`

## 通用插件

- `\Yansongda\Pay\Plugin\Apple\AddRadarPlugin`
- `\Yansongda\Pay\Plugin\Apple\ResponsePlugin`
