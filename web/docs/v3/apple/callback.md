# 接收 Apple 回调

| 方法名 | 参数 | 返回值 |
|:---:|:---:|:---:|
| callback | 无/array/ServerRequestInterface | Collection |

## 例子

```php
Pay::config($this->config);

// 是的，你没有看错，就是这么简单！
$result = Pay::apple()->callback();
```

## 参数

### 第一个参数

#### null

如果您没有传参，或传 `null`，则 `yansongda/pay` 会自动识别请求并处理，通过 `Collection` 实例返回 App Store Server Notifications V2 通知参数

:::warning
建议仅在 php-fpm 下使用，swoole 方式请使用 `ServerRequestInterface` 参数传递方式
:::

#### ServerRequestInterface

推荐在 swoole 环境下传递此参数，传递此参数后，yansongda/pay 会自动进行后续处理

#### array

也可以自行解析请求参数（如 `['signedPayload' => '...']`），传递一个 array 会自动进行后续处理

## 通知类型

回调通知为 App Store Server Notifications V2（body 为 `{"signedPayload": "<JWS>"}`），验签解码后可通过 `notificationType` 字段区分通知类型，常见类型如下：

- `REFUND`：退款
- `REFUND_DECLINED`：退款被拒绝
- `CONSUMPTION_REQUEST`：消费请求
- `SUBSCRIBED`：订阅成功
- `DID_CHANGE_RENEWAL_STATUS`：续订状态变更
- `EXPIRED`：订阅过期
- `REVOKED`：订阅被撤销

更多类型与字段说明请参考 [官方文档](https://developer.apple.com/documentation/appstoreservernotifications/responsebodyv2decodedpayload)。

## 幂等处理

通知可能重复投递，建议业务层以 `notificationUUID` 为主键做幂等去重（SDK 原样返回该字段，不做持久化）。

## 归属校验

验签通过后，SDK 会自动进行归属校验（对齐 Apple 官方库行为，防「真实 Apple 签名但错误归属」的伪造通知）：

- 若配置了 `bundle_id`，则通知内的 `bundleId` 必须与之一致，否则抛出验签异常
- 通知内的 `environment` 必须与 `mode` 匹配（`MODE_SANDBOX` 对应 `Sandbox`，`MODE_NORMAL`/`MODE_SERVICE` 对应 `Production`），否则抛出验签异常；TestFlight（`Xcode`）与本地 StoreKit 测试（`LocalTesting`）的通知会被拒绝

未配置 App Store Server API 四件套（`issuer_id` 等）的租户会跳过 `bundleId` 校验，但 `environment` 校验始终执行。

## 确认回调

```php
Pay::config($this->config);

return Pay::apple()->success();
```
