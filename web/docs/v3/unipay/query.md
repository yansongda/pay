# 银联查询订单

|  方法名  |      参数      |    返回值     |
|:-----:|:------------:|:----------:|
| query | array $order | Collection |

## 查询普通支付订单

```php
Pay::config($this->config);

$order = [
    'txnTime' => '20220911041647',
    'orderId' => 'pay20220911041647',
    // '_action' => 'web', // 网页支付订单，默认
    // '_action' => 'qr_code', // 二维码支付订单
    // '_action' => 'qra_pos', // QRA 平台，刷卡支付
];

$result = Pay::unipay()->query($order);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请查看「请求参数」一节。

- [网页支付订单](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=757&apiservId=448&version=V2.2&bussType=0)
- [二维码支付](https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=792&apiservId=468&version=V2.2&bussType=0)
- [QRA 平台，刷卡支付](https://up.95516.com/open/openapi/doc?index_1=2&index_2=1&chapter_1=274&chapter_2=293)

## 查询退款订单

```php
Pay::config($this->config);

$order = [
    'out_trade_no' => '20220911041647',
    'out_refund_no' => 'pay20220911041647',
    // '_action' => 'qra_pos_refund', // QRA 平台，刷卡支付退款
];

$result = Pay::unipay()->query($order);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请查看「请求参数」一节。

- [QRA 平台，刷卡支付退款](https://up.95516.com/open/openapi/doc?index_1=2&index_2=1&chapter_1=274&chapter_2=296)
