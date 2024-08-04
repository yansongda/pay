# 抖音查询订单

|  方法名  |      参数      |    返回值     |
|:-----:|:------------:|:----------:|
| query | array $order | Collection |

## 查询支付订单

```php
Pay::config($config);

$order = [
    'out_trade_no' => '202408040747147327',
    // '_action' => 'mini', // 查询小程序支付，默认
];

$result = Pay::douyin()->query($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [小程序订单](https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/ecpay/pay-list/query)

## 查询退款订单

```php
Pay::config($config);

$order = [
    'transaction_id' => '1217752501201407033233368018',
    '_action' => 'refund',
    // '_action' => 'refund_mini', // 查询小程序退款订单，refund action 默认
];

$result = Pay::douyin()->query($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [小程序订单](https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/ecpay/refund-list/query)
