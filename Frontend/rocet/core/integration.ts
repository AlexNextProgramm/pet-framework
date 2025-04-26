import { rocet } from "../interface"
import { Rocet } from "./rocet"
import { RocetNode } from "./rocet_virtual_node"



//** интеграция в реакт ))
export function integ(tag:string|Function, props:any|null, ...children:any):rocet{

    // Проверка на то что попадает в массив 
   const rChildren = children.filter((rocet:any, i:number) =>{
    if(i == 0 && typeof rocet == 'string') return true
    if(rocet?.tag) return true
    });

   if(typeof tag == 'function'){

    if(props == null) return tag(...rChildren)

    return tag(props, ...rChildren)

   }else{
       
       if(typeof rChildren[0] == "string"){
        
           return RocetNode(tag, props, rChildren[0])
       }
       return RocetNode(tag, props, rChildren)

   }
}