# Apple 支付

Apple 目前直接内置支持以下快捷方式支付方法，对应的支付 method 如下：

| 方法名 | 说明 | 参数 | 返回值 |
|:---:|:---:|:---:|:---:|
| payToken | 支付令牌验签解密 | array $params | Collection |
| merchantSession | Apple Pay 网页商户验证 | array $params | Collection |

## payToken

### 例子

```php
Pay::config($this->config);

// 注意：入参必须是数组形态，token 值支持 JSON 字符串 / 数组 / Collection
$result = Pay::apple()->payToken(['token' => $token]);
```

### 参数说明

`token` 为 Apple 支付令牌，其结构如下：

| 字段 | 说明 |
|:---:|:---:|
| data | base64 编码的加密卡数据 |
| signature | base64 编码的 PKCS#7 签名 |
| version | 令牌版本（EC_v1 / RSA_v1） |
| header | 令牌头（ephemeralPublicKey、publicKeyHash、transactionId、applicationData 等） |

### 返回值

验签解密成功后返回明文卡数据，主要字段如下：

| 字段 | 说明 |
|:---:|:---:|
| applicationPrimaryAccountNumber | DPAN 设备卡号 |
| applicationExpirationDate | 卡有效期（YYMM） |
| currencyCode | 货币代码 |
| transactionAmount | 交易金额 |

:::warning
该能力为服务端本地验签解密（PKCS#7 证书链 + ECDH + AES-256-GCM），未与真实 Apple 设备联调验证，请在生产环境使用前自行充分测试。
:::

## merchantSession

### 例子

```php
Pay::config($this->config);

$result = Pay::apple()->merchantSession([
    'validation_url' => $url, // 前端 onvalidatemerchant 事件获取的一次性 URL（5 分钟过期）
    'initiative_context' => 'shop.yansongda.cn', // 必填：Apple Pay 网页注册的商户域名
    'display_name' => '示例商户', // 选填
    '_http' => [
        'cert' => ['/path/to/cert.pem', 'passphrase'], // TLS 双向认证证书透传
    ],
]);
```

:::warning
debug 日志会记录 payload 全量（含 `_http` 中的证书口令），生产环境请注意日志级别。
:::
