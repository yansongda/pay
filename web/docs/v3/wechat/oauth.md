# 微信 OAuth 用户身份

> OAuth 配置请参考 [初始化 - 微信配置](/docs/v3/quick-start/init.md)

微信 OAuth 用户身份能力支持公众号网页授权与小程序登录等场景，用于获取用户身份信息。

|   方法名   |      参数      |    返回值     |
|:--------:|:------------:|:----------:|
|  oauth   | array $payload | Collection |

:::tip 官方文档
- [网页授权 access_token](https://developers.weixin.qq.com/doc/service/api/webdev/access/api_snsaccesstoken.html)
- [刷新 access_token](https://developers.weixin.qq.com/doc/service/api/webdev/access/api_snsrefreshtoken.html)
- [拉取用户信息](https://developers.weixin.qq.com/doc/service/api/webdev/access/api_snsuserinfo.html)
- [小程序登录凭证校验](https://developers.weixin.qq.com/miniprogram/dev/server/API/user-login/api_code2session.html)
:::

### 配置

`mp_app_secret` 与 `mini_app_secret` 均为可选配置，未使用 OAuth 能力的纯支付用户无需配置，零影响。不同 `_action` 所需配置如下：

- `session`：需要 `mini_app_id` + `mini_app_secret`
- `web_token`、`refresh`：需要 `mp_app_id` + `mp_app_secret`
- `userinfo`：无需 secret（凭 `access_token` + `openid`）

:::warning
- **风险提示**：开启 Artful 日志（`logger.enable = true`）时，日志会包含带 secret 的 URL，生产环境注意日志安全
- **凭据与安全**：网页授权 `access_token`（用户级）与基础 `access_token`（`stable_token`，应用级）互相隔离；`unionid` 需绑定微信开放平台；`code` 仅一次有效且 5 分钟过期；`session_key` 请勿下发给前端之外的任何端
:::

### 小程序登录（session）

```php
Pay::config($config);

$result = Pay::wechat()->oauth(['_action' => 'session', 'js_code' => 'xxx']);
// 返回 Collection 实例，包含以下字段：
// $result->openid      - 用户唯一标识
// $result->session_key - 会话密钥，用于数据签名校验与用户敏感数据解密
// $result->unionid     - 用户在微信开放平台的唯一标识（需绑定微信开放平台，未绑定时无该字段）
```

### 网页授权换取 access_token（web_token）

```php
Pay::config($config);

$result = Pay::wechat()->oauth(['_action' => 'web_token', 'code' => 'xxx']);
// 返回 Collection 实例，包含以下字段：
// $result->access_token    - 网页授权接口调用凭证（用户级，与基础 access_token 不同）
// $result->expires_in      - access_token 有效期，单位秒
// $result->refresh_token   - 用于刷新 access_token
// $result->openid          - 用户唯一标识
// $result->unionid         - 用户在微信开放平台的唯一标识（需绑定微信开放平台，且 scope 为 snsapi_userinfo 时返回）
// $result->is_snapshotuser - 是否为快照页模式虚拟账号（仅快照页模式虚拟账号时返回，值为 1）
```

### 拉取用户信息（userinfo）

```php
Pay::config($config);

$result = Pay::wechat()->oauth(['_action' => 'userinfo', 'access_token' => 'xxx', 'openid' => 'xxx']);
// 返回 Collection 实例，包含以下字段：
// $result->openid     - 用户的唯一标识
// $result->nickname   - 用户昵称
// $result->sex        - 用户性别（1 男性，2 女性，0 未知）
// $result->province   - 用户个人资料填写的省份
// $result->city       - 用户个人资料填写的城市
// $result->country    - 国家，如中国为 CN
// $result->headimgurl - 用户头像
// $result->privilege  - 用户特权信息，JSON 数组
// $result->unionid    - 用户在微信开放平台的唯一标识（需绑定微信开放平台，未绑定时无该字段）
```

### 刷新 access_token（refresh）

```php
Pay::config($config);

$result = Pay::wechat()->oauth(['_action' => 'refresh', 'refresh_token' => 'xxx']);
// 返回 Collection 实例，包含以下字段：
// $result->access_token  - 新的网页授权接口调用凭证
// $result->expires_in    - 新的 access_token 有效期，单位秒
// $result->refresh_token - 新的 refresh_token
// $result->openid        - 用户唯一标识
// $result->scope         - 用户授权的作用域，使用逗号（,）分隔
```
