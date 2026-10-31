'use strict';

// Customers list for search
const CustomersList = {
  FD        : new FormData(),
  data      : [],
  searchData: Object.create(null),

  getFormData() {
    this.FD.set('mode', 'DB');
    this.FD.set('dbAction', 'loadCustomers');
    this.FD.set('countPerPage', '1000');
  },

  init() {
    this.searchComponent.init({
      popup: false,
      node: this.node,
      searchData: this.searchData,
      showResult: this.showResult.bind(this),
    });
  },

  setData(node) {
    !this.node && (this.node = node);

    this.node.addEventListener('input', this.inputSearch);
    this.getFormData();
    this.searchComponent = f.searchInit();

    f.Post({data: this.FD}).then(data => {
      data['customers'] && this.prepareSearchData(data['customers']);
      this.init();
    });
  },

  prepareSearchData(data) {
    this.data = data.reduce((r, i) => {
      let phone;
      try { phone = JSON.parse(i['contacts'])['phone'].replace(/ |-|_|\(|\)|@/g, ''); }
      catch { phone = ''; }

      this.searchData[i.id] = i['name'] + i['ITN'] + phone;
      r[i.id] = i;
      return r;
    }, Object.create(null));
  },

  showResult(node, resultIds) {
    if (resultIds.length) {
      f.setLoading(this.node);
      //this.FD.set('search', '1');
      this.FD.set('customerIds', JSON.stringify(resultIds));

      f.Post({data: this.FD}).then(data => {
        f.removeLoading(this.node);
        if (data['customers']) customers.fillTable(data['customers'], true);
      });
    } else customers.fillTable([], true);
  },

  inputSearch(e) {
    clearTimeout(this.delay);
    this.delay = setTimeout(() => {
      let value = e.target.value;

      if (value.length < 2) {
        customers.queryParam.dbAction = 'loadCustomers';
        customers.query();
      }
    }, 300);
  },
}

const orders = {
  data: Object.create(null),

  /**
   * @param id int
   * @param data string
   */
  setData(id, data) {
    if (!id) return;
    this.data[id] = data.split(',');
    return this;
  },

  getOrders(id) {
    //this.data[id] ? this.data[id] : [];
    let obj = this.data[id].reduce((r, i) => {
      r.push({value: i});
      return r;
    }, []);

    return f.replaceTemplate(this.ordersTmp, obj);
  },

  initTemplate() {
    this.ordersTmp = f.gT('#tableOrdersNumbers');
  },
}

