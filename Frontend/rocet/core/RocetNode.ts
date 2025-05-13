import {  attribute} from "../interface"
import { Rocet } from "./rocet";

export type props = attribute | string | RocetNode | Array<RocetNode> | undefined;
export type children = string | RocetNode| Array<RocetNode> | undefined;

export interface RocetElement extends JSX.Element { 

}

export class RocetNode
{
    public tag: string
    public props: attribute = {}
    public children: Array<RocetNode> = [];
    public elem?:HTMLElement

    constructor(tag: string, props: props, children: children) {
        console.log(tag, props, children)
        this.tag = tag;
        if (props) {
            this.HtmlContentStringInProps(props);
            this.isObjectProps(props);
        }
        this.HtmlContentStringChildren(children);
        this.RocetNodeContentChildren(children);
        console.log(this)
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
        if (children instanceof RocetNode) {
            this.children.push(children)
        }
        if (Array.isArray(children)) {
            children.forEach((el) => { 
                this.HtmlContentStringChildren(el)
                if (el instanceof RocetNode) {
                    this.children.push(el)
                }
            })
        }

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