import versions from './versions'
import fg from 'fast-glob'

// const mdModules = fg.sync(['./docs/**/sidebar.ts'], { absolute: true })

// console.log(mdModules)

// const a = await import(mdModules[0])

// console.log(a)

let sidebars = versions.reduce(
  async (sidebars, version) => {
    return {
      ...sidebars,
      [`/docs/${version}/`]: await import(`../docs/${version}/sidebar.ts`)
    }
  },
  {}
)

export default sidebars
