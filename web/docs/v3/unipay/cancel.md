# 银联取消订单

|  方法名   |      参数      |    返回值     |
|:------:|:------------:|:----------:|
| cancel | array $order | Collection |

## 在线网关取消订单

```php
Pay::config($this->config);

$order = [
    'txnTime' => date('YmdHis'),
    'txnAmt' => 1,
    'orderId' => 'cancel'.date('YmdHis'),
    'origQryId' => '062209121414535249018'
];

$result = Pay::unipay()->cancel($order);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=755&apiservId=448&version=V2.2&bussType=0)，查看「请求参数」一栏。

## 二维码取消订单

```php
Pay::config($this->config);

$order = [
    'txnTime' => date('YmdHis'),
    'txnAmt' => 1,
    'orderId' => 'cancel'.date('YmdHis'),
    'origQryId' => '062209121414535249018',
    '_type' => 'qr_code',
];

$result = Pay::unipay()->cancel($order);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=800&apiservId=468&version=V2.2&bussType=0)，查看「请求参数」一栏。
