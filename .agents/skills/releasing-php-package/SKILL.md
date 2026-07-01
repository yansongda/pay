---
name: releasing-php-package
description: Use when preparing to publish a new version of a PHP Composer package and need to write or update CHANGELOG, upgrade guides, and documentation before tagging and releasing
---

# 发布 PHP Composer 包

## 概述

用于发布 PHP Composer 包的结构化流程，避免 CHANGELOG 格式混乱、升级指南缺失、文档示例过期等常见问题，并强制要求在打 tag 前通过独立分支 + PR 完成文档改动。

## 使用场景

- 准备发布新版本（patch/minor/major/beta/rc）
- CHANGELOG 使用了非标准分类或前缀
- 目标版本还没有对应的升级指南
- 文档中包含已移除或已弃用功能的示例
- 上次发布曾因文档问题被反馈

**不要使用：**
- 项目已有自动化发布工具（semantic-release、release-please）
- 仅单文件改动且对用户无可见影响

## 版本分类

| 变更类型 | 版本号变化 | 是否需要升级指南 |
|---|---|---|
| 仅 Bug 修复 | Patch（1.0.X） | 否 |
| 新功能，向后兼容 | Minor（1.X.0） | 是 |
| 破坏性变更 | Major（X.0.0） | 是 |
| 预发布（beta/rc/alpha） | 预发布版本（1.0.0-beta.1） | 是 |

## 核心模式

### 错误示例

```markdown
## v1.2.0 - 2024-01-01

### BREAKING CHANGES
- change: PHP version bumped to 8.2

### Added
- feat: add new provider support (#42)
- feat: add new config system (#43)

### Changed
- fix: correct error code (#45)
- chore: update dependencies (#46)
```

### 正确示例（符合 Keep a Changelog）

```markdown
## [v1.2.0] - 2024-01-01

### Added
- New payment provider support (#42)
- Typed configuration objects replacing arrays (#43)

### Changed
- Minimum PHP version from 8.0 to 8.2 (#41)
- All internal plugins migrated to trait-based architecture (#44)

### Removed
- Legacy helper functions file, migrated to static methods (#44)
- Deprecated `getOldConfig()` functions, use `getProviderConfig()` (#41)
```

## 速查表

| 任务 | 命令 / 文件 | 关键规则 |
|---|---|---|
| 查看当前分支 | `git branch --show-current` | 禁止在 `master`/`main` 上直接修改 CHANGELOG |
| 创建发布分支 | `git checkout -b docs/changelog-vX.Y.Z` | 所有文档改动必须走 PR |
| 列出自上一个 tag 以来的提交 | `git log --oneline --no-merges $(git describe --tags --abbrev=0)..HEAD` | 排除 merge commit |
| 标准分类 | `Added` → `Changed` → `Deprecated` → `Removed` → `Fixed` → `Security` | 严格顺序，不新增分类 |
| 去除前缀 | 移除 `feat:`、`fix:`、`refactor:`、`chore:` | 仅当存在时才移除；已干净的中文描述保持原样 |
| 升级指南 | `docs/v{major}/upgrade/v{version}.md` 或实际等价路径 | 先探测真实 docs 路径 |
| 更新导航 | 侧边栏 / 菜单配置文件 | 若 `.gitignore` 忽略 docs 目录，使用 `git add -f` |
| 创建 annotated tag | `git tag -a vX.Y.Z -m "release: vX.Y.Z"` | 必须在 PR 合并后执行 |
| 创建 GitHub Release | `gh release create vX.Y.Z --notes-file release-notes.md --prerelease` | beta/rc/alpha 需标记为 pre-release |

## 执行步骤

### 0. 分支隔离（关键）

**在修改任何发布相关文件前，必须先创建独立分支。**

```bash
# 先确认当前分支
git branch --show-current

# 如果在 master/main 上，立即切出发布分支
git checkout -b docs/changelog-vX.Y.Z
```

**禁止**在 `master`/`main` 上直接提交 CHANGELOG 或升级指南改动。

### 1. 分析提交

```bash
latest=$(git describe --tags --abbrev=0)
git log --oneline --no-merges ${latest}..HEAD
```

重点关注：PR 编号、破坏性变更、新功能、已弃用 / 已移除项。

**仅文档发布判断：** 如果自上一个 tag 以来的代码变更已经在目标分支上，而你只是整理文档，则按「仅文档发布」处理。流程相同，但提交范围只包含 CHANGELOG / 升级指南 / 导航。

**关键验证步骤：**
分析完提交后，务必验证实际代码状态，避免把「新增后又移除」的功能写进发布说明：

```bash
# 验证文件增删
git diff ${latest}..HEAD --stat

# 验证 src/ 下实际代码变更
git diff ${latest}..HEAD -- src/
```

**验证规则：**
1. 对于 commit 消息中的「新增」项：
   - 确认文件 / 类 / 方法在 HEAD 中仍然存在
   - 如果后续 commit 已移除，则从发布说明中排除
   - 示例：#1157 新增 NetworkException，但 #1161 又移除了 → 不要写进 Added

2. 对于 commit 消息中的「移除」项：
   - 确认文件 / 类 / 方法在 HEAD 中已不存在
   - 如果仍然存在，则排除或改为「已弃用」

