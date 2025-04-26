export function replaceAll(srtSearch:string|Array<string>, sumbol:string, str:string){
    if(typeof srtSearch == 'string'){
        const len = str.split(srtSearch).length
        let strResult = str
        if(len != 0){
          for(let i = 1; i < len; i++){
              strResult = strResult.replace(srtSearch,sumbol)
          }
        }
        return strResult
    }else{
        return replaceArrayAll( srtSearch, sumbol, str)
    }
}

function replaceArrayAll(array:Array<string>, sumbol:string, string:string):string{
    let strResult:string = string
    for(let i = 0; i< array.length; i++){
      const len:number = string.split(array[i]).length
      if(len != 0){
        for(let t = 1; t < len; t++){
            strResult = strResult.replace(array[i],sumbol)
        }
      }
    }
    return strResult
}



// Проверка на файл JSON
export function IsJsonString(str:string){
    try{
        JSON.parse(str);
    }catch(e){
        return false;
    }
    return true
}

export function family_min(str:string):string{
    const fam:Array<string> = str.split(' ')
    let returnStr:string = fam[0]
    if(fam[1]) returnStr += ` ${fam[1].split('')[0]}.` 
    if(fam[2]) returnStr += ` ${fam[2].split('')[0]}.` 
    return returnStr
}