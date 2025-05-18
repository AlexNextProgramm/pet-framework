// Расширяем интерфейс Element, чтобы добавить новые свойства и методы
// Сохраняем оригинальные методы
(Element.prototype as any)._originalAdd = Element.prototype.addEventListener;
(Element.prototype as any)._originalRemove = Element.prototype.removeEventListener;
interface EventListenerRecord {
  type: string;
  listener: EventListenerOrEventListenerObject;
  useCapture: boolean;
}

interface Element {
  eventListenerList?: { [type: string]: EventListenerRecord[] };

  addEventListener(
    type: string,
    listener: EventListenerOrEventListenerObject,
    useCapture?: boolean
  ): void;

  removeEventListener(
    type: string,
    listener: EventListenerOrEventListenerObject,
    useCapture?: boolean
  ): void;

  getEventListeners(type?: string): { [type: string]: EventListenerRecord[] } | EventListenerRecord[] | undefined;
}

interface EventListenerObject{
  [type: string]: EventListenerRecord[]
}


// Переопределяем addEventListener с типами
Element.prototype.addEventListener = function (
  this: Element,
  type: string,
  listener: EventListenerOrEventListenerObject,
  useCapture: boolean = false
): void {
 
  (this as any)._originalAdd(type, listener, useCapture);

  if (!this.eventListenerList) this.eventListenerList = {};
  if (!this.eventListenerList[type]) this.eventListenerList[type] = [];
  this.eventListenerList[type].push({ type, listener, useCapture });
};

// Переопределяем removeEventListener с типами
Element.prototype.removeEventListener = function (
  this: Element,
  type: string,
  listener: EventListenerOrEventListenerObject,
  useCapture: boolean = false
): void {
   (this as any)._originalRemove(type, listener, useCapture);

  if (!this.eventListenerList) this.eventListenerList = {};
  if (!this.eventListenerList[type]) this.eventListenerList[type] = [];
  const list = this.eventListenerList[type];

  for (let i = 0; i < list.length; i++) {
    if (list[i].listener === listener && list[i].useCapture === useCapture) {
      list.splice(i,1);
      break;
    }
  }

  if (list.length === 0) {
    delete this.eventListenerList[type];
  }
};

// getEventListeners с типами
Element.prototype.getEventListeners = function (
  this: Element,
  type?: string
):
| { [type: string]: EventListenerRecord[] }
| EventListenerRecord[]
| undefined {
  if (!this.eventListenerList) this.eventListenerList = {};

  if (type) {
    return this.eventListenerList[type];
  }
  
  return this.eventListenerList;
};