import versions from "./versions"

let sidebars = versions.reduce(
  (sidebars, version) => {
    return {
      ...sidebars,
      [`/docs/${version}/`]: import(`../docs/${version}/sidebar.ts`)
    }
  },
  {}
)

export default sidebars;
