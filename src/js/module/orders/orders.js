'use strict';

// Orders list for search

class allOrdersList {
  constructor(param) {
    const {node = false} = param;
    if (!node) return;

    const data = this.getFormData(param);

    this.node            = node;
    this.type            = param.tableType;
    this.data            = [];
    this.searchData      = Object.create(null);
    this.searchComponent = f.searchInit();
    this.loader          = new f.LoaderIcon(this.node);

    f.Post({data}).then(data => {
      data['orders'] && this.prepareSearchData(data['orders']);
      this.init(param);
      this.loader.stop();
    });
  }

  getFormData(param) {
    const fd = new FormData();
    fd.set('mode', 'DB');
    fd.set('dbAction', param.dbAction);
    fd.set('countPerPage', '1000');
    return fd;
  }

  init() {
    //this.count = 1;
    this.searchComponent.init({
      popup: false,
      node: this.node,
      searchData: this.searchData,
      showResult: this.showResult.bind(this),
    });
  }

  prepareSearchData(data) {
    if (this.type === 'order') {
      this.data = data.reduce((r, i) => {
        this.searchData[i['O.ID']] = i['O.ID'] + i['C.name'] + i['name'];
        r[i['O.ID']] = i;
        return r;
      }, Object.create(null));
    } else {
      this.data = data.reduce((r, i) => {
        this.searchData[i['cpNumber']] = i['cpNumber'] + ' ' + i['createDate'] + ' ' + i['total'];
        r[i['cpNumber']] = i;
        return r;
      }, Object.create(null));
    }
  }

  showResult(node, resultIds) {
    let array = [];
    resultIds.forEach(i => array.push(this.data[i]));
    orders.fillTable(array, true);
    /* Todo что бы учитывать настройки пагинации нужен запрос
    if (resultIds.length) {
      f.setLoading(this.node);
      this.FD.set('search', '1');
      this.FD.set('orderIds', JSON.stringify(resultIds));

      f.Post({data: this.FD}).then(data => {
        f.removeLoading(this.node);
        if (data['orders']) orders.fillTable(data['orders'], true);
      });
    } else orders.fillTable([], true);*/
  }
}