3. 对于 commit 消息中的「变更」项：
   - 确认变更在 HEAD 中真实存在
   - 如果已被 revert，则从发布说明中排除

**验证命令：**
```bash
# 确认文件存在于 HEAD
git show HEAD:path/to/file

# 确认文件变更
git diff ${latest}..HEAD -- path/to/file
```

### 2. 规范化 CHANGELOG

**分类顺序（严格）**：Added、Changed、Deprecated、Removed、Fixed、Security。

**规则：**
- 仅当存在 `feat:` / `fix:` / `refactor:` / `chore:` 等前缀时才移除
- 不要重写已经是干净中文描述的条目
- 将 `BREAKING CHANGES` 内容移入 `Changed` 或 `Removed`
- 合并重复条目（如同一文件被删除两次）
- 保留 PR 引用 `(#1234)` 以便追溯
- 子列表缩进 2 个空格

**版本标题格式：**
```markdown
## [v1.2.0] - 2024-01-01
```

### 3. 编写升级指南

**先探测真实 docs 路径。** 常见位置：
- `docs/v{major}/upgrade/v{version}.md`
- `web/docs/v{major}/upgrade/v{version}.md`
- `src/docs/v{major}/upgrade/v{version}.md`

如果目标升级指南已存在（例如后续 beta 版本），则在原有基础上追加新内容，不要整篇替换。

**章节结构：**
1. `## 重点检查`
   - `### 运行环境` — PHP 版本、扩展、其他运行时要求
   - `### 简单使用者` — **仅 end-user 可见变更**（命名空间变化、移除的方法、配置调整）。不要罗列内部重构（内部辅助函数重组、不影响公共 API 的基类变化、代码组织调整）。
   - `### 自有插件开发者` — 影响自定义插件或扩展的内部架构变更（新基类、方法签名变化、移除的内部工具）。
2. `## 更改版本号` — `composer require vendor/package:~{version}`
3. `### BREAKING CHANGES`（如有）— 完整破坏性变更列表

### 4. 清理过期示例

搜索文档中已移除或已弃用功能的使用：
```bash
grep -rn "deprecatedMethod\|removedClass\|oldNamespace" docs/
```

**规则：** 删除已移除功能的示例。不要保留「兼容两种写法」的双示例。文档只反映当前版本。

### 5. 更新导航

将新升级指南链接加入文档导航配置（如侧边栏、菜单、索引文件）。

**注意：** 如果 `.gitignore` 包含 docs 目录匹配规则，新文件需用 `git add -f` 强制添加。

### 6. PR → Tag → Release

**创建 PR：**
```bash
git add -f CHANGELOG.md docs/  # 或 web/docs/，按实际路径调整

git commit -m "docs: update CHANGELOG and upgrade guide for vX.Y.Z"

git push -u origin docs/changelog-vX.Y.Z

# 通过 GitHub / GitLab / Gitea 等平台创建 PR
```

**PR 合并后（继续前必须确认 PR 已合并）：**
```bash
# 确认 master 已包含合并提交
git fetch origin
git log --oneline origin/master | head -5

# 打 tag 并推送
git checkout master && git pull origin master
git tag -a vX.Y.Z -m "release: vX.Y.Z"
git push origin vX.Y.Z
```

**创建 Release（以 GitHub 为例）：**
```bash
# 从 CHANGELOG 提取当前版本段落
sed -n '/^## vX.Y.Z/,/^## v/p' CHANGELOG.md | sed '$d' > release-notes.md

# beta/rc/alpha 版本需添加 --prerelease
gh release create vX.Y.Z --title "vX.Y.Z" --notes-file release-notes.md --prerelease
```

## 常见错误

| 错误 | 原因 | 修正 |
|---|---|---|
| 直接在 master 提交 CHANGELOG | 跳过分支隔离 | 必须先创建 `docs/changelog-vX.Y.Z` 分支 |
| CHANGELOG 保留 `feat:`/`fix:` 前缀 | 从 commit 消息复制粘贴 | 仅在有前缀时剥离；保持已有干净描述 |
| 内部重构写入「简单使用者」 | 未区分用户可见变更与内部变更 | 移到「自有插件开发者」或直接删除 |
| 文档保留新旧双示例 | 试图在文档中保持向后兼容 | 删除旧示例；文档只反映当前版本 |
| 忘记更新导航 | 创建升级指南后未注册 | 立即加入侧边栏 / 菜单 |
| PR 未合并就创建 tag | 急于完成发布 | 先确认 `origin/master` 包含合并提交 |
| 使用 lightweight tag | `git tag` 缺少 `-a` | 始终使用 `git tag -a` 并附带 message |
| Release notes 与 CHANGELOG 不一致 | 单独编写 release notes | 从 CHANGELOG 提取或复制 |
| 预发布版本未标记 pre-release | 忘记 beta/rc/alpha 后缀 | 按版本后缀在发布平台勾选 pre-release |
| 写入「新增后又移除」的功能 | 仅依赖 commit 消息未验证代码 | 用 `git diff` 确认功能在 HEAD 中真实存在 |

