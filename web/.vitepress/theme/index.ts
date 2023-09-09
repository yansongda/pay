import { h } from 'vue'
import DefaultTheme from 'vitepress/theme'
import HomePrimary from '@components/Home/Primary.vue'
import HomeAuthorize from '@components/Home/Authorize.vue'

export default {
  ...DefaultTheme,
  Layout() {
    return h(DefaultTheme.Layout, null, {
      'home-hero-before': () => h(HomePrimary),
      'home-features-after': () => h(HomeAuthorize)
    })
  }
}