export const orders = {
  M: f.initModal(),
  form: new FormData(),

  needReload: false,
  confirmMsg: false,

  queryParam: {
    mode        : 'DB',
    dbAction    : 'loadOrders',
    tableName   : 'orders',
    sortColumn  : 'createDate',
    sortDirect  : false, // true = DESC, false
    currPage    : 0,
    countPerPage: 20,
    pageCount   : 0,
  },

  selected: Object.create(null),   // Выделенные строки таблиц
  statusList: Object.create(null), // Возможные статусы

  btnOneOrderOnly: f.qA('#actionBtnWrap input.oneOrderOnly'),
  btnMoreZero: f.qA('#actionBtnWrap input:not(.oneOrderOnly)'),

  init() {
    this.p = new f.Pagination( '#paginator', {
      dbAction : 'loadOrders',
      sortParam: this.queryParam,
      query: this.query.bind(this),
    });
    this.setParam();
    this.setTableTemplate('order');

    new f.SortColumns({
      thead: this.table.querySelector('thead'),
      query: this.query.bind(this),
      dbAction : 'loadOrders',
      sortParam: this.queryParam,
    });
    this.loaderTable = new f.LoaderIcon(this.table);
    this.selected = new f.SelectedRow({table: this.table});

    this.query();

    this.onEvent();
  },

  setParam() {
    this.table = f.qS('#commonTable');
    this.confirm = f.qS('#confirmField');

    this.template = {
      order    : f.gTNode('#orderTableTmp'),
      impValue : f.gT('#tableImportantValue'),
      searchMsg: f.gT('#noFoundSearchMsg'),
    }

    let node = f.qS('#orderVisitorTableTmp');
    node && (this.template.visit = node.content.children[0]);
  },

  setTableTemplate(tmp) {
    this.table.dataset.type = tmp;
    this.table.innerHTML = this.template[tmp].innerHTML;
    this.onSearchFocus();
  },

  fillTable(data, search = false) {
    data = data.map(item => {
      if (item.importantValue) {
        let value = '';

        if (false /* TODO настройки вывода даты*/) {
          for (let i in item) {
            if(i.includes('date')) {
              //let date = new Date(item[i]);
              item[i] = item[i].replace(/ |(\d\d:\d\d:\d\d)/g, '');
            }
          }
        }

        try {
          value = JSON.parse(item.importantValue);
          !Array.isArray(value) && (value = Object.values(value).length && [value]);
          if(value.length) {
            value = value.map(i => { i.key = _(i.key); return i; });
            value = f.replaceTemplate(this.template.impValue, value);
          } else value = '-';
        }
        catch (e) { console.log(`Заказ ID:${item['O.ID']} имеет не правильное значение`); }
        item.importantValue = value;
      }

      item.total = (+item.total).toFixed(2) || 0;
      return item;
    })

    let html  = '',
        tbody = this.template[this.table.dataset.type].querySelector('tbody tr').outerHTML;
    data.length && (html += f.replaceTemplate(tbody, data));
    !data.length && search && (html = this.template.searchMsg);
    this.table.querySelector('tbody').innerHTML = html;

    //data.length && this.selected[this.currentTable].onTableEvent();
    data.length && this.selected.checkedRows();
  },

  // Заполнить статусы
  fillSelectStatus(data) {
    let tmp = f.gT('#changeStatus'), html  = '';

    html += f.replaceTemplate(tmp, data);

    f.gI('selectStatus').innerHTML = html;
  },

  // Открыть заказ TODO кнопка скрыта
  showOrder(data) {
    if(!data['order']) console.log('error');

    let tmp = f.gT('#orderOpenForm'),
        html = document.createElement('div');

    data['order']['importantValue'] = JSON.parse(data['order']['importantValue'])[0];

    html.innerHTML = f.replaceTemplate(tmp, data['order']);

    this.M.show('Заказ ' + data['order']['ID'], html);
  },

  query() {
    Object.entries(this.queryParam).map(param => {
      this.form.set(param[0], param[1]);
    })

    this.loaderTable.start();
    f.Post({data: this.form}).then(data => {
      if(this.needReload) {
        this.needReload = false;
        this.selected.clear();
        this.queryParam.dbAction = 'loadOrders';
        this.queryParam.orderIds = '[]';
        this.query();
        return;
      } else {
        this.confirmMsg && f.showMsg(this.confirmMsg, data.status) && (this.confirmMsg = false);
      }

      if(data['orders']) this.fillTable(data['orders']);
      if(data['countRows']) this.p.setCountPageBtn(data['countRows']);
      if(data['statusOrders']) this.fillSelectStatus(data['statusOrders']);

      this.loaderTable.stop();
    });
  },

  // Events function
  //--------------------------------------------------------------------------------------------------------------------

  // кнопки открыть закрыть и т.д.
  actionBtn(e) {
    let hideActionWrap = true,
        target         = e.target,
        action         = target.dataset.action,
        selectedSize   = this.selected.getSelectedSize();

    if (!selectedSize && !('orderType').includes(action)) { f.showMsg('Выберите заказ!', 'warning'); return; }
    this.queryParam.orderIds = JSON.stringify(this.selected.getSelected());
    if (!['confirmYes', 'confirmNo'].includes(action)) this.queryParam.dbAction = action;

    let select = {
      'changeStatusOrder': () => {
        this.needReload = true;

        let node = f.qS('#selectStatus');
        this.onEventNode(node, this.changeSelectInput, {}, 'change');
        node.dispatchEvent(new Event('change'));

        this.confirmMsg = 'Статусы Сохранены';
        f.show(this.confirm, node);
      },
      'delOrders': () => {
        this.needReload = true;
        this.confirmMsg = 'Удаление выполнено';
        f.show(this.confirm);
      },
      'loadOrder': () => {
        hideActionWrap = false;
        if (selectedSize !== 1) { f.showMsg('Выберите 1 заказ!', 'warning'); return; }

        this.form.set('dbAction', 'loadOrder');
        this.form.set( 'orderIds', this.queryParam.orderIds);
        f.Post({data: this.form})
          .then((data) => this.showOrder(data));
      },
      'openOrder': () => {
        if (selectedSize !== 1) {
          hideActionWrap = false; f.showMsg('Выберите 1 заказ!', 'warning'); return;
        }

        let link = f.gI(f.ID.PUBLIC_PAGE),
            query = this.table.dataset.type === 'order' ? 'orderId=' : 'orderVisitorId=';
        link.href += '?' + query + this.selected.getSelected()[0];
        link.click();
      },
      'printOrder': () => {
        if (selectedSize !== 1) {
          hideActionWrap = false; f.showMsg('Выберите 1 заказ!', 'warning'); return;
        }
        f.show(f.qS('#printTypeField'));
      },
      'printReport': () => {
        if(selectedSize !== 1) { f.showMsg('Выберите 1 заказ!', 'warning'); return; }
        let P = f.initPrint(),
            type = target.dataset.type || false,
            data = new FormData();

        data.set('mode', 'docs');
        data.set('docType', 'print');
        data.set('fileTpl', 'pdfTpl'); // ToDO сделать по умолчанию?
        data.set('orderIds', this.queryParam.orderIds);
        data.set('useUser', 'true');

        f.Post({data}).then((data) => {
            try { data && P.print(data['printBody']); }
            //try { data && P.print(f.printReport, data, type); }
            catch (e) { console.log(e.message); }
          });

        f.hide(f.qS('#printTypeField'));
        f.show(f.qS('#actionBtnWrap'));
        hideActionWrap = false;
      },
      'savePdf': () => {
        hideActionWrap = false;
        if (selectedSize !== 1) { f.showMsg('Выберите 1 заказ!', 'warning'); return; }

        let data = new FormData();
        data.set('useUser', 'true');
        data.set('orderIds', this.queryParam.orderIds);

        f.downloadPdf(target, {}, data, () => target.blur());
      },
      'sendOrder': () => {
        hideActionWrap = false;
        if (selectedSize !== 1) { f.showMsg('Выберите 1 заказ!', 'warning'); return; }
        let form = f.gTNode('#sendMailTmp');

        let fd = new FormData();
        fd.set('mode', 'DB');
        fd.set('dbAction', 'loadCustomerByOrder');
        fd.set('orderIds', this.queryParam.orderIds);
        f.Post({data: fd})
          .then(data => {
            if(data['customer'] && data['customer']['contacts']) {
              let contacts = JSON.parse(data['customer']['contacts']),
                  user = data['users'],
                  node = form.querySelector('[name="email"]');

              this.queryParam.mode = 'docs';
              this.queryParam.docsAction = 'mail';
              this.queryParam.docType = 'pdf';
              this.queryParam.name = user.name || user['login'];
              this.queryParam.phone = user.contacts.phone || '';
              this.queryParam.email = contacts['email'];

              this.onEventNode(node, this.changeSelectInput, {}, 'change');
              contacts['email'] && (node.value = contacts['email']);

              this.M.btnConfig('confirmYes', {value: 'Отправить'});
              this.M.show('Отправить на почту', form);

              this.confirmMsg = 'Отправлено';
              // TODO Добавить проверку почты
              //f.initValid(() => {}, );
            }
          });
      },
      'cancelPrint': () => {
        hideActionWrap = false;
        f.show(f.qS('#actionBtnWrap'));
        f.hide(f.qS('#printTypeField'));
      },
      'orderType': () => {
        hideActionWrap = false;
        if (this.orderType === target.value) return;
        this.orderType = target.value;

        this.queryParam.sortColumn = 'createDate';
        this.queryParam.sortDirect = false;
        this.queryParam.currPage   = 0;
        this.queryParam.pageCount  = 0;
        delete this.queryParam.orderIds;

        if (this.orderType.toString() === 'visit') {
          this.queryParam.dbAction  = 'loadVisitorOrders';
          this.queryParam.tableName = 'client_orders';
          this.table.dataset.type = 'visit';

          f.hide(f.qS('#orderBtn'));
        } else {
          this.queryParam.dbAction  = 'loadOrders';
          this.queryParam.tableName = 'orders';
          this.table.dataset.type = 'order';
          f.show(f.qS('#orderBtn'));
        }

        this.setTableTemplate(this.table.dataset.type);
        this.query();
      },
    }

    if (action.includes('confirm')) { // Закрыть подтверждение
      f.hide(this.confirm, f.qS('#selectStatus'), f.qS('#printTypeField'));
      f.show(f.qS('#actionBtnWrap'));

      if(action === 'confirmYes') {
        this.queryParam.commonValues = this.queryParam.orderIds;
        this.query();
      }
    } else { // Открыть подтверждение
      select[action] && select[action]();
      hideActionWrap && f.hide(f.qS('#actionBtnWrap'));
    }
  },

  // Добавить проверку почты
  changeTextInput(e) {
    if (e.target.value.length === 0) return;
    else if (e.target.value.length <= 2) { e.target.value = 'Ошибка'; return; }
    this.queryParam[e.target.name] = e.target.value;
  },
  changeSelectInput(e) {
    this.queryParam[e.target.name] = e.target.value;
  },

  focusSearch(e) {
    const dbAction = orders.table.dataset.type === 'order'
                     ? 'loadOrders'
                     : 'loadVisitorOrders';

    new allOrdersList({dbAction, node: e.target, tableType: orders.table.dataset.type});
  },

  // Bind events
  //--------------------------------------------------------------------------------------------------------------------

  /**
   * @param node
   * @param func
   * @param options
   * @param eventType {string}
   */
  onEventNode(node, func, options = {}, eventType = 'click') {
    node.addEventListener(eventType, (e) => func.call(this, e), options);
  },

  onEvent() {
    // Top buttons
    f.qA('input[data-action]', 'click', (e) => this.actionBtn.call(this, e));
  },

  onSearchFocus() {
    // Focus Search Init
    let node = f.qS('#search');
    node.removeEventListener('focus', this.focusSearch);
    node.addEventListener('focus', this.focusSearch, {once: true});
  },

  /*onCheckEdit(node) {
    node.querySelectorAll('input').forEach(n => {
      n.addEventListener('blur', (e) => this.blurInput(e));
      n.addEventListener('focus', (e) => this.focusInput(e));
    });
  },*/
}
