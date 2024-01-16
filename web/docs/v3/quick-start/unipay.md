# 银联快速入门

在初始化完毕后，就可以直接方便的享受 `yansongda/pay`  带来的便利了。

## 电脑支付

```php
Pay::config($this->config);

return Pay::unipay()->web([
    'txnTime' => date('YmdHis'),
    'txnAmt' => 1,
    'orderId' => 'yansongda'.date('YmdHis'),
]);
```

## 手机网站支付

```php
Pay::config($this->config);

return Pay::unipay()->h5([
    'txnTime' => date('YmdHis'),
    'txnAmt' => 1,
    'orderId' => 'yansongda'.date('YmdHis'),
 ]);
```

## 扫码支付

```php
Pay::config($this->config);

$result = Pay::unipay()->scan([
    'txnTime' => date('YmdHis'),
    'txnAmt' => 1,
    'orderId' => 'scan'.date('YmdHis'),
    // '_action' => 'pre_auth', // 预授权
    // '_action' => 'pre_order', // 统一下单
    // '_action' => 'fee', // 缴费二维码
]);

return $result->qrCode; // 二维码 url
```

## 刷卡支付（付款码，被扫码）

```php
Pay::config($this->config);

$result = Pay::unipay()->pos([
    'qrNo' => '123456789012345678',
    'txnTime' => date('YmdHis'),
    'txnAmt' => 1,
    'orderId' => 'pos'.date('YmdHis'),
    // '_action' => 'pre_auth', // 预授权
]);

// 银联条码支付综合前置平台
return Pay::unipay()->pos([
    '_action' => 'qra',
    'out_trade_no' => 'pos-qra-20240106163401',
    'body' => '测试商品',
    'total_fee' => 1,
    'mch_create_ip' => '127.0.0.1',
    'auth_code' => '131969896307360385',
    'op_device_id' => '123',
    'terminal_info' => json_encode([
        'device_type' => '07',
        'terminal_id' => '123',
    ]),
]);
```

## 查询订单

```php
Pay::config($this->config);

$result = Pay::unipay()->query([
   'txnTime' => '20240105164725',
   'orderId' => 'pay20240105164725',
]);
```

## 退款

```php
Pay::config($this->config);

return Pay::unipay()->refund([
   'txnTime' => '20240105165842',
   'txnAmt' => 1,
   'orderId' => 'refundpay20240105165842',
   'origQryId' => '052401051658427862748'
]);
```
