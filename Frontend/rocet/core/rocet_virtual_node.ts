import { atribute, rocet } from "../interface"

export function RocetNode(tag:string, props:atribute|string|undefined|rocet|Array<rocet> = undefined, children:string|Array<rocet>|undefined|rocet = undefined):rocet {
 
    let rProps:atribute = {}
    let rChildren: Array<rocet> = []
    

    if(props &&typeof props  == 'string'){
        if(props.indexOf('</') >= 0 && props.indexOf('>') >= 0 || props.indexOf('<br>') >= 0 ){
            rProps.innerHTML = props
        }else{
            rProps.textContent = props
        }
    }

    if(props && typeof props  == 'object'){
        if(Array.isArray(props)){
            rChildren = props
        }else{
          props.tag? rChildren.push(<rocet>props): rProps = <atribute>props
         }
    }

    if(typeof children == 'string'){
        if(children.indexOf('<') >= 0 && children .indexOf('</')  >= 0 || children .indexOf('<br>') >= 0 ){
                if(tag == 'code' ){
                    rProps.textContent? rProps.textContent = rProps.textContent + children: rProps.textContent  = children
                }else{
                    rProps.innerHTML? rProps.innerHTML = rProps.innerHTML + children: rProps.innerHTML  = children
                }
        }else{
            rProps.textContent? rProps.textContent = rProps.textContent + children: rProps.textContent  = children
        }
    }

    if(!Array.isArray(children) && typeof children == 'object' && children.tag){
            rChildren.push({tag:children.tag,  props:children.props,  children:children.children})
    }

    if(Array.isArray(children)) rChildren.push(...children)
    return {tag:tag, props:rProps, children:rChildren}
}
