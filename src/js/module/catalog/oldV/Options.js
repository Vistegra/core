'use strict';

import {Common} from "./Main";

export class Options extends Common {
  constructor(props) {
    super('options', props);

    const field = f.qS(`#${this.type}Field`);

    this.queryParam.tableName = 'options_elements';

    props.tmp.chooseFile       = f.gT('#chooseFileTmp');
    props.tmp.chooseLoadedFile = f.gT('#chooseLoadedFileTmp');
    this.setNodes(field, props.tmp);

    this.setFileModal();
    this.setParam();
    this.paginator = new f.Pagination(`#${this.type}Field .pageWrap`,{
      dbAction : 'openElement',
      sortParam: this.sortParam,
      query    : action => this.query(action).then(d => this.load(d, false)),
    });
    this.id = new f.SelectedRow({table: this.node.fieldT});
    this.id.subscribe(this.selectedRow.bind(this));

    f.observer.subscribe(`openSection`, () => this.hideWrap());
    f.observer.subscribe(`openElement`, d => this.open(d));
    f.observer.subscribe(`sortEvent`, d => this.load(d, false));
    f.observer.subscribe(`delElements`, d => this.checkElements(d));
    f.observer.subscribe('searchInput', (d, c) => this.searchEvent(d, c));
    this.onEvent();
  }

  setFileModal() {
    this.fModal = f.initModal();
  }
  setParam() {
    this.mainMoney = Object.values(this.db.money).find(m => +m.main === 1); // не используется.
  }

  /**
   * Скрыть Область вывода
   */
  hideWrap() {
    f.hide(this.node.field);
  }
  open(id) {
    this.queryParam.elementsId = id;
    this.queryParam.dbAction = 'openElement';
    this.query().then(d => this.load(d));
  }
  load(data, idClear = true) {
    f.hide(this.node.tableWrap);
    idClear && this.id.clear();
    data['options'] && this.prepareItems(data['options']);
    data['countRowsOptions'] && this.paginator.setCountPageBtn(data['countRowsOptions']);
  }
  checkElements(id) {
    id.includes(this.queryParam.elementsId) && this.hideWrap();
  }
  searchEvent(data, clearSearch) {
    f.hide(this.node.tableWrap, this.node.btnWrap);
  }
  // Money function
  getRate(nInputM, nOutputM) {
    const inRate = this.db.money[nInputM.value].rate,
          outRate = this.db.money[nOutputM.value].rate;

    return inRate/outRate;
  }
  changeMoneyInput(t, nPercent, nOutput, rate) {
    const v = +t.value * (1 + +nPercent.value / 100) * rate;
    nOutput.value = v.toFixed(2);
  }
  changeMoneyInputId(t) { t.dispatchEvent(new Event('change')) }
  changeOutputPercent(t, nInputM, nOutput, rate) {
    const v = +nInputM.value * (1 + +t.value / 100) * rate;
    nOutput.value = v.toFixed(2);
  }
  changeMoneyOutput(t, nInputM, nPercent, rate) {
    const v = (+t.value / rate / +nInputM.value - 1) * 100;
    nPercent.value = v.toFixed(2);
  }
  changeMoneyOutputId(t) { t.dispatchEvent(new Event('change')) }
  initMoneyControl(form) {
    let nInput   = form.querySelector('[name="inputPrice"]'),
        nInputM = form.querySelector('[name="moneyInputId"]'),
        nPercent = form.querySelector('[name="outputPercent"]'),
        nOutput  = form.querySelector('[name="outputPrice"]'),
        nOutputM = form.querySelector('[name="moneyOutputId"]');

    nInput.addEventListener('change', e => this.changeMoneyInput(e.target, nPercent, nOutput, this.getRate(nInputM, nOutputM)));
    nInputM.addEventListener('change', () => this.changeMoneyInputId(nInput));
    nPercent.addEventListener('change', e => this.changeOutputPercent(e.target, nInput, nOutput, this.getRate(nInputM, nOutputM)));
    nOutput.addEventListener('change', e => this.changeMoneyOutput(e.target, nInput, nPercent, this.getRate(nInputM, nOutputM)));
    nOutputM.addEventListener('change', () => this.changeMoneyOutputId(nInput));
  }


