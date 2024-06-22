# 江苏银行e融支付查询订单(退款订单 or 交易支付订单)

|  方法名  |      参数      |    返回值     |
|:-----:|:------------:|:----------:|
| query | array $order | Collection |

## 查询交易支付订单

```php
Pay::config($this->config);

//查询交易支付订单
$order = [
    'outTradeNo' => '1514027114',
];
$result = Pay::jsb()->query($order);
```

## 查询退款订单

```php
Pay::config($this->config);

//查询退款单号查询退款订单
$order = [
    'outTradeNo' => 'RK-1514027114',
];

$result = Pay::jsb()->query($order);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考文档。
