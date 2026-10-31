'use strict';

const getFieldNode = (p, field) => p.querySelector(`[data-field=${field}]`);

import {Properties} from "./properties";

export const setting = {
  form: {
    mail      : f.qS('#mailForm'),
    user      : f.qS('#userForm'),
    custom    : f.qS('#customForm'),
    manager   : f.qS('#managerForm'),
    permission: f.qS('#permission'),
    rate      : f.qS('#rateForm'),
    status    : f.qS('#ordersStatusForm'),
  },

  field   : Object.create(null),
  template: Object.create(null),

  queryParam: {
    mode: 'setting',
  },

  init() {
    this.setParam();
    this.loadSetting();

    new Properties(this.M);

    this.onEvent();
    return this;
  },

  setParam() {
    this.M = f.initModal();

    if (this.form.manager) {
      this.field.customField = getFieldNode(this.form.manager, 'customField');
      this.template.customField = f.gTNode('#customField');
    }

    if (this.form.rate) {
      this.template.rateModal = f.gTNode('#rateModalTmp');
    }

    if (this.form.status) {
      this.field.statusField = getFieldNode(this.form.status, 'statusField');
      this.template.statusField = f.gTNode('#orderStatus');
    }
  },

  query(data) {
    Object.entries(this.queryParam).map(param => {
      data.set(param[0], param[1]);
    })

    return f.Post({data}).then(data => {
      f.showMsg('Сохранено');
      return data;
    });
  },

  loadSetting() {
    let node = f.qS('#userSetting'),
        value = node ? JSON.parse(node.value) : '';

    if (value) {
      node.remove();

      if (value['managerSetting']) {
        Object.values(value['managerSetting']).forEach(v => {
          this.addManagerField(v.name, v.type);
        });
      }
      //value.setting && (value.setting = JSON.parse(value.setting));
      //value.user.customization && (value.user.customization = JSON.parse(value.user.customization));
    }

    node = f.qS('#permissionSetting');
    value = node ? JSON.parse(node.value) : '';
    if (value) {
      // todo убрать reduce не надо
      this.permission = value.reduce((r, item) => {
        let id = item.ID;
        delete item.ID;

        if (item['accessVal'] && item['accessVal']['menuAccess']) {
          let menus = item['accessVal']['menuAccess'].split(','),
              node = this.form.permission.querySelector(`[name="permMenuAccess_${id}"]`);

          menus.forEach(menu => node.querySelector(`[value="${menu}"]`).selected = true);
        }
        r[id] = item;
        return r;
      }, {});
    }

    node = f.qS('#dataOrdersStatus');
    value = node ? JSON.parse(node.value) : false;
    if (value) {
      value.forEach(v => this.addOrderStatusField(v));
      this.field.statusField.lastId = value[value.length - 1].ID;
    }
  },

  // add to FormData status
  prepareSaveOrderStatusField(form) {
    const items = this.field.statusField.querySelectorAll('[data-field="orderStatusItem"]');
    if (items.length) {
      let status = [];
      items.forEach(node => {
        const id   = getFieldNode(node, 'key'),
              name = getFieldNode(node, 'name');

        status.push({
          ID: id.value,
          name: name.value
        });
      });
      form.set('orderStatus', JSON.stringify(status));
    } else {
      f.showMsg('Заказы обязаны иметь 1 статус. Изменения не будут применены', 'error');
    }
  },

  // bind events
  //--------------------------------------------------------------------------------------------------------------------

  onEvent() {
    f.qS('#settingForm').addEventListener('click', e => this.commonClick(e));
    f.qA('[type="password"]').forEach(n => n.addEventListener('change', (e) => this.changePassword(e)))
  },

  // Events function
  //--------------------------------------------------------------------------------------------------------------------

  commonClick(e) {
    let target = e.target,
        action = target.dataset.action;

    if (!action) return;
    this.queryParam.setAction = action;

    const select = {
      'save': () => this.saveSetting(target),

      // Доп. поля для пользователей
      'addCustomManagerField': () => this.addManagerField(),
      'removeCustomManagerField': () => this.removeManagerField(),

      // Права
      'addPermType': () => this.addPermType(),
      'removePermType': () => this.removePermType(),

      // Курсы
      'loadRate': () => this.loadRate(),

      // Статусы заказов
      'addOrderStatusField': () => this.addOrderStatusField(),
      'removeOrderStatusField': () => this.removeOrderStatusField(),
    }

    select[action] && select[action]();
  },

  saveSetting(target) {
    const form = new FormData(),
          customization = Object.create(null),
          setData = (f) => {for (const [k, v] of (new FormData(f)).entries()) form.set(k, v)};

    this.form.mail && setData(this.form.mail);
    this.form.custom && setData(this.form.custom);
    this.form.user && setData(this.form.user);
    this.form.manager && setData(this.form.manager);

    if (this.form.permission) {
      let node = this.form.permission.querySelector('[name="permIds"]');
      form.set(node.name, node.value);

      this.form.permission.querySelectorAll('[multiple]').forEach(select => {
        form.set(select.name, f.getMultiSelect(select).join(','));
      });
    }

    this.form.status && this.prepareSaveOrderStatusField(form);

    // Special field
    form.get('onlyOne') && (customization['onlyOne'] = true);
    form.set('customization', JSON.stringify(customization));

    f.setLoading(target);
    this.query(form).then(() => f.removeLoading(target));
  },

  addManagerField(keyValue = false, typeValue = false) {
    let node = this.template.customField.cloneNode(true),
        key = getFieldNode(node, 'key'),
        type = getFieldNode(node, 'type'),
        randName = new Date().getTime();
    key.name = 'mCustomFieldKey' + randName;
    key.value = keyValue || 'Поле' + randName.toString().slice(-2);
    type.name = 'mCustomFieldType' + randName;
    type.value = typeValue || 'string';
    this.field.customField.append(node);
  },
  removeManagerField() {
    let last = this.field.customField.querySelector('[data-field="customFieldItem"]:last-child');
    last && last.remove();
  },

  changePassword() {
    let value = [],
        node = this.form.user.querySelector('[name="password"]');

    value[0] = node.value;
    if (!value[0]) return;

    node = this.form.user.querySelector('[name="passwordRepeat"]');
    value[1] = node.value;

    if (!value[1]) return;

    if (value[0] !== value[1]) this.errorNode = f.showMsg('Пароли не совпадают', 'error');
    else if (this.errorNode) { this.errorNode.remove(); delete this.errorNode; }
  },

  addPermType() {
    let node = this.form.permission.querySelector('[name="permType"]'),
        value = node && node.value;

    if (!node || !value || value.length < 3) return;

    this.queryParam.setAction = 'addPermType';
    this.queryParam.permType = value;
    this.query(new FormData()).then(data => {
      if (data['status']) Location.reload();
    });
  },
  removePermType() {
    let node = this.form.permission.querySelector('[data-field="permTypes"]'),
        value = node && node.value;

    if (!node || !value || !confirm('Удалить ' + value + '?')) return;

    this.queryParam.setAction = 'removePermType';
    this.queryParam.permId = value;
    this.query(new FormData()).then(data => {
      if (data['status']) Location.reload();
    });
  },

  loadRate() {
    const data = new FormData();
    data.set('mode', 'DB');
    data.set('dbAction', 'loadRate');
    f.Post({data}).then(data => {
      if (data['rate']) {
        const body = this.template.rateModal.querySelector('tbody');

        body.innerHTML = Object.values(data['rate']).map(c =>
          '<tr>' + Object.entries(c).map(([k, v]) => {
            if (k === 'ID') return '';
            else if (k === 'main') return `<td><input type="checkbox" name="${k}" ${+v && 'checked'}></td>`;
            else return `<td><input type="text" name="${k}" value="${v}"></td>`;
          }).join('') + '</tr>'
        ).join('');

        this.M.show('Курсы валют', this.template.rateModal);
      } else f.showMsg('', 'error');
    })
  },

  addOrderStatusField(status = {}) {
    let node     = this.template.statusField.cloneNode(true),
        id       = getFieldNode(node, 'key'),
        name     = getFieldNode(node, 'name'),
        randName = new Date().getTime();

    id.value = status.ID || ++this.field.statusField.lastId;
    name.value = status.name || 'Статус-' + randName;
    this.field.statusField.append(node);
  },
  removeOrderStatusField() {
    let last = this.field.statusField.querySelector('[data-field="orderStatusItem"]:last-child');
    last && last.remove();
  },
}
