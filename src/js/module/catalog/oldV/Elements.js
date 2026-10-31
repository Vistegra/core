'use strict';

import {Common} from "./Main";

export class Elements extends Common {
  constructor(props) {
    super('elements', props);

    const field = f.qS(`#${this.type}Field`);

    this.queryParam.tableName = this.type;
    this.setNodes(field, props.tmp);

    this.paginator = new f.Pagination(`#${this.type}Field .pageWrap`,{
      dbAction : 'openSection',
      sortParam: this.sortParam,
      query    : action => this.query(action).then(d => this.load(d, false)),
    });
    this.id = new f.SelectedRow({table: this.node.fieldT});
    this.id.subscribe(this.selectedRow.bind(this));

    f.observer.subscribe(`sortEvent`, d => this.load(d, false));
    f.observer.subscribe(`openSection`, d => this.open(d));
    f.observer.subscribe(`searchInput`, (d, c) => this.searchEvent(d, c));
    this.onEvent();
  }

  open(id) {
    this.queryParam.sectionId = id || false;
    this.queryParam.dbAction = 'openSection';
    this.query().then(d => this.load(d));
  }
  load(data, idClear = true) {
    idClear && this.id.clear();
    data['elements'] && this.prepareItems(data['elements']);
    data['countRowsElements'] && this.paginator.setCountPageBtn(data['countRowsElements']);
  }
  searchEvent(p, clearSearch) {
    this.sortParam.pageNumber = 0;

    if (clearSearch) {
      this.paginator.setQueryAction('openSection');
      this.load({elements: [], countRowsElements: 0});
    } else {
      this.queryParam.searchValue = p.value;
      this.paginator.setQueryAction('searchElements');
      this.load(p.data);
    }
  }

  checkSection() {
    if (!this.queryParam.sectionId) { f.showMsg('Ошибка раздела', 'error'); return true; }
    return false;
  }

  getPopularType() {
    let obj = {}, count = -1, key;

    for (let item of this.itemList.values()) {
      let code = item['symbolCode'];
      !obj[code] && (obj[code] = 0);
      obj[code]++;

      if (count < obj[code]) {
        count = obj[code];
        key = code;
      }
    }

    return key;
  }

  // Events function
  //--------------------------------------------------------------------------------------------------------------------
  // Создать элемент
  createElement() {
    let form = this.tmp.form.cloneNode(true);
    let node = form.querySelector('[name="type"]');
    node.value = this.getPopularType();

    node = form.querySelector('[name="parentId"]');
    if (this.queryParam.sectionId) {
      node.value    = this.queryParam.sectionId;
      node.disabled = true;
    }

    this.queryParam.form = form;
    this.M.show('Создание элемента', form);
    form.querySelector('[name="name"]').focus();
    this.reloadAction = {
      dbAction: 'openSection',
      callback: data => {
        f.showMsg('Элемент создан');
        this.id.clear();
        this.load(data);
      },
    };
  }
  // Открыть элемент
  openElement() {
    if (this.id.getSelectedSize() !== 1) { f.showMsg('Выберите только 1 элемент', 'error'); return; }

    this.queryParam.elementsId = this.id.getSelected()[0];
    this.id.clear();
    f.observer.fire('openElement', this.queryParam.elementsId);
  }
  // Изменить элемент
  changeElements() {
    if (!this.id.getSelectedSize() || this.checkSection()) { f.showMsg('Выберите минимум 1 элемент', 'error'); return; }

    let form        = this.tmp.form.cloneNode(true),
        oneElements = this.id.getSelectedSize() === 1,
        id          = this.id.getSelected(),
        element     = this.itemList.get(id[0]);

    this.queryParam.elementsId = JSON.stringify(id);
    this.delayFunc = () => this.id.clear();

    let node = form.querySelector('[name="type"]');
    if (oneElements) node.value = element['symbolCode'];
    else node.closest('.formRow').remove();

    node = form.querySelector('[name="name"]');
    if (oneElements) node.value = element['E.name'];
    else node.closest('.formRow').remove();

    node = form.querySelector('[name="parentId"]');
    node.value = this.queryParam.sectionId;

    node = form.querySelector('[name="activity"]');
    node.checked = oneElements ? !!(+element['activity']) : true;

    node = form.querySelector('[name="sort"]');
    if (oneElements) node.value = element['sort'];
    else node.closest('.formRow').remove();

    this.queryParam.form = form;
    this.M.show('Изменение элемента', form);
    this.reloadAction = {
      dbAction: 'openSection',
      callback: data => {
        f.showMsg('Элемент изменен');
        this.id.clear();
        this.load(data);
      },
    };
  }
  // Копировать Элемент
  copyElement() {
    if (this.id.getSelectedSize() !== 1) { f.showMsg('Выберите только 1 элемент', 'error'); return; }

    let form = this.tmp.form.cloneNode(true),
        id          = this.id.getSelected(),
        element     = this.itemList.get(id[0]);

    let node = form.querySelector('[name="type"]');
    node.value = this.getPopularType();

    node = form.querySelector('[name="name"]');
    node.value = element['E.name'];
    node.focus();

    node = form.querySelector('[name="parentId"]');
    node.value = this.queryParam.sectionId;
    node.disabled = true;

    this.queryParam.form = form;
    this.M.show('Копирование элемента', form);
    this.reloadAction = {
      dbAction: 'openSection',
      callback: data => {
        f.showMsg('Элемент скопирован');
        this.id.clear();
        this.load(data);
      },
    };
  }
  // Удалить элемент
  delElements() {
    if (!this.id.getSelectedSize()) return;
    this.queryParam.elementsId = JSON.stringify(this.id.getSelected());

    this.M.show('Удалить элемент', 'Удалить элемент и варианты?');
    this.reloadAction = {
      dbAction: 'openSection',
      callback: data => {
        f.observer.fire('delElements', this.id.getSelected());
        f.showMsg('Элемент(ы) удалены!');
        this.id.clear();
        this.load(data);
      },
    };
  }

  // Bind events
  //--------------------------------------------------------------------------------------------------------------------
  onEvent() {
    this.node.field.addEventListener('click', e => this.commonEvent(e));
    this.node.field.addEventListener('dblclick', e => this.dblClick(e));
  }
}
