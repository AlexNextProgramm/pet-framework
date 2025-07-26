interface RocetObjectInterface { 
    [name: string]: any
}

export class RocetObject implements RocetObjectInterface{ 
    classList: DOMTokenList
    value: string
    className: string
    checked: boolean
    tagName: string
    onclick: Function | null
    length: number
    offsetTop: number
    files: any
    src:string
}