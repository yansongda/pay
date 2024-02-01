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
    'origQryId' => '062209121414535249018',
    // '_action' => 'web', // 在线网关支付，默认
    // '_action' => 'qr_code', // 二维码支付
];

$result = Pay::unipay()->cancel($order);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考查看「请求参数」一栏。

- [在线网关支付](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=755&apiservId=448&version=V2.2&bussType=0)
- [二维码支付](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=800&apiservId=468&version=V2.2&bussType=0)

## QRA 平台，刷卡支付取消订单

```php
Pay::config($this->config);

$result = Pay::unipay()->refund([
    'out_trade_no' => date('YmdHis'),
    '_action' => 'qra_pos',
]);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://up.95516.com/open/openapi/doc?index_1=2&index_2=1&chapter_1=274&chapter_2=294)，查看「请求参数」一栏。
