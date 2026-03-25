# 支付宝取消订单

|  方法名   |         参数         |    返回值     |
|:------:|:------------------:|:----------:|
| cancel |    array $order    | Collection |

:::warning 注意
更多功能请查看源码，文档里仅仅列举了一些常用的功能。
:::

## 取消订单操作

:::tip
Alipay V3 撤销订单统一走 `POST /v3/alipay/trade/cancel`。
:::

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

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open-v3/02s3k0)，查看「请求参数」一栏。
