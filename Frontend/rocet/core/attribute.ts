import { attribute } from "../interface"
import { replaceAll } from "./string"

interface exception { 
     [name:string]:string|Function
}




export const exception:exception = {
     className: (e: HTMLElement | any, k:string, n: string) => { e.setAttribute('class', n) },
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
     required: setBoolean,
     innerHTML: setInnerHTML,
}
export function universalSetAttribute(e: HTMLElement|any, k: string, n: string) {
     e[k] = n;
     e.setAttribute(k, n)
}
export function setProperty(e: HTMLElement|any, k: string, n: string) {
     e[k] = n;
}
export function setStyle(e: HTMLElement | any, k: string, n: string) { 
     let style = JSON.stringify(n)
     style = replaceAll(['"', '{'], '', style)
     style = replaceAll('}', ';', style)
     n = replaceAll(',', '; ', style)
     e.setAttribute(k,  n)
}
export function setBoolean(e: HTMLElement | any, k: attribute, n: boolean){ 
     if (n == true) {
          e.setAttribute(k, '');
     } else { 
          e.removeAttribute(k, n);
     }
}
export function setInnerHTML(e: HTMLElement | any, k: attribute, n: string){ 
     e.innerHTML = n;
}
