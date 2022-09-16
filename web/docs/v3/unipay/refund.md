# 银联退款

|  方法名   |      参数      |    返回值     |
|:------:|:------------:|:----------:|
| refund | array $order | Collection |

## 在线网关退款

```php
Pay::config($this->config);

$result = Pay::unipay()->refund([
    'txnTime' => date('YmdHis'),
    'txnAmt' => 1,
    'orderId' => 'refund'.date('YmdHis'),
    'origQryId' => '392209121420295251518'
]);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=756&apiservId=448&version=V2.2&bussType=0)，查看「请求参数」一节。

## 二维码退款

```php
Pay::config($this->config);

$result = Pay::unipay()->refund([
    'txnTime' => date('YmdHis'),
    'txnAmt' => 1,
    'orderId' => 'refund'.date('YmdHis'),
    'origQryId' => '392209121420295251518',
    '_type' => 'qr_code',
]);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=799&apiservId=468&version=V2.2&bussType=0)，查看「请求参数」一栏。
