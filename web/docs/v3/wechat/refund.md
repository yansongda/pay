# 微信退款

|  方法名   |      参数      |    返回值     |
|:------:|:------------:|:----------:|
| refund | array $order | Collection |

## 退款操作

```php
Pay::config($config);

$order = [
    'out_trade_no' => '1514192025',
    'out_refund_no' => time(),
    'amount' => [
        'refund' => 1,
        'total' => 1,
        'currency' => 'CNY',
    ],
    // '_action' => 'jsapi', // jsapi 退款，默认
    // '_action' => 'app', // app 退款
    // '_action' => 'combine', // 合单退款
    // '_action' => 'h5', // h5 退款
    // '_action' => 'mini', // 小程序退款
    // '_action' => 'native', // native 退款

];

$result = Pay::wechat()->refund($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [JSAPI订单](https://pay.weixin.qq.com/docs/merchant/apis/jsapi-payment/create.html)
- [APP订单](https://pay.weixin.qq.com/docs/merchant/apis/in-app-payment/create.html)
- [合单订单](https://pay.weixin.qq.com/docs/merchant/apis/combine-payment/refunds/create.html)
- [H5订单](https://pay.weixin.qq.com/docs/merchant/apis/h5-payment/create.html)
- [小程序订单](https://pay.weixin.qq.com/docs/merchant/apis/mini-program-payment/create.html)
- [Native订单](https://pay.weixin.qq.com/docs/merchant/apis/native-payment/create.html)
