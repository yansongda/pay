# 支付宝查询订单

|  方法名  |      参数      |    返回值     |
|:-----:|:------------:|:----------:|
| query | array $order | Collection |

:::warning 注意
更多功能请查看源码，文档里仅仅列举了一些常用的功能。
:::

## 查询普通支付订单

:::tip
Alipay V3 的服务端查询能力已按官方 open-v3 REST API 实现，普通交易查询走 `POST /v3/alipay/trade/query`，转账查询走 `GET /v3/alipay/fund/trans/common/query`。

`_action` 仍可用于兼容不同支付场景，但在 V3 下普通交易查询会统一走同一个 REST 端点。
:::

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

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open-v3/02s2bo)，查看「请求参数」一栏。

## 查询退款订单

:::tip
Alipay V3 退款查询统一走 `POST /v3/alipay/trade/fastpay/refund/query`。
:::

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

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open-v3/02s7k7)，查看「请求参数」一栏。

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

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open-v3/02a68g)，查看「请求参数」一栏。
