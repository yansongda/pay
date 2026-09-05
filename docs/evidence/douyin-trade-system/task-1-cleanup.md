# 2026-09-05 11:51:46

## Task 1 清场：删除老担保支付实现 + DouyinConfig 重定义 + Provider 裁剪（仓库回绿）

### 处理事项

1. **留存老文件到 evidence**：`mkdir -p docs/evidence/douyin-trade-system/legacy`，对 13 个老文件逐个 `git show master:<path> > docs/evidence/douyin-trade-system/legacy/<路径平铺>.php`（路径 `/` 全部替换为 `_`）。留存清单以「后续任务要参考的」为准，含 AddPayloadSignaturePlugin（后续 SignPlugin 结构参考）。
2. **删除**：`git rm -r` 删除 `src/Plugin/Douyin/V1/Pay/`（整目录，8 个老插件）、`src/Shortcut/Douyin/`（3 个 Shortcut）、`tests/Plugin/Douyin/`（整目录，8 个插件测试）、`tests/Shortcut/Douyin/`（3 个 Shortcut 测试）、`tests/Provider/DouyinTest.php`。
3. **生成 RSA 密钥对**（`/usr/bin/openssl`，LibreSSL 2.8.3 → PKCS1 私钥，契约 C6 允许）：
   - `tests/Cert/douyinAppPrivateKey.pem`（app 正向签名用例）
   - `tests/Cert/douyinAppPublicKey.pem`
   - `tests/Cert/douyinPlatformPrivateKey.pem`（模拟平台签回调）
   - `tests/Cert/douyinPlatformPublicKey.pem`（进测试配置用于验签）
4. **重写 `src/Config/DouyinConfig.php`**：删除 `miniAppId/mchSecretToken/mchSecretSalt/mchId/thirdpartyId`；新增 `appId/appSecret/appPrivateKey/platformPublicKey/refundNotifyUrl` + token 缓存 `_accessToken/_accessTokenExpiry`（命名对齐 PaypalConfig 实际风格）；保留 `notifyUrl/mode`；`validateRequired()` 校验 `['appId','appSecret']`。
5. **裁剪 `src/Provider/Douyin.php`**：URL 改为 open.douyin.com 三段；`callback()` 保留宽签名但方法体改为抛 `InvalidParamsException(PARAMS_METHOD_NOT_SUPPORTED, '参数异常: 抖音回调暂未迁移至新交易系统')`；删除 `getCallbackParams()` 与 4 个失效 import；docblock 更新为「小程序下单签名（新交易系统）」；保留 `__call/pay/query/cancel/close/refund/success`。
6. **`tests/TestCase.php` douyin 块**：原 default/service_provider/empty_salt 三个租户整体替换为单一 `default` 租户，新键 app_id/app_secret/app_private_key（真实引用 Cert fixture）/platform_public_key/refund_notify_url/notify_url/mode=SANDBOX。此块定稿冻结。
7. **重写 `tests/Config/DouyinConfigTest.php`**：缺 app_id/app_secret 分别抛 `InvalidConfigException(CONFIG_DOUYIN_INVALID)`；可选 getter、accessToken、mode 合法性测试，风格对齐 PaypalConfigTest。
8. **更新 `tests/Traits/DouyinTraitTest.php`**：URL 期望 developer.toutiao.com → open.douyin.com；构造 DouyinConfig 字段改为新配置键。

### 命令与真实输出

**openssl 生成密钥**（宿主机）：
```
LibreSSL 2.8.3
-rw-r--r--  tests/Cert/douyinAppPrivateKey.pem        (1675 B)
-rw-r--r--  tests/Cert/douyinAppPublicKey.pem         (451 B)
-rw-r--r--  tests/Cert/douyinPlatformPrivateKey.pem   (1675 B)
-rw-r--r--  tests/Cert/douyinPlatformPublicKey.pem    (451 B)
```

**三绿**（容器内全链）：
```
cs-fix: Found 0 of 470 files that can be fixed
analyse: [OK]（470/470）
test:   OK (1359 tests, 3281 assertions)  [基线 1401 tests/3357 assertions，删除老测试减少 42 属预期]
```

### 偏差与处理

1. **phpstan `trait.unused`**（需上报处理，非计划预期）：删除插件后 `src/Traits/DouyinTrait.php` 成为 src 内唯一无人使用的 trait，phpstan 报 `trait.unused` 导致 analyse 失败。plan 的 grep 验收路径明确要求 `src/Traits/DouyinTrait.php` 必须保留（否则 grep 路径不存在会报错），且 Task 10 新交易系统会用该 trait——与「analyse 必须通过」硬门槛冲突。**处理**：在 `src/Provider/Douyin.php`（本 todo 已列文件）中 `use DouyinTrait;`，使 trait 在 src 内保持被使用，Task 10 接线时天然可用。属删除的机械性后果，非设计变更；不删 trait、不删 DouyinTraitTest、未新增 phpstan 配置。
2. **cs-fix 波及**：首轮 cs-fix 检出 `src/Provider/Douyin.php` 末尾残留空行（删除 getCallbackParams 遗留），用 `vendor/bin/php-cs-fixer fix src/Provider/Douyin.php` 定点修复后重跑三绿。仅波及本 todo 文件，无越界改动。
3. **留存文件数**：plan 正文写「12 个文件」但枚举 13 个，按枚举清单执行（含 AddPayloadSignaturePlugin），13 个全部留存。
4. **未入库**：`docs/alipay-v3.md`、`docs/douyin-trade-system.md` 为任务开始前已存在的未跟踪文件，非本任务产出，未纳入 commit。

### 验收结果

1. 三绿：cs-fix 0 fixable / analyse OK / test OK (1359 tests, 3281 assertions) ✅
2. `git status --short` 无 Must NOT 涉及文件（Pay.php/Config.php/Exception.php/ProviderInterface.php 均未动）✅
3. grep `ecpay|mch_secret|mini_app_id|thirdparty_id` 于 5 个指定文件：零命中（grep exit=1）✅
4. `test ! -e src/Plugin/Douyin/V1/Pay && test ! -e src/Shortcut/Douyin/MiniShortcut.php && echo OK` → OK ✅
5. grep `miniAppId` 于 3 个指定文件：零命中（grep exit=1）✅
6. `ls tests/Cert/douyin*.pem` 恰好 4 个 ✅
