# 支付宝快速入门

在初始化完毕后，就可以直接方便的享受 `yansongda/pay`  带来的便利了。

## 网页支付

```php
Pay::config($this->config);

// 注意返回类型为 Response，具体见详细文档
return Pay::alipay()->web([
    'out_trade_no' => ''.time(),
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 1',
]);

// 如果想获取跳转代码（表单形式），可以使用如下代码（详情请自行了解 PSR 规范）
// $web = Pay::alipay()->web([
//     'out_trade_no' => ''.time(),
//     'total_amount' => '0.01',
//     'subject' => 'yansongda 测试 - 1',
// ]);

// return (string) $web->getBody();
```

## H5 支付

```php
Pay::config($this->config);

// 注意返回类型为 Response，具体见详细文档
return Pay::alipay()->h5([
    'out_trade_no' => time(),
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 01',
    'quit_url' => 'https://yansongda.cn',
 ]);
```

## APP 支付

```php
Pay::config($this->config);

// 注意返回类型为 Response，具体见详细文档
return Pay::alipay()->app([
    'out_trade_no' => time(),
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 01',
]);
```

## 小程序支付

```php
Pay::config($this->config);

$result = Pay::alipay()->mini([
    'out_trade_no' => time().'',
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 01',
    'buyer_id' => '2088622190161234',
]);

return $result->get('trade_no');  // 支付宝交易号
// return $result->trade_no;
```

## 刷卡支付（付款码，被扫码）

```php
Pay::config($this->config);

$result = Pay::alipay()->pos([
    'out_trade_no' => time(),
    'auth_code' => '284776044441477959',
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 01',
]);
```

## 扫码支付

```php
Pay::config($this->config);

$result = Pay::alipay()->scan([
    'out_trade_no' => time(),
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 01',
]);

return $result->qr_code; // 二维码 url
```

## 转账

```php
Pay::config($this->config);

$result = Pay::alipay()->transfer([
    'out_biz_no' => '202106051432',
    'trans_amount' => '0.01',
    'product_code' => 'TRANS_ACCOUNT_NO_PWD',
    'biz_scene' => 'DIRECT_TRANSFER',
    'payee_info' => [
        'identity' => 'ghdhjw7124@sandbox.com',
        'identity_type' => 'ALIPAY_LOGON_ID',
        'name' => '沙箱环境'
    ],
]);
```

## 退款

```php
Pay::config($this->config);

$result = Pay::alipay()->refund([
    'out_trade_no' => '1623160012',
    'refund_amount' => '0.01',
    // '_action' => 'agreement', // 商家收款退款
    // '_action' => 'authorization', // 预授权退款
    // '_action' => 'transfer', // 转账退款
]);
```

## 查询订单

```php
Pay::config($this->config);

$order = [
    'out_trade_no' => '1514027114',
    // '_action' => 'agreement', // 商家收款查询
    // '_action' => 'authorization', // 预授权查询
    // '_action' => 'transfer', // 转账查询
    // '_action' => 'face' // 刷脸结果信息查询
    // '_action' => 'transfer' // 转账查询
    // '_action' => 'refund' // 退款查询
];

$result = Pay::alipay()->query($order);
```

## 支付宝回调处理

```php
Pay::config($this->config);

$result = Pay::alipay()->callback();
```

## 响应支付宝回调

```php
Pay::config($this->config);

return Pay::alipay()->success();
```

## 敏感信息加密响应解密

部分接口（如获取会员手机号等涉及敏感信息的接口）返回的响应内容是 AES 加密的密文。

在配置了 `aes_key` 后，Pay 会自动完成验签与解密动作，无需关心解密细节，返回结构中，`_sign` 为支付宝签名，其余字段均为解密后的业务字段（Collection）：

```php
Pay::config($this->config);

// 以下仅为返回结构示例；仅当接口返回加密响应且已配置 aes_key 时才会自动解密，
// 普通明文接口（如普通查询）的返回行为不受影响
$result = Pay::alipay()->query([
    'out_trade_no' => '1514027114',
]);

// `_sign` 为支付宝签名，其余字段为解密后的业务字段
return $result->all();
```

::: warning 注意
如果未配置 `aes_key`，当收到加密响应时，将会抛出异常，提示未配置支付宝 AES 密钥 [aes_key]。
:::
