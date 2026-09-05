# 抖音支付

抖音支付目前直接内置支持以下快捷方式支付方法，对应的支付 method 如下：

|  method  |         说明          |      参数      |    返回值     |
|:--------:|:-------------------:|:------------:|:----------:|
|   mini   | 小程序支付（JSAPI 下单签名） | array $order | Collection |

:::tip
v3.8.0-beta.6 起接入抖音「通用交易系统」（trade_basic），老的「担保支付」（ecpay）已删除。
:::

## 小程序支付

与微信/支付宝不同，抖音通用交易系统的小程序支付主路径为**前端 JSAPI 下单**：服务端**不发 HTTP 请求**，只负责透传官方下单参数并使用**应用私钥**生成签名，产出 `data` 与 `byteAuthorization` 两个值，交由前端 `tt.requestOrder` 发起下单。

### 例子

```php
Pay::config($config);

$order = [
    'outOrderNo' => date('YmdHis').mt_rand(1000, 9999),
    'totalAmount' => 1,
    'skuList' => [
        [
            'skuId' => 'sku-001',
            'title' => '闫嵩达 - test - subject - 01',
            'quantity' => 1,
            'price' => 1,
        ],
    ],
    'orderEntrySchema' => [
        'path' => 'pages/order/detail',
        'params' => '{"out_order_no":"202408040747147327"}',
    ],
];

$result = Pay::douyin()->mini($order);
// 返回 Collection 实例，包含两个值：
// $result->data —— 下单参数（JSON 字符串），传给前端 tt.requestOrder 的 data 参数
// $result->byteAuthorization —— 签名请求头值（SHA256-RSA2048 appid="...",nonce_str="...",timestamp="...",key_version=1,signature="..."），
//                               传给前端 tt.requestOrder 的 byteAuthorization 参数
```

### 前端调起支付

将服务端返回的 `data` 与 `byteAuthorization` 原样传给前端，调用 `tt.requestOrder` 即可（后续调用不在本文档讨论范围内，请自行参考[官方文档](https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/api/industry/general_trade/create_order/requestOrder)）：

```js
tt.requestOrder({
  data, // 服务端返回的 data
  byteAuthorization, // 服务端返回的 byteAuthorization
  success(res) {
    // res.orderId
  },
  fail(res) {
    // 处理失败
  },
});
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，签名所需的 `appid`、`nonce_str`、`timestamp`、`signature` 等，大家只需传入订单类主观参数即可。

所有订单配置参数和官方无任何差别，兼容所有功能（`outOrderNo`/`totalAmount`/`skuList`/`orderEntrySchema`/`merchantUid` 等官方 camelCase 字段，SDK 原样透传组装并 JSON 序列化），所有参数请参考[「生成下单参数与签名」官方文档](https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/order/request-order-data-sign)，查看「下单参数说明」一栏。

### 签名说明

- 待签串共五行，每行末尾以 `\n` 结尾：`POST\n/requestOrder\n{timestamp秒}\n{nonce}\n{data JSON字符串}\n`
- 使用配置的 `app_private_key`（应用私钥）以 SHA256withRSA 签名
- `byteAuthorization` 的 `appid` 值带引号，以官方[签名算法总览](https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/signature-algorithm)为准

:::warning 注意
`timestamp` 超过 1 小时的请求会被平台拒绝，SDK 默认取当前时间，请勿长时间缓存签名结果。
:::
