# 支付宝退款

|  方法名   |      参数      |    返回值     |
|:------:|:------------:|:----------:|
| refund | array $order | Collection |

:::warning 注意
更多功能请查看源码，文档里仅仅列举了一些常用的功能。
:::

## 普通退款操作

:::tip
Alipay V3 的退款与退款查询已按官方 open-v3 REST API 实现，分别走 `POST /v3/alipay/trade/refund` 与 `POST /v3/alipay/trade/fastpay/refund/query`。

`_action` 仍可用于兼容不同支付场景，但在 V3 下退款请求会统一走同一个 REST 端点。
:::

```php
Pay::config($this->config);

$result = Pay::alipay()->refund([
    'out_trade_no' => '1623160012',
    'refund_amount' => '0.01',
    // '_action' => 'web', // 默认值，退款网页订单
    // '_action' => 'app', // 退款 APP 订单
    // '_action' => 'mini', // 退款小程序订单
    // '_action' => 'pos', // 退款刷卡订单
    // '_action' => 'scan', // 退款扫码订单
    // '_action' => 'h5', // 退款 H5 订单
]);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open-v3/05xskz)，查看「请求参数」一栏。
