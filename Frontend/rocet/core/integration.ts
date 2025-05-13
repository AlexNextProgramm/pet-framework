import { attribute} from "../interface"
import { Rocet } from "./rocet"
import { RocetNode } from "./RocetNode"



//** интеграция в реакт ))
export function integ(tag:string, props:any, ...children:any): RocetNode
{
    return new RocetNode(tag, props, children)
}