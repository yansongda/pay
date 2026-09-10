---
feature: action-enum
status: delivered
updated: 2026-09-10
branch: feat/action-enum
commits: 9fdd0f34..b9936646
---

# `_action` 统一为 string-backed enum + match 分发

## Report

**What was built** — 在 `src/Action/` 新增 28 个按功能/入口拆分的 string-backed enum，作为全库 `_action` 的单一事实来源（取值统一 snake_case）。Provider（Alipay callback、Wechat callback/success）与全部读取 `_action` 的 Shortcut 改为 `tryFrom` + `match($enum)`，删除 `Str::camel` 动态方法分发。破坏性变更：payscore 的 `permissionsQuery`/`permissionsTerminate` 硬切为 `permissions_query`/`permissions_terminate`；PayScore 保留显式 `default` 别名（与 `create` 同管道）。测试、payscore 文档与 CHANGELOG 已同步。

**Verification** — 容器镜像 `registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.5-alpine`：
- `php vendor/bin/phpstan analyse -l 6 ./src` → PASS
- `php vendor/bin/phpunit -c phpunit.xml` → PASS（1634 tests, 4075 assertions）
- `php vendor/bin/php-cs-fixer fix --dry-run` → PASS（0 files）

**Journey log** —
1. 本地无 PHP：按 `AGENTS.md` → `dev-guide` → `container-dev`，用 Apple Container 跑验证，不再在 PATH 上空转找 php。
2. 评审 major「Wechat callback 未知 `_action` 未抛错」判定为 **不成立**：改造前 `=== 'virtual' ? VirtualCallback : Callback` 对任意非 virtual 值本就回落 CallbackPlugin，属保留行为；Alipay 因历史就对非法值抛错而保持抛错。
3. PayScore 原 `defaultPlugins` 可被显式 `'_action'=>'default'` 命中，清单补 `Default=default` 并与 `Create` 同臂，否则单测 `testDefaultAction` 回归。
4. 异常消息统一为 `($params['_action'] ?? '')`，避免缺键时 PHP 8 告警。

## [S1] Problem

`_action` 在各 Provider / Shortcut 上以字符串字面量散落在 `match` 与 `Str::camel(...).'Plugins'` 动态分发中，没有统一定义。结果是：

1. **总清单不可见**：无法一眼看出全局支持哪些 `_action`、归属哪个入口。
2. **功能内清单不可见**：新增/修改只能靠翻方法名或测试反推。
3. **取值风格不统一**：多数为 snake_case，但存在 `permissionsQuery` / `permissionsTerminate` 等 camel 值。
4. **动态方法匹配脆弱**：`Str::camel` + `method_exists` 依赖方法名大小写与字符串形态（如 `h5`/`H5Plugins`、`pre_auth` 顺带兼容 `preAuth`）。

目标：按功能/入口建立 PHP string-backed enum；**规范值全部 snake_case**；**分发一律先转 enum 再 `match`**，删除动态字符串拼方法名。

## [S2] Design

### 已拍板决策

| 轴 | 决策 |
|---|---|
| 表示形态 | PHP `enum X: string` |
| 拆分粒度 | 按功能/入口（与 Shortcut / Provider 分发点一一对应） |
| 取值规范 | **全部 snake_case**；非法 camel 值硬切不兼容 |
| 分发形态 | **先 `tryFrom` 转 enum，再 `match ($enum)`**；删除 `Str::camel` 动态分发 |
| 替换范围 | 仅 `src/` 分发点；tests 本轮先改到与新值一致（因存在破坏性取值变更） |
| 工作区 | `.worktrees/action-enum` / `feat/action-enum` |
| 调用形态 | 仍是数组字符串：`['_action' => 'permissions_query']`，不支持传 enum 实例 |

### 破坏性变更（必须进升级说明）

| 旧值 | 新值 | 入口 |
|---|---|---|
| `permissionsQuery` | `permissions_query` | `Pay::wechat()->payscore()` |
| `permissionsTerminate` | `permissions_terminate` | `Pay::wechat()->payscore()` |

其它历史“顺带可用”的非规范形态（例如 Unipay 传 `preAuth` 而非 `pre_auth`）**一律不再接受**，`tryFrom` 失败即抛 `PARAMS_SHORTCUT_ACTION_INVALID`。

### 放置与命名

