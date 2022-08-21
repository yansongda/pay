# 查询订单

| 方法名  |         参数          |    返回值     |
|:----:|:-------------------:|:----------:|
| find | string/array $order | Collection |

## 查询普通支付订单

```php
Pay::config($this->config);

$order = [
    'out_trade_no' => '1514027114',
];
// $order = '1514027114';

$result = Pay::alipay()->find($order);
```

## 查询退款订单

```php
Pay::config($this->config);

$order = [
    'out_trade_no' => '1514027114',
    'out_request_no' => '1514027114',
    '_type' => 'refund',
];

$result = Pay::alipay()->find($order);
```

## 查询转账订单

```php
Pay::config($this->config);

$order = [
    'out_trade_no' => '1514027114',
    '_type' => 'transfer'
];

$result = Pay::alipay()->find($order);
```

## 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下地址，查看「请求参数」一节。

- 支付订单：[这里](https://opendocs.alipay.com/apis/api_1/alipay.trade.query)
- 退款订单：[这里](https://opendocs.alipay.com/apis/api_1/alipay.trade.fastpay.refund.query)
- 转账订单：[这里](https://opendocs.alipay.com/apis/api_28/alipay.fund.trans.order.query)
    
