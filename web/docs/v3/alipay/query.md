# 支付宝查询订单

|  方法名  |      参数      |    返回值     |
|:-----:|:------------:|:----------:|
| query | array $order | Collection |

:::warning 注意
更多功能请查看源码，文档里仅仅列举了一些常用的功能。
:::

## 查询普通支付订单

```php
Pay::config($this->config);

$order = [
    'out_trade_no' => '1514027114',
    // '_action' => 'web', // 默认值，查询普通支付网页订单
    // '_action' => 'app', // 查询 APP 订单
    // '_action' => 'mini', // 查询小程序订单
    // '_action' => 'pos', // 查询刷卡订单
    // '_action' => 'scan', // 查询扫码订单
    // '_action' => 'h5', // 查询 H5 订单

];

$result = Pay::alipay()->query($order);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [网页订单](https://opendocs.alipay.com/open/bff76748_alipay.trade.query?pathHash=e3ddce1d&ref=api&scene=23)
- [APP 订单](https://opendocs.alipay.com/open/82ea786a_alipay.trade.query?pathHash=0745ecea&ref=api&scene=23)
- [小程序订单](https://opendocs.alipay.com/open/6f534d7f_alipay.trade.query?pathHash=98c03720&ref=api&scene=23)
- [刷卡订单](https://opendocs.alipay.com/open/6f534d7f_alipay.trade.query?pathHash=98c03720&ref=api&scene=23)
- [扫码订单](https://opendocs.alipay.com/open/02ekfh?pathHash=925e7dfc&ref=api&scene=23)
- [H5 订单](https://opendocs.alipay.com/open/4e2d51d1_alipay.trade.query?pathHash=8abc6ffe&ref=api&scene=common)

## 查询退款订单

```php
Pay::config($this->config);

$order = [
    'out_trade_no' => '1514027114',
    'out_request_no' => '1514027114',
    // '_action' => 'refund', // 默认值，查询退款网页订单
    // '_action' => 'refund_web', // 默认值，查询退款网页订单
    // '_action' => 'refund_app', // 查询 APP 退款订单
    // '_action' => 'refund_mini', // 查询小程序退款订单
    // '_action' => 'refund_pos', // 查询刷卡退款订单
    // '_action' => 'refund_scan', // 查询扫码退款订单
    // '_action' => 'refund_h5', // 查询 H5 退款订单
];

$result = Pay::alipay()->query($order);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [网页订单](https://opendocs.alipay.com/open/357441a2_alipay.trade.fastpay.refund.query?pathHash=01981dca&ref=api&scene=common)
- [APP 订单](https://opendocs.alipay.com/open/8c776df6_alipay.trade.fastpay.refund.query?pathHash=fb6e1894&ref=api&scene=common)
- [小程序订单](https://opendocs.alipay.com/mini/4a0fb7bf_alipay.trade.fastpay.refund.query?pathHash=2e6cbb7c&ref=api&scene=common)
- [刷卡订单](https://opendocs.alipay.com/open/c1cb8815_alipay.trade.fastpay.refund.query?pathHash=6557d527&ref=api&scene=common)
- [扫码订单](https://opendocs.alipay.com/open/02ekfl?pathHash=a223936b&ref=api&scene=common)
- [H5 订单](https://opendocs.alipay.com/open/7be83133_alipay.trade.fastpay.refund.query?pathHash=7cf4fed5&ref=api&scene=common)

## 查询转账订单

```php
Pay::config($this->config);

$order = [
    'out_biz_no' => '202209032319',
    '_action' => 'transfer'
];

$result = Pay::alipay()->query($order);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open/58a29899_alipay.fund.trans.common.query?pathHash=aad07c6d&ref=api&scene=f9fece54d41f49cbbd00dc73655a01a4)，查看「请求参数」一栏。
