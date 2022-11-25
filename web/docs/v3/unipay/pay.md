# 银联支付

银联支付目前直接内置支持以下快捷方式支付方法，对应的支付 method 如下：

|  method  |   说明   |      参数      |    返回值     |
|:--------:|:------:|:------------:|:----------:|
|   web    |  电脑支付  | array $order |  Response  |
|   wap    | 手机网站支付 | array $order |  Response  |
|   scan   |  扫码支付  | array $order | Collection |
|   pos    |  刷卡支付  | array $order | Collection |

更多接口调用请参考后续文档

## 电脑支付

### 例子

```php
Pay::config($this->config);

return Pay::unipay()->web([
    'txnTime' => date('YmdHis'),
    'txnAmt' => 1,
    'orderId' => 'yansongda'.date('YmdHis'),
]);
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`version`，`bizType` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=754&apiservId=448&version=V2.2&bussType=0)，查看「请求参数」一栏。

## 手机网站支付

### 例子

```php
Pay::config($this->config);

return Pay::unipay()->wap([
    'txnTime' => date('YmdHis'),
    'txnAmt' => 1,
    'orderId' => 'yansongda'.date('YmdHis'),
 ]);
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`version`，`bizType` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=754&apiservId=448&version=V2.2&bussType=0)，查看「请求参数」一栏。

## 扫码支付

### 例子

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

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`version`，`bizType` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=793&apiservId=468&version=V2.2&bussType=0)，查看「请求参数」一栏。

## 刷卡支付（付款码，被扫码）

### 例子

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

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`version`，`bizType` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/apis/api_1/alipay.trade.pay)，查看「请求参数」一栏。
