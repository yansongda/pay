# 微信查询订单

| 方法名  |         参数          |    返回值     |
|:----:|:-------------------:|:----------:|
| find | string/array $order | Collection |

## 查询支付订单

```php
Pay::config($config);

$order = [
    'transaction_id' => '1217752501201407033233368018',
];
// $order = '1217752501201407033233368018';

$result = Pay::wechat()->find($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_2.shtml)，查看「请求参数」一栏。

## 查询退款订单

```php
Pay::config($config);

$order = [
    'transaction_id' => '1217752501201407033233368018',
    '_type' => 'refund'
];

$result = Pay::wechat()->find($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_10.shtml)，查看「请求参数」一栏。

## 查询合单支付订单

```php
Pay::config($config);

$order = [
    'combine_out_trade_no' => '1217752501201407033233368018',
];
//$order = [
//    'transaction_id' => '1217752501201407033233368018',
//    '_type' => 'combine',
//];

$result = Pay::wechat()->find($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter5_1_11.shtml)，查看「请求参数」一栏。
