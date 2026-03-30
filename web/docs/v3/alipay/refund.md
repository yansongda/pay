# 支付宝退款

|  方法名   |      参数      |    返回值     |
|:------:|:------------:|:----------:|
| refund | array $order | Collection |

:::warning 注意
更多功能请查看源码，文档里仅仅列举了一些常用的功能。
:::

## 普通退款操作

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

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [网页订单](https://opendocs.alipay.com/open/f60979b3_alipay.trade.refund?pathHash=e4c921a7&ref=api&scene=common)
- [APP 订单](https://opendocs.alipay.com/open/6c0cdd7d_alipay.trade.refund?pathHash=4081e89c&ref=api&scene=common)
- [小程序订单](https://opendocs.alipay.com/mini/05xskz?pathHash=b18b975d&ref=api&scene=common)
- [刷卡订单](https://opendocs.alipay.com/open/3aea9b48_alipay.trade.refund?pathHash=04122275&ref=api&scene=common)
- [扫码订单](https://opendocs.alipay.com/open/02ekfk?pathHash=b45b14f7&ref=api&scene=common)
- [H5 订单](https://opendocs.alipay.com/open/4b7cc5db_alipay.trade.refund?pathHash=d98b006d&ref=api&scene=common)
