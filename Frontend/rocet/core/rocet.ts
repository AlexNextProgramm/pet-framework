import { exception } from "./attribute";
import { RocetElement, RocetNode } from "./RocetNode";
import { RocetObject } from "./RocetObject";



type ElementEvent =
  | HTMLInputElement
  | HTMLButtonElement
  | HTMLSelectElement
  | HTMLTextAreaElement;

export class Rocet extends RocetObject {


  private ExecAfter: Array<Function> = [];
  public ExecElements: Array<Function> = [];
  public Elements: Array<HTMLElement> = [];
  private renderObserver: Function = null;
  constructor(data: string | HTMLElement | RocetElement | EventTarget | null = null) {
    super()
    if (data instanceof RocetNode) {
      this.Elements.push(this.create(data))
    }
    if (data instanceof HTMLElement) {
      this.Elements.push(data)
    }
    if (typeof data == 'string') {
      if (this.isHTMLString(data)) {
        this.Elements[0] = this.createElementFromHTML(data);
      } else {
        this.getIt(data);
        if (this.Elements.length == 0) {
          this.watchElement(data)
        }
      }
    }

    return new Proxy(this, {
      get(target: any, prop: string | symbol, receiver) {
        if (typeof prop === 'string') {
          const protoProps = Object.getOwnPropertyNames(Object.getPrototypeOf(target));
          if (protoProps.includes(prop)) {
            const value = target[prop];
            return typeof value === 'function' ? value.bind(target as Rocet) : value
          }
          if (target[prop]) return (target as Rocet).Elements
          if (prop == 'length') return (target as Rocet).Elements.length
          if (target.Elements && target.Elements.length > 0) {
            return (target.Elements[0] as any)[prop];
          }
        }
        return undefined;
      },

      set(target: any, prop: string | symbol, value) {
        const protoProps = Object.getOwnPropertyNames(Object.getPrototypeOf(target));
        if (protoProps.includes(prop as string) && typeof target[prop] != 'function') {
          target[prop] = value;
          return true;
        }
        this.Elements.forEach((el: any) => el[prop] = value);
        return true;
      }

    });
  }
  private isHTMLString(str: string): boolean {
    const htmlPattern = /^\s*<([a-zA-Z]+|!)[^>]*>/;
    return htmlPattern.test(str);
  }

  private createElementFromHTML(htmlString: string): HTMLElement | null {
    const template = document.createElement('template');
    template.innerHTML = htmlString.trim();
    return template.content.firstChild as HTMLElement;
  }
  public getIt(id: string): Rocet {
    this.Elements = Array.from(document.querySelectorAll(id));
    return this;
  }

  public find(selector: string): Rocet {
    const $rocket = r()
    this.Elements.forEach((el: HTMLElement) => {
      const find: NodeListOf<HTMLElement> = el.querySelectorAll(selector)
      find.forEach((findElm: HTMLElement) => {
        $rocket.Elements.push(findElm);
      });
    })
    return $rocket;
  }

  public render(rocet: RocetElement | Function): Rocet {
    if (this.Elements.length == 0) {
      this.renderObserver = typeof rocet == "function" ? rocet : () => rocet;
      return;
    }
    const arr: Array<HTMLElement> = [];
    this.Elements.forEach((el: HTMLElement, i) => {
      let RNode: RocetNode;
      if (typeof rocet == 'function') RNode = rocet(this, i);
      if (RNode instanceof RocetNode) {
        const newElm = this.create(RNode)
        el.replaceWith(newElm);
        arr.push(newElm);
        this.execureElements($(newElm), i)
      }
    })
    this.Elements = arr
    this.execure()
    return this;
  }

  public add(element: RocetElement | RocetNode | HTMLElement | Rocet) {
    if (element instanceof HTMLElement) {
      this.Elements.forEach((el) => {
        el.append(element)
      })
    }
    if (element instanceof RocetNode) {
      this.Elements.forEach((el) => {
        el.append(this.create(element))
      })
    }
    if (element instanceof Rocet) {
      this.Elements.forEach((el) => {
        el.append(element.item(0))
      })
    }
  }

