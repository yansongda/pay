import versions from '../sidebar/versions'
import { DefaultTheme } from 'vitepress'

let sidebars: DefaultTheme.Sidebar = {}

versions.forEach(async (version) => {
  const path = `/docs/${version}/`

  // @ts-ignore
  sidebars[path] = (await import('../sidebar/v3')).default as DefaultTheme.Sidebar
});

export default sidebars
