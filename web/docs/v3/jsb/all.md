# 江苏银行e融支付更多方便的插件

得益于 yansongda/pay 的基础架构和良好的插件机制，
您可以自有的使用任何内置插件和自定义插件调用支付宝的任何 API。

诸如签名、API调用、解密、验签、解包等基础插件已经内置在 Pay 中，
您可以使用 `Pay::jsb()->mergeCommonPlugins(array $plugins)` 来获取调用 API 所必须的常用插件

首先，查找你想使用的插件，然后

```php
Pay::config($config);

$params = [
    'outTradeNo' => '1514027114',
];

$allPlugins = Pay::epay()->mergeCommonPlugins([QueryPlugin::class]);

$result = Pay::epay()->pay($allPlugins, $params);
```

关于插件的详细介绍，如果您感兴趣，可以参考 [yansongda/artful](https://artful.yansongda.cn/)

## 支付

### 扫码支付(聚合支付, 支持支付宝、微信、银联、江苏银行e融支付)

- 交易预创建

  `\Yansongda\Pay\Plugin\Jsb\Pay\Scan\PayPlugin`

- 交易退款接口

  `\Yansongda\Pay\Plugin\Jsb\Pay\Scan\RefundPlugin`

- 交易/退款结果查询

  `\Yansongda\Pay\Plugin\Jsb\Pay\Scan\QueryPlugin`
