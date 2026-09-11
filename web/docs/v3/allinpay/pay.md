# 通联支付

通联支付目前直接内置支持以下快捷方式支付方法，对应的支付 method 如下：

|   method    |     说明     |      参数      |    返回值     |
|:---------:|:----------:|:------------:|:----------:|
|  unified   |   统一支付   | array $order | Collection |
|   scan     | 被扫支付（付款码） | array $order | Collection |
|   native   | 主扫支付（收款码） | array $order | Collection |

更多接口调用请参考后续文档

## 统一支付

统一支付（`/unitorder/pay`）支持微信、支付宝、数字人民币、云闪付等多渠道，
通过 `paytype` 区分（如 `W02` 微信 JS 支付、`A02` 支付宝 JS 支付、`W06` 微信小程序、
`U02` 云闪付 JS 等，详见官方文档附录），`acct`（openid/user_id）在 JS 支付时必填。

### 例子

```php
Pay::config($this->config);

$result = Pay::allinpay()->unified([
    'reqsn'   => 'order-1001',
    'trxamt'  => 1, // 单位：分
    'paytype' => 'W02',
    'acct'    => '用户的 openid', // JS 支付时必填
    'body'    => '测试商品',
]);

return $result->payinfo; // 支付串（二维码串 / js 参数 / 跳转链接，因 paytype 而异）
```

## 被扫支付

### 例子

```php
Pay::config($this->config);

$result = Pay::allinpay()->scan([
    'reqsn'    => 'order-1002',
    'trxamt'   => 1,
    'authcode' => '134567890123456789', // 用户付款码
    'terminfo' => ['devicetype' => '11', 'termno' => '00000001'], // 数组或 json 字符串均可
]);

return $result->trxstatus; // 刷卡支付时为实际支付结果；2000 时建议间隔 10 秒轮询查询
```

## 主扫支付

### 例子

```php
Pay::config($this->config);

$result = Pay::allinpay()->native([
    'reqsn'      => 'order-1003',
    'trxamt'     => 1,
    'expiretime' => '20991231235959', // yyyyMMddHHmmss 格式的绝对时间
]);

return $result->payinfo; // 含订单信息的二维码串，自行生成二维码展示给用户扫码
```

:::warning
主扫下单后用户未扫码前平台并未生成订单，此时交易查询返回 `trxstatus=1001`（交易不存在）；
交易结果建议使用 `queryConfirm`（交易确认查询）接口确认。
:::

## 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，
`cusid`、`appid`、`signtype`、`randomstr`、`version`、`sign` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://prodoc.allinpay.com/doc/256/)，查看「请求参数」一栏。

:::tip
复合参数（`terminfo`、`extendparams`、`benefitdetail` 等）官方契约为 json 字符串，
直接传 array 或 json 字符串均可，扩展包会自动处理并保证签名一致。
:::
