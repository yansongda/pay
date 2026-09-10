---
feature: bestpay
status: designed
updated: 2026-03-25
branch: feat/bestpay-provider
commits: 9fdd0f34..453e9751 # 首版 Spec；调研增量待二次提交回填
---

# 翼支付（BestPay）Provider 调研与设计

对应 Issue：[yansongda/pay#1030](https://github.com/yansongda/pay/issues/1030)

本轮边界：**只调研与设计，不写业务代码**。公开文档接口清单与回调/公共参数契约已核验；下单请求字段与数字信封细节仍需商户侧文档或 Java demo（见 [S4]）。

## Report

（交付时填写）

## [S1] Problem

- 用户希望 yansongda/pay 支持中国电信翼支付（BestPay），Issue 指向官方开发者平台：  
  <https://render.bestpay.cn/open-developers/index.html#/documentCenterLayout/accessProcess>
- 官网仅提供 Java demo，文档站为 SPA，社区几乎无可用 PHP SDK。
- 仓库内已有远端分支 `origin/copilot/add-yipay-support`（+1458 行），但基于 **旧架构**（`Functions.php` + 数组配置），与当前 master 的 Typed Config / Trait / `Pay::PROVIDER_*` 常量体系不一致，**不能直接合入**。
- 调研已通过平台文档 API（`/gapi/telecomPortal/getApiDocument*`）拿到 **产品 1008 超级收银台 / 1006 线下聚合** 的接口目录、公共请求/响应参数、回调报文与状态机；交易接口的完整请求字段表仍缺（文档列表项 `requestParams` 为空，需登录文档中心或 Java demo）。

## [S2] Design

### [S2.1] Provider 命名与入口

| 项 | 取值 | 说明 |
|---|---|---|
| Provider key | `bestpay` | 与 `Pay::bestpay()`、`Pay::PROVIDER_BESTPAY` 一致 |
| 中文名 | 翼支付 / 中国电信翼支付 | Issue 用语 |
| 配置类 | `BestpayConfig` | 继承 `AbstractConfig` |
| 常量 | `Pay::PROVIDER_BESTPAY = 'bestpay'` | 注册进 `Config::PROVIDERS`、`Pay::$providers` |
| Service | `BestpayServiceProvider` | 继承 `AbstractServiceProvider`，`getProviderName()` 返回常量 |
| 事件名 | `'bestpay'` | `MethodCalled` / `CallbackReceived` 第一参数 |

> 不用 `yipay` 作为 key：易与「易支付」聚合混淆；分支名可保留历史，代码侧统一 `bestpay`。

### [S2.2] API 代际结论（调研核心）

公开证据表明翼支付商户开放接口至少存在 **两代**：

#### 代际 A —— 旧版商户 HTTP 接口（Copilot 分支所写）

来源：`origin/copilot/add-yipay-support` 代码本身（非官方确认）。

| 项 | 值 |
|---|---|
| Base URL | `https://api.bestpay.com.cn/` / `https://sandbox.bestpay.com.cn/` |
| 方法 | 业务接口均 POST |
| 路径 | `pay/cashierPay`、`pay/createQrPay`、`pay/queryPayOrder`、`refund/applyRefund` |
| 签名 | `signType=MD5`；过滤空值/`sign`/`signType` → `ksort` → `http_build_query` + `&key={app_key}` → `strtolower(md5(...))` |
| 配置 | `merchant_no`、`platform`（测试样例为 `HELIPAY`）、`app_key`、`notify_url`、`return_url` |
| 成功码 | `returnCode === '0000'`，`returnMsg` 为文案 |
| 回调 | JSON body，字段含 `merchantNo`/`platform`/`tradeOrder`/`totalAmount`/`sign` |

风险：GitHub 全网检索 `createQrPay` + bestpay **零结果**；Packagist 无翼支付包；该代端点与算法**无法独立交叉验证**。可能对应部分历史商户通道，也可能不完整/不准确。

#### 代际 B —— 现行开发者平台 MAPI（Issue 链接所指，目标实现）

来源：SPA 前端静态提取 + 网关探活 + **官方文档 API 无鉴权可读部分**（2026-03-25）。

**网关**

| 项 | 值 |
|---|---|
| 业务网关 | `https://mapi.bestpay.com.cn/mapi`（GET 405，需 POST JSON） |
| 平台网关 | `https://mapi.bestpay.com.cn/gapi/mapi-gateway` |
| 文件服务 | `https://mapi-file.bestpay.com.cn/mapi-file` |
| 文档 API | `GET/POST /gapi/telecomPortal/getApiDocument?productCode={id}`、`?auxiliaryCode={docKey}` |

**公共请求参数（官方《公共请求/响应参数》auxiliaryCode=PublicParameters1007）**

| 字段 | 类型 | 必填 | 说明 |
|---|---|---|---|
| `signType` | String | M | CA 证书固定 `S002`；国密证书 `S008` |
| `signMerchantNo` | String | C | 加签 CA 证书对应商户号 |
| `institutionCode` | String | C | 与 `signMerchantNo` 一致 |
| `sign` | String | M | 签名值（数字信封 / 证书验签，**非** Copilot 分支的 MD5+app_key） |

**公共响应参数**

| 字段 | 类型 | 说明 |
|---|---|---|
| `success` | Boolean | 通讯处理结果 |
| `result` | Object | 业务载荷；`success=false` 时为空 |
| `errorCode` | String(10) | 失败错误码（如 `API100001` 签名认证失败、`API500009` 数字信封解密失败） |
| `errorMsg` | String(1024) | 错误描述 |
| `sign` | String | 返回结果签名，用于验签 |

**产品 1008「超级收银台」接口目录（官方，已核验）**

| API 路径 | 名称 | 一期映射 |
|---|---|---|
| `/pay/tradeCreate` | 聚合统一下单 | P0 支付（web/h5/app 共用，场景参数区分） |
| `/integrate/orderQuery` | 订单查询 | P0 `query()` |
| `/integrate/refund` | 订单退款 | P0 `refund()` |
| `/integrate/refundQuery` | 退款查询 | P1 |
| `/pay/closeOrder` | 订单关闭 | P0 `close()` |
| `/integrate/splitDelayedConfirm` | 担保交易确认/取消 | 二期 |
| `/integrate/splitDelayedQuery` | 担保交易确认/取消查询 | 二期 |
| 文档：`aggregatePayOrRefundNotify` | 支付/回调 | P0 `callback()` |
| 文档：`aggregateGetOpenid` | 获取用户 openid | P1（小程序/公众号） |
| 文档：`PublicParameters1007` / `symbolDefinition` / `aggregateRemark*` | 公共参数、枚举、附录 | 实现参考 |

**产品 1006「线下聚合」接口目录（官方，已核验）**

| API 路径 | 名称 | 一期映射 |
|---|---|---|
| `/aggregate/aggregatepay/offline/c2b/payOrder` | 聚合收款码下单 | P0 `scan()`（被扫/主扫码单） |
| `/aggregate/aggregatepay/offline/b2c/pay` | 聚合付款码下单 | P1 `micropay()`（需终端报备） |
| `/aggregate/aggregatepay/offline/refund` | 退款 | 可与 1008 退款并存，按签约产品选用 |
| `/aggregate/aggregatepay/tradeQuery` | 下单结果查询 | 与 1008 查询按产品分流 |
| `/o2o/aggregatePay/service/closeTrade` | 关单 | 可选 |
| `/o2o/aggregatePay/app/offlineCancel` | 撤销 | P1 |

**回调契约（官方 `aggregatePayOrRefundNotify`，已核验）**

- 方向：翼支付 POST JSON 到商户 `notify_url`
- 关键字段：`institutionCode`、`merchantNo`、`outTradeNo`、`outRefundNo`（仅 REFUND）、`notifyType`（`PAY`|`REFUND`）、`totalAmt`/`payAmt`/`refundAmt`（单位**分**）、`tradeStatus`、`tradeNo`、`sign`、`payTool`、`payFinishedDate`/`refundFinishedDate`
- **必须以 `tradeStatus` 判断交易结果**（官方红字强调），`SUCCESS`/`FAIL`/`NOTPAY`/`CLOSE`
- 商户成功应答（验签通过后）：`{"resultCode":"SUCCESS","resultMsg":"OK"}`
- 失败应答：`{"resultCode":"FAILED","resultMsg":"FAILED"}`
- 重试：最多 2 次，间隔约 5 分钟；商户需处理重复通知
- 出口 IP 白名单（文档给出）：测试 `116.228.151.2`；生产 `58.213.97.1`、`58.213.98.128`、`58.213.98.232-239`、`117.68.124.1-8`

**交易渠道枚举（`payTool` / 支付平台类型，官方附录）**

`BESTPAY` 翼支付、`ALIPAY`、`WECHAT`、`QUICKPASS` 云闪付、`CTDIGICCY` 数字货币、`WEBCASHIER` PC 收银台、`MOBILECASHIER` 手机收银台、`APPLETCASHIER` 小程序收银台、`AGGCODE` 线上三码合一、`MIXCODE` 聚合码、`B2C`/`B2B` 网银、`JINGDONGPAY`

**结论**：一期默认实现 **代际 B，产品线 1008（线上收银台）+ 1006（线下扫码）**。Copilot 代际 A 仅作对照，不写入默认文档。

### [S2.3] 架构映射（对齐当前 master）

按 `.agents/skills/dev-guide/SKILL.md` 六阶段，与最近合入的 Airwallex/Jsb 保持一致：

```
src/
├── Config/BestpayConfig.php
├── Provider/Bestpay.php
├── Service/BestpayServiceProvider.php
├── Traits/BestpayTrait.php          # getBestpayUrl / verifyBestpaySign /（MAPI 则加）encryptBestpayPayload
├── Plugin/Bestpay/V1/
│   ├── StartPlugin.php              # 注入 merchantNo/platform/时间戳等公共字段
│   ├── GetPublicKeyPlugin.php       # 代际 B：拉取平台 RSA 公钥（可缓存 CertManager）
│   ├── AddPayloadSignPlugin.php     # 代际 A: MD5；代际 B: AES+RSA 信封
│   ├── AddRadarPlugin.php
│   ├── ResponsePlugin.php           # 业务码校验（A: returnCode 0000；B: 按官方文档）
│   ├── CallbackPlugin.php           # 必须验签
│   └── Pay/
│       ├── Web/PayPlugin.php        # 收银台/电脑网站
│       ├── H5/PayPlugin.php         # 手机网站（若 API 有）
│       ├── App/PayPlugin.php        # APP 支付（若 API 有）
│       ├── Scan/PayPlugin.php       # 扫码/被扫
│       ├── QueryPlugin.php
│       └── RefundPlugin.php
└── Shortcut/Bestpay/
    ├── WebShortcut.php
    ├── H5Shortcut.php               # 视接口事实裁剪
    ├── AppShortcut.php
    ├── ScanShortcut.php
    ├── QueryShortcut.php
    └── RefundShortcut.php
```

注册触点（实现时同步改）：

- `src/Pay.php`：`PROVIDER_BESTPAY`、`@method static Bestpay bestpay()`、`$providers`
- `src/Config.php`：`PROVIDERS` 与 `match` 映射
- `src/Exception/Exception.php`：`CONFIG_BESTPAY_INVALID`、`PARAMS_BESTPAY_*`
- `README.md` 支持列表 + `web/docs/v3/**` + `web/.vitepress/sidebar/v3.js`
- 测试：`tests/Provider/BestpayTest.php`、`tests/Plugin/Bestpay/**`（Mock HTTP，禁止真实请求）

插件管道默认：

```
StartPlugin
  → [GetPublicKeyPlugin]          # 仅代际 B 需要时
  → 业务插件 (Web|H5|App|Scan|Query|Refund)
  → AddPayloadSignPlugin
  → AddRadarPlugin
  → ResponsePlugin
  → ParserPlugin
```

`cancel` / `close`：若官方无对应 API，与 Jsb 一致抛 `PARAMS_METHOD_NOT_SUPPORTED`。

### [S2.4] 配置契约（基于官方公共参数，字段名可落码）

| 配置键 | 必填 | 说明 |
|---|---|---|
| `merchant_no` | 是 | 商户号（回调示例 `3178002072268273`） |
| `sign_merchant_no` / `institution_code` | 视证书 | 加签 CA 对应商户号 / 机构号，文档要求与 signMerchantNo 一致 |
| `mch_secret_cert_path` | 是 | 商户私钥（数字信封加签），走 `CertManager::getPrivateCert()` |
| `mch_public_cert_path` | 视对接 | 商户公钥（平台验签用） |
| `bestpay_public_cert_path` | 是 | 翼支付公钥（响应/回调验签） |
| `sign_type` | 否 | 默认 `S002`；国密 `S008` |
| `notify_url` | 否（可 per-order） | 支付/退款异步通知 |
| `return_url` | 否 | 同步跳转（收银台场景） |
| `mode` | 否 | `MODE_NORMAL` / `MODE_SANDBOX`；不支持 `MODE_SERVICE` |

校验：`supportedModes(): [MODE_NORMAL, MODE_SANDBOX]`；`validateRequired()` 缺商户号/私钥/平台公钥抛 `CONFIG_BESTPAY_INVALID`。

> 金额统一 **分**（官方回调/附录），与其他 Provider 对外「元」的习惯不同，文档必须醒目说明。

### [S2.5] 一期能力范围（用户已确认：更全一期，映射官方 1008/1006）

| Shortcut | 对外方法 | 官方接口 | 优先级 |
|---|---|---|---|
| Web | `web()` | 1008 `/pay/tradeCreate` + `WEBCASHIER` | P0 |
| H5 | `h5()` | 1008 `/pay/tradeCreate` + `MOBILECASHIER` | P0 |
| App | `app()` | 1008 `/pay/tradeCreate`（APP 场景，字段待文档中心确认） | P1 |
| Scan | `scan()` | 1006 `/aggregate/aggregatepay/offline/c2b/payOrder` | P0 |
| Micropay | `micropay()` | 1006 `/aggregate/aggregatepay/offline/b2c/pay` | P1 |
| Query | `query()` | 1008 `/integrate/orderQuery`（1006 订单按产品分流） | P0 |
| Refund | `refund()` | 1008 `/integrate/refund` | P0 |
| Close | `close()` | 1008 `/pay/closeOrder` | P0 |
| Callback | `callback()` + `success()` | `aggregatePayOrRefundNotify` | P0 |
| QueryRefund | `queryRefund()` | 1008 `/integrate/refundQuery` | P1 |
| cancel | — | 官方无对等 cancel；1006 有 `offlineCancel`，映射为 `micropayCancel()` 而非 Provider::cancel | P1 |

### [S2.6] 安全与回调

1. **回调必须验签**，禁止信任未验签 body（AGENTS.md 硬约束）。
2. 验签方式：官方公共响应含 `sign`，配合 `signType=S002|S008` 与平台公钥做证书验签 / 数字信封校验（算法细节见 [S4] T1）。
3. 业务判定以 `tradeStatus` 为准（`SUCCESS` 才成功）；同时校验 `outTradeNo`/`totalAmt` 与商户侧一致。
4. `success()` 返回官方应答：`Response(200, ['Content-Type'=>'application/json'], '{"resultCode":"SUCCESS","resultMsg":"OK"}')`。
5. 日志/异常消息中文；`declare(strict_types=1)`；禁止全限定命名空间字面量。
6. 金额字段单位为分，SDK 不做自动换算，文档与 PHPDoc 标明。

### [S2.7] 测试边界

- 加签/验签：固定证书与明文 → 期望 sign（覆盖 `signType` S002 路径）。
- 回调：合法签名通过；篡改字段失败；缺 `sign` 失败；`tradeStatus` 非 `SUCCESS` 不得当成功。
- `success()` body 精确等于 `{"resultCode":"SUCCESS","resultMsg":"OK"}`。
- 响应插件：HTTP 非 2xx / `success=false` / 业务失败 → `InvalidResponseException`。
- Provider：`mergeCommonPlugins` 顺序、`close`/`cancel` 行为、shortcut 分发。
- 全部 Mock `HttpClientInterface`；不依赖外网。

## [S3] Out of Scope

- 本轮不编写 `src/` 业务实现、不提交 feature 代码。
- 不实现服务商/机构模式（翼支付一期商户直连）。
- 不对接翼支付 C 端 App SDK、小程序插件（文档有 `APPLETCASHIER`/`aggregateGetOpenid`，二期再评估）。
- 不迁移/直接合入 `origin/copilot/add-yipay-support`（架构与算法均过时）。
- 不承诺代际 A 端点可用。
- 不实现：担保交易延时分账、对账文件、商户进件（1001）、代扣（1009）、企业账户（1012）、灵活用工（1017）等非收单主路径产品。

## [S4] 阻塞项 / 待确认事实（实现前必须关闭）

已关闭（2026-03-25 公开文档 API）：

- [x] 目标代际：**只做 B（MAPI）**，产品线 1008 + 1006
- [x] 接口目录：见 [S2.2]
- [x] 公共请求/响应字段与 `signType=S002|S008`
- [x] 回调报文、成功应答 `{"resultCode":"SUCCESS","resultMsg":"OK"}`、以 `tradeStatus` 为准
- [x] 金额单位为分
- [x] 官方存在 `/pay/closeOrder`，一期实现 `close()`

仍待关闭：

1. **交易接口完整请求字段表**（`/pay/tradeCreate`、`/integrate/orderQuery`、`/integrate/refund`、1006 下单）——文档列表项 `requestParams` 为空，需登录文档中心或 Java demo。
2. **数字信封/加签精确算法**（原文拼接规则、填充、是否二次封装）；错误码 `API500009`/`API100001` 提示存在信封与证书链。
3. **沙箱 base URL** 与生产是否同构；测试商户如何申请。
4. **S008 国密**是否一期支持，或仅 S002。

关闭方式：书面结论回填 [S2.2]/[S2.4] 后再 start 实现任务。

## Tasks

实现阶段任务（**T1 未完成前不要 start T2+**）：

- [ ] T1: 补齐 [S4] 未关闭四项（交易字段表 + 信封算法 + 沙箱 + S008）— acceptance: 字段表与算法有可引用来源，T2+ 可落码 (covers: S2.2, S2.4)
- [ ] T2: 骨架注册 — BestpayConfig + Provider + ServiceProvider + Pay/Config/Exception 常量 — acceptance: `Pay::bestpay()` 可实例化；缺配置抛 `CONFIG_BESTPAY_INVALID` (covers: S2.1)
- [ ] T3: 签名与网络插件 — StartPlugin / AddPayloadSignPlugin / AddRadarPlugin / ResponsePlugin + BestpayTrait（`getBestpayUrl`、`verifyBestpaySign`）— acceptance: 固定样例签名单测通过；管道可组装 (covers: S2.3, S2.6; depends: T1, T2)
- [ ] T4: P0 业务插件与 Shortcut（web/h5/scan/query/refund/close）— acceptance: shortcut `_url`/`_method` 与 [S2.2] 一致，Mock HTTP 返回 Collection (covers: S2.3, S2.5; depends: T3)
- [ ] T5: 回调验签 + `success()` — acceptance: 合法/非法签名用例全绿；应答 body 精确匹配官方格式 (covers: S2.6; depends: T3)
- [ ] T6: P1（app/micropay/queryRefund/micropayCancel）— acceptance: 有官方字段才合入，否则文档声明不支持 (covers: S2.5; depends: T1, T4)
- [ ] T7: `composer cs-fix && composer analyse && composer test` — acceptance: 三项全绿 (covers: S2.7; depends: T4, T5)
- [ ] T8: 文档 — README、`web/docs/v3/quick-start/bestpay.md`、`web/docs/v3/bestpay/*`、sidebar；醒目标注金额单位「分」— acceptance: 配置示例与 BestpayConfig 一致 (covers: S2.1, S2.4, S2.6; depends: T2, T4, T5)

## 调研附录

### A. 已探活 / 已拉取端点（2026-03-25）

```
POST https://mapi.bestpay.com.cn/gapi/mapi-gateway/auth/getPublicKey
→ 200 {"result":"MIICIjANBgkqhkiG9w0BAQ...","success":true}

GET  https://mapi.bestpay.com.cn/gapi/telecomPortal/getApiDocument?productCode=1008
→ 1008 超级收银台接口目录（见 S2.2）

GET  https://mapi.bestpay.com.cn/gapi/telecomPortal/getApiDocument?productCode=1006
→ 1006 线下聚合接口目录

GET  https://mapi.bestpay.com.cn/gapi/telecomPortal/getApiDocument?auxiliaryCode=PublicParameters1007
→ 公共请求/响应参数（signType S002/S008 等）

GET  https://mapi.bestpay.com.cn/gapi/telecomPortal/getApiDocument?auxiliaryCode=aggregatePayOrRefundNotify
→ 支付/退款回调完整字段与示例

GET  https://mapi.bestpay.com.cn/gapi/telecomPortal/getApiDocument?auxiliaryCode=aggregateRemark
→ tradeStatus / payTool / 错误码等附录

GET  https://ctcdn.bestpay.cn/html/images/collectpay/product.json
→ 产品目录（1001/1006/1008/1009/...）
```

产品码速查：`1001` 商户进件、`1006` 线下聚合、`1008` 超级收银台、`1009` 统一代扣、`1611` 统一超收、`1016` 分账。

### B. Copilot 分支能力清单（仅历史对照，**不要照抄**）

| 文件 | 路径/职责 | 与官方 B 的差异 |
|---|---|---|
| StartPlugin | `signType=MD5` + `merchantNo`/`platform` | 官方为 `signType=S002` + CA 证书字段 |
| AddPayloadSignPlugin | MD5(`ksort`+`app_key`) | 官方为证书/数字信封加签 |
| Web/Scan/Query/Refund | `pay/cashierPay` 等 | 官方为 `/pay/tradeCreate`、`/integrate/*`、1006 聚合路径 |
| ResponsePlugin | `returnCode==='0000'` | 官方 `success` + `tradeStatus` |
| CallbackPlugin | MD5 验签 | 官方证书验签；成功应答不同 |

### C. 对齐检查清单（实现 PR 自检）

- [ ] 使用 `Pay::PROVIDER_BESTPAY`，无字符串字面量 `'bestpay'` 散落
- [ ] 回调验签失败路径有测试；成功应答精确匹配
- [ ] 金额单位「分」在 PHPDoc/文档中写明
- [ ] `declare(strict_types=1)` + use 导入 + 中文日志
- [ ] 文档与配置类字段一致
- [ ] 不引入对旧 `Functions.php` 风格的依赖
- [ ] 不把代际 A 路径写进用户文档
