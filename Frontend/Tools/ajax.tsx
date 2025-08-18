
import { $ } from "../rocet/core/rocet";
import { Fire } from "./Fire";
import { inputTextFormRender } from "./UI/input";

export class ajax {
    static async send(name:string, body:any, isJson:boolean = true) {
        return JSON.parse(await ajax.post(body, {}, '/ajax/' + name));
    }


    static async post(data: BodyInit | null | any | HTMLFormElement, headers:any = {}, href:string = null) {
        let body: any;
        let url: string = href || location.href;
        if (data instanceof HTMLFormElement) {
            body = new FormData(data);
            this.checkboxNormalize(data, body);
            if (data.hasAttribute('name')) { 
                headers['form-name'] = data.getAttribute('name');
            }

            body.append('csrf-token', data.getAttribute('csrf-token'));
        } else { 
            body = new FormData();
            Object.keys(data).forEach((key: string) => {
                if (typeof data[key] == 'object' && !(data[key] instanceof File)) {
                    body.append(key, JSON.stringify(data[key]));
                } else {
                    body.append(key, data[key]);
                }
            });
        }

       const result =  await fetch(url, {
            method: 'POST',
            body,
            headers,
            redirect: 'follow'
       })
        if (result.headers.get('Content-type') == 'application/json;') { 
            return await result.json();
        }
        return await result.text();
    }

    public static eventForm(data: any = {}): boolean
    {
        if (data?.type == 'fire') { Fire.show(data); return false}
        if (data?.type == 'reload') location.reload();
        if (data?.type == 'redirect') location.href = data.href;
        if (data?.type ==  'modal') ajax.modalOpen(data)
        return true;
    }

    public static modalOpen(data: any , callback:Function = null) {
        const callbackModal = data['callbackModal'] || null;
        const callbackClose = data['callbackClose'] || null;
        ajax.post(data, {}, '/modal').then((response) => {
            const parse = JSON.parse(response)
            const body = document.body
            $('.modal-carset').remove();
            const modal:HTMLElement = document.createElement('div') as HTMLElement
            modal.classList.add('modal-carset');
            modal.innerHTML = parse.html;
            body.append(modal);

            $('input[ui=text-form]').render(inputTextFormRender)
            $('button[type=submit]').on('click', function (evt: MouseEvent) {
                evt.preventDefault();
                const $button = $(evt.target as HTMLElement)
                const form: HTMLFormElement = $button.closest('form').item(0) as  HTMLFormElement;
                ajax.post(form).then((dataForm) => ajax.eventForm(dataForm));
            });

            $('.close-modal').on('click', () => { 
                modal.remove();
                if (typeof window[callbackClose] === 'function') {
                    (window[callbackClose] as Function)(data)
                }
            })
            if (typeof window[callbackModal] === 'function') {
                (window[callbackModal] as Function)(data)
            }
            if (callback) { 
                callback()
            }
        });
    }


    private  static checkboxNormalize(form:HTMLFormElement , fdata:FormData) { 
        form.querySelectorAll('input[type=checkbox]').forEach((el:HTMLInputElement) => { 
            fdata.delete(el.name);
            fdata.append(el.name, String(el.checked ? 1 : 0));
        })
    }
}