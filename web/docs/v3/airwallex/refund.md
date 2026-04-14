# Airwallex 退款

| 方法名 | 参数 | 返回值 |
|:--:|:--:|:--:|
| refund | array $order | Collection |

## 例子

```php
Pay::config($config);

$order = [
    'payment_intent_id' => 'int_xxx',
    'amount' => 10,
    // 'reason' => 'requested_by_customer',
    // 'metadata' => [
    //     'refund_no' => 'R20260414001',
    // ],
];

$result = Pay::airwallex()->refund($order);
```

## 参数说明

- `payment_intent_id`：必填，待退款的 Payment Intent ID
- `amount`：选填，退款金额
- `reason`：选填，退款原因
- `metadata`：选填，自定义扩展信息
