import { EventChange } from "../config.rocet";
import { EventChangeValue, atribute, rocet, settingRocet } from "../interface";
import { removeAttribute, setAttributeElement } from "./attribute";

export const NAME: { [name: string]: ElementEvent } = {};
export const ID: { [name: string]: HTMLElement } = {};
export const VALUE: { [name: string]: string } = {};


type ElementEvent =
  | HTMLInputElement
  | HTMLButtonElement
  | HTMLSelectElement
  | HTMLTextAreaElement;

export class Rocet {


  public ExecAfter:Array<Function>
  Element: HTMLElement;
  RocetVirtualNode: rocet | undefined;
  private RocetVirtualFunction: Function | undefined;
  private id:string|HTMLElement

  constructor(id: string | HTMLElement = "body") {
    if (id === null) id = "body";
    this.Element = typeof id == 'string'? this.open(id): id;
    this.RocetVirtualNode;
    this.id = id
    this.ExecAfter = []

  }

  private open(id: string): HTMLElement {
    let element = <HTMLElement|null>document.querySelector(id);
    if(!element)element = <HTMLElement>document.querySelector("#" + id)
    if(!element)element = <HTMLElement>document.querySelector("." + id);
    
      if(!element){
           console.error("Error: Element not found Rocet assembly not possible");
      }else{
          return element;
      }
  }

  public render(rocet: Function|undefined = undefined) {
    if(this.Element == undefined) return console.error("Error: Element not found Rocet assembly not possible");
    
    if (typeof rocet == 'function') this.RocetVirtualFunction = rocet
    const NewVirtualNode = this.RocetVirtualFunction(this)

    
    if(!this.RocetVirtualNode){
      if(this.Element.children.length != 0) this.Element.innerHTML = ''
      this.Element.replaceWith(this.create(NewVirtualNode));
      this.Element = typeof this.id == 'string'? this.open(this.id): this.id
    } else {
      this.compare( this.Element, NewVirtualNode, this.RocetVirtualNode );
    }

    this.RocetVirtualNode = NewVirtualNode; // Должна присваиваться После
    this.execure()
  }

  compare(elem: HTMLElement | ElementEvent, NewRVN: rocet, RVN: rocet){

    
    NewRVN.elem = elem
    if (elem.localName != NewRVN.tag){

        elem.replaceWith(this.create(NewRVN));

    }else{

        const lenNew = Object.keys(NewRVN.props);
        const lenOld: Array<string> = Object.keys(RVN.props);

        lenNew.forEach((key: string) => { setAttributeElement(elem, NewRVN.props, key); });
        lenOld.forEach((key: string) => { if(!lenNew.includes(key))  removeAttribute(elem, key); })

        if(NewRVN.children.length >= RVN.children.length){

            NewRVN.children.forEach((ch, i)=>{

              if(RVN.children[i]){

                if(RVN.children[i].elem){
                  this.compare(<HTMLElement>RVN.children[i].elem, NewRVN.children[i], RVN.children[i])
                }else{
                  console.error(`Error: No Element:${RVN.children[i].elem}`)
                }
              }else{
                elem.insertAdjacentElement("beforeend",this.create(ch))
              }
            })

        }

        if(NewRVN.children.length < RVN.children.length){
            RVN.children.forEach((ch, i)=>{
                if(NewRVN.children[i]){
                   this.compare(<HTMLElement>RVN.children[i].elem, NewRVN.children[i], RVN.children[i])
                }else{
                   RVN.children[i].elem.remove()
                }
              })
        }
      }

  }

  private create(rocet: rocet): HTMLElement | ElementEvent {
   

    try{
      const NewCreateElement = <HTMLElement>document.createElement(rocet.tag);
    
      if (Object.keys(rocet.props).length != 0) {
        for (let key in rocet.props)
          setAttributeElement(NewCreateElement, rocet.props, key);

        if (EventChange.tagElement.includes(rocet.tag))
          this.events(rocet.props, <ElementEvent>NewCreateElement);
        if (NewCreateElement.hasAttribute("name"))
          NAME[<string>rocet.props.name] = <ElementEvent>NewCreateElement;
        if (NewCreateElement.hasAttribute("name"))
          ID[<string>rocet.props.id] = NewCreateElement;
      }

      if (rocet.children.length != 0) {
        rocet.children.forEach((RocetElement: rocet) => {
          if(!RocetElement?.tag){
            NewCreateElement.innerHTML += RocetElement
          }else{
              NewCreateElement.append(this.create(RocetElement));
            }
        });
      }
      rocet.elem = NewCreateElement
      return NewCreateElement;
    }catch(err){
      
      console.error(`Error: Rocet.create => {tag:${rocet.tag}, props:${rocet.props}, children:[${rocet.children}]}`+ err);
    }
  }



  private events(props: atribute, element: ElementEvent | any) {
        try {

        EventChange.event.forEach((event: any) => {
            element[event] = (ev: Event) => {
            VALUE[<any>props.name] = element.value;
            if (props[event]) <Function>props[event](ev);
            };
        });

        } catch (err) {
        console.error("Error: Rocet.events => " + err);
        }
  }


 static DeleteGlobalVALUE(name:string){
    delete VALUE[name]
    delete NAME[name]
  }

  State(WatchObject:{[name:string]:any}|undefined){

    let DOM = this

    if(WatchObject){
          return new Proxy(WatchObject,{
              set: function(target:any, props:any, newValue:any){
                  target[props] = newValue
                  DOM.render()
                  return true
              }
          })
    }else{
      DOM.render()
    }
    console.log(DOM)
  }

  private execure(){
          if(this.ExecAfter.length != 0){
            this.ExecAfter.forEach((func:Function)=>{
              func()
            })
          }
  }
}
