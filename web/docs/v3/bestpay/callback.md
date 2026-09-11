# 接收翼支付回调

|   方法名    |               参数               |    返回值     |
|:--------:|:------------------------------:|:----------:|
| callback | 无/array/ServerRequestInterface | Collection |

## 例子

```php
Pay::config($this->config);

// 是的，你没有看错，就是这么简单！
$result = Pay::bestpay()->callback();
```

## 参数

### 第一个参数

#### `null`

如果您没有传参，或传 `null` 则 `yansongda/pay` 会自动识别翼支付的回调请求并处理，
通过 `Collection` 实例返回翼支付的处理参数

:::warning
建议仅在 php-fpm 下使用，swoole 方式请使用 `ServerRequestInterface` 参数传递方式
:::

#### `ServerRequestInterface`

推荐在 swoole 环境下传递此参数，传递此参数后， yansongda/pay 会自动进行后续处理

#### `array`

也可以自行解析请求参数，传递一个 array 会自动进行后续处理

### 第二个参数

第二个参数主要是传递相关自定义变量的，类似于 `pay()` 中的 `_config` 等参数。

例如，如果你想在回调的时候使用非默认配置，则可以 `Pay::bestpay()->callback(null, ['_config' => 'yansongda'])` 切换为 `yansongda` 这个租户的配置信息。

:::tip
`yansongda/pay` 收到翼支付回调后会自动使用平台公钥验签（SHA1withRSA / SHA256withRSA 均尝试），
验签失败会抛出 `InvalidSignException` 异常。
:::

## 返回参数

|    字段     |      说明      |       取值       |
|:---------:|:------------:|:--------------:|
| notifyType | 通知类型 | PAY / REFUND |
| tradeStatus | 交易状态 | SUCCESS / FAIL / NOTPAY / CLOSE |
| outTradeNo | 商户订单号 | - |
| tradeNo | 翼支付交易号 | - |
| totalAmt | 交易金额（分） | - |

具体字段以翼支付开发者文档 `aggregatePayOrRefundNotify` 契约为准（需商户登录），
建议业务处理时同时校验 `outTradeNo` 与金额，并处理重复通知（幂等）。
