# 微信关闭订单

|  方法名  |         参数          |    返回值     |
|:-----:|:-------------------:|:----------:|
| close | string/array $order | Collection |

## 关闭普通订单操作

```php
Pay::config($config);

$order = [
    'out_trade_no' => '1514027114',
];

// $order = '1514027114';

$result = Pay::wechat()->close($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_3.shtml)，查看「请求参数」一栏。

## 关闭合单操作

```php
Pay::config($config);

$order = [
    'combine_out_trade_no' => '1514027114',
    'sub_orders' => '123456'
];

//$order = [
//    'out_trade_no' => '1514027114',
//    'sub_orders' => '123456',
//    '_type' => 'combine',
//];

$result = Pay::wechat()->close($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter5_1_12.shtml)，查看「请求参数」一栏。
