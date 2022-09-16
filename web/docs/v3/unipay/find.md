# 银联查询订单

| 方法名  |      参数      |    返回值     |
|:----:|:------------:|:----------:|
| find | array $order | Collection |

## 查询普通支付订单

```php
Pay::config($this->config);

$order = [
    'txnTime' => '20220911041647',
    'orderId' => 'pay20220911041647',
];

$result = Pay::unipay()->find($order);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=757&apiservId=448&version=V2.2&bussType=0)，查看「请求参数」一节。

## 查询二维码支付订单

```php
Pay::config($this->config);

$order = [
    'txnTime' => '20220911041647',
    'orderId' => 'pay20220911041647',
    '_type' => 'qr_code',
];

$result = Pay::unipay()->find($order);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=792&apiservId=468&version=V2.2&bussType=0)，查看「请求参数」一节。
