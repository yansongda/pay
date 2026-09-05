# 2026-09-05 13:26:31 (worker-low - Task 11 文档重写 + CHANGELOG v3.8.0-beta.6 + 升级说明)

## 处理事项

开工前已通读 `docs/learning/douyin-trade-system.md` 与技术设计文档 `docs/douyin-trade-system.md`（含契约快照与官方链接附录 B）。

1. 重写 `web/docs/v3/douyin/`：pay.md（mini() JSAPI 签名，camelCase 字段透传，输出 `{data, byteAuthorization}`，前端 `tt.requestOrder` 对接示例，签名五行待签串说明）、query.md（`_action: order(默认)/cps/refund` 三分发，order_id/out_order_no 二选一、退款三选一）、refund.md（创建退款 + 响应退款申请回调同步应答示例，含 `{"err_no":0,"err_tips":"success","data":{...}}` 应答 JSON，明确 SDK 只验签+解析、应答由业务方构造）、**新增 refund-audit.md**（`_action=audit` 退款审核，1 同意/2 拒绝 + deny_message）、callback.md（三回调入口均收 ServerRequestInterface + Byte-Timestamp/Byte-Nonce-Str/Byte-Signature 三行串 RSA 验签说明 + array 入参不再可用 + success() 应答 + ⚠️「未经实测」danger 标注）、response.md（err_no 语义，成功 0，err_msg/err_tips 双格式）、all.md（新插件全清单 GetClientToken*/ObtainClientToken/AddRadar/Response/Pay/{Sign,Query,QueryCps,Callback}/Refund/{Refund,QueryRefund,Audit,Callback,PreRefundCallback}）、close.md/cancel.md（结论保持「官方无此 API」，机械性修正异常 FQCN `Yansongda\Pay\Exceptions\InvalidParamsException` → `Yansongda\Artful\Exception\InvalidParamsException`，与 src/Provider/Douyin.php 实际抛出类一致）。
2. 因 refund.md 内容拆分出 refund-audit.md，机械性补充 `web/.vitepress/sidebar/v3.js` 抖音段「退款审核」一条（任务书授权）。
3. 重写 `web/docs/v3/quick-start/douyin.md`（新版 mini 用法 + v3.8.0-beta.6 起老担保支付删除提示 + 「trade_basic 业务接口无沙盒环境；MODE_SANDBOX 仅适合验证 client_token」warning 原文）。
4. `CHANGELOG.md`：`## Unreleased` 下新增 `## [v3.8.0-beta.6] - Unreleased`，Added（新交易系统全量能力）+ Removed（BREAKING：老插件/老 Shortcut/老配置字段清单 + 迁移要点 mini_app_id→app_id、MD5 salt→RSA app_private_key、SHA1 token→platform_public_key 验签、回调 form 数组→ServerRequestInterface、array 入参不再支持）。
5. `README.md` 仅动两处抖音段：能力列表（约 80 行）更新为新交易系统能力清单；配置示例段（约 303 行起）全量替换为新配置（app_id/app_secret/app_private_key/platform_public_key/refund_notify_url）与 mini（camelCase + data/byteAuthorization 返回前端）/callback（ServerRequestInterface 入参）用法。未触碰其他段落。
6. `web/docs/v3/upgrade/v3.8.md`：简单使用者 bullet 补 beta.6 一条；BREAKING CHANGES 补抖音删除一条；文末新增「## 抖音：担保支付 → 通用交易系统（3.7 → 3.8 迁移指引）」（配置字段映射表 + 用法变化）。

## 命令与真实输出

验收 1（文档构建，pnpm 经 npx 可用，未降级）：

```
$ cd web && npx pnpm web:build
vitepress v1.6.3
✓ building client + server bundles...
✓ rendering pages...
build complete in 12.04s.
（错别字修正后复跑）build complete in 10.49s.
```

产物核验：`.vitepress/dist/docs/v3/douyin/refund-audit.html` 等全部页面生成；vitepress 配置无 `ignoreDeadLinks`（grep 零命中），内部死链会直接构建失败 → 文档内部链接（/docs/v3/douyin/refund-audit、/docs/v3/douyin/query、/docs/v3/upgrade/v3.8 等）经构建验证有效。

