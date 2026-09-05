# 支付宝 V3 API

`yansongda/pay` 现已支持支付宝 **OpenAPI V3**：RESTful 风格（`/v3/` 路径）、JSON 报文、HTTP 头签名（`ALIPAY-SHA256withRSA`）。

SDK 采用 **接口级自动分流**：调用某个接口时，如果 SDK 已实现该接口的 V3 版本，则自动使用 V3 最新版 API；否则自动回落 V2（网关签名 + form 表单）。**无需任何版本配置**，调用代码不变：

- `pos`、`scan`、`query`、`refund`、`cancel`、`close` 六个服务端接口自动走 V3（`https://openapi.alipay.com/v3/...`，`mode` 为沙箱时自动切换 V3 沙箱网关）；
- `web`、`h5`、`app`、`mini`、`transfer` 等其余接口自动走 V2，行为完全不变。

本文档为 V3 API 的用法说明。V2 API 文档请见 [支付宝](/docs/v3/alipay/pay.md)。

## 支持的接口

V3 支持以下服务端接口（与 V2 同名方法，调用方式不变）：

|  method  |     说明      |      参数      |    返回值    |
|:--------:|:-----------:|:------------:|:----------:|
|   pos    | 付款码支付（被扫码） | array $order | Collection |
|   scan   | 扫码支付（预创建，主动扫） | array $order | Collection |
|  query   |    交易查询     | array $order | Collection |
|  refund  |    交易退款     | array $order | Collection |
|  cancel  |    交易撤销     | array $order | Collection |
|  close   |    交易关闭     | array $order | Collection |

:::tip
页面类接口 `web` / `h5` / `app` / `mini` 与 `transfer` 由 SDK 自动回落 V2 管道处理，无需任何额外配置。
:::

## 配置说明

V2 与 V3 **共用一套租户配置**（单一 `AlipayConfig`），且 V3 仅支持**证书模式**（与 V2 相同的证书体系）：

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| `app_id` | string，必填 | 支付宝分配的 app_id |
| `app_secret_cert` | string，必填 | 应用私钥（字符串或路径），V2/V3 请求签名共用 |
| `app_public_cert_path` | string，必填 | 应用公钥证书路径，V2/V3 计算 `app_cert_sn` 共用 |
| `alipay_public_cert_path` | string，必填 | 支付宝公钥证书路径，V2/V3 验签共用 |
| `alipay_root_cert_path` | string，V2 必填 | 支付宝根证书路径；仅 V2 计算 `root_cert_sn` 使用（V3 协议无 `root-cert-sn`，不需要） |
| `notify_url` 等其余字段 | 选填 | 含义与 V2 一致，请参考 [初始化](/docs/v3/quick-start/init.md) |

```php
$config = [
    'alipay' => [
        'default' => [
            'app_id' => '2021001178660000',
            // 「必填」应用私钥 字符串或路径
            'app_secret_cert' => 'MIIEvAIBADANBgkqhkiG9w0BAQEFAA...',
            // 「必填」应用公钥证书 路径
            'app_public_cert_path' => '/Users/yansongda/pay/cert/appCertPublicKey_2021001178660000.crt',
            // 「必填」支付宝公钥证书 路径
            'alipay_public_cert_path' => '/Users/yansongda/pay/cert/alipayCertPublicKey_RSA2.crt',
            // 「选填」支付宝根证书 路径（仅 V2 管道使用；V3 无需）
            'alipay_root_cert_path' => '/Users/yansongda/pay/cert/alipayRootCert.crt',
            // 「选填」异步通知地址
            'notify_url' => 'https://yansongda.cn/alipay/notify',
        ],
    ],
];
```

:::warning
当前版本 V3 **不支持公钥模式**（`alipay_public_key` 字符串），也暂不支持 AES 加解密（`encrypt_key`）。请勿为应用开通接口加密能力（或在配置中添加 `encrypt_key`），否则异步通知中的业务字段为密文，SDK 无法自动解密。上述能力将在后续版本按需支持。
:::

## 自行实现验签相关逻辑的注意点

如果您需要自行实现与 V3 签名/验签相关的逻辑（例如自行处理同步响应验签），请注意：支付宝 V3 的签名时间戳（`Authorization` 中的 `timestamp`、同步响应头 `alipay-timestamp`）为**毫秒级（13 位）**，与微信 V3 的秒级时间戳不同。

## 升级注意

如果您从旧版本升级，并曾配置过 `version` 或 `alipay_public_key` 字段，请按以下说明处理：

- `version` 配置项已**移除**，配置数组中遗留的 `version` 键不再生效（可自行清理）；
- `alipay_public_key` 配置项已**移除**，V3 仅支持证书模式：请为 V3 商户配置 `app_public_cert_path` 与 `alipay_public_cert_path`（均可在支付宝开放平台下载）；
- `AlipayV2Config`/`AlipayV3Config` 配置类已合并为单一 `AlipayConfig`（`Yansongda\Pay\Config\AlipayConfig`）；
- `pos`/`scan`/`query`/`refund`/`cancel`/`close` 的行为变化为走 V3 管道：返回 `Collection` 字段与支付宝官方 V3 接口响应体一致（与 V2 响应字段可能存在差异），请核对业务代码中依赖的响应字段；
- 沙箱模式下六接口使用 V3 沙箱网关（与 V2 沙箱域名不同）；
- `alipay_root_cert_path` 从配置构造必填改为 V2 管道调用时校验，仅使用 V3 接口的租户可不配置。