- 目录：`src/Action/`
- 命名空间：`Yansongda\Pay\Action`
- 命名：`{Provider}{Feature}Action` 或 `{Provider}{Entry}Action`
- case 名：PascalCase；`->value`：snake_case

```php
namespace Yansongda\Pay\Action;

enum WechatPayScoreAction: string
{
    case Create = 'create';
    case PermissionsQuery = 'permissions_query';
    case PermissionsTerminate = 'permissions_terminate';
}
```

### 分发契约（统一模板）

```php
$action = XxxAction::tryFrom($params['_action'] ?? XxxAction::Default->value)
    ?? throw new InvalidParamsException(
        Exception::PARAMS_SHORTCUT_ACTION_INVALID,
        "不支持的 _action [{$params['_action']}]",
    );

return match ($action) {
    XxxAction::Default, XxxAction::Order => $this->orderPlugins(),
    XxxAction::Refund => $this->refundPlugins(),
};
```

规则：

1. **缺省值**写在 `tryFrom` 的 `??` 右侧（与现状缺省一致）；无缺省的入口（Oauth）用空串/缺键导致 `tryFrom` 失败并抛错。
2. **非法值**：`tryFrom` 失败立即抛 `Exception::PARAMS_SHORTCUT_ACTION_INVALID`，消息保留用户传入值，便于排查。
3. **不再**出现 `Str::camel($action).'Plugins'` / `method_exists` 分发。
4. 原 `*Plugins()` 私有方法保留为实现细节，由 match 臂显式调用；不强制重命名。
5. Provider 级（Alipay/Wechat callback、Wechat success）同样走 enum + match。

### 全局 `_action` 清单（单一事实来源）

#### Provider 级

| 枚举 | 文件 | 消费点 | cases（value） | 缺省 |
|---|---|---|---|---|
| `AlipayCallbackAction` | `src/Provider/Alipay.php` | `callback()` | `Gw=gw` | 无（缺省走普通 CallbackPlugin，不进枚举） |
| `WechatCallbackAction` | `src/Provider/Wechat.php` | `callback()` | `Virtual=virtual` | 无（缺省 CallbackPlugin） |
| `WechatSuccessAction` | `src/Provider/Wechat.php` | `success()` | `Payscore=payscore`, `Virtual=virtual` | 无（缺省通用成功响应） |

#### Wechat Shortcut

| 枚举 | 消费点 | 缺省 | cases（value） |
|---|---|---|---|
| `WechatPapayAction` | `PapayShortcut` | `default` | `Default=default`, `Order=order`, `Contract=contract`, `Apply=apply` |
| `WechatPayScoreAction` | `PayScoreShortcut` | `create` | `Default=default`, `Create=create`, `Query=query`, `Cancel=cancel`, `Complete=complete`, `Modify=modify`, `Sync=sync`, `Pay=pay`, `Permissions=permissions`, `PermissionsQuery=permissions_query`, `PermissionsTerminate=permissions_terminate` |
| `WechatVirtualAction` | `VirtualShortcut` | `default` | `Default=default`, `OrderQuery=order_query`, `OrderRefund=order_refund`, `OrderStartDownload=order_start_download`, `OrderQueryDownload=order_query_download`, `OrderDownloadBill=order_download_bill`, `OrderNotifyProvideGoods=order_notify_provide_goods`, `CurrencyPay=currency_pay`, `CurrencyCancel=currency_cancel`, `CurrencyQueryBalance=currency_query_balance`, `CurrencyPresent=currency_present`, `GoodsStartUpload=goods_start_upload`, `GoodsQueryUpload=goods_query_upload`, `GoodsStartPublish=goods_start_publish`, `GoodsQueryPublish=goods_query_publish`, `WithdrawCreate=withdraw_create`, `WithdrawQuery=withdraw_query`, `WithdrawQueryBalance=withdraw_query_balance`, `SubscribeSendPrePayment=subscribe_send_pre_payment`, `SubscribeSubmitPayOrder=subscribe_submit_pay_order`, `SubscribeQueryContract=subscribe_query_contract`, `SubscribeCancelContract=subscribe_cancel_contract` |
| `WechatOauthAction` | `OauthShortcut` | **无** | `WebToken=web_token`, `Refresh=refresh`, `Userinfo=userinfo`, `Session=session` |
| `WechatTransferAction` | `TransferShortcut` | `default` | `Default=default`, `Transfer=transfer` |
| `WechatCloseAction` | `CloseShortcut` | `default` | `Default=default`, `App=app`, `H5=h5`, `Jsapi=jsapi`, `Mini=mini`, `Native=native`, `Combine=combine` |
| `WechatRefundAction` | `RefundShortcut` | `default` | `Default=default`, `App=app`, `Combine=combine`, `H5=h5`, `Jsapi=jsapi`, `Mini=mini`, `Native=native` |
| `WechatCancelAction` | `CancelShortcut` | `default` | `Default=default`, `Transfer=transfer` |
| `WechatQueryAction` | `QueryShortcut` | `default` | `Default=default`, `App=app`, `Combine=combine`, `H5=h5`, `Jsapi=jsapi`, `Mini=mini`, `Native=native`, `Transfer=transfer`, `Refund=refund`, `RefundApp=refund_app`, `RefundCombine=refund_combine`, `RefundH5=refund_h5`, `RefundJsapi=refund_jsapi`, `RefundMini=refund_mini`, `RefundNative=refund_native` |

