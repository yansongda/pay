# 支持的支付方法

支付宝支付目前支持 6 中支付方法，对应的支付 method 如下：

| method | 说明 |
| :---: | :---: |
| web | 电脑支付 |
| wap | 手机网站支付 |
| app | APP 支付 |
| pos | 刷卡支付 |
| scan | 扫码支付 |
| transfer | 账户转账 |

# 使用方法

## 一、电脑支付

### 0、 例子

```php
$order = [
    'out_trade_no' => time(),
    'total_amount' => '0.01',
    'subject'      => 'test subject-测试订单',
];

return $alipay->web($order)->send(); // laravel 框架中请直接 return $alipay->web($order)
```

### 1、 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://docs.open.alipay.com/270/alipay.trade.page.pay)，查看「请求参数一栏」。

# 返回值



# 异常