验收 2：

```
$ grep -n "v3.8.0-beta.6" CHANGELOG.md
9:## [v3.8.0-beta.6] - Unreleased
```

验收 3：

```
$ grep -rn "mch_secret\|mini_app_id" web/docs/v3/douyin/ web/docs/v3/quick-start/douyin.md
（零命中，exit=1）
```

验收 4（局部断言，未做全文件 grep）：

```
$ grep -c '^### 抖音$' README.md
2
$ sed -n '/^### 抖音$/,/^### /p' README.md | grep -c "mch_id\|mch_secret\|mini_app_id\|thirdparty_id\|ecpay"
0
```

## 偏差与机械性修正记录

- 【机械性修正】设计文档附录 B 的官方链接路径与搜索引擎收录的线上路径存在差异（如线上为 `.../server/trade-system/...` 无 `payment/` 段、`.../server/interface-request-credential/...` 无 `basic-abilities` 段、`create_refund` 下划线等）。处理：官方链接按任务书指定的唯一事实来源（设计文档附录 B）原样引用，未擅自改链；curl 实测抖音文档站为纯 SPA（任意路径 200 无 title），无法离线判活，建议维护者发布前抽查链接。
- 【机械性修正】`tt.requestOrder` 前端调用形态经搜索核实为对象参数（data/byteAuthorization/success/fail），官方 API 页 `.../develop/api/industry/general_trade/create_order/requestOrder` 已链接；`merchantUid` camelCase 命名经官方「生成下单参数与签名」页摘要核实（数据字段确为 camelCase，与设计文档附录中 trade_basic 服务端 API 的 `merchant_uid` snake_case 属不同层，不冲突）。
- 【错别字修正】CHANGELOG.md 与 upgrade/v3.8.md 初稿中出现「报音」，已定点 perl 替换为「抖音」（仅这两个新改文件，grep 复查零残留）。
- 【范围外遗留，未动】`web/docs/v3/quick-start/init.md` 117-134 行仍有抖音老配置示例（mch_secret_token/mch_secret_salt/mini_app_id/thirdparty_id），超出本任务文件边界（任务书只授权 quick-start/douyin.md），建议后续任务处理；`web/docs/v3/others/faq.md` 与 `wechat/virtual.md` 的 mch_secret/mini_app_id 命中属微信自身，无需处理。

## 验收结果

4 条 Acceptance criteria 全部通过（构建成功未降级；CHANGELOG 命中 beta.6；douyin/ 与 quick-start/douyin.md 旧键零命中；README 标题计数 2、抖音段旧键计数 0）。

# 2026-09-05 13:35:00 (main agent 亲自验证)

- 验收复跑：A2 `grep -n "v3.8.0-beta.6" CHANGELOG.md` → 第 9 行命中 ✅；A3 douyin/ 与 quick-start/douyin.md 旧键 scoped grep 零命中 ✅；A4 README `^### 抖音$` 计数=2、抖音段旧键=0 ✅。
- 文档构建复跑（宿主机 npx pnpm web:build）：`build complete in 10.14s` ✅。
- diff 抽查：CHANGELOG v3.8.0-beta.6 段 Added（全量能力）/Removed BREAKING（删除类清单+字段清单+四条迁移要点+存量订单提示）完整；17 文件均在白名单内（douyin/ 8 文档 + quick-start + sidebar 1 行条目 + upgrade/v3.8.md + README + CHANGELOG + evidence）。
- **范围外遗留（上抛用户）**：`web/docs/v3/quick-start/init.md` 117-134 行抖音配置块仍为老键（mch_id/mch_secret_token/mch_secret_salt/mini_app_id/thirdparty_id）——该文件不在 Task 11 白名单与 plan 验收范围，worker 遵守边界未动；属 plan 覆盖遗漏，待用户裁决是否补修。
- 结论：Task 11 通过，勾选 [x]。全部 11 todo 完成，进入 Final verification wave。