#### Alipay Shortcut

| 枚举 | 消费点 | 缺省 | cases（value） |
|---|---|---|---|
| `AlipayAuthAction` | `AuthShortcut` | `token_app` | `TokenApp=token_app`, `Query=query` |
| `AlipayCloseAction` | `CloseShortcut` | `default` | `Default=default`, `Agreement=agreement`, `App=app`, `Authorization=authorization`, `Mini=mini`, `Pos=pos`, `Scan=scan`, `H5=h5`, `Web=web` |
| `AlipayCancelAction` | `CancelShortcut` | `default` | `Default=default`, `Agreement=agreement`, `Authorization=authorization`, `Mini=mini`, `Pos=pos`, `Scan=scan` |
| `AlipayQueryAction` | `QueryShortcut` | `default` | `Default=default`, `Agreement=agreement`, `App=app`, `Authorization=authorization`, `Face=face`, `Mini=mini`, `Pos=pos`, `Scan=scan`, `H5=h5`, `Web=web`, `Transfer=transfer`, `Refund=refund`, `RefundApp=refund_app`, `RefundAuthorization=refund_authorization`, `RefundMini=refund_mini`, `RefundPos=refund_pos`, `RefundScan=refund_scan`, `RefundH5=refund_h5`, `RefundWeb=refund_web` |
| `AlipayRefundAction` | `RefundShortcut` | `default` | `Default=default`, `Agreement=agreement`, `App=app`, `Authorization=authorization`, `Mini=mini`, `Pos=pos`, `Scan=scan`, `H5=h5`, `Web=web`, `Transfer=transfer` |

#### Douyin / Unipay / Paypal / Stripe / Airwallex

| 枚举 | 消费点 | 缺省 | cases（value） |
|---|---|---|---|
| `DouyinQueryAction` | `Douyin/QueryShortcut` | `default`（→order 插件） | `Default=default`, `Order=order`, `Cps=cps`, `Refund=refund` |
| `DouyinRefundAction` | `Douyin/RefundShortcut` | `default` | `Default=default`, `Audit=audit` |
| `UnipayPosAction` | `Unipay/PosShortcut` | `default` | `Default=default`, `PreAuth=pre_auth`, `Qra=qra` |
| `UnipayScanAction` | `Unipay/ScanShortcut` | `default` | `Default=default`, `PreAuth=pre_auth`, `PreOrder=pre_order`, `Fee=fee` |
| `UnipayQueryAction` | `Unipay/QueryShortcut` | `default` | `Default=default`, `Web=web`, `QrCode=qr_code`, `QraPos=qra_pos`, `QraPosRefund=qra_pos_refund` |
| `UnipayRefundAction` | `Unipay/RefundShortcut` | `default` | `Default=default`, `Web=web`, `QrCode=qr_code`, `QraPos=qra_pos` |
| `UnipayCancelAction` | `Unipay/CancelShortcut` | `default` | `Default=default`, `Web=web`, `QrCode=qr_code`, `QraPos=qra_pos` |
| `PaypalQueryAction` | `Paypal/QueryShortcut` | `default` | `Default=default`, `Order=order`, `Refund=refund` |
| `PaypalWebAction` | `Paypal/WebShortcut` | `default` | `Default=default`, `Pay=pay`, `Capture=capture` |
| `StripeQueryAction` | `Stripe/QueryShortcut` | `default` | `Default=default`, `Order=order`, `Refund=refund` |
| `AirwallexQueryAction` | `Airwallex/QueryShortcut` | `default` | `Default=default`, `Order=order`, `Refund=refund` |

