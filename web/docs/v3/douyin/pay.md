# 抖音支付

抖音支付目前直接内置支持以下快捷方式支付方法，对应的支付 method 如下：

|  method  |   说明   |      参数      |    返回值     |
|:--------:|:------:|:------------:|:----------:|
|   mini   | 小程序支付  | array $order | Collection |

## 小程序支付

### 例子

```php
Pay::config($config);

$order = [
    'out_trade_no' => time().'',
    'description' => 'subject-测试',
    'amount' => [
        'total' => 1,
        'currency' => 'CNY',
    ],
    'payer' => [
        'openid' => '123fsdf234',
    ]
];

$result = Pay::wechat()->mini($order);
// 返回 Collection 实例。包含了调用 JSAPI 的所有参数，如appId，timeStamp，nonceStr，package，signType，paySign 等；
// 可直接通过 $result->appId, $result->timeStamp 获取相关值。
// 后续调用不在本文档讨论范围内，请自行参考官方文档。
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`trade_type`，`appid`，`sign` 等参数，大家只需传入订单类主观参数即可。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://pay.weixin.qq.com/docs/merchant/apis/mini-program-payment/mini-prepay.html)，查看「请求参数」一栏。

### 调用支付

后续调起支付不再本文档讨论范围内，请参考[官方文档](https://pay.weixin.qq.com/docs/merchant/apis/mini-program-payment/mini-transfer-payment.html)
