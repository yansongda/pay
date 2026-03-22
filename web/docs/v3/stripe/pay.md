# Stripe 支付

:::tip
Stripe 的实现由 GitHub Copilot 生成
:::

Stripe 支付目前直接内置支持以下快捷方式支付方法：

|  method  |        说明         |      参数      |    返回值     |
|:--------:|:-----------------:|:------------:|:----------:|
|  intent  |  PaymentIntent    | array $order | Collection |
|   web    | Checkout Session  | array $order | Collection |

## PaymentIntent 支付

适用于自定义支付界面场景，通过 Stripe.js 在前端完成支付。

### 例子

```php
Pay::config($config);

$order = [
    'amount' => 1000,      // 必填：最小货币单位，如 USD 为分（$10.00 = 1000）
    'currency' => 'usd',   // 必填：三位 ISO 货币代码
    // 「选填」支付方式类型，如 'card'
    // 'payment_method_types' => ['card'],
    // 「选填」支付意图描述
    // 'description' => 'yansongda/pay - test order',
];

$result = Pay::stripe()->intent($order);

// 将 client_secret 返回前端，供 Stripe.js 完成支付
$clientSecret = $result->get('client_secret');
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考 [Stripe 官方文档](https://stripe.com/docs/api/payment_intents/create)，查看「请求参数」一栏。

## Checkout Session 支付

适用于托管支付页面场景，Stripe 托管结账流程，用户完成支付后跳转回指定页面。

### 例子

```php
Pay::config($config);

$order = [
    'line_items' => [
        [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'yansongda/pay - test order',
                ],
                'unit_amount' => 1000,
            ],
            'quantity' => 1,
        ],
    ],
    // 「选填」以下参数覆盖配置文件中的 success_url / cancel_url
    // 'success_url' => 'https://example.com/stripe/success',
    // 'cancel_url' => 'https://example.com/stripe/cancel',
];

$result = Pay::stripe()->web($order);

// 将用户重定向至 Stripe Checkout 页面
$checkoutUrl = $result->get('url');
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考 [Stripe 官方文档](https://stripe.com/docs/api/checkout/sessions/create)，查看「请求参数」一栏。
