import { attribute } from "../interface"
import { children, props, RocetNode } from "./RocetNode"

export function input(props:props = undefined, children:children = undefined):RocetNode{
    return new RocetNode('input', props, children)
}

export function div(props:props = undefined, children:children = undefined ):RocetNode{
       return new RocetNode('div', props, children)
}

export function h1(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('h1', props, children)
}

export function h2(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('h2', props, children)
}

export function h3(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('h3', props, children)
}

export function h4(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('h4', props, children)
}

export function h5(props:props = undefined, children:children = undefined):RocetNode{
    return new RocetNode('h4', props, children)
}

export function img(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('img', props, children)
}

export function body(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('body', props, children)
}

export function a(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('a', props, children)
}

export function nav(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('nav', props, children)
}

export function btn(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('button', props, children)
}

export function button(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('button', props, children)
}

export function p(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('p', props, children)
}

export function label(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('label', props, children)
}

export function ul(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('ul', props, children)
}

export function ol(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('ol', props, children)
}

export function li(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('li', props, children)
}

export function option(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('option', props, children)
}

export function select(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('select', props, children)
}
export function table(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('table', props, children)
}

export function tr(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('tr', props, children)
}

export function th(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('th', props, children)
}

export function td(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('td', props, children)
}

export function textarea(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('textarea', props, children)
}

export function span(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('span', props, children)
}

export function clipPath(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('clipPath', props, children)
}

export function path(props:props = undefined, children:children = undefined):RocetNode{
       return new RocetNode('path', props, children)
}
//    ====Сприсок может пополняться