import { atribute } from "../interface"
import { replaceAll } from "./string"

interface exception { 
     [name:string]:string|Function
}




export const exception:exception = {
     className: (e: HTMLElement | any, p: atribute, n: string) => { e.setAttribute('class', p[n]) },
     class: 'class',
     name: 'name',
     selected: setBoolean,
     src: 'src',
     for: 'for',
     style: setStyle,
     maxlength: 'maxlength',
     minlength: 'minlength',
     rows: 'rows',
     cols: 'cols',
     draggable: 'draggable',
     data: 'data',
     id: 'id',
     href: 'href',
     value: universalSetAttribute,
     textContent: setProperty,
     checked: setBoolean,
     disabled: setBoolean,
     readonly: setBoolean,
     multiple: setBoolean,
     placeholder: setProperty,
     autofocus: setBoolean,
     required: setBoolean
}
export function universalSetAttribute(e: HTMLElement|any, p: atribute, n: string) {
     e[n] = p[n];
     e.setAttribute(n,  p[n])
}
export function setProperty(e: HTMLElement|any, p: atribute, n: string) {
     e[n] = p[n];
}
export function setStyle(e: HTMLElement | any, p: atribute, n: string) { 
     let style = JSON.stringify(p[n])
     style = replaceAll(['"', '{'], '', style)
     style = replaceAll('}', ';', style)
     p[n] = replaceAll(',', '; ', style)
     e.setAttribute(n,  p[n])
}
export function setBoolean(e: HTMLElement | any, p: atribute, n: string){ 
     if (p[n] == true) {
          e.setAttribute(n, n);
     } else { 
          e.removeAttribute(n, n);
     }
}



export function setAttributeElement(Element:HTMLElement|any, props:atribute, NameAttribute:any){

     try {
          if (NameAttribute.startsWith('on')) {
               const eventName = NameAttribute.toLowerCase();
               if (typeof props[NameAttribute] === 'function') {
                    Element[eventName] = props[NameAttribute];
               }
               return;
          }
          if (exception[NameAttribute]) {
               if (typeof exception[NameAttribute] == 'function') { 
                    return exception[NameAttribute](Element, props, NameAttribute)
               }
          }
          if (props[NameAttribute]) { 
               Element.setAttribute(NameAttribute,  props[NameAttribute])
          }
     } catch (err) {
          console.error(`Error: It was not possible to assign the attribute ${NameAttribute} to the element ${Element.tagName} : ${err}`)
     }
}

export function removeAttribute(Element:HTMLElement|any, NameAttribute:any){

     try{
          if (NameAttribute.startsWith('on')) {
               const eventName = NameAttribute.slice(2).toLowerCase();
               Element[eventName] = null
               return;
          }
          Element.removeAttribute(Element);
      }catch(err){
           console.error(`Error: It was not possible to assign the attribute ${NameAttribute} to the element ${Element.tagName} : ${err}` ) 
      }

}
