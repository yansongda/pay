# PayPal 退款

|  方法名   |      参数      |    返回值     |
|:------:|:------------:|:----------:|
| refund | array $order | Collection |

## 退款操作

:::tip
PayPal 退款基于已捕获的支付（Capture）发起，退款时需要提供 `capture_id`。
:::

```php
Pay::config($config);

$order = [
    'capture_id' => 'CAPTURE_ID', // 必填：捕获支付时返回的 capture id
    // 「选填」部分退款时传入，不传则全额退款
    'amount' => [
        'value' => '5.00',
        'currency_code' => 'USD',
    ],
    // 「选填」退款备注
    'note_to_payer' => 'Defective product',
    // 「选填」商户退款单号
    'invoice_id' => 'REFUND-20240101001',
];

$result = Pay::paypal()->refund($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [退款](https://developer.paypal.com/docs/api/payments/v2/#captures_refund)
