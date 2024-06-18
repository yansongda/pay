# 支付宝退款

|  方法名   |      参数      |    返回值     |
|:------:|:------------:|:----------:|
| refund | array $order | Collection |

:::warning 注意
更多功能请查看源码，文档里仅仅列举了一些常用的功能。
:::

## 普通退款操作

```php
Pay::config($this->config);

$result = Pay::epay()->refund([
    'outTradeNo' => 'YC202406170004',
    'refundAmt' => 0.01,
    'outRefundNo' => 'TK-YC202406170004',
]);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考支付文档。
