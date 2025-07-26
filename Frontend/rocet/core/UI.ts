import { Rocet } from "./rocet";


export class UI{
    private Elements: HTMLElement[] = [];
    public callback: Function = null;
    public events: Array<Function> = [];
    private renderObserver:Function
    public countElemets = 0; 
    constructor(id:string) { 
        this.open(id);
    }

    private open(id: string)
    {
        this.Elements = Array.from(document.querySelectorAll(id));
        if (this.Elements.length === 0) this.Elements = Array.from(document.querySelectorAll("#" + id));
        if (this.Elements.length === 0) this.Elements = Array.from(document.querySelectorAll("." + id));
        if (this.Elements.length === 0) { 
            this.watchElement(id, this)
        }
    }

    public render(renderFunction: Function | undefined = undefined) {
        if (this.Elements.length == 0) { 
            this.renderObserver = renderFunction;
            return;
        }
        this.Elements.forEach((element:HTMLElement) => {
            const context = new Rocet(element)
            if (this.callback) { 
                this.callback(context);
            }
            context.addExecAll(this.events);
            context.render(()=>renderFunction(context));
        });
    }

    private watchElement(selector: string, UI:UI) {

        return new Promise((resolve) => {
            const observer = new MutationObserver(() => {
                UI.Elements = Array.from(document.querySelectorAll(selector));
                if (UI.Elements.length != 0) {
                    UI.render(UI.renderObserver);
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        });
    }
    static selector(selector:string ) {
         return new Promise((resolve) => {
            const observer = new MutationObserver(() => {
                const el = document.querySelector(selector);
                if (el) {
                    resolve(el)
                    observer.disconnect();
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        });
    }
}