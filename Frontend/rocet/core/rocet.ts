import { EventChange } from "../config.rocet";
import { EventChangeValue, attribute, settingRocet } from "../interface";
import { exception} from "./attribute";
import { RocetElement, RocetNode } from "./RocetNode";




type ElementEvent =
  | HTMLInputElement
  | HTMLButtonElement
  | HTMLSelectElement
  | HTMLTextAreaElement;

export class Rocet {


  public ExecAfter: Array<Function> = [];
  public Element: HTMLElement;


  constructor(data: string | HTMLElement | RocetElement) {
    if (data instanceof RocetNode) { 
      this.Element = this.create(data)
    }
    if (data instanceof HTMLElement) {
      this.Element = data;
    }
    if (typeof data == 'string') { 
      this.Element = this.getIt(data);
    }
  }

  public getIt(id: string): HTMLElement
  {
    let element = <HTMLElement|null>document.querySelector(id);
    if (element instanceof HTMLElement) {
      this.Element = element;
    } else {
      console.error("Error: Element not found Rocet assembly not possible");
    }
    return element;
  }

  public render(rocet:RocetElement|Function) {
    if (typeof rocet == 'function') rocet = rocet();
    if (rocet instanceof RocetNode) {
      const newElm = this.create(rocet)
      this.Element.replaceWith(newElm);
      this.Element = newElm
      this.execure()
    }
  }

  public add(jsx: any, selector: string | null = null) {
    if (selector) {
      this.Element.querySelector(selector).append(this.create(jsx))
    } else {
      this.Element.append(this.create(jsx))
    }
  }

  public create(rocet: RocetNode): HTMLElement | ElementEvent {

      const NewCreateElement = <HTMLElement>document.createElement(rocet.tag);
    
        for (let key in rocet.props)
      this.setAttribute(NewCreateElement, key, rocet.props[key]);
    
        rocet.children.forEach((RocetElement: RocetNode) => {
           NewCreateElement.append(this.create(RocetElement));
        });
      rocet.elem = NewCreateElement
      return NewCreateElement;
  }

  private setAttribute(Element: HTMLElement|any, name: string, value:Function|string) { 
    try {
       
          if (name.startsWith('on')) {
               const eventName = name.toLowerCase();
               if (typeof value === 'function') {
                     Element[eventName] = value;
               }
               return;
          }
          if (exception[name]) {
               if (typeof exception[name] == 'function') { 
                    return exception[name](Element, name, value)
               }
          }
          if (value) { 
               Element.setAttribute(name, value)
          }
     } catch (err) {
          console.error(`Error: It was not possible to assign the attribute ${name} to the element ${Element.tagName} : ${err}`)
     }
  }


  private execure(){
          if(this.ExecAfter.length != 0){
            this.ExecAfter.forEach((func:Function)=>{
              func()
            })
          }
  }

  public delete(selector:string) { 
      this.Element.querySelectorAll(selector).forEach((el) => {
        el.remove();
      })
  }

  public attr(name: string, value: string | null = null) {
    if (value) { 
      return this.Element.setAttribute(name, value)
    }
    return this.Element.getAttribute(name)
  }
}
