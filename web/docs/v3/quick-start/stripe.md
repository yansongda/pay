# Stripe 快速开始

:::tip
Stripe 的实现由 GitHub Copilot 生成
:::

## 配置

```php
use Yansongda\Pay\Pay;

$config = [
    'stripe' => [
        'default' => [
            // 「必填」Stripe Secret Key
            // 在 https://dashboard.stripe.com/apikeys 中获取
            'secret_key' => '',
            // 「必填」Stripe Webhook Secret
            // 在 https://dashboard.stripe.com/webhooks 中配置 Webhook 后获取，用于回调签名验证
            'webhook_secret' => '',
            // 「选填」支付完成后 Stripe 跳转的成功回调地址（Checkout Session 模式使用）
            'success_url' => 'https://example.com/stripe/success',
            // 「选填」用户取消支付后 Stripe 跳转的取消地址（Checkout Session 模式使用）
            'cancel_url' => 'https://example.com/stripe/cancel',
            // 「选填」默认为正常模式。可选为： MODE_NORMAL:正式环境, MODE_SANDBOX:沙箱环境
            'mode' => Pay::MODE_NORMAL,
        ],
    ],
];

Pay::config($config);
```

## 支付

### PaymentIntent 支付

```php
$order = [
    'amount' => 1000,    // 最小货币单位，如 USD 为分（$10.00 = 1000）
    'currency' => 'usd',
];

$result = Pay::stripe()->intent($order);
$clientSecret = $result->get('client_secret');
```

### Checkout Session 支付

```php
$order = [
    'line_items' => [
        [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => ['name' => 'Test Order'],
                'unit_amount' => 1000,
            ],
            'quantity' => 1,
        ],
    ],
];

$result = Pay::stripe()->web($order);
$checkoutUrl = $result->get('url');
// 重定向用户至 $checkoutUrl
```

## 查询

```php
$result = Pay::stripe()->query(['payment_intent_id' => 'pi_xxx']);
```

## 退款

```php
$result = Pay::stripe()->refund(['payment_intent' => 'pi_xxx']);
```

## 取消

```php
$result = Pay::stripe()->cancel(['payment_intent_id' => 'pi_xxx']);
```

## 接收回调

```php
$result = Pay::stripe()->callback();

return Pay::stripe()->success();
```
