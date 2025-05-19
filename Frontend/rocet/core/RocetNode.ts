import {  attribute} from "../interface"
import { Rocet } from "./rocet";

export type props = attribute | string | RocetNode | Array<RocetNode> | undefined;
export type children = string | RocetNode| Array<RocetNode> | undefined;

export interface RocetElement extends JSX.Element {}

export class RocetNode implements RocetElement
{
    public props: attribute = {}
    public children: Array<RocetNode> = [];
    public type: any;
    public key: string;
    public elem?: HTMLElement

    constructor(tag: string, props: props, children: children) {
        this.type = tag;
        if (props) {
            this.HtmlContentStringInProps(props);
            this.isObjectProps(props);
        }
        this.HtmlContentStringChildren(children);
        this.RocetNodeContentChildren(children);
    }
        
    private HtmlContentStringInProps(props: props) {
        if (typeof props == 'string') {
            const htmlRegex = /<[^>]+>/;
            if (htmlRegex.test(props)) {
                this.props.innerHTML = props
            } else {
                this.props.textContent = props
            }
        }
    }
    private isObjectProps(props: props) { 
        if (typeof props == 'object') { 
            this.props = <attribute>props;
        }
    }

    private RocetNodeContentChildren(children: children ) {
        if (this.isPrototypeStructure(children))
        {
            this.children.push(children as RocetNode)
        }
        if (Array.isArray(children)) {
            children.forEach((el) => { 
                this.HtmlContentStringChildren(el)
                if (this.isPrototypeStructure(el)) {
                    this.children.push(el as RocetNode)
                }
            })
        }
    }

    private isPrototypeStructure(obj: any): boolean
    {
        if (typeof obj != 'object' || Array.isArray(obj) || obj === null) return false;
        const proto = Object.getPrototypeOf(obj);
        const protoProps = Object.getOwnPropertyNames(RocetNode);
        const objProps = Object.keys(obj);

        const dataProtoProps = protoProps.filter(prop => {
            const descriptor = Object.getOwnPropertyDescriptor(proto, prop);
            return descriptor && typeof descriptor.value !== 'function';
        });
        for (const prop of dataProtoProps) {
            if (!objProps.includes(prop)) {
                return false;
            }
        }
        return true;
    }

    private HtmlContentStringChildren(children:children) {
        if (typeof children == 'string') {
            const htmlRegex = /<[^>]+>/;
            if (htmlRegex.test(children)) {
                this.props.innerHTML  = this.props.innerHTML ? this.props.innerHTML  + children : children;
            } else {
                this.props.textContent = this.props.textContent? this.props.textContent + children : children;
            }
        }
    }
}