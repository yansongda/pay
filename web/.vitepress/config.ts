import { defineConfig } from 'vitepress';
import { resolve } from 'path';
import nav from './nav';
import sidebar from "./sidebar";

export default defineConfig({
  vite: {
    resolve: {
      alias: {
        '@': resolve(__dirname, './theme/components')
      },
    },
  },
  lang: 'zh-CN',
  title: 'Pay',
  description: '可能是我用过的最优雅的支付宝SDK、微信支付SDK、银联支付SDK 了; yansongda pay 让支付开发更简单',
  lastUpdated: true,
  head: [
    ['link', { rel: 'icon', href: '/images/icon.png' }],
    ['script', { defer: '', src: 'https://static.cloudflareinsights.com/beacon.min.js', 'data-cf-beacon': '{"token": "0bff42137b6e45f0be5c8256c00cfc3a"}' }]
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
    },
    search: {
      provider: 'algolia',
      options: {
        appId: 'UJ4V77W9P7',
        apiKey: '181f0abb91e2400ab3c9907a4ab29532',
        indexName: 'yansongda'
      }
    }
  }
})
