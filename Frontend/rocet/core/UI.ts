import { Rocet } from "./rocet";


export class UI{
    private Elements: HTMLElement[] = [];
    public callback: Function = null;

    constructor(id:string) { 
        this.open(id);
    }

    private open(id: string)
    {
        this.Elements = Array.from(document.querySelectorAll(id));
        if(this.Elements.length === 0) this.Elements = Array.from(document.querySelectorAll("#" + id))
        if(this.Elements.length === 0) this.Elements = Array.from(document.querySelectorAll("." + id));
    }

    public render(renderFunction:Function|undefined = undefined) {
        this.Elements.forEach((element:HTMLElement) => {
            const context = new Rocet(element)
            if (this.callback) { 
                this.callback(context);
            }
            context.render(renderFunction);
        });
    }
}