# 支付宝关闭订单

|  方法名  |         参数         |    返回值     |
|:-----:|:------------------:|:----------:|
| close |    array $order    | Collection |

:::warning 注意
更多功能请查看源码，文档里仅仅列举了一些常用的功能。
:::

## 关闭订单操作

```php
Pay::config($this->config);

$result = Pay::alipay()->close([
    'out_trade_no' => '1623161325',
    // '_action' => 'web', // 默认值，关闭网页订单
    // '_action' => 'mini', // 关闭小程序订单
    // '_action' => 'scan', // 关闭扫码订单
    // '_action' => 'pos', // 关闭刷卡订单
    // '_action' => 'h5', // 关闭 H5 订单
]);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [网页订单](https://opendocs.alipay.com/open/8dc9ebb3_alipay.trade.close?pathHash=0c042d2b&ref=api&scene=common)
- [小程序订单](https://opendocs.alipay.com/mini/05xhst?pathHash=f30ab879&ref=api&scene=common)
- [扫码订单](https://opendocs.alipay.com/open/02o6e7?pathHash=7a011fc5&ref=api&scene=common)
- [刷卡订单](https://opendocs.alipay.com/open/e84f0d79_alipay.trade.close?pathHash=b25c3fc7&ref=api&scene=common)
- [H5 订单](https://opendocs.alipay.com/open/a62e8677_alipay.trade.close?pathHash=0801e763&ref=api&scene=common)
