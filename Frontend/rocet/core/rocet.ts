import { EventChange } from "../config.rocet";
import { EventChangeValue, attribute, settingRocet } from "../interface";
import { exception } from "./attribute";
import { RocetElement, RocetNode } from "./RocetNode";




type ElementEvent =
  | HTMLInputElement
  | HTMLButtonElement
  | HTMLSelectElement
  | HTMLTextAreaElement;

export class Rocet {


  public ExecAfter: Array<Function> = [];
  public Elements: Array<HTMLElement> = [];
  private renderObserver:Function = null;

  constructor(data: string | HTMLElement | RocetElement) {
    if (data instanceof RocetNode) {
      this.Elements.push(this.create(data))
    }
    if (data instanceof HTMLElement) {
      this.Elements.push(data)
    }
    if (typeof data == 'string') {
       this.getIt(data);
       if (this.Elements.length == 0) { 
         this.watchElement(data)
       }
    }
  }

  public getIt(id: string){
    this.Elements = Array.from(document.querySelectorAll(id));
  }

  public render(rocet: RocetElement | Function) {
      if (this.Elements.length == 0) { 
            this.renderObserver = ()=> typeof rocet == "function" ? rocet(this): rocet;
            return;
        }
    if (typeof rocet == 'function') rocet = rocet(this);
    if (rocet instanceof RocetNode) {
      const newElm = this.create(rocet)
      const arr:Array<HTMLElement> = [];
      this.Elements.forEach((el:HTMLElement) => {
        el.replaceWith(newElm);
        arr.push(newElm);
      })
      this.Elements = arr
      this.execure()
    }
  }

  public add(jsx: any, selector: string | null = null) {
    if (selector) {
      this.Elements.forEach((el) => { 
        el.querySelector(selector).append(this.create(jsx))
      })
    } else {
       this.Elements.forEach((el) => { 
        el.append(this.create(jsx))
      })
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

  private setAttribute(Element: HTMLElement | any, name: string, value: Function | string) {
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

  private execure() {
    if (this.ExecAfter.length != 0) {
      this.ExecAfter.forEach((func: Function) => {
        func()
      })
    }
  }

  public remove(selector: string|null = null) {
    this.Elements.forEach((el: HTMLElement) => {
      if (selector) {
        el.querySelectorAll(selector).forEach((chil) => chil.remove());
      } else { 
        el.remove()
      }

    })
  }

  public attr(name: string, value: string | null = null) {
    if (value) {
      this.Elements.forEach((el: HTMLElement) => {
        el.setAttribute(name, value)
      })
    }
    return this.Elements[0]?.getAttribute(name)
  }

  public on(type: string, callback: any) {
    this.Elements.forEach((el: HTMLElement) => { 
      el.addEventListener(type, callback);
    })
  }

   private watchElement(selector: string) {

        return new Promise((resolve) => {
            const observer = new MutationObserver(() => {
                this.Elements = Array.from(document.querySelectorAll(selector));
                if (this.Elements.length != 0) {
                    this.render(this.renderObserver);
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        });
    }
}
