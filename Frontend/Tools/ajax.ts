export class ajax { 
    static async post(data: BodyInit | null | any | HTMLFormElement, headers:any = {}) {
        let body: any;
        if (data instanceof HTMLFormElement) {
            headers = {};
            body = new FormData(data);
            headers['form-name'] = data.getAttribute('name');
        } else { 
            body = new FormData();
            Object.keys(data).forEach((key: string) => {
                body.append(key, data[key])
            })
        }

       const result =  await fetch(location.href, {
            method: 'POST',
            body,
            headers,
            redirect: 'follow'
       })
       return await result.text();
    }
}