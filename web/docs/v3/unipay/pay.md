# 银联支付

银联支付目前直接内置支持以下快捷方式支付方法，对应的支付 method 如下：

| method |  说明   |      参数      |    返回值     |
|:------:|:-----:|:------------:|:----------:|
|  web   | 电脑支付  | array $order |  Response  |
|   h5   | H5 支付 | array $order |  Response  |
|  scan  | 扫码支付  | array $order | Collection |
|  pos   | 刷卡支付  | array $order | Collection |

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

## H5 支付

### 例子

```php
Pay::config($this->config);

return Pay::unipay()->h5([
    'txnTime' => date('YmdHis'),
    'txnAmt' => 1,
    'orderId' => 'yansongda'.date('YmdHis'),
 ]);
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`version`，`bizType` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=754&apiservId=448&version=V2.2&bussType=0)，查看「请求参数」一栏。

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

$result = Pay::unipay()->pos([
    'out_trade_no' => date('YmdHis'),
    'body' => '测试 - yansongda - 1',
    'total_fee' => 1,
    'mch_create_ip' => '1.2.4.8',
    'auth_code' => '123456789012345678',
    'op_device_id' => '123',
    'terminal_info' => '123',
    // '_type' => 'qra', // QRA 平台
]);
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`version`，`bizType` 等参数。
所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考查看「请求参数」一栏。

- [刷卡支付](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=798&apiservId=468&version=V2.2&bussType=0)
- [预授权](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=797&apiservId=468&version=V2.2&bussType=0)
- [QRA 平台](https://up.95516.com/open/openapi/doc?index_1=2&index_2=1&chapter_1=274&chapter_2=292)

## 扫码支付

### 例子

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

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`version`，`bizType` 等参数。
所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考查看「请求参数」一栏。

- [扫码支付](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=793&apiservId=468&version=V2.2&bussType=0)
- [预授权](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=794&apiservId=468&version=V2.2&bussType=0)
- [统一下单](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=795&apiservId=468&version=V2.2&bussType=0)
- [缴费二维码](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=796&apiservId=468&version=V2.2&bussType=0)
