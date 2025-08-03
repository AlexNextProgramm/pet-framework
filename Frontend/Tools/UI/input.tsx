
import { integ } from "../../rocet/core/integration";
import { $, Rocet } from "../../rocet/core/rocet";
import { RocetNode } from "../../rocet/core/RocetNode";

export function inputTextFormRender(Rocet: Rocet, i: number) { 
    const el = $(Rocet.Elements[i]);
    if (el.attr('render')) return;
    el.attr('render', '1');
    const input: RocetNode = <input /> as RocetNode
    input.props = el.getObjectAttr();
    input.props.onerror = (msg:any) => { 
        console.log(msg);
    }
    return <div className="form-input">
            <label for={el.attr('name')} className="form-label">{el.attr('label')}</label>
            {input}
        <span>{el.attr('data-error')}</span>
    </div>
}

$('input[ui="text-form"]').render(inputTextFormRender);

export function inputMaskedFormRender(Rocet: Rocet, i: number) { 
    const el = $(Rocet.Elements[i]);
    if (el.attr('render')) return;
    el.attr('render', '1');
    let input = <input /> as RocetNode
    switch (el.attr('type')) {
        case "checkbox":
            input = <label data-class="switch">
                <input type="checkbox" checked={el.checked} name={el.attr('name')} disabled={el.isAttr('disabled')} />
                        <span class="slider"></span>
                    </label> as RocetNode;

    }
    const props =  el.getObjectAttr();
    const glass: RocetNode = (props.type == 'password' ? <i class="glass glass-eye-off" onclick={onGlass}></i> : <i></i>) as RocetNode;
    input.props =  Object.assign(input.props ,  props);
    input.props.className = el.attr('ui')
    return  <div class="form-row"> 
        <label>{el.attr('label')}</label>
        <div className="box-input">
        {input}
        {glass}
        </div>
        </div>
}
$('input[ui="masked-p"]').render(inputMaskedFormRender);
$('input[ui="unmasked"]').render(inputMaskedFormRender);
$('input[ui=password-form]')
    .render((Rocet: Rocet, i: number) => {
        const el = $(Rocet.item(i));
        const input = <input /> as RocetNode
        input.props = el.getObjectAttr();
      
        return <div className="form-input">
            <label for={el.attr('name')} className="form-label">{el.attr('label')}</label>
            <i class="glass glass-eye-off" onclick={onGlass}></i>
            {input}
            <span>{el.attr('data-error')}</span>
    </div>
});

function onGlass(evt: MouseEvent) {
    const eye = (evt.target as HTMLElement)
    const $input = $(eye.parentElement).find('input');
    if ($input.attr('type') == 'text') {
        $input.attr('type', 'password');
        eye.classList.remove('glass-eye');
        eye.classList.add('glass-eye-off');
        return;
    }

    if ($input.attr('type') == 'password') {
        $input.attr('type', 'text');
        eye.classList.add('glass-eye');
        eye.classList.remove('glass-eye-off');
    }

}


$('input[ui=text]').render((Rocet: Rocet, i: number) => {
    const el = $(Rocet.Elements[i]);
    const input: RocetNode = <input /> as RocetNode
    input.props = el.getObjectAttr();

    return <div className="mb-3 d-flex flex-column">
        <label for={el.attr('name')} className="form-label">{el.attr('label')}</label>
        {input}
    </div>

});



$('input[ui=switсh]').render((Rocet: Rocet, i: number) => {
    const el = $(Rocet.item(i));
    const input = <input /> as RocetNode
    if (el.attr('render')) return;
    el.attr('render', '1');
    input.props = el.getObjectAttr();
    input.props.className = "form-check-input"
    input.props.type = "checkbox"
    input.props.role = 'switch'
    input.props.checked = el.isAttr('checked')
    return <div className={'form-check form-switch' + el.className} >
            {input}
        <label className="form-check-label" for={el.attr('id')} >{el.attr('label')}</label>
    </div>
});

$('input[ui=file]').render((Rocet: Rocet, i: number) => {
    const el = $(Rocet.item(i));
    const input = <input /> as RocetNode
    input.props = el.getObjectAttr();
    input.props.multiple = el.isAttr('multiple')
    input.props.class = "form-control"
    return <div className={"input-group mb-3" + el.className}>
        {input}
        <label className="input-group-text" for={el.attr('id')}>Загрузить</label>
    </div>
});



