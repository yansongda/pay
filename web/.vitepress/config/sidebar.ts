import { DefaultTheme } from 'vitepress'
import fg from 'fast-glob'

const versionFiles = fg.sync('.vitepress/sidebar/v[[:digit:]].ts', { objectMode: true, absolute: true })

let sidebars: DefaultTheme.Sidebar = {}

versionFiles.forEach(async (file) => {
  const version = file.name.replace('.ts', '')

  // @ts-ignore
  sidebars[`/docs/${version}/`] = (await import(file.path)).default as DefaultTheme.Sidebar

  // @ts-ignore
  sidebars[`/docs/${version}/`] = (await import('../sidebar/v3')).default as DefaultTheme.Sidebar
});

export default sidebars
