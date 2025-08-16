export class Socet {
    private url:string;
    private port:string;
    private socet: WebSocket;

    constructor(url: string, port:string) {
        this.url = url;
        this.port = port;
        this.socet = new WebSocket(this.url+':'+ this.port);
    }
    public getSocet() { 
        return this.socet;
    }
}