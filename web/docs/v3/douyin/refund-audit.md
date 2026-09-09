# 抖音退款审核

用户发起退款申请后，若退款单状态为「待审核」，开发者需通过退款审核接口同步审核结果（同意/拒绝）。

|  方法名   |      参数      |    返回值     |
|:------:|:------------:|:----------:|
| refund | array $order | Collection |

## 退款审核

```php
Pay::config($config);

$order = [
    'refund_id' => '7398108028895054107',
    'refund_audit_status' => 1, // 1：同意退款；2：拒绝退款
    // 'deny_message' => '商品已核销，无法退款', // 拒绝（refund_audit_status 为 2）时必填
    '_action' => 'audit',
];

$result = Pay::douyin()->refund($order);
```

### 订单配置参数

`refund_id`（抖音侧退款单号）+ `refund_audit_status`（审核状态，`1` 同意退款/`2` 拒绝退款）必填；`refund_audit_status` 为 `2`（拒绝）时，`deny_message`（拒绝原因）必填。

其余参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [退款审核](https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/refund/refund-audit)

:::tip
退款申请（`type=pre_create_refund` 回调）的同步应答请参考 [响应退款申请回调](/docs/v3/douyin/refund.md#响应退款申请回调)。
:::
