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

## H5支付

```php
Pay::config($this->config);

// 注意返回类型为 Response，具体见详细文档
return Pay::alipay()->wap([
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
]);
```

## 查询订单

```php
Pay::config($this->config);

$order = [
    'out_trade_no' => '1514027114',
];
// $order = '1514027114';

$result = Pay::alipay()->find($order);
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
