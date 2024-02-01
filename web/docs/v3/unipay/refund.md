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
    'origQryId' => '392209121420295251518',
    // '_action' => 'web', // 网页退款，默认
    // '_action' => 'qr_code', // 二维码退款
]);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考查看「请求参数」一节。

- [在线网关退款](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=756&apiservId=448&version=V2.2&bussType=0)
- [二维码退款](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=799&apiservId=468&version=V2.2&bussType=0)

## QRA 平台，刷卡支付退款

```php
Pay::config($this->config);

$result = Pay::unipay()->refund([
    'out_trade_no' => date('YmdHis'),
    'out_refund_no' => 'refund'.date('YmdHis'),
    'total_fee' => 1,
    'refund_fee' => 1,
    'op_user_id' => '123',
    '_action' => 'qra_pos',
]);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://up.95516.com/open/openapi/doc?index_1=2&index_2=1&chapter_1=274&chapter_2=295)，查看「请求参数」一栏。
