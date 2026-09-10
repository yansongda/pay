---
feature: bestpay
status: designed
updated: 2026-03-25
branch: feat/bestpay-provider
commits: 9fdd0f34..9fdd0f34 # 本轮仅调研 + Spec，实现提交后回填
---

# 翼支付（BestPay）Provider 调研与设计

对应 Issue：[yansongda/pay#1030](https://github.com/yansongda/pay/issues/1030)

本轮边界：**只调研与设计，不写业务代码**。实现启动前需补齐「官方接口事实」（见 [S4] 阻塞项）。

## Report

（交付时填写）

## [S1] Problem

- 用户希望 yansongda/pay 支持中国电信翼支付（BestPay），Issue 指向官方开发者平台：  
  <https://render.bestpay.cn/open-developers/index.html#/documentCenterLayout/accessProcess>
- 官网仅提供 Java demo，文档站为 SPA，社区几乎无可用 PHP SDK。
- 仓库内已有远端分支 `origin/copilot/add-yipay-support`（+1458 行），但基于 **旧架构**（`Functions.php` + 数组配置），与当前 master 的 Typed Config / Trait / `Pay::PROVIDER_*` 常量体系不一致，**不能直接合入**。
- 公开可核验的接口事实有限：SPA 抓不到接口清单；`getApiDocumentMap` 无鉴权返回空对象。字段级契约在未拿到官方文档/沙箱前只能标为 provisional。

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

#### 代际 B —— 现行开发者平台 MAPI（Issue 链接所指）

来源：对 `render.bestpay.cn/open-developers` SPA 前端 JS 的静态提取 + 探活。

| 项 | 值 |
|---|---|
| 业务网关 | `https://mapi.bestpay.com.cn/mapi`（GET 返回 405，需 POST） |
| 平台网关 | `https://mapi.bestpay.com.cn/gapi/mapi-gateway` |
| 文件服务 | `https://mapi-file.bestpay.com.cn/mapi-file` |
| 鉴权 | `POST /mapi-gateway/auth/getPublicKey` → RSA 公钥（已探活，返回 2048-bit RSA base64）；`POST /mapi-gateway/auth/getAesKey` 会话密钥 |
| 信封加密 | 明文 JSON → AES-256（`encyType=C006`）；AES key 用平台 RSA 公钥加密；`sign` = 明文 MD5；另有 RSA-SHA512 签名路径（`aesKeySign` / `clientRsaPublicKeySign`） |
| 文档接口 | `/telecomPortal/getApiDocument`、`/telecomPortal/getApiDocumentMap`（无鉴权返回空） |
| 产品接口 | `/merchant-product/mctProductSignService/queryProductInfoDetail` |

结论：**Issue 指向的是代际 B**。代际 A 最多作为历史兼容参考，不应作为默认实现目标，除非维护者确认仍有商户在用旧通道。

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

### [S2.4] 配置契约（provisional）

代际 B（目标）字段草案，**以官方文档最终字段名为准**：

| 配置键 | 必填 | 说明 |
|---|---|---|
| `mer_id` / `merchant_no` | 是 | 商户号（命名待文档确认，二选一，全库统一） |
| `platform` | 视通道 | 代际 A 测试出现 `HELIPAY`；B 是否需要待确认 |
| `app_id` / `product_id` | 待确认 | 产品/应用标识 |
| `app_secret` / `app_key` | 视签名方案 | 代际 A 为 MD5 key；B 可能改为商户 RSA 私钥 |
| `bestpay_public_key` / 证书路径 | B 待确认 | 验签/解密用平台公钥；可走 `CertManager` |
| `notify_url` | 否（可 per-order） | 回调地址 |
| `return_url` | 否 | 同步跳转 |
| `mode` | 否 | `MODE_NORMAL` / `MODE_SANDBOX`；不支持 `MODE_SERVICE` |

校验：`supportedModes(): [MODE_NORMAL, MODE_SANDBOX]`；`validateRequired()` 缺关键字段抛 `CONFIG_BESTPAY_INVALID`。

### [S2.5] 一期能力范围（用户已确认：更全一期）

| Shortcut | 对外方法 | 优先级 | 依赖接口事实 |
|---|---|---|---|
| Web | `Pay::bestpay()->web()` | P0 | 收银台/网站支付 |
| Scan | `Pay::bestpay()->scan()` | P0 | 扫码/当面付 |
| Query | `query()` | P0 | 订单查询 |
| Refund | `refund()` | P0 | 退款 |
| Callback | `callback()` + `success()` | P0 | 异步通知验签 |
| H5 | `h5()` | P1 | 官方有手机网站支付时 |
| App | `app()` | P1 | 官方有 APP 支付时 |
| cancel/close | 显式不支持 | — | 无 API 则抛异常 |

### [S2.6] 安全与回调

1. **回调必须验签**，禁止信任未验签 body（AGENTS.md 硬约束）。
2. 代际 A：MD5 常数时间比较（`hash_equals`）。
3. 代际 B：按官方信封验签（RSA/MD5 组合），失败抛 `InvalidSignException`。
4. 成功应答 `success()` 的 body 形状以官方文档为准（A 样例为 `{"returnCode":"0000","returnMsg":"成功"}`）。
5. 日志/异常消息中文；`declare(strict_types=1)`；禁止全限定命名空间字面量。

### [S2.7] 测试边界

- 签名算法：固定 key/明文 → 期望 digest（覆盖空值过滤、排序、大小写）。
- 回调：合法签名通过；篡改字段失败；缺 `sign` 失败。
- 响应插件：HTTP 非 2xx / 业务码非成功 → `InvalidResponseException`。
- Provider：`mergeCommonPlugins` 顺序、`cancel/close` 抛错、shortcut 分发。
- 全部 Mock `HttpClientInterface`；不依赖外网。

## [S3] Out of Scope

- 本轮不编写 `src/` 业务实现、不提交 feature 代码。
- 不实现服务商/机构模式（翼支付一期商户直连）。
- 不对接翼支付 C 端 App SDK、小程序支付，除非官方 MAPI 明确开放且一期确认纳入。
- 不迁移/直接合入 `origin/copilot/add-yipay-support`（架构过时，只作字段参考）。
- 不承诺代际 A 端点可用；未验证前不得在文档中写成「已支持」。

## [S4] 阻塞项 / 待确认事实（实现前必须关闭）

1. **官方接口清单与字段**：商户后台或对接人提供 PDF/截图/Java demo 包，或沙箱账号可登录文档中心。
2. **目标代际**：明确只做 B，还是 A+B 双通道（配置 `api_generation` 或分 mode）。
3. **签名与加解密最终算法**：AES 模式/填充、RSA 公钥编码、是否强制 `encyType=C006`。
4. **回调 Content-Type 与成功应答格式**。
5. **是否有公开沙箱**（`sandbox` base URL 是否与生产同构）。

关闭方式：把上述结论回填 [S2.2]/[S2.4]，将 provisional 字段改为最终契约，再勾选实现任务。

## Tasks

实现阶段任务（**API 事实关闭前不要 start**）：

- [ ] T1: 核对官方接口事实并回填 [S2.2]/[S2.4]/[S4] — acceptance: [S4] 五项均有书面结论，字段无 TBD (covers: S2.2, S2.4)
- [ ] T2: 骨架注册 — BestpayConfig + Provider + ServiceProvider + Pay/Config/Exception 常量，`Pay::bestpay()` 可实例化 — acceptance: 单测 `Pay::bestpay()` 返回 Provider，缺配置抛 `CONFIG_BESTPAY_INVALID` (covers: S2.1)
- [ ] T3: 签名与网络插件 — StartPlugin / AddPayloadSignPlugin / AddRadarPlugin / ResponsePlugin + BestpayTrait — acceptance: 固定样例签名单测通过；管道可 `pay()` 组装 (covers: S2.3, S2.6; depends: T1, T2)
- [ ] T4: P0 业务插件与 Shortcut（web/scan/query/refund）— acceptance: 四个 shortcut 调用组装正确 `_url`/`_method`，Mock HTTP 下返回 Collection (covers: S2.3, S2.5; depends: T3)
- [ ] T5: 回调验签 + `success()` — acceptance: 合法/非法签名用例全绿；未验签数据不得放行 (covers: S2.6; depends: T3)
- [ ] T6: P1 支付方式（h5/app，仅官方 API 存在时）— acceptance: shortcut 与文档一致，无 API 则任务放弃并在文档声明不支持 (covers: S2.5; depends: T1, T4)
- [ ] T7: 测试补齐 + `composer cs-fix && composer analyse && composer test` — acceptance: 三项命令全绿，新增文件无 phpstan 报错 (covers: S2.7; depends: T4, T5)
- [ ] T8: 文档 — README 支持列表、`web/docs/v3/quick-start/bestpay.md`、`web/docs/v3/bestpay/*.md`、sidebar — acceptance: 文档站可导航，示例配置与 BestpayConfig 字段一致 (covers: S2.1, S2.4; depends: T2, T4, T5)

## 调研附录

### A. 已探活端点（2026-03-25）

```
POST https://mapi.bestpay.com.cn/gapi/mapi-gateway/auth/getPublicKey
→ 200 {"result":"MIICIjANBgkqhkiG9w0BAQ...","success":true}

POST https://mapi.bestpay.com.cn/gapi/telecomPortal/getApiDocumentMap
→ 200 {"result":{},"success":true}   # 无鉴权空集

GET  https://mapi.bestpay.com.cn/mapi
→ 405 Method Not Allowed
```

### B. Copilot 分支能力清单（参考，非目标契约）

| 文件 | 路径/职责 |
|---|---|
| `Plugin/Bestpay/V1/StartPlugin` | 合并 `signType=MD5`、`merchantNo`、`platform`、`requestTimestamp` |
| `AddPayloadSignPlugin` | MD5 签名写入 `sign` |
| `AddRadarPlugin` | 组装 PSR-7 Request |
| `Pay/Web/PayPlugin` | `_url=pay/cashierPay` |
| `Pay/Scan/PayPlugin` | `_url=pay/createQrPay` |
| `Pay/QueryPlugin` | `_url=pay/queryPayOrder` |
| `Pay/RefundPlugin` | `_url=refund/applyRefund` |
| `CallbackPlugin` | 校验 `sign`，方向 NoHttpRequest |
| `ResponsePlugin` | `returnCode === '0000'` |

### C. 对齐检查清单（实现 PR 自检）

- [ ] 使用 `Pay::PROVIDER_BESTPAY`，无字符串字面量 `'bestpay'` 散落
- [ ] 回调验签失败路径有测试
- [ ] `declare(strict_types=1)` + use 导入 + 中文日志
- [ ] 文档与配置类字段一致
- [ ] 不引入对旧 `Functions.php` 风格的依赖
