# Airwallex 支付

Airwallex 当前内置支持以下支付方式：

| method | 说明 | 参数 | 返回值 |
|:--:|:--:|:--:|:--:|
| intent | Payment Intent 初始化支付 | array $order | Collection |

## Payment Intent 支付

适用于服务端发起 Airwallex 支付的场景。

### 默认模式

默认情况下，`intent()` 只执行创建：

1. 调用 `/api/v1/pa/payment_intents/create` 创建 Payment Intent
2. 返回前端 Airwallex 组件后续所需的支付数据

这种模式适用于：

- Airwallex.js
- Airwallex Elements
- Hosted Payment Page
- 其他前端完成支付确认的接入方式

### Native API 模式

如果你已经开通 Airwallex Native API，并希望服务端继续调用 confirm，
请显式传入 `_native_api => true`。

此时 `intent()` 会执行：

1. 调用 `/api/v1/pa/payment_intents/create` 创建 Payment Intent
2. 解析创建结果中的 `id`
3. 调用 `/api/v1/pa/payment_intents/{id}/confirm` 确认支付

### 例子

```php
Pay::config($config);

$order = [
    'amount' => 100,
    'currency' => 'USD',
    'merchant_order_id' => 'order_20260414_001',
    // 'return_url' => 'https://example.com/airwallex/return',
    // 'customer_id' => 'cus_xxx',
    // 'payment_method' => [
    //     'type' => 'card',
    // ],
    // 'payment_method_options' => [
    //     'card' => [
    //         'auto_capture' => true,
    //     ],
    // ],
    // 'metadata' => [
    //     'biz_order_no' => 'A20260414001',
    // ],
];

$result = Pay::airwallex()->intent($order);
```

如果你要使用 Native API：

```php
$order = [
    '_native_api' => true,
    'amount' => 100,
    'currency' => 'USD',
    'merchant_order_id' => 'order_20260414_001',
    'payment_method' => [
        'type' => 'card',
        'card' => [
            'number' => '4111111111111111',
            'expiry_month' => '12',
            'expiry_year' => '2030',
            'name' => 'John Doe',
            'cvc' => '123',
        ],
    ],
];

$result = Pay::airwallex()->intent($order);
```

### 订单配置参数

当前 SDK 会将常见参数透传到创建流程；如果 `_native_api` 为真，也会透传到确认流程，例如：

- `amount`
- `currency`
- `merchant_order_id`
- `_native_api`
- `return_url`
- `payment_method`
- `payment_method_options`
- `customer_id`
- `customer`
- `descriptor`
- `order`
- `metadata`

如需了解完整参数说明，请参考 [Airwallex Payment Intents 文档](https://www.airwallex.com/docs/payments/get-started/quickstart)。

## `_native_api` 参数说明

`_native_api` 是 `yansongda/pay` 为 Airwallex Provider 增加的运行时控制参数，不是 Airwallex 官方 API 字段。

```php
'_native_api' => true
```

含义如下：

- 不传或传 `false`：只调用 `/api/v1/pa/payment_intents/create`
- 传 `true`：创建完成后，继续调用 `/api/v1/pa/payment_intents/{id}/confirm`

### `_native_api = true` 时建议完整参数

通常至少应包含：

- `amount`
- `currency`
- `merchant_order_id`
- `payment_method.type`
- 与该支付方式匹配的完整 `payment_method` 明细

以银行卡为例：

```php
$order = [
    '_native_api' => true,
    'amount' => 100,
    'currency' => 'USD',
    'merchant_order_id' => 'order_20260414_001',
    'return_url' => 'https://example.com/airwallex/return',
    'payment_method' => [
        'type' => 'card',
        'card' => [
            'number' => '4111111111111111',
            'expiry_month' => '12',
            'expiry_year' => '2030',
            'name' => 'John Doe',
            'cvc' => '123',
        ],
    ],
    'payment_method_options' => [
        'card' => [
            'auto_capture' => true,
        ],
    ],
    'customer' => [
        'email' => 'demo@example.com',
    ],
    'metadata' => [
        'biz_order_no' => 'A20260414001',
    ],
];
```

:::warning
`_native_api = true` 仅适用于已开通 Airwallex Native API 权限的账号。

如果账号未开通，或未满足 PCI / Partner / 3DS / Device Fingerprinting 等要求，
即使参数格式正确，也可能被 Airwallex 拒绝。
:::
