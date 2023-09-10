// @ts-nocheck
import { DefaultTheme } from 'vitepress'
import fg from 'fast-glob'

const versionFiles = fg.sync('.vitepress/sidebar/v[[:digit:]].js', { objectMode: true, absolute: true })

let sidebars: DefaultTheme.Sidebar = {}

versionFiles.forEach(async (file) => {
  const version = file.name.replace('.js', '')
  
  sidebars[`/docs/${version}/`] = (await import(file.path)).default as DefaultTheme.Sidebar
});

export default sidebars
