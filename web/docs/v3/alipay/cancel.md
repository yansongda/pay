# 支付宝取消订单

|  方法名   |         参数          |    返回值     |
|:------:|:-------------------:|:----------:|
| cancel | string/array $order | Collection |

## 取消订单操作

```php
Pay::config($this->config);

$order = [
    'out_trade_no' => '1514027114',
];
// $order = '1514027114';

$result = Pay::alipay()->cancel($order);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/apis/api_1/alipay.trade.cancel)，查看「请求参数」一栏。
