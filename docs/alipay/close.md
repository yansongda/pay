# 说明

| 方法名 | 参数 | 返回值 |
| :---: | :---: | :---: |
| close | string/array $order | Collection |

# 使用方法

## 例子

```php
$order = [
    'out_trade_no' => '1514027114',
];

// $order = '1514027114';

$result = $alipay->close($order);
```

## 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://docs.open.alipay.com/api_1/alipay.trade.close/)，查看「请求参数」一栏。

# 返回值

返回 Collection 类型，可以通过 `$collection->xxx` 得到服务器返回的数据。

# 异常

* Hanwenbo\Pay\Exceptions\InvalidSignException ，表示验签失败。
* Hanwenbo\Pay\Exceptions\GatewayException ，表示支付宝服务器返回的数据非正常结果，例如，参数错误等。
* Hanwenbo\Pay\Exceptions\InvalidConfigException ，表示缺少配置参数，如，`ali_public_key`, `private_key` 等



