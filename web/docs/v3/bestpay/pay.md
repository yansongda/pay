# 翼支付支付

翼支付目前直接内置支持以下快捷方式支付方法，对应的支付 method 如下：

|  method |      说明      |      参数      |    返回值     |
|:------:|:------------:|:------------:|:----------:|
|  web   | PC 收银台（超级收银台） | array $order | Collection |
|   h5   | 手机收银台 | array $order | Collection |
|  scan  | 聚合收款码 | array $order | Collection |

> 金额单位为「分」，与其他渠道习惯不同，请注意换算。

## PC 收银台（web）

超级收银台（`/pay/tradeCreate`，`tradeChannel=WEBCASHIER`），返回 `result` 内含跳转/收银台地址，自行引导用户完成支付。

### 例子

```php
Pay::config($this->config);

$result = Pay::bestpay()->web([
    'outTradeNo' => '1514027114', // 商户订单号
    'tradeAmt' => '1', // 单位：分
    'ccy' => '156', // 人民币，默认 156
    'subject' => 'yansongda 测试',
    'goodsInfo' => 'yansongda 测试',
    'operator' => '3178033925245778', // 操作员，常与商户号相同
    'mediumType' => 'WIRELESS', // 媒介类型（以商户开通产品联调为准）
    'requestDate' => date('Y-m-d H:i:s'), // 有效范围约 T-1 ~ T+1 天
]);

return $result; // 含 result.payUrl / result.cashierUrl 等字段（以沙箱联调为准）
```

## 手机收银台（h5）

超级收银台（`/pay/tradeCreate`，`tradeChannel=MOBILECASHIER`），参数与 `web` 一致：

```php
Pay::config($this->config);

$result = Pay::bestpay()->h5([
    'outTradeNo' => '1514027114',
    'tradeAmt' => '1',
    'subject' => 'yansongda 测试',
]);

return $result;
```

## 聚合收款码（scan）

线下聚合（1006，`/aggregate/aggregatepay/offline/c2b/payOrder`），返回 `result.codeUrl` 二维码串，自行生成二维码展示给用户扫码：

```php
Pay::config($this->config);

$result = Pay::bestpay()->scan([
    'outTradeNo' => '1514027114',
    'tradeAmt' => '1',
    'subject' => 'yansongda 测试',
    // 字段以商户开通产品为准
]);

return $result->get('result.codeUrl'); // 二维码内容
```

## 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，
`merchantNo`（自动从配置注入，可覆盖）、`institutionType`、`institutionCode`（写入 `commonParams`）、
`sign`、`path`、`BESTPAY_MAPI_VERSION` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，具体字段请以翼支付开发者文档
[render.bestpay.cn/open-developers](https://render.bestpay.cn/open-developers/index.html) 为准（需商户登录），
`tradeCreate` 等接口的精确 `bizContent` 字段以沙箱联调为准。

:::warning
`web` / `h5` / `scan` 下单均为异步类交易，最终结果请以回调（`callback`）或查询（`query`）为准。
:::
