'use strict';

const getFieldNode = (p, field) => p.querySelector(`[data-field=${field}]`);

export class Properties {
  constructor(modal) {
    this.form = f.qS('#propertiesTable');
    if (!this.form) return;

    this.setParam(modal);
    //this.tmp = f.gTNode('#properties');

    this.onEvent();
  }

  setParam(modal) {
    this.M = modal;

    this.needReload = false;
    this.delayFunc = () => {};
    this.queryParam = {
      dbAction: 'loadProperties',
    };

    this.field = {
      body: this.form.querySelector('tbody'),
    }

    this.tmp = {
      create: f.gTNode('#propertiesCreateTmp'),
      property: this.field.body.innerHTML,
    };

    this.field.propertyType = getFieldNode(this.tmp.create, 'propertyType');
    this.field.colsField = getFieldNode(this.tmp.create, 'propertiesCols');
    this.tmp.colItem = getFieldNode(this.tmp.create, 'propertiesColItem');
    this.tmp.colItem.remove();

    this.loader = new f.LoaderIcon(this.field.body, false, true, {small: false});
    this.selected = new f.SelectedRow({table: this.form});
  }

  reloadQuery() {
    this.queryParam = {dbAction: 'loadProperties'};
    this.needReload = false;
    this.query();
  }

  setProperties(properties) {
    this.propertiesList = new Map();
    Object.entries(properties).forEach(([k, v]) => this.propertiesList.set(k, v));
  }
  showPropertiesTables(tables) {
    let properties = Object.entries(tables).reduce((r, [property, value]) => {
          r.push(Object.assign({property}, value));
          return r;
        }, []);

    this.field.body.innerHTML = f.replaceTemplate(this.tmp.property, properties);
  }

  query() {
    let form = new FormData();

    form.set('mode', 'DB');
    Object.entries(this.queryParam).map(param => form.set(param[0], param[1]));

    setTimeout(() => this.loader.start(), 1);
    f.Post({data: form}).then(data => {
      if (this.needReload) {
        this.reloadQuery();
        return;
      }

      if (data['propertiesTables']) {
        this.setProperties(data['propertiesTables']);
        this.showPropertiesTables(data['propertiesTables']);
      }

      if (data['propertyValue']) {
        this.setPropertyValue(data['propertyValue']);
        this.showPropertyValue(data['propertyValue']);
      }

      this.loader.stop();
    });
  }
  // Events function
  //--------------------------------------------------------------------------------------------------------------------

  actionBtn(e) {
    let target = e.target,
        action = target.dataset.action;

    if (!action) return;

    let select = {
      'loadProperties': () => !e.target.parentNode.open && this.reloadQuery(),
      'createProperty': () => this.createProperty(),
      'changeProperty': () => this.changeProperty(),
      'delProperties': () => this.delProperties(),

      'addCol': () => this.addCol(),
      'remCol': () => this.remCol(),
    }

    if (action === 'confirmYes') { // Закрыть подтверждением
      this.delayFunc();
      this.delayFunc = () => {};
      this.needReload = true;
      this.selected.clear();
      this.query();
    } else {
      !['addCol', 'remCol'].includes(action) && (this.queryParam.dbAction = action);
      select[action] && select[action]();
    }
  }

  createProperty() {
    this.delayFunc = () => {
      for (const [k, v] of new FormData(this.tmp.create).entries()) this.queryParam[k] = v;
    };

    // default Form;
    //this.field.propertyType.value = 's_text';
    //f.eraseNode(this.field.colsField);

    this.tmp.create.querySelector('[name="tableCode"]').removeAttribute('disabled', 'disabled');

    this.M.show('Добавить новое свойство', this.tmp.create);
    f.relatedOption(this.tmp.create);
  }
  changeProperty() {
    let props = this.selected.getSelected();
    if (props.length !== 1) { f.showMsg('Выберите 1 параметр', 'error'); return; }
    const form = this.tmp.create;
    this.delayFunc = () => {
      for (const [k, v] of new FormData(form).entries()) this.queryParam[k] = v;
    };

    let prop = this.propertiesList.get(props[0]),
        node = form.querySelector('[name="tableName"]');
    node.value = prop.name;

    node = form.querySelector('[name="tableCode"]');
    this.queryParam['tableCode'] = node.value = props[0];
    node.setAttribute('disabled', 'disabled');

    node = form.querySelector('[name="dataType"]');
    node.value = (prop.type === 'select' ? 'h_' : 's_') + prop.type;

    this.queryParam.props = props;
    this.M.show('Изменить параметр?', form);
    f.relatedOption(form);
  }
  delProperties() {
    let props = this.selected.getSelected();
    if (!props.length) { f.showMsg('Выберите параметр', 'error'); return; }

    this.queryParam.props = props;
    this.M.show('Удалить параметр?', props.join(', '));
  }

  addCol(keyValue = false, typeValue = false) {
    let node = this.tmp.colItem.cloneNode(true),
        key = getFieldNode(node, 'key'),
        type = getFieldNode(node, 'type'),
        randName = new Date().getTime();

    key.name = 'colName' + randName;
    key.value = keyValue || 'Поле' + randName.toString().slice(-2);
    type.name = 'colType' + randName;
    type.value = typeValue || 'string';
    this.field.colsField.append(node);
  }
  remCol() {
    this.field.colsField.lastChild.remove();
  }

  // Bind events
  //--------------------------------------------------------------------------------------------------------------------

  onEvent() {
    f.qA('#propertiesWrap [data-action]', 'click', (e) => this.actionBtn.call(this, e));

    // Кнопки Модалки
    [this.M.btnCancel, this.M.btnConfirm].forEach(n => n.addEventListener('click', (e) => this.actionBtn.call(this, e)));

    // Форма свойств
    this.tmp.create.addEventListener('click', (e) => this.actionBtn.call(this, e));
  }
}
