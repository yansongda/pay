# 通联支付快速入门

在初始化完毕后，就可以直接方便的享受 `yansongda/pay`  带来的便利了。

## 统一支付

```php
Pay::config($this->config);

$result = Pay::allinpay()->unified([
    'reqsn'   => 'order-1001',
    'trxamt'  => 1, // 单位：分
    'paytype' => 'W02', // 交易方式，详见官方文档附录
    'body'    => '测试商品',
]);

return $result->payinfo; // 支付串（二维码串/js 参数等，因 paytype 而异）
```

## 主扫支付（收款码）

```php
Pay::config($this->config);

$result = Pay::allinpay()->native([
    'reqsn'      => 'order-1002',
    'trxamt'     => 1,
    'expiretime' => '20991231235959', // yyyyMMddHHmmss 绝对时间
]);

return $result->payinfo; // 二维码串，可自行生成二维码
```

## 查询订单

```php
Pay::config($this->config);

$result = Pay::allinpay()->query([
    'reqsn' => 'order-1001', // 与 trxid 二选一
]);

return $result->trxstatus; // 交易状态
```

## 退款

```php
Pay::config($this->config);

$result = Pay::allinpay()->refund([
    'reqsn'    => 'refund-1',
    'trxamt'   => 1,
    'oldreqsn' => 'order-1001', // 与 oldtrxid 二选一
]);
```

## 回调处理

```php
Pay::config($this->config);

$result = Pay::allinpay()->callback();
```

## 响应回调

```php
Pay::config($this->config);

return Pay::allinpay()->success();
```
