"use strict";
// Поиск
import {f} from "./func";

export const searching = () => {
  const obj = Object.create(null);

  obj.init = function (param) {
    let {popup = true, node, searchData,
          finishFunc = () => {},
          showResult = () => {}} = param,
        func = (e) => this.searchFocus(e);

    this.usePopup = popup; // Показывать результаты в сплывающем окне
    this.searchData = searchData;
    this.resultFunc = (index) => finishFunc(index);
    this.returnFunc = (resultIds) => showResult(this.resultTmp, resultIds);

    node.removeEventListener('focus', func);
    node.addEventListener('focus', func);
    node.dispatchEvent(new Event('focus'));
  }

  obj.setSearchData = function (data) {
    this.searchData = data;
    return this;
  }

  // Переделать когда нить. в вордпрессе очень крутой поисковик
  obj.search = function (need) {
    let pattern     = /(-|_|\(|\)|@)/gm,
        cyrillic    = 'УКЕНХВАРОСМТукенхваросмт',
        latin       = 'YKEHXBAPOCMTykehxbapocmt',
        //cyrillicKey = 'ЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮйцукенгшщзхъфывапролджэячсмитьбю',
        //latinKey    = 'QWERTYUIOP{}ASDFGHJKL:\"ZXCVBNM<>qwertyuiop[]asdfghjkl;\'zxcvbnm,.',
        replacerLC    = (match) => latin.charAt(cyrillic.indexOf(match)),
        replacerCL    = (match) => cyrillic.charAt(latin.indexOf(match)),
        //replacerKeyLC = (match) => latinKey.charAt(cyrillicKey.indexOf(match)),
        //replacerKeyCL = (match) => cyrillicKey.charAt(latinKey.indexOf(match)),
        lettersL = new RegExp(`(${latin.split('').join('|')})`, 'gi'),
        lettersC = new RegExp(`(${cyrillic.split('').join('|')})`, 'gi');
    //funcKeyL = new RegExp(`(${latinKey.split('').join('|')})`, 'gi'),
    //funcKeyC = new RegExp(`(${cyrillicKey.split('').join('|')})`, 'gi');

    need = need.replace(pattern, '');
    if (need.includes(' ')) need += '|' + need.split(' ').reverse().join(' ');

    let regArr = [], i, test;

    (i = need.replace(lettersL, replacerCL).replace(/ /gm, '.+')) && regArr.push(i);
    (i = need.replace(lettersC, replacerLC).replace(/ /gm, '.+')) && regArr.push(i);
    //(i = need.replace(funcKeyL, replacerKeyCL).replace(/ /gm, '.+')) && regArr.push(i);
    //(i = need.replace(funcKeyC, replacerKeyLC).replace(/ /gm, '.+')) && regArr.push(i);
    //i = `${regArr.join('|')}`;
    test = new RegExp(`${regArr.join('|')}`, 'i');

    return Object.entries(this.searchData)
                 .map(i => test.test(i[1].replace(pattern, '')) && i[0]).filter(i => i);
  }

  obj.clear = function (inputNode) {
    inputNode.removeEventListener('keyup', this.bindInputNodeEvent);
    setTimeout(() => {
      this.usePopup && this.resultTmp.remove();
    }, 0);
  }

  // Events
  const inputNodeEvent = function (e) {
    let value = e.target.value;
    if(value.length > 1) {
      f.show(this.resultTmp);
      this.returnFunc(this.search(value));
    } else {
      f.hide(this.resultTmp);
      this.returnFunc(Object.keys(this.searchData));
    }
    e.key === 'Enter' && e.target.dispatchEvent(new Event('blur')) && e.target.blur();
  }

  obj.searchFocus = function (e) {
    let target = e.target,
        wrap = target.parentNode;

    if(this.usePopup && !this.resultTmp) {
      this.resultTmp = f.gTNode('#searchResult');
      this.resultTmp.addEventListener('click', (e) => this.clickResult(e, target));
    }

    this.bindInputNodeEvent = inputNodeEvent.bind(this);
    target.addEventListener('keyup', this.bindInputNodeEvent);
    target.addEventListener('blur', () => setTimeout(() => this.clear(target), 100), {once: true});

    if(this.usePopup) {
      wrap.style.position = 'relative';
      wrap.append(this.resultTmp);
    }

    target.dispatchEvent(new Event('keyup'));
  }

  obj.clickResult = function (e, inputNode) {
    if(this.resultTmp === e.target) return;
    let index = +e.target.dataset.id;

    this.clear(inputNode);
    //inputNode.value = this.data[index].name;
    this.resultFunc(index);
  }

  return obj;
}

/*<template id="searchResult">
 <!--Перенести в компонент поиска-->
 <div class="border position-absolute bg-white w-100" style="top: 100%; z-index: +1; cursor: pointer"></div>
 </template>*/
