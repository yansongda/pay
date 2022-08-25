# 微信退款

|  方法名   |      参数      |    返回值     |
|:------:|:------------:|:----------:|
| refund | array $order | Collection |

## 退款操作

```php
$order = [
    'out_trade_no' => '1514192025',
    'out_refund_no' => time(),
    'total_fee' => '1',
    'refund_fee' => '1',
    'refund_desc' => '测试退款haha',
];

$result = $wechat->refund($order);
```


## 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_4)，查看「请求参数」一栏。

### APP/小程序退款

如果您需要退 `APP/小程序` 的订单，请传入参数：`['type' => 'app']`/`['type' => 'miniapp']`



## 返回值

返回 Collection 类型，可以通过 `$collection->xxx` 得到服务器返回的数据。
