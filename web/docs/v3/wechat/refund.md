# 微信退款

|  方法名   |      参数      |    返回值     |
|:------:|:------------:|:----------:|
| refund | array $order | Collection |

## 退款操作

```php
Pay::config($config);

$order = [
    'out_trade_no' => '1514192025',
    'out_refund_no' => time(),
    'amount' => [
        'refund' => 1,
        'total' => 1,
        'currency' => 'CNY',
    ],
];

$result = Pay::wechat()->refund($order);
```
 
### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_9.shtml)，查看「请求参数」一栏。