export const customers = {
  form: new FormData(),

  needReload: false,
  table: f.gI('customersTable'),
  tbody: '',
  impValue: '',
  confirm: f.gI('confirmField'),

  queryParam: {
    mode        : 'DB',
    tableName   : 'customers',
    dbAction    : 'loadCustomers',
    sortColumn  : 'name',
    sortDirect  : false, // true = DESC, false
    currPage    : 0,
    countPerPage: 20,
    pageCount   : 0,
  },

  confirmMsg: false,

  delayFunc: () => {},
  //statusList: Object.create(null), // Типы доступов

  usersList: new Map(),

  init() {
    this.M = f.initModal();
    this.p = new f.Pagination( '#paginator',{
      dbAction : 'loadCustomers',
      query    : this.query.bind(this),
      sortParam: this.queryParam,
    });
    new f.SortColumns({
      thead: this.table.querySelector('thead'),
      query: this.query.bind(this),
      dbAction : 'loadCustomers',
      sortParam: this.queryParam,
    });
    this.selected = new f.SelectedRow({table: this.table});

    this.query();
    this.onEvent();
  },

  setUsers(data) {
    this.usersList = new Map();
    data.forEach(i => this.usersList.set(i.id, i));
  },
  fillTable(data, search = false) {
    this.contValue || (this.contValue = f.gT('#tableContactsValue'));
    this.searchMsg || (this.searchMsg = f.gT('#noFoundSearchMsg'));
    this.orderBtn || (this.orderBtn = f.gT('#tableOrderBtn'));
    data = data.map(item => {
      if (item['contacts']) {
        let value = '';

        try {
          value = JSON.parse(item['contacts']);
          item['contactsParse'] = value;
          if (Object.values(value).length) {
            let arr = Object.entries(value).map(n => {
              return {key: _(n[0]), value: n[1]}
            });
            value = f.replaceTemplate(this.contValue, arr);
          } else value = '';
        } catch (e) {
          console.log(`Клиент ID: ${item.id} имеет не правильное значение`);
        }
        item['contacts'] = value;
      }

      /*if(true /!* TODO настройки вывода даты*!/) {
        for (let i in item) {
          if(i.includes('date')) {
            item[i] = item[i].replace(/ |(\d\d:\d\d:\d\d)/g, '');
          }
        }
      }*/

      if (item['orders']) {
        orders.setData(item.id, item['orders'])
        item['orders'] = f.replaceTemplate(this.orderBtn, {id: item.id});
      }

      return item;
    })

    let html  = '';
    this.tbody || (this.tbody = this.table.querySelector('tbody tr').outerHTML);
    data.length && (html += f.replaceTemplate(this.tbody, data));
    !data.length && search && (html = this.searchMsg);
    this.table.querySelector('tbody').innerHTML = html;
  },
  checkCustomers() {
    for (const id of this.selected.getSelected()) {
      if (this.usersList.get(id).orders) {
        f.showMsg(`У Клиента ${id} имеются заказы!`, 'error');
        return true;
      }
    }
    return false;
  },

  query() {
    Object.entries(this.queryParam).map(param => {
      this.form.set(param[0], param[1].toString());
    })

    f.Post({data: this.form}).then(data => {
      if (!data.status) { f.showMsg('Ошибка'); return; }

      if (this.needReload) {
        this.needReload = false;
        this.queryParam.dbAction = 'loadCustomers';
        this.queryParam.orderIds = '[]';
        this.query();
        return;
      }

      data.status && this.confirmMsg && f.showMsg(this.confirmMsg) && delete this.confirmMsg;

      if(data['customers']) { this.setUsers(data['customers']); this.fillTable(data['customers']); }
      if(data['countRows']) this.p.setCountPageBtn(data['countRows']);
    });
  },

  // TODO events function
  //--------------------------------------------------------------------------------------------------------------------

  // кнопки открыть закрыть и т.д.
  actionBtn(e) {
    let target = e.target, form,
        action = target.getAttribute('data-action');

    if (!action) return;

    if (['addCustomer', 'changeCustomer'].includes(action)) {
      this.delayFunc = () => {
        const f = new FormData(form), res = {};
        for (const [k, v] of f.entries()) {
          res[k] = v;
        }
        this.queryParam.authForm = JSON.stringify(res);
      };
    }

    let select = {
      'addCustomer': () => {
        form = f.gTNode('#customerForm');

        ['name', 'phone', 'email', 'address', 'ITN'].map(i => {
          let node = form.querySelector(`[name="${i}"]`);
          i === 'phone' && f.maskInit(node, '+375 (__) ___ __ __');
          i === 'ITN' && f.maskInit(node, '_________');
        });

        this.confirmMsg = 'Клиент добавлен';
        this.M.btnConfig('confirmYes', {value: 'Подтвердить'});
        this.M.show('Добавление пользователя', form);
        f.relatedOption(form);
      },
      'changeCustomer': () => {
        if (this.selected.getSelectedSize() !== 1) { f.showMsg('Выберите клиента!'); return; }

        let node,
            id = this.selected.getSelected(),
            customer = this.usersList.get(id[0]);

        form = f.gTNode('#customerForm');

        this.queryParam.customerId = id[0];
        node = form.querySelector('[name="name"]');
        node.value = customer['name'];

        // Contacts
        let {phone = '', email = '', address = ''} = customer['contactsParse'];

        node = form.querySelector(`[name="phone"]`);
        f.maskInit(node, '+375 (__) ___ __ __');
        node.value = phone;

        node = form.querySelector(`[name="email"]`);
        node.value = email;

        node = form.querySelector(`[name="address"]`);
        node.value = address;

        node = form.querySelector(`[name="ITN"]`);
        f.maskInit(node, '_________');
        node.value = customer['ITN'];

        this.confirmMsg = 'Изменения сохранены';
        this.M.btnConfig('confirmYes', {value: 'Подтвердить'});
        this.M.show('Изменение клиента', form);
        f.relatedOption(form);
      },
      'delCustomer': () => {
        if (!this.selected.getSelectedSize()) { f.showMsg('Выберите клиента!', 'error'); return; }
        if (this.checkCustomers()) return;

        this.queryParam.customerId = JSON.stringify(this.selected.getSelected());

        this.confirmMsg = 'Успешно удалено';
        this.M.btnConfig('confirmYes', {value: 'Подтвердить'});
        this.M.show('Удалить', 'Удалить выбранных клиентов?');

        this.delayFunc = () => this.selected.clear();
      },
      'openOrders': () => {
        let id = target.dataset.id,
            div = document.createElement('div');

        orders.ordersTmp || orders.initTemplate();
        div.innerHTML = orders.getOrders(id);

        this.M.btnConfig('confirmYes', {value: 'Открыть'});
        this.M.show('Заказы', div);

        this.delayFunc = () => {
          let checked = div.querySelector('input:checked');
          if (!checked) return;

          let link = f.gI(f.ID.PUBLIC_PAGE);
          link.href += '?orderId=' + checked.value;
          link.click();
        };
      }
    }

    if (action === 'confirmYes') { // Закрыть подтверждение
      this.delayFunc();
      this.delayFunc = () => {};
      this.needReload = {dbAction: 'loadCustomers'};
      this.query();
    } else { // Открыть подтверждение
      this.queryParam.dbAction = action;
      select[action] && select[action]();
    }
  },

  focusSearch(e) {
    CustomersList.setData(e.target);
  },

  // Bind events
  //--------------------------------------------------------------------------------------------------------------------

  /**
   * @param node
   * @param func
   * @param options
   * @param eventType
   */
  onEventNode(node, func, options = {}, eventType = 'click') {
    node.addEventListener(eventType, (e) => func.call(this, e), options);
  },

  onEvent() {
    // Action buttons
    f.qA('input[data-action]', 'click', e => this.actionBtn(e));
    // Click show orders
    this.table.addEventListener('click', e => this.actionBtn(e));

    // Focus Search Init
    this.onEventNode(f.gI('search'), this.focusSearch, {once: true}, 'focus');
  },
}
