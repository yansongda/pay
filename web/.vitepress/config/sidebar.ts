// @ts-nocheck
import { DefaultTheme } from 'vitepress'

let sidebars: DefaultTheme.Sidebar = {
  '/docs/v1': (await import('../sidebar/v1')).default as DefaultTheme.Sidebar,
  '/docs/v2': (await import('../sidebar/v2')).default as DefaultTheme.Sidebar,
  '/docs/v3': (await import('../sidebar/v3')).default as DefaultTheme.Sidebar,
}

export default sidebars
