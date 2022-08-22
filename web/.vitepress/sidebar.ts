// @ts-ignore
import path from 'path'
import versions from "./versions";

let sidebars = versions.reduce(
    (sidebars, version) => ({
        ...sidebars,
        [`/docs/${version}/`]: require(path.join(
            __dirname, `../docs/${version}/sidebar`
        ))
    }),
    {}
);

export default sidebars;