  clearFiles(node) {
    let input = document.createElement('input');
    input.type = 'file';
    node.files = input.files;
  }
  showLoadedFiles(data) {
    let html = '<div class="row overflow-auto" style="max-height: 80vh">';

    data.forEach(file => {
      html += f.replaceTemplate(this.tmp.chooseLoadedFile, {
        id: file.ID,
        name : file.name,
        image: file.path,
      });
    });

    this.fModal.show('Выбор файлов', html);
  }
  showFiles(fileField) {
    let html = '';

    Object.entries(this.queryFiles).forEach(([index, file]) => {
      html += f.replaceTemplate(this.tmp.chooseFile, {
        index,
        name : file.name,
        error: file.fileError ? this.cssClass.error : '',
      });
    });

    fileField.innerHTML = html;
  }
  initChooseFile(form, option) {
    const inputN = form.querySelector('input[type="file"]'),
          fileField = form.querySelector('#fileField'),
          btnN = form.querySelector('.chooseFile');

    this.queryFiles = Object.create(null);

    btnN.addEventListener('click', () => {
      f.Get({data: 'mode=DB&dbAction=loadFiles'})
       .then(data => data['files'] && this.showLoadedFiles(data['files']));
    });

    inputN.addEventListener('change', () => {
      Object.values(inputN.files).forEach(file => {
        let id = Math.random() * 10000 | 0;

        file.fileError = file.size > 1024*1024;
        //if (file.fileError && !error) error = true;

        this.queryFiles[id] && (id += '1');
        this.queryFiles[id] = file;
      });
      this.clearFiles(inputN);
      this.showFiles(fileField);
    });

    fileField.addEventListener('click', e => {
      e.preventDefault();
      const action = e.target.dataset.action;

      if (action === 'removeFile') {
        delete this.queryFiles[e.target.dataset.id];
        this.showFiles(fileField);
      }
    });

    if (option && option.images.length) {
      option.images.forEach(img => {
        this.queryFiles[img.ID] = {name: img.name, path: img.path};
      });
      this.showFiles(fileField);
    }
  }

  // Events function
  //--------------------------------------------------------------------------------------------------------------------
  // Добавить вариант
  createOption() {
    let form = this.tmp.form.cloneNode(true);
    form.querySelectorAll('.onlyMany').forEach(n => n.remove());
    f.show(form.querySelector('[data-field="property"]'));

    this.initMoneyControl(form);
    this.initChooseFile(form);

    this.queryParam.form = form;
    this.M.show('Добавить вариант', form);
    form.querySelector('[name="name"]').focus();
    this.reloadAction = {
      dbAction: 'openElement',
      callback: data => {
        f.showMsg('Создан');
        this.id.clear();
        this.load(data);
      },
    };
  }
  // Изменить вариант
  changeOptions() {
    if (!this.id.getSelectedSize()) { f.showMsg('Выберите варианты', 'error'); return; }

    const form       = this.tmp.form.cloneNode(true),
          oneElement = this.id.getSelectedSize() === 1,
          id         = this.id.getSelected(),
          option     = this.itemList.get(id[0]),
          initParam  = (option, skip = []) => {
            Object.entries(option).forEach(([k, v]) => {
              if (skip.includes(k)) return;
              let node = form.querySelector(`[name="${k}"]`);
              node && (node.type === 'checkbox' ? node.checked = !!+v : node.value = v);
            });
          };

    let nodeProp = form.querySelector('[data-field="property"]');

    if (oneElement) {
      form.querySelectorAll('.onlyMany').forEach(n => n.remove());
      f.show(nodeProp);

      initParam(option, ['property']);
      option.property && initParam(JSON.parse(option.property));
      this.initMoneyControl(form, option);
      //this.initImages(form, option.images);
      this.initChooseFile(form, option);
    } else {
      const node = form.querySelector('#property');
      node.addEventListener('change', () => {
        f.show(nodeProp);
        option.property && initParam(JSON.parse(option.property));
        node.parentElement.previousElementSibling.remove();
        node.parentElement.remove();
        //node.remove();
      }, {once: true});

      form.querySelectorAll('.onlyOne').forEach(n => n.remove());
    }

    this.queryParam.form = form;
    this.queryParam.optionsId = JSON.stringify(id);
    this.M.show('Изменение вариантов', form);
    this.reloadAction = {
      dbAction: 'openElement',
      callback: data => {
        f.showMsg('Изменения сохранены');
        this.id.clear();
        this.load(data);
      },
    };
  }
  // Копировать вариант
  copyOption() {
    if (!this.id.getSelectedSize()) { f.showMsg('Выберите варианты', 'error'); return; }

    let form = this.tmp.form.cloneNode(true);

    this.queryParam.form = form;
    this.M.show('Копировать вариантов', form);
  }
  // Удалить вариант
  delOptions() {
    if (!this.id.getSelectedSize()) return;

    this.queryParam.optionsId = JSON.stringify(this.id.getSelected());
    this.delayFunc = () => this.id.clear();

    this.M.show('Удалить вариант', 'Удалить выбранные варианты?');
    this.reloadAction = {dbAction: 'openElement'};
  }

  // Bind events
  //--------------------------------------------------------------------------------------------------------------------

  onEvent() {
    this.node.field.addEventListener('click', e => this.commonEvent(e));
    this.node.field.addEventListener('dblclick', e => this.dblClick(e));
  }
}
