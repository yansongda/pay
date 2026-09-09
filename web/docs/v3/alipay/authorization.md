# 支付宝应用授权（ISV）

|  方法名  | 参数  |    返回值     |
|:------:|:---:|:----------:|
|  auth  | array | Collection |

## 前置说明

本文介绍的是 ISV/代理商模式下的**第三方应用授权**，即以你的第三方应用（ISV 应用）身份，引导商户完成授权后，获取**应用级**的 `app_auth_token`，再代商户调用接口。

它与「用户级授权」不同：用户级授权指的是 `Yansongda\Pay\Plugin\Alipay\V2\Member\Authorization` 下的能力（`alipay.system.oauth.token` 等），换取的是某个**用户个人**的 token，用于获取用户信息等场景；而本文的 `app_auth_token` 授权主体是**商户应用**，用于 ISV 代商户发起业务请求（对应官方接口 `alipay.open.auth.token.app` 及 `alipay.open.auth.token.app.query`）。

## 生成授权链接

引导商户（企业管理员）在浏览器中打开授权页，商户确认后，支付宝将带着 `app_auth_code` 重定向到你指定的回调地址。

SDK **不提供**生成授权链接的 helper，请按如下规则自行拼接：

```php
$url = sprintf(
    'https://openauth.alipay.com/oauth2/appToAppAuth.htm?app_id=%s&redirect_uri=%s&state=%s',
    '你的第三方应用 app_id',
    urlencode('https://your.domain/alipay/auth/callback'),
    bin2hex(random_bytes(16)) // state：自定义随机串，用于防 CSRF，建议入库与会话绑定
);
```

| 参数 | 说明 |
|:---:|:---|
| `app_id` | 你（ISV）的第三方应用 app_id |
| `redirect_uri` | 授权完成后支付宝回跳的地址，需 urlencode |
| `state` | 自定义随机串，支付宝会原样回传，用于防 CSRF |

> 授权页域名及参数请以官方文档为准：[第三方应用授权总览](https://opendocs.alipay.com/open/10603/drudqy)

## 接收回调

商户在授权页确认后，支付宝会重定向到 `redirect_uri`，并附带参数：

- `app_auth_code`：应用授权码。**一次性使用，且 24 小时内有效**，拿到后应尽快换取 `app_auth_token`；
- `state`：你在生成授权链接时传入的自定义串，**原样回传**，务必校验其与发起授权时保存的值一致，防止 CSRF。

```php
// redirect_uri 对应的处理逻辑（示意）
$state = $_GET['state'] ?? '';
if ($state !== $stateSavedInSession) {
    throw new UnexpectedValueException('非法的 state');
}

$appAuthCode = $_GET['app_auth_code'] ?? '';
```

## 换取 app_auth_token

拿到 `app_auth_code` 后，调用 `auth` 换取 `app_auth_token`：

```php
$result = Pay::alipay()->auth([
    'grant_type' => 'authorization_code',
    'code' => $appAuthCode,
]);
```

> 缺省时 `auth` 即为换取/刷新令牌（等价于显式传 `'_action' => 'token_app'`）；下文的查询场景则需显式传 `'_action' => 'query'`。

成功响应（返回 `Yansongda\Supports\Collection`，字段以实际响应为准）：

```json
{
    "code": "10000",
    "msg": "Success",
    "app_auth_token": "202509BBxxxxxxxxxxxxxxxx",
    "app_refresh_token": "202509BBxxxxxxxxxxxxxxxx",
    "auth_app_id": "202100xxxxxxxxxxxx",
    "user_id": "2088xxxxxxxxxxxx",
    "expires_in": "31536000",
    "re_expires_in": "32140800"
}
```

| 字段 | 说明 |
|:---:|:---|
| `app_auth_token` | 应用授权令牌，用于代商户调用业务接口 |
| `app_refresh_token` | 刷新令牌，用于刷新 `app_auth_token` |
| `auth_app_id` | 被授权商户的 app_id |
| `user_id` | 被授权商户的 user_id |
| `expires_in` / `re_expires_in` | 有效期相关字段（单位：秒），字段名以实际响应为准 |

**关于有效期，请务必遵循官方口径：**

- `app_auth_token` **长期有效**，**不要**按有效期轮换 `app_auth_token`；
- 需要管理的是 `app_refresh_token` 的刷新窗口：`re_expires_in` 为刷新令牌的有效时间（从接口调用时间起算，单位秒），应在其窗口内使用 `app_refresh_token` 刷新（见下节）；
- 商户解除授权等场景下，`app_auth_token` 会**主动失效**，业务侧需要有兜底：代调用失败时判断业务错误，引导商户重新走一遍授权流程。

## 刷新

`app_refresh_token` 的刷新窗口内，刷新令牌：

```php
$result = Pay::alipay()->auth([
    'grant_type' => 'refresh_token',
    'refresh_token' => $appRefreshToken,
]);
```

刷新成功后，响应中会返回新的 `app_auth_token` 与 `app_refresh_token`（字段以实际响应为准），请注意更新你存储的值。

## 查询授权信息

查询商户的授权信息，例如授权商户的 `auth_app_id`、`user_id` 及授权了哪些接口（`auth_methods`）等（字段以实际响应为准）：

```php
$result = Pay::alipay()->auth([
    '_action' => 'query',
    'app_auth_token' => $appAuthToken,
]);
```

## 代商户发起调用

拿到 `app_auth_token` 后即可代商户调用业务接口，两种方式：

**1. 全局配置**：如果你只服务固定的单个商户，可直接在 config 中配置 `app_auth_token`，所有请求自动携带：

```php
$config = [
    // ... 其他配置
    'app_auth_token' => '202509BBxxxxxxxxxxxxxxxx',
];
```

**2. 按请求传入**：多商户场景请通过 `_app_auth_token` 参数按请求传入（不同商户使用各自的 token）：

```php
$result = Pay::alipay()->scan([
    '_app_auth_token' => $appAuthToken,
    'out_trade_no' => date('YmdHis') . mt_rand(1000, 9999),
    'total_amount' => '0.01',
    'subject' => 'test subject - 测试',
]);
```

## 注意

- `app_auth_code`、`app_auth_token`、`app_refresh_token` 均属敏感信息，**不得**明文入库、**不得**打印到日志；
- token 的存储与刷新由业务方自行负责，SDK 仅提供发起换取/刷新/查询请求的能力，不会自动刷新；
- 失败不一定抛出异常：未携带签名的错误响应，SDK 会抛出 `InvalidResponseException`；而**带签名的业务错误响应在验签通过后，会按 `Collection` 原样返回**（例如商户已解除授权等场景），业务代码需**自行判断响应中的 `code` 字段**是否为 `10000`，再进行兜底处理。
