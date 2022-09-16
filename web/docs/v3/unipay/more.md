# 银联更多方便的插件

得益于 yansongda/pay 的基础架构和良好的插件机制，
您可以自有的使用任何内置插件和自定义插件调用支付宝的任何 API。

诸如签名、API调用、解密、验签、解包等基础插件已经内置在 Pay 中，
您可以使用 `Pay::unipay()->mergeCommonPlugins(array $plugins)` 来获取调用 API 所必须的常用插件

首先，查找你想使用的插件，然后

```php
Pay::config($config);

$params = [
    'out_trade_no' => '1514027114',
];

$allPlugins = Pay::unipay()->mergeCommonPlugins([QueryPlugin::class]);

$result = Pay::unipay()->pay($allPlugins, $params);
```

关于插件的详细介绍，如果您感兴趣，可以参考 [这篇说明文档](/docs/v3/kernel/plugin.md)