> 未读取 `_action` 的 Shortcut 本轮不新增枚举。

### 特殊语义保留

| 入口 | 现状 | 保留方式 |
|---|---|---|
| Alipay `callback` | `_action` 可从 merge 后数组读取；`null` → CallbackPlugin | match：`null => CallbackPlugin`，`Gw => GatewayCallbackPlugin` |
| Wechat `callback` | `virtual` 换 VirtualCallbackPlugin | match on `WechatCallbackAction\|null` |
| Wechat `success` | `payscore`→204；`virtual`(+`_format`)→虚拟支付成功体 | match on `WechatSuccessAction\|null`；`_format` 仍为原字符串 |
| Oauth | 无缺省，缺 `_action` 即抛 | `tryFrom($params['_action'] ?? '')` 失败抛错 |
| Auth | 缺省 `token_app` | `tryFrom(... ?? AlipayAuthAction::TokenApp->value)` |
| PayScore | 缺省 `create` | `tryFrom(... ?? WechatPayScoreAction::Create->value)` |

### 错误行为

- Shortcut 分发：非法/已废弃值抛 `InvalidParamsException` + `PARAMS_SHORTCUT_ACTION_INVALID`，消息包含用户传入的原始 `_action` 值。
- Alipay `callback`：非法 `_action` 抛错（与改造前一致）。
- Wechat `callback` / `success`：**非 virtual / 非 payscore 的未知值回落默认路径**（CallbackPlugin / 通用成功应答），与改造前 `=== 'virtual' ? ... : default` 语义一致，不抛错。
- 不引入新的异常码。

### 测试与文档

- **tests**：随硬切更新字面量（至少 `permissionsQuery`→`permissions_query`、`permissionsTerminate`→`permissions_terminate`）；非法 action 测试仍断言抛错。
- **web/docs / CHANGELOG**：实现后把 payscore 两处旧值与升级说明改掉；文档站其它已是 snake_case 的示例无需改。若本轮时间不够，文档站可作为紧随任务，但 CHANGELOG 必须在实现提交内记录破坏性变更。

## [S3] Out of Scope

- `_format`、`_type`、`_config` 等其它下划线键
- 调用侧直接传 enum 实例
- 缺省动作语义调整（Oauth 补 default、Auth 缺省改名等）
- 新 Provider / 新 Shortcut
- 保留 `permissionsQuery` 等旧 camel 别名

## Tasks

- [x] T1: 落盘 `src/Action/` 全部 enum，value 全 snake_case 且与清单一致 — acceptance: 枚举可加载，payscore 两枚举值为 `permissions_query`/`permissions_terminate` (covers: S2)
- [x] T2: Provider 级改为 enum+match（Alipay callback / Wechat callback+success）— acceptance: 无 `Str::camel` 分发；非法 `_action` 行为符合 S2 (covers: S2; depends: T1)
- [x] T3: Wechat 全部 Shortcut 改为 tryFrom+match — acceptance: Papay/PayScore/Virtual/Oauth/Transfer/Close/Refund/Cancel/Query 均不再动态拼方法名 (covers: S2; depends: T1)
- [x] T4: Alipay 全部 Shortcut 改为 tryFrom+match — acceptance: Auth/Close/Cancel/Query/Refund 均 match enum；Auth 缺省仍 `token_app` (covers: S2; depends: T1)
- [x] T5: Douyin/Unipay/Paypal/Stripe/Airwallex 改为 tryFrom+match — acceptance: 上述分发点全部 enum match (covers: S2; depends: T1)
- [x] T6: 更新受影响单测字面量与异常断言 — acceptance: `composer test` PASS（容器）(covers: S2; depends: T2,T3,T4,T5)
- [x] T7: CHANGELOG 记录破坏性变更；文档站 payscore 旧值修正 — acceptance: 仓库内不再以文档推荐 `permissionsQuery` (covers: S2; depends: T3)
- [x] T8: 容器内 `composer cs-fix`、`composer analyse`、`composer test` 全绿 — acceptance: 三项 PASS 或仅 PRE-EXISTING (covers: S2; depends: T6)
