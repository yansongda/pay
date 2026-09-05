# 抖音退款

|  方法名   |      参数      |    返回值     |
|:------:|:------------:|:----------:|
| refund | array $order | Collection |

## 创建退款

```php
Pay::config($config);

$order = [
    'order_id' => 'xxxx',
    'out_refund_no' => '202408040747147327R',
    'refund_reason' => [
        '测试退款',
    ],
    // 'notify_url' => 'https://yansongda.cn/douyin/refund/notify', // 选填，默认取配置中的 refund_notify_url
];

$result = Pay::douyin()->refund($order);
```

### 订单配置参数

`order_id`（抖音侧订单号）+ `out_refund_no`（商户侧退款单号，全局唯一）+ `refund_reason[]`（退款理由，数组）必填；`notify_url` 选填（不传则取配置中的 `refund_notify_url`，配置中也为空则平台使用下单时传入的地址）。

其余参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [发起退款](https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/refund/create-refund)
- [查询退款](/docs/v3/douyin/query.md)（`_action` 为 `refund`）

## 退款审核

退款审核（`_action` 为 `audit`）请参考 [退款审核](/docs/v3/douyin/refund-audit.md)。

## 响应退款申请回调

用户从交易模板组件发起退款后，平台会向开发者服务端发送 `type=pre_create_refund` 的**退款申请回调**，SDK 提供的 `preRefundCallback()` 只负责**验签 + 解析**，**同步应答体由业务方自行构造并返回**（详见 [接收回调](/docs/v3/douyin/callback.md)）。

:::warning 注意
若不响应（未按下方格式应答），平台会一直重试并导致用户退款卡单，请务必实现同步应答。
:::

结合 `preRefundCallback()` 返回的 `Collection`（含 `refund_id`/`out_refund_no`/`need_refund_audit`/`refund_audit_deadline` 等，其中 `out_refund_no` 仅在该退款单已存在时非空），业务方**自行生成商户侧退款单号**并按下述格式应答：

```php
// $result = Pay::douyin()->preRefundCallback($request);
// $result 中包含 refund_id、order_id、need_refund_audit、refund_audit_deadline 等退款申请信息

return response()->json([
    'err_no' => 0,
    'err_tips' => 'success',
    'data' => [
        'out_refund_no' => '202408040747147327R', // 业务方自行生成的商户侧退款单号
        'order_entry_schema' => $result->get('order_entry_schema'),
    ],
]);
```

对应的应答 JSON 结构如下（各字段含义请参考[退款申请回调官方文档](https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/refund/refund-callback)）：

```json
{
    "err_no": 0,
    "err_tips": "success",
    "data": {
        "out_refund_no": "202408040747147327R",
        "order_entry_schema": {
            "path": "pages/refund/detail",
            "params": "{\"out_refund_no\":\"202408040747147327R\"}"
        }
    }
}
```
