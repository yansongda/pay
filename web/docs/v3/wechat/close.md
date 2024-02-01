# 微信关闭订单

|  方法名  |      参数      | 返回值  |
|:-----:|:------------:|:----:|
| close | array $order | null |

## 关闭普通订单操作

```php
Pay::config($config);

$order = [
    'out_trade_no' => '1514027114',
    // '_action' => 'jsapi', // jsapi 关单，默认
    // '_action' => 'app', // app 关单
    // '_action' => 'combine', // 合单关单
    // '_action' => 'h5', // h5 关单
    // '_action' => 'miniapp', // 小程序关单
    // '_action' => 'native', // native 关单
];

$result = Pay::wechat()->close($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [JSAPI订单](https://pay.weixin.qq.com/docs/merchant/apis/jsapi-payment/close-order.html)
- [APP订单](https://pay.weixin.qq.com/docs/merchant/apis/in-app-payment/close-order.html)
- [合单订单](https://pay.weixin.qq.com/docs/merchant/apis/combine-payment/orders/close-order.html)
- [H5订单](https://pay.weixin.qq.com/docs/merchant/apis/h5-payment/close-order.html)
- [小程序订单](https://pay.weixin.qq.com/docs/merchant/apis/mini-program-payment/close-order.html)
- [Native订单](https://pay.weixin.qq.com/docs/merchant/apis/native-payment/close-order.html)

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
//    '_action' => 'combine',
//];

$result = Pay::wechat()->close($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://pay.weixin.qq.com/docs/merchant/apis/combine-payment/orders/close-order.html)，查看「请求参数」一栏。
