import { h } from 'vue'
import DefaultTheme from 'vitepress/theme'
import HomePrimary from './components/HomePrimary.vue'
import HomeAuthorize from './components/HomeAuthorize.vue'

export default {
    ...DefaultTheme,
    Layout() {
        return h(DefaultTheme.Layout, null, {
            'home-hero-before': () => h(HomePrimary),
            'home-features-after': () => h(HomeAuthorize)
        })
    }
}
