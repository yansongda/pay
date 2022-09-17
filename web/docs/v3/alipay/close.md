# 支付宝关闭订单

|  方法名  |         参数          |    返回值     |
|:-----:|:-------------------:|:----------:|
| close | string/array $order | Collection |

## 关闭订单操作

```php
Pay::config($this->config);

$result = Pay::alipay()->close([
    'out_trade_no' => '1623161325',
]);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/apis/api_1/alipay.trade.close)，查看「请求参数」一栏。
