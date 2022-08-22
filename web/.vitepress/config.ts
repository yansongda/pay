import { defineConfig } from 'vitepress';
import nav from './nav';
import sidebar from "./sidebar";

export default defineConfig({
    lang: 'zh-CN',
    title: 'Pay',
    description: '让支付开发更简单',
    lastUpdated: true,
    head: [
        ['link', { rel: 'icon', href: '/images/icon.png' }]
    ],
    themeConfig: {
        logo: '/images/logo2.png',
        nav: nav,
        sidebar: sidebar,
        socialLinks: [
            { icon: 'github', link: 'https://github.com/yansongda/pay' },
        ],
        editLink: {
            pattern: 'https://github.com/yansongda/pay/edit/master/web/:path',
            text: 'Edit this page on GitHub'
        },
        footer: {
            message: 'Released under the MIT License.',
            copyright: 'Copyright © 2017-present yansongda'
        }
    }
})
