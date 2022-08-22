# 介绍

<p align="center">
<a href="https://pay.yansongda.cn" target="_blank" rel="noopener noreferrer"><img width="200" src="https://pay.yansongda.cn/images/logo.png" alt="Logo"></a>
</p>

该文档为 v3 版本的文档，如果您正在使用 v2.x 版本的 SDK，请您传送至 [这里](/docs/v2/)。

v3 版与 v2 版在底层有很大的不同，基础架构做了重新的设计，更易扩展，使用起来更方便。

## 前言

开发了多次支付宝与微信支付后，很自然产生一种反感，惰性又来了，想在网上找相关的轮子，可是一直没有找到一款自己觉得逞心如意的，要么使用起来太难理解，要么文件结构太杂乱，只有自己撸起袖子干了。

欢迎 Star，欢迎 PR！

hyperf 扩展包请 [传送至这里](https://github.com/yansongda/hyperf-pay)

laravel 扩展包请 [传送至这里](https://github.com/yansongda/laravel-pay)

yii 扩展包请 [传送至这里](https://github.com/guanguans/yii-pay)

## 特点

- 多租户支持
- Swoole 支持
- 灵活的插件机制
- 丰富的事件系统
- 命名不那么乱七八糟
- 隐藏开发者不需要关注的细节
- 根据支付宝、微信最新 API 开发而成
- 高度抽象的类，免去各种拼json与xml的痛苦
- 文件结构清晰易理解，可以随心所欲添加本项目中没有的支付网关
- 方法使用更优雅，不必再去研究那些奇怪的的方法名或者类名是做啥用的
- 内置自动获取微信公共证书方法，再也不用再费劲去考虑第一次获取证书的的问题了
- 符合 PSR2、PSR3、PSR4、PSR7、PSR11、PSR14、PSR18 等各项标准，你可以各种方便的与你的框架集成

## 运行环境

- PHP 7.3+ (v3.1.0 开始需 7.4+)
- composer

## LICENSE

MIT
