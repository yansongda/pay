# Airwallex 快速开始

> 官方文档：
> [API Introduction](https://www.airwallex.com/docs/api/introduction)
> [Using PaymentIntents API](https://www.airwallex.com/docs/api/payments/payment_intents/create)
> [Native API](https://www.airwallex.com/docs/api/payments/payment_intents/create)


## 配置

```php
use Yansongda\Pay\Pay;

$config = [
    'airwallex' => [
        'default' => [
            // 「必填」Airwallex Client ID
            'client_id' => '',
            // 「必填」Airwallex API Key
            'api_key' => '',
            // 「必填」Webhook Secret，用于回调验签
            'webhook_secret' => '',
            // 「选填」支付完成后的返回地址
            'return_url' => 'https://example.com/airwallex/return',
            // 「选填」Airwallex API 版本
            'api_version' => '2024-06-14',
            // 「选填」平台模式代商户调用时使用
            // 'on_behalf_of' => 'open_id_xxx',
            // 「选填」默认为正式模式。可选值：
            // MODE_NORMAL: 正式环境
            // MODE_SANDBOX: 沙箱环境
            'mode' => Pay::MODE_NORMAL,
        ],
    ],
];

Pay::config($config);
```

## 支付

```php
$order = [
    'amount' => 100,
    'currency' => 'USD',
    'merchant_order_id' => 'order_20260414_001',
];

$result = Pay::airwallex()->intent($order);
```

默认情况下，`intent()` 只会创建 Payment Intent：

1. 调用 `/api/v1/pa/payment_intents/create`
2. 返回前端 Airwallex 组件继续支付所需的数据，例如 `id`、`client_secret` 等

适用于 Airwallex.js、Elements、Hosted Payment Page 等前端集成场景。

### Native API 模式

如果你已经获得 Airwallex Native API 权限，并且希望由服务端继续确认支付，需要显式传入 `_native_api`：

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

传入 `_native_api => true` 后，SDK 会继续执行：

1. 创建 Payment Intent：`/api/v1/pa/payment_intents/create`
2. 确认 Payment Intent：`/api/v1/pa/payment_intents/{id}/confirm`

如果你做的是微信支付、支付宝香港、支付宝中国这类跳转或二维码场景，
可以按下面方式传入：

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

支付确认后，通常可从响应里的 `next_action` 取出支付链接：

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

SDK 也会额外补充便捷字段，业务里可以直接读取：

```php
$payUrl = $result->get('pay_url');
$paymentIntentId = $result->get('payment_intent_id');
$nextActionType = $result->get('next_action_type');
```

## 查询

```php
$result = Pay::airwallex()->query([
    'payment_intent_id' => 'int_xxx',
]);
```

查询退款：

```php
$result = Pay::airwallex()->query([
    '_action' => 'refund',
    'refund_id' => 'ref_xxx',
]);
```

## 退款

```php
$result = Pay::airwallex()->refund([
    'payment_intent_id' => 'int_xxx',
    'amount' => 10,
]);
```

## 取消

```php
$result = Pay::airwallex()->cancel([
    'payment_intent_id' => 'int_xxx',
]);
```

## 接收回调

```php
$result = Pay::airwallex()->callback();

return Pay::airwallex()->success();
```