  public create(rocet: RocetNode | Rocet): HTMLElement | ElementEvent {
    if (rocet instanceof Rocet) return rocet.Elements[0];
    const NewCreateElement = <HTMLElement>document.createElement(rocet.type);

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
          r(Element).on(eventName.substring(2, eventName.length), value)
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

  public classAdd(name: string | null = null) {
    if (name) {
      this.Elements.forEach((el) => el.classList.add(name))
    }
  }
  public classReplase(name: string, newname: string) {
    this.Elements.forEach((el) => el.classList.replace(name, newname))
  }
  public classToggle(name: string) {
    this.Elements.forEach((el) => el.classList.toggle(name))
  }

  private execureElements($RocketElem: Rocet, i: any) {
    if (this.ExecElements[i])
      this.ExecElements[i]($RocketElem)
  }

  private execure() {
    if (this.ExecAfter.length != 0) {
      this.ExecAfter.forEach((func: Function) => {
        func(this)
      })
    }
  }
  public addExecAll(fun: Function | Array<Function>): void {
    if (Array.isArray(fun)) {
      fun.forEach((f: Function) => this.addExecAll(f));
    } else {
      this.ExecAfter.push(fun);
    }
  }

  public remove(selector: string | null = null) {
    this.Elements.forEach((el: HTMLElement) => {
      if (selector) {
        el.querySelectorAll(selector).forEach((chil) => chil.remove());
      } else {
        el.remove()
      }

    })
  }

  public attrRemove(name: string) {
    this.Elements.forEach((el: HTMLElement) => {
      el.removeAttribute(name)
    })
    return this;
  }

  public attr(name: string, value: string | null = null) {
    if (typeof value == 'string') {
      this.Elements.forEach((el: HTMLElement) => {
        el.setAttribute(name, value)
      })
    }
    return this.Elements[0]?.getAttribute(name)
  }
  public data(name: string, value: string | null = null) {
    const data = this.Elements[0].dataset;
    if (typeof value == 'string') {
      this.Elements.forEach((el: HTMLElement) => {
        el.setAttribute('data-' + name, value)
      })
    }
    return data[name] || null;
  }

  public val(value: string | null = null) {
    if (typeof value == 'string') {
      this.Elements.forEach((el: ElementEvent) => {
        el.value = value
        el.setAttribute('value', value);
      })
    } else {
      return $(this.item(0)).value || this.Elements[0].getAttribute('value')
    }
  }

  public on(type: string, callback: any) {
    this.Elements.forEach((el: HTMLElement) => {
      el.addEventListener(type, callback);
    })
  }
  public trigger(type: string) {
    this.Elements.forEach((el: HTMLElement) => {
      const eventList: any = el.getEventListeners()
      if (eventList) {
        Object.keys(eventList).forEach((type: string) => {
          eventList[type].forEach((eventObject: any) => {
            if (eventObject.type == type) {
              let event: any = new Event(type, { bubbles: true });
              event.rocketTrigger = true;
              el.dispatchEvent(event);
            }
          })
        })
      }
    })
  }

  static loadPage(callback: (this: Window, ev: Event) => any) {
    window.addEventListener('load', callback);
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

  public addAttributeJSX(Element: RocetElement, item: number = 0): RocetElement {
    const el = this.Elements[item]
    for (let i = 0; i < el.attributes.length; i++) {
      const attr = el.attributes[i];
      Element.props[attr.name] = attr.value;
    }
    return Element;
  }

  public item(key: number = 0): HTMLElement | ElementEvent {
    return this.Elements[key];
  }

  public closest(selector: string): Rocet {
    return new Rocet(this.Elements[0].closest(selector) as HTMLElement)
  }

  public each(callback: Function): void {
    for (const [i, v] of this.Elements.entries()) {
      if (callback($(v), i) === false) {
        break;
      }
    }
  }

  public clone(): Rocet {
    const el = new Rocet(this.Elements[0].cloneNode(true) as HTMLElement);
    el.cloneEvent(this.Elements[0], el.Elements[0]);
    return el;
  }

  public getObjectAttr() {
    const el = this.Elements[0];
    const attrs: any = {};
    for (let i = 0; i < el.attributes.length; i++) {
      const attr = el.attributes[i];
      attrs[attr.name] = attr.value;
    }
    return attrs
  }

  private cloneEvent(el: HTMLElement, chahgeElement: HTMLElement) {
    const eventList: any = el.getEventListeners()
    if (eventList) {
      Object.keys(eventList).forEach((type: string) => {
        eventList[type].forEach((eventObject: any) => {
          if (eventObject.type.startsWith('on')) eventObject.type = eventObject.type.toLowerCase().substring(2, type.length);
          r(chahgeElement).on(eventObject.type, eventObject.listener);
        })
      })
    }
    for (let i = 0; i < el.children.length; i++) {
      this.cloneEvent(el.children[i] as HTMLElement, chahgeElement.children[i] as HTMLElement);
    }
  }

  public isAttr(attr: string): boolean {
    return this.Elements[0].hasAttribute(attr);
  }

  Exec(func: Function | Array<Function>, i: any = null) {
    if (Array.isArray(func)) {
      func.forEach((f: Function) => this.Exec(func));
    } else {
      if (i !== null) {
        this.ExecElements[i] = func;
      } else {
        let isFun = false
        this.ExecAfter.forEach((fun: Function) => {
          if (!isFun)
            isFun = fun.toString() == func.toString(); // поиск одинаковых функций
        });
        if (!isFun) this.ExecAfter.push(func);
      }
    }
  }

  isVisible(): boolean {
    const el = this.item()
    if (!el) return false;
    const style = window.getComputedStyle(el);
    const isDisplayed = style.display !== 'none';
    const isVisible = style.visibility !== 'hidden' && style.opacity !== '0';
    const rect = el.getBoundingClientRect();
    const inDocument = rect.width > 0 && rect.height > 0;
    return isDisplayed && isVisible && inDocument;
  }

  hide() {
    this.each((el: Rocet) => {
      const style = el.attr('style') || '';
      el.attr('style', style + 'display: none;')
    })
  }

  public show() {
    this.each((el: Rocet) => {
      const style = el.attr('style') || '';
      const styles = style.split(';').map(s => s.trim());
      const filteredStyles = styles.filter(s => {
        if (s === '') return false;
        return !s.toLowerCase().startsWith('display');
      });
      if (filteredStyles.length > 0) {
        el.item(0).setAttribute('style', filteredStyles.join('; ') + ';');
      } else {
        el.item(0).removeAttribute('style');
      }
    })
  }

  public text(str: string | null = null) {
    if (str === null) {
      return this.Elements[0].textContent;
    } else {
      this.Elements[0].textContent = str;
    }
  }
}

export function r(data: string | HTMLElement | RocetElement | EventTarget | null = null) {
  return new Rocet(data);
}
export function $(data: string | HTMLElement | RocetElement | EventTarget | null = null) {
  return new Rocet(data);
}


