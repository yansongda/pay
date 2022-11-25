# 支付宝支付

支付宝支付目前直接内置支持以下快捷方式支付方法，对应的支付 method 如下：

|  method  |   说明   |      参数      |    返回值     |
|:--------:|:------:|:------------:|:----------:|
|   web    |  电脑支付  | array $order |  Response  |
|   wap    | 手机网站支付 | array $order |  Response  |
|   app    | APP 支付 | array $order |  Response  |
|   pos    |  刷卡支付  | array $order | Collection |
|   scan   |  扫码支付  | array $order | Collection |
| transfer |  账户转账  | array $order | Collection |
|   mini   | 小程序支付  | array $order | Collection |

更多接口调用请参考后续文档

## 电脑支付

### 例子

```php
Pay::config($this->config);

return Pay::alipay()->web([
    'out_trade_no' => ''.time(),
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 1',
]);
```

#### GET 方式提交

为您考虑到了这一点，如果您想使用 GET 方式提交请求，可以在参数中增加 `['_method' => 'get']` 即可，例如

```php
Pay::config($this->config);

return Pay::alipay()->web([
    'out_trade_no' => ''.time(),
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 1',
    '_method' => 'get',
]);
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`product_code` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open/028r8t?scene=22)，查看「请求参数」一栏。

## 手机网站支付

### 例子

```php
Pay::config($this->config);

return Pay::alipay()->wap([
    'out_trade_no' => time(),
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 01',
    'quit_url' => 'https://yansongda.cn',
 ]);
```

#### GET 方式提交

为您考虑到了这一点，如果您想使用 GET 方式提交请求，可以在参数中增加 `['_method' => 'get']` 即可，例如

```php
Pay::config($this->config);

return Pay::alipay()->wap([
    'out_trade_no' => ''.time(),
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 1',
    '_method' => 'get',
]);
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`product_code` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open/02ivbs?scene=21)，查看「请求参数」一栏。

## APP 支付

### 例子

```php
Pay::config($this->config);

// 后续 APP 调用方式不在本文档讨论范围内，请参考官方文档。
return Pay::alipay()->app([
    'out_trade_no' => time(),
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 01',
]);
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`product_code` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open/02e7gq?scene=20)，查看「请求参数」一栏。

## 小程序支付

### 例子

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

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`product_code` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open/02ekfj?ref=api&scene=de4d6a1e0c6e423b9eefa7c3a6dcb7a5)，查看「请求参数」一栏。

小程序支付接入文档：[https://docs.alipay.com/mini/introduce/pay](https://opendocs.alipay.com/mini/introduce/pay)。

## 刷卡支付（付款码，被扫码）

### 例子

```php
Pay::config($this->config);

$result = Pay::alipay()->pos([
    'out_trade_no' => time(),
    'auth_code' => '284776044441477959',
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 01',
]);
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`product_code` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open/02ekfp?ref=api&scene=32)，查看「请求参数」一栏。

## 扫码支付

### 例子

```php
Pay::config($this->config);

$result = Pay::alipay()->scan([
    'out_trade_no' => time(),
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 01',
]);

return $result->qr_code; // 二维码 url
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`product_code` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open/02ekfg?ref=api&scene=19)，查看「请求参数」一栏。

## 账户转账

### 例子

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

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/apis/api_28/alipay.fund.trans.uni.transfer)，查看「请求参数」一栏。


:::tip
转账查询等，请参考 [查询](/docs/v3/alipay/find.md)
:::
