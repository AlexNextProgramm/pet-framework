import { $, Rocet } from "@rocet/rocet";

export class Mask {
    private select: string = '[type=phone]';
    private sample: string | null = '';
    private sampleClear: string | null = '';
    private value: string;
    private rep: Array<[Number, String]>;
    constructor(selector: string = null) {
        this.select = selector || this.select;
    }
    /**
     * Model * = replace number
     * Template = +7(***)-**-**'
     * 
     **/
    public modelNumber(sample: string, rep: Array<[Number, String]>) {
        if (sample == '') return console.warn('Mask model not null');
        this.sample = sample;
        this.rep = rep
        this.claerInt();
        this.init();
    }
    private init(): void {
        const $input = $(this.select);
        if ($input.length == 0) return console.log('Mask elements 0');
        $input.on('keydown', (ev: KeyboardEvent) => {
            ev.preventDefault();
            this.change($(ev.target), ev.key, this)
        });
        $input.on('paste', (ev: ClipboardEvent) => {
            const data: string = String(ev.clipboardData.getData('text')) || '';
            for (let i = 0; i < data.length; i++) {
                let key = data[i];
                if (Number.isNaN(parseInt(key))) {
                    continue;
                }
                if (!Number.isNaN(parseInt(this.sampleClear[i])) ) {
                    continue;
                }
                this.change($(ev.target), key, this);
            }
           
            ev.preventDefault();
        });
    }

    private change($target: Rocet, key: any, mask: Mask) {
        let template: string = $target.attr('data-mask') || mask.sample;
        if (key == 'Backspace') {
            let del: string[] = ($target.val() || '').split('');
            let pattern = ($target.attr('data-mask') || mask.sample).split('');
            for (let i = del.length - 1; i >= 0; i--) {

                if (!Number.isNaN(parseInt(del[i]))) {
                    del[i] = '';
                    if (mask.sample[i] == "*")
                        pattern[i] = "*";
                    break;
                }
                del[i] = '';
            }
        
            $target.attr('data-mask', pattern.join(''));
            $target.val(del.join(''));
        }

        let data = parseInt(key);
        if (Number.isNaN(data)) return;
        let regx = new RegExp('([\*]{1,1})', 'i');
        template = template.replace(regx, String(data));
        $target.attr('data-mask', template);
        let value = '';

        for (let i = 0; i < template.length; i++) {
            if (template[i] == '*' ) {
                break;
            }
            value += template[i];
        }
        this.rep.forEach((r: any) => {
            value = value.substring(0, (r[0]- 1)) + r[1] + value.substring((r[0]- 1) + 1);
        })
        $target.val(value);
    }


    private claerInt() {
        for (let i = 0; i < this.sample.length; i++) {
            if (this.sample[i] == '*' || !Number.isNaN(parseInt(this.sample[i]))) {
                this.sampleClear += this.sample[i];
                break;
            }
        }
    }
}