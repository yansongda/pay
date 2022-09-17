# 支付宝退款

|  方法名   |      参数      |    返回值     |
|:------:|:------------:|:----------:|
| refund | array $order | Collection |

## 普通退款操作

```php
Pay::config($this->config);

$result = Pay::alipay()->refund([
    'out_trade_no' => '1623160012',
    'refund_amount' => '0.01',
]);
```

### 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/apis/api_1/alipay.trade.refund)，查看「请求参数」一栏。
