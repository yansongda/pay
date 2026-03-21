# PayPal 支付

:::tip
PayPal 的实现由 GitHub Copilot 生成
:::

PayPal 支付目前直接内置支持以下快捷方式支付方法，对应的支付 method 如下：

|  method  |   说明    |      参数      |    返回值     |
|:--------:|:-------:|:------------:|:----------:|
|   web    | Web 下单  | array $order | Collection |

## Web 支付

PayPal Web 支付分为两步：**创建订单**和**捕获订单**。

### 创建订单

用户在商户侧发起支付后，先调用此接口创建订单，然后将用户重定向至 PayPal 授权页面。

#### 例子

```php
Pay::config($config);

$order = [
    'purchase_units' => [
        [
            'amount' => [
                'currency_code' => 'USD',
                'value' => '10.00',
            ],
            'description' => 'yansongda/pay - test order',
        ],
    ],
    // 「选填」以下参数覆盖配置文件中的 return_url / cancel_url
    // 'return_url' => 'https://example.com/paypal/return',
    // 'cancel_url' => 'https://example.com/paypal/cancel',
];

$result = Pay::paypal()->web($order);

// 从 links 中获取用户授权跳转地址
$approveUrl = '';
foreach ($result->get('links', []) as $link) {
    if ('approve' === ($link['rel'] ?? '')) {
        $approveUrl = $link['href'];
        break;
    }
}

// 将用户重定向至 $approveUrl
```

#### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`_access_token` 等参数，大家只需传入订单类主观参数即可。

如果您需要从外部传入 `access_token`（例如自行管理令牌），可以在订单参数中传入 `_access_token`：

```php
$order = [
    '_access_token' => 'your-external-access-token',
    'purchase_units' => [
        // ...
    ],
];
```

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://developer.paypal.com/docs/api/orders/v2/#orders_create)，查看「请求参数」一栏。

### 捕获订单

用户在 PayPal 授权页面完成支付后，会跳转回 `return_url`，此时需要调用捕获接口完成扣款。

#### 例子

```php
Pay::config($config);

// $orderId 从 return_url 的 query 参数 `token` 中获取
$result = Pay::paypal()->web([
    '_action' => 'capture',
    'order_id' => $orderId,
]);
```
