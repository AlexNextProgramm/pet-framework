import { atribute } from "../interface"
import { replaceAll } from "./string"






const exception:any= {
 className:'class',
 class:'class',
 name:'name',
 selected:'selected',
 src:'src',
 for:'for',
 style:'style',
 maxlength:'maxlength',
 minlength:'minlength',
 rows:'rows',
 cols:'cols',
 draggable:'draggable',
 data:'data',
 id:'id',
 href:'href',
 value:"value"
}
const universal:any = {
     value:"value"
}


export function setAttributeElement(Element:HTMLElement|any, props:atribute, NameAttribute:any){

     try{

          if(exception[NameAttribute]){

             

               if(NameAttribute == 'style'){
                   let style = JSON.stringify(props[NameAttribute])
                   style = replaceAll(['"', '{'],'', style)
                   style = replaceAll('}',';', style)
                   props[NameAttribute] = replaceAll(',','; ', style)
               }


               if(!Element.hasAttribute(NameAttribute) || Element.getAttribute(NameAttribute) != props[NameAttribute]){
                    switch(NameAttribute){
                         case "selected": if(props[NameAttribute] == true) Element.setAttribute(exception[NameAttribute], 'selected'); break;
                         default:
                              Element.setAttribute(exception[NameAttribute], props[NameAttribute]);

                    }
                    if(universal[NameAttribute]) Element[NameAttribute] = props[NameAttribute]
               }
          }else{


               const attr =  NameAttribute

               if(react_default_atribute[NameAttribute]) NameAttribute = react_default_atribute[NameAttribute]
               if(Element[NameAttribute] != props[attr])  Element[NameAttribute] = props[attr]

          }

     }catch(err){

          console.error(`Error: It was not possible to assign the attribute ${NameAttribute} to the element ${Element.tagName} : ${err}` ) 

     }
}

export function removeAttribute(Element:HTMLElement|any, NameAttribute:any){

     try{
          // if(NameAttribute == 'name') Rocet.DeleteGlobalVALUE(NameAttribute)
          if(exception[NameAttribute]){
               Element.removeAttribute(Element)
   
          }else{
              if(react_default_atribute[NameAttribute]) NameAttribute = react_default_atribute[NameAttribute]
                    Element[NameAttribute] = default_atribute[NameAttribute]
               
       
          }
   
      }catch(err){
   
           console.error(`Error: It was not possible to assign the attribute ${NameAttribute} to the element ${Element.tagName} : ${err}` ) 
   
      }

}
export const react_default_atribute:any = {
     onClick:'onclick',
     onChange:'onchange'
}


export const default_atribute:any = {
     onabort:null,
     onanimationend:null,
     onanimationiteration:null,
     onanimationstart:null,
     onauxclick:null,
     onbeforecopy:null,
     onbeforecut:null,
     onbeforeinput:null,
     onbeforematch:null,
     onbeforepaste:null,
     onbeforetoggle:null,
     onbeforexrselect:null,
     onblur:null,
     oncancel:null,
     oncanplay:null,
     oncanplaythrough:null,
     onchange:null,
     onclick:null,
     onclose:null,
     oncontentvisibilityautostatechange:null,
     oncontextlost:null,
     oncontextmenu:null,
     oncontextrestored:null,
     oncopy:null,
     oncuechange:null,
     oncut:null,
     ondblclick:null,
     ondrag:null,
     ondragend:null,
     ondragenter:null,
     ondragleave:null,
     ondragover:null,
     ondragstart:null,
     ondrop:null,
     ondurationchange:null,
     onemptied:null,
     onended:null,
     onerror:null,
     onfocus:null,
     onformdata:null,
     onfullscreenchange:null,
     onfullscreenerror:null,
     ongotpointercapture:null,
     oninput:null,
     oninvalid:null,
     onkeydown:null,
     onkeypress:null,
     onkeyup:null,
     onload:null,
     onloadeddata:null,
     onloadedmetadata:null,
     onloadstart:null,
     onlostpointercapture:null,
     onmousedown:null,
     onmouseenter:null,
     onmouseleave:null,
     onmousemove:null,
     onmouseout:null,
     onmouseover:null,
     onmouseup:null,
     onmousewheel:null,
     onpaste:null,
     onpause:null,
     onplay:null,
     onplaying:null,
     onpointercancel:null,
     onpointerdown:null,
     onpointerenter:null,
     onpointerleave:null,
     onpointermove:null,
     onpointerout:null,
     onpointerover:null,
     onpointerrawupdate:null,
     onpointerup:null,
     onprogress:null,
     onratechange:null,
     onreset:null,
     onresize:null,
     onscroll:null,
     onscrollend:null,
     onsearch:null,
     onsecuritypolicyviolation:null,
     onseeked:null,
     onseeking:null,
     onselect:null,
     onselectionchange:null,
     onselectstart:null,
     onslotchange:null,
     onstalled:null,
     onsubmit:null,
     onsuspend:null,
     ontimeupdate:null,
     ontoggle:null,
     ontransitioncancel:null,
     ontransitionend:null,
     ontransitionrun:null,
     ontransitionstart:null,
     onvolumechange:null,
     onwaiting:null,
     onwebkitanimationend:null,
     onwebkitanimationiteration:null,
     onwebkitanimationstart:null,
     onwebkitfullscreenchange:null,
     onwebkitfullscreenerror:null,
     onwebkittransitionend:null,
     onwheel:null,


     isConnected:true,
     isContentEditable: false,
     hidden:false,
     inert:false,


     spellcheck:true,
     value:""
}


