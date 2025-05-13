


export interface settingRocet{
    EventChengeValue:EventChangeValue
}
export interface EventChangeValue{
    
        tagElement:Array<string>
        event:Array<string>
    
}

// Подстраиваем под свои аттрибуты 
// Нужно описать интерфейс style
declare module "react" {
    interface Attributes extends attribute{
     style?:any
    }
}

declare global {
    namespace JSX {
      interface IntrinsicElements {
        [name:string]: any;
      }
    }
  }

export interface attribute  {
    [name: string ]: any;
    className?: string;
    id?: string;
    innerHTML?: string;
    type?: string;
    value?: string;
    textContent?: string;
    src?: string;
    alt?: string;
    onclick?: any;
    href?: string;
    name?: string;
    for?: string;
    placeholder?: string;
    onkeydown?: ((ev: KeyboardEvent) => any) | null;
    onkeyup?: ((this: GlobalEventHandlers, ev: KeyboardEventInit) => any) | null;
    onblur?: ((this: GlobalEventHandlers, ev: Event) => any) | null;
    onchange?: ((this: GlobalEventHandlers, ev: Event) => any) | null;
    onfocus?:((this: GlobalEventHandlers, ev: Event) => any) | null;
    ondrop?:((this:GlobalEventHandlers, ev:Event)=>any) | null;
    ondragstart?:((this:GlobalEventHandlers, ev:Event)=>any) | null;
    ondragover?:((this:GlobalEventHandlers, ev:Event)=>any) | null;
    ontouchmove?:((this:Window, ev:TouchEvent)=>any) | null
    maxlength?: string;
    action?: string;
    minlength?: string;
    autocomplete?: string;
    pattern?: string;
    rel?: string;
    loading?: string;
    referrerpolicy?: string;
    allowfullscreen?: string;
    step?: string;
    max?: string;
    enctype?: string;
    method?: string;
    data?: string;
    checked?:boolean
    localname?:string
    title?:string
    selected?:boolean
    required?: boolean
    rows?:string|number
    cols?:string
    draggable?:boolean
    outerHTML?:string
}

