# 支付宝关闭订单

|  方法名  |         参数         |    返回值     |
|:-----:|:------------------:|:----------:|
| close |    array $order    | Collection |

:::warning 注意
更多功能请查看源码，文档里仅仅列举了一些常用的功能。
:::

## 关闭订单操作

:::tip
Alipay V3 关闭订单统一走 `POST /v3/alipay/trade/close`。
:::

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

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open-v3/02s3kq)，查看「请求参数」一栏。
