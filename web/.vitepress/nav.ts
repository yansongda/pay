export default [
    { text: 'Home', link: '/' },
    {
        text: '版本',
        items: [
            {
                text: '停止维护',
                items: [
                    { text: 'v1.x', link: '/docs/v1/', activeMatch: '^/docs/v1/' }
                ]
            },
            {
                text: '安全支持',
                items: [
                    { text: 'v2.x', link: '/docs/v2/', activeMatch: '^/docs/v2/' }
                ]
            },
            {
                text: '积极开发中',
                items: [
                    { text: 'v3.x', link: '/docs/v3/', activeMatch: '^/docs/v3/' }
                ]
            }
        ]
    }
]
