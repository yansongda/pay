# 江苏银行e融支付快速入门

在初始化完毕后，就可以直接方便的享受 `yansongda/pay`  带来的便利了。

## 扫码支付

```php
Pay::config($this->config);

$result = Pay::jsb()->scan([
    'outTradeNo' => 'YC202406170003',
    'totalFee'   => 0.01,
    'proInfo'    => '充值'
]);

return $result->payUrl; // 二维码 url
```

## 退款

```php
Pay::config($this->config);

$result = Pay::jsb()->refund([
    'outTradeNo' => 'YC202406170004',
    'refundAmt' => 0.01,
    'outRefundNo' => 'TK-YC202406170004',
]);
```

## 查询订单

```php
Pay::config($this->config);

//查询交易支付订单
$order = [
    'outTradeNo' => '1514027114',
];

$result = Pay::jsb()->query($order);
```

## 回调处理

```php
Pay::config($this->config);

$result = Pay::jsb()->callback();
```

## 响应回调

```php
Pay::config($this->config);

return Pay::jsb()->success();
```
