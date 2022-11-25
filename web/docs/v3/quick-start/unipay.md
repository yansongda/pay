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

return Pay::unipay()->wap([
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
    // '_type' => 'pre_auth', // 预授权
    // '_type' => 'pre_order', // 统一下单
    // '_type' => 'fee', // 缴费二维码
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
    // '_type' => 'pre_auth', // 预授权
]);
```
