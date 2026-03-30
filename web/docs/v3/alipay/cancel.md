# 支付宝取消订单

|  方法名   |         参数         |    返回值     |
|:------:|:------------------:|:----------:|
| cancel |    array $order    | Collection |

:::warning 注意
更多功能请查看源码，文档里仅仅列举了一些常用的功能。
:::

## 取消订单操作

```php
Pay::config($this->config);

$order = [
    'out_trade_no' => '1514027114',
    // '_action' => 'pos', // 默认值，取消刷卡订单
    // '_action' => 'mini', // 取消小程序订单
    // '_action' => 'scan', // 取消扫码订单
];

$result = Pay::alipay()->cancel($order);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [刷卡订单](https://opendocs.alipay.com/open/13399511_alipay.trade.cancel?pathHash=b0a8222c&ref=api&scene=common)
- [扫码订单](https://opendocs.alipay.com/open/02ekfi?pathHash=45b45d97&ref=api&scene=common)
- [小程序订单](https://opendocs.alipay.com/mini/05xunj?pathHash=ca2a9ea6&ref=api&scene=common)
