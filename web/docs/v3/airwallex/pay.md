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

### `_native_api = true` 时的移动端支付方式示例

除了银行卡，Airwallex Native API 也常用于服务端确认以下支付方式：

- `wechatpay`
- `alipayhk`
- `alipaycn`

参照常见业务写法，可以这样组织参数：

```php
$order = [
    '_native_api' => true,
    'amount' => 100,
    'currency' => 'USD',
    'merchant_order_id' => 'order_20260414_001',
    'payment_method' => [
        'type' => 'wechatpay',
        'wechatpay' => [
            'flow' => 'mobile_web',
        ],
    ],
];

$result = Pay::airwallex()->intent($order);
```

如果是支付宝香港：

```php
$order = [
    '_native_api' => true,
    'amount' => 100,
    'currency' => 'USD',
    'merchant_order_id' => 'order_20260414_001',
    'payment_method' => [
        'type' => 'alipayhk',
        'alipayhk' => [
            'flow' => 'mobile_web',
            'os_type' => 'android', // ios / android
        ],
    ],
];
```

如果是支付宝中国：

```php
$order = [
    '_native_api' => true,
    'amount' => 100,
    'currency' => 'USD',
    'merchant_order_id' => 'order_20260414_001',
    'payment_method' => [
        'type' => 'alipaycn',
        'alipaycn' => [
            'flow' => 'qrcode', // 或 mobile_web
        ],
    ],
];
```

### `_native_api = true` 时 `payment_method` 常见字段说明

#### `payment_method.type`

支付方式类型，常见值：

- `wechatpay`
- `alipayhk`
- `alipaycn`
- `card`

#### `payment_method.{type}.flow`

支付拉起方式，常见值：

- `mobile_web`
- `qrcode`

#### `payment_method.alipayhk.os_type`

支付宝香港场景下常见需要指定终端系统：

- `ios`
- `android`

## Native API 响应说明

启用 `_native_api => true` 后，Airwallex 在确认支付成功返回时，
通常会在 `next_action` 中给出下一步动作。

为了便于业务直接使用，SDK 会在保留 Airwallex 原始响应字段的同时，额外补充以下便捷字段：

- `payment_intent_id`：等同于响应中的 `id`
- `next_action_type`：等同于 `next_action.type`
- `pay_url`：从 `next_action.qrcode_url` 或 `next_action.url` 自动提取

常见结构如下：

```php
$result = Pay::airwallex()->intent($order);

$type = $result->get('next_action.type');
```

典型处理方式：

```php
$payUrl = '';
$type = $result->get('next_action.type', '');

switch ($type) {
    case 'render_qrcode':
        $payUrl = $result->get('next_action.qrcode_url')
            ?? $result->get('next_action.url', '');
        break;
    case 'redirect':
        $payUrl = $result->get('next_action.url', '');
        break;
}
```

也就是说，业务上通常可以取：

- `next_action.qrcode_url`
- `next_action.url`

也可以直接使用 SDK 整理好的：

- `payment_intent_id`
- `next_action_type`
- `pay_url`

并整理成你自己的 `pay_url` 返回给前端。

### 推荐的业务封装示例

```php
$order = [
    '_native_api' => true,
    'amount' => 100,
    'currency' => 'USD',
    'merchant_order_id' => 'order_20260414_001',
    'payment_method' => [
        'type' => 'wechatpay',
        'wechatpay' => [
            'flow' => 'mobile_web',
        ],
    ],
];

$result = Pay::airwallex()->intent($order);

$payUrl = '';
$type = $result->get('next_action.type', '');

switch ($type) {
    case 'render_qrcode':
        $payUrl = $result->get('next_action.qrcode_url')
            ?? $result->get('next_action.url', '');
        break;
    case 'redirect':
        $payUrl = $result->get('next_action.url', '');
        break;
}

return [
    'pay_url' => $payUrl,
    'raw' => $result,
];
```

如果你直接使用 SDK 补充好的便捷字段，则可以简化为：

```php
$result = Pay::airwallex()->intent($order);

return [
    'payment_intent_id' => $result->get('payment_intent_id'),
    'pay_url' => $result->get('pay_url'),
    'next_action_type' => $result->get('next_action_type'),
    'raw' => $result,
];
```

:::warning
`_native_api = true` 仅适用于已开通 Airwallex Native API 权限的账号。

如果账号未开通，或未满足 PCI / Partner / 3DS / Device Fingerprinting 等要求，
即使参数格式正确，也可能被 Airwallex 拒绝。
:::
