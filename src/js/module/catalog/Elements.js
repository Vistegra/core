'use strict';

export const data = {
  elementsModal: {
    display        : false,
    confirmDisabled: true,
    title          : '',
    single         : true,
  },

  elements: [],
  elementsLoading: false,

  element: {
    id      : 0,
    type    : 0,
    parentId: 0,
    name    : '',
    activity: true,
    sort    : 100,
  },

  fieldChange: {
    type    : true,
    parentId: true,
    activity: true,
    sort    : true,
  },

  sectionLoaded: 0,
  elementsSelected: [],
  elementsSelectedShow: false,
  elementParentModalDisabled: false,
  elementParentModalSelected: undefined,
}

export const watch = {
  'element.parentId'() {
    this.elementParentModalSelected = {[this.element.parentId]: true};
  },

  'element.name'() {
    this.elementsModal.confirmDisabled = !this.element.name;
  },
}

export const computed = {
  sectionTreeModal() {
    return this.sectionTree ? this.sectionTree[0].children : [{key: 0}];
  },

  getSectionId() {
    return this.sectionLoaded || this.sectionTreeModal[0].key;
  },

  getElementsSelectedId() {
    return this.elementsSelected.map(i => i.id);
  }
}

const prepareData = data => data.map(el => {
  el['activity'] = f.toNumber(el['activity']);
  return el;
});

const reload = that => ({
  dbAction : 'openSection',
  callback: (fData, aData) => {
    that.elements         = prepareData(aData['elements']);
    that.elementsLoading  = false;
    that.elementsSelected = [];
  }
});

export const methods = {
  loadElements() {
    this.queryParam.dbAction = 'openSection';
    this.sectionLoaded = this.queryParam.sectionId;
    this.elementsLoading = true;
    this.query().then(data => {
      this.elements        = prepareData(data['elements']);
      this.elementsLoading = false;
    });
  },

  checkLoadedElement() {
    //if (this.elementsSelected.includes(this.elementLoaded)) this.options = [];
  },

  enableField() {
    this.fieldChange = {
      type    : true,
      parentId: true,
      activity: true,
      sort    : true,
    };
  },

  getPopularType() {
    let obj = {}, count = -1, key;

    for (let item of this.elementsSelected) {
      let code = item['symbolCode'];
      !obj[code] && (obj[code] = 0);
      obj[code]++;

      if (count < obj[code]) {
        count = obj[code];
        key = code;
      }
    }

    return key;
  },
  getAvgSort(selected) {
    return selected.reduce((r, i) => (r += f.toNumber(i.sort)), 0) / selected.length;
  },

  setElementModal(title, confirmDisabled, single) {
    single && this.enableField();
    this.elementsModal = {display: true, confirmDisabled, title, single};
  },

  // Events function
  //--------------------------------------------------------------------------------------------------------------------
  selectedAll() {
    this.elementsSelected = Object.values(this.elements);
  },
  clearAll() {
    this.elementsSelected = [];
  },
  unselectedElement(id) {
    this.elementsSelected = this.elementsSelected.filter(i => i.id !== id);
    this.elementsSelectedShow = !!this.elementsSelected.length;
  },

  elementParentModalSelectedChange(v) {
    this.element.parentId = Object.keys(v)[0];
  },

  elementNameInput() {
    this.elementsModal.confirmDisabled = !this.element.name;
  },

  // Создать
  createElement() {
    this.queryParam.dbAction = 'createElement';

    this.element.type = this.codes[0]['symbolCode'];
    this.element.name = '';
    this.element.parentId = this.getSectionId;
    this.element.sort = 100;

    this.setElementModal('Создать элемент', true, true);
    this.reloadAction = reload(this);
  },
  changeElements() {
    if (!this.elementsSelected.length) { return; }
    const el     = this.elementsSelected[0],
          single = this.elementsSelected.length === 1;

    this.queryParam.elementsId = JSON.stringify(this.getElementsSelectedId);
    this.queryParam.dbAction = 'changeElements';

    this.element.name     = single ? el.name : '';
    this.element.type     = single ? el['symbolCode'] : this.getPopularType();
    this.element.parentId = this.getSectionId;
    this.element.activity = single ? !!el.activity : true;
    this.element.sort     = single ? f.toNumber(el.sort) : this.getAvgSort(this.elementsSelected);

    this.setElementModal('Редактировать элемент', false, single);
    this.reloadAction = reload(this);
  },
  copyElement() {
    if (this.elementsSelected.length !== 1) { return; }
    const el = this.elementsSelected[0];

    this.queryParam.dbAction = 'copyElement';

    this.element.name     = el.name;
    this.element.type     = el['symbolCode'];
    this.element.parentId = this.getSectionId;
    this.element.activity = !!el.activity;
    this.element.sort     = f.toNumber(el.sort);

    this.setElementModal('Копировать элемент', false, true);
    this.reloadAction = reload(this);
  },
  deleteElements() {
    if (!this.elementsSelected.length) return;

    this.queryParam.elementsId = JSON.stringify(this.getElementsSelectedId);
    this.queryParam.dbAction   = 'deleteElements';

    this.setElementModal('Удалить элемент(ы)', false, false);
    this.reloadAction = {
      dbAction : 'openSection',
      callback: (fData, aData) => {
        this.checkLoadedElement();

        this.elements         = prepareData(aData['elements']);
        this.elementsLoading  = false;
        this.elementsSelected = [];
      }
    };
  },

  elementConfirm() {
    //this.elementsLoading = true;
    this.queryParam = Object.assign(this.queryParam, this.element, {fieldChange: JSON.stringify(this.fieldChange)});
    this.query();
    this.elementsModal.display = false;
  },
  elementCancel() {
    this.elementsModal.display = false;
  },

  loadElement(e) {
    let tr = e.target.closest('tr'),
        n  = tr && tr.querySelector('[data-id]'),
        id = n && +n.dataset.id;
    id && this.loadOptions(id);
  },

  onToggle(value) {
    this.optionsColumnsSelected = this.optionsColumns.filter(col => value.includes(col));
  }
}
