# 微信查询订单

|  方法名  |      参数      |    返回值     |
|:-----:|:------------:|:----------:|
| query | array $order | Collection |

## 查询支付订单

```php
Pay::config($config);

$order = [
    'out_trade_no' => '1217752501201407033233368018',
    // '_action' => 'jsapi', // 默认为 jsapi
    // '_action' => 'app', // 查询 App 支付
    // '_action' => 'h5', // 查询 H5 支付
    // '_action' => 'mini', // 查询小程序支付
    // '_action' => 'native', // 查询 Native 支付
    // '_action' => 'combine', // 查询合单支付
];

$result = Pay::wechat()->query($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [JSAPI订单](https://pay.weixin.qq.com/docs/merchant/apis/jsapi-payment/query-by-out-trade-no.html)
- [APP订单](https://pay.weixin.qq.com/docs/merchant/apis/in-app-payment/query-by-out-trade-no.html)
- [合单订单](https://pay.weixin.qq.com/docs/merchant/apis/combine-payment/orders/query-order.html)
- [H5订单](https://pay.weixin.qq.com/docs/merchant/apis/h5-payment/query-by-out-trade-no.html)
- [小程序订单](https://pay.weixin.qq.com/docs/merchant/apis/mini-program-payment/query-by-out-trade-no.html)
- [Native订单](https://pay.weixin.qq.com/docs/merchant/apis/native-payment/query-by-out-trade-no.html)

## 查询退款订单

```php
Pay::config($config);

$order = [
    'transaction_id' => '1217752501201407033233368018',
    '_action' => 'refund',
    // '_action' => 'refund_jsapi', // 查询 jsapi 退款订单，默认
    // '_action' => 'refund_app', // 查询 App 退款订单
    // '_action' => 'refund_h5', // 查询 H5 退款订单
    // '_action' => 'refund_mini', // 查询小程序退款订单
    // '_action' => 'refund_native', // 查询 Native 退款订单
    // '_action' => 'refund_combine', // 查询合单退款订单
];

$result = Pay::wechat()->query($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [JSAPI订单](https://pay.weixin.qq.com/docs/merchant/apis/jsapi-payment/query-by-out-refund-no.html)
- [APP订单](https://pay.weixin.qq.com/docs/merchant/apis/in-app-payment/query-by-out-refund-no.html)
- [合单订单](https://pay.weixin.qq.com/docs/merchant/apis/combine-payment/refunds/query-by-out-refund-no.html)
- [H5订单](https://pay.weixin.qq.com/docs/merchant/apis/h5-payment/query-by-out-refund-no.html)
- [小程序订单](https://pay.weixin.qq.com/docs/merchant/apis/mini-program-payment/query-by-out-refund-no.html)
- [Native订单](https://pay.weixin.qq.com/docs/merchant/apis/native-payment/query-by-out-refund-no.html)

## 查询合单支付订单

```php
Pay::config($config);

$order = [
    'combine_out_trade_no' => '1217752501201407033233368018',
];
//$order = [
//    'transaction_id' => '1217752501201407033233368018',
//    '_action' => 'combine',
//];

$result = Pay::wechat()->query($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://pay.weixin.qq.com/docs/merchant/apis/combine-payment/orders/query-order.html)，查看「请求参数」一栏。

## 查询转账订单

```php
Pay::config($config);

$order = [
    'out_batch_no' => '1217752501201407033233368018',
    'out_detail_no' => '1217752501201407033233368018',
    '_action' => 'transfer',

    //'out_bill_no' => '1217752501201407033233368018', // 商户单号查询转账，与微信单号二选一
    //'transfer_bill_no' => '1217752501201407033233368018', // 或微信单号查询转账，与商户单号二选一
    //'_action' => 'mch_transfer', // 新版微信商家转账
];

$result = Pay::wechat()->query($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下文档中「请求参数」一栏

- [旧版](https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/transfer-detail/get-transfer-detail-by-out-no.html)
- [新版](https://pay.weixin.qq.com/doc/v3/merchant/4012716437)

