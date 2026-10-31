"use strict";

import '../../../css/module/admindb/handsontable.full.min.css';
import mustache from 'mustache';

import {Handsontable} from "./handsontable.full.min";
import {handson} from "./handsontable.option";

export class Main {
  constructor() {
    this.action = '';
    this.btnSaveEnable = false;
    this.queryResult = {};
    this.mainNode   = f.qS('#insertToDB');
    this.btnSave    = f.qS('#btnSave');
    this.btnRefresh = f.qS('#btnRefresh');
    this.viewsField = f.qS('#viewField');

    this.tableName = new URLSearchParams(location.search).get('tableName') || '';
    this.loaderTable = new f.LoaderIcon(this.mainNode, false, true, {small: false});

    this.setPageStyle();
    this.onBtnEvent();
    this.disableBtnSave();
  }

  setPageStyle() {
    document.body.style.overflow = 'hidden';
  }
  dbAction(e) {
    // Если список таблица не в меню (не используется)
    //let input = f.qS('input[name="tablesList"]:checked');
    //input && (this.tableName = input.value);

    this.action = typeof e === "string" ? e.toString() : e.target.dataset.dbaction;

    this.loaderTable.start();

     return this.query().then(data => {
      if (data.status) {
        this.queryResult = data;
        this.setTableName();
        f.eraseNode(this.mainNode);
        this.handsontable && (this.handsontable.destroyEditor());
        if (data['csvValues'] && data['XMLValues']) {
          return 'form';
        } else if (data['dbValues']) {
          return 'db';
        } else if (data['csvValues']) {
          return 'csv';
        } else if (data['XMLValues']) {
          return 'XMLValues';
        }
      }
      this.loaderTable.stop();
    });
  }
  setTableName() {
    let node = f.qS('#tableNameField'),
        name = this.tableName.substring(this.tableName.lastIndexOf("/") + 1).replace('.csv', '');

    name = _(name);
    node && (node.innerHTML = name);
    document.title = name;
  }
  showTablesName(data) {
    if (!data['tables'] && !data['csvFiles']) throw Error('Error load DB');

    let string = f.qS('#tablesListTmp').innerHTML;
    f.qS('#DBTablesWrap').innerHTML = f.replaceTemplate(string, data['tables'] || data['csvFiles']);
  }
  showDbTable() {
    const data = this.queryResult['dbValues'].reduce((r, row) => {
            r.push(Object.values(row));
            return r;
          }, []),
          columns = this.queryResult.columns,
          colHeaders = [],
          columnValueTmp = f.gT('#columnName'),
          div = document.createElement('div');

    this.mainNode.append(div);

    columns.map(col => {
      colHeaders.push(f.replaceTemplate(columnValueTmp, col));
    });

    this.handsontable = new Handsontable(div, Object.assign(handson.optionCommon, handson.optionDb, {
      data: handson.removeSlashesData(data), colHeaders,
      cells(row, col) {
        const colParam = columns[col],
              res = {readOnly: colParam.extra.includes('auto')};

        if (['bit', 'int(1)'].includes(colParam.type)) {
          res.type = 'checkbox';
          res.checkedTemplate = '+';
          res.uncheckedTemplate = '-';
        }
        else res.type = colParam.type.includes('varchar') ? 'text' : 'numeric';

        return res;
      }
    }));

    this.handsontable.updateSettings(handson.contextDb);
    this.handsontable.admindb = this;
  }
  showCsvTable() {
    const div = document.createElement('div');
    this.mainNode.append(div);

    this.handsontable = new Handsontable(div, Object.assign(handson.optionCommon, handson.optionCsv, {
      data: handson.removeSlashesData(this.queryResult['csvValues']),
      colHeaders: this.queryResult['csvValues'][0].map(h => _(h)),
    }));

    this.handsontable.updateSettings(handson.contextCsv);
    this.handsontable.admindb = this;
  }
  disableBtnSave() {
    if (this.btnSaveEnable) {
      this.btnSave.setAttribute('disabled', 'disabled');
      this.btnSaveEnable = false;
      this.handsontable && (this.handsontable.tableChanged = false);
      this.disWindowReload();
    }
  }
  enableBtnSave() {
    if (!this.btnSaveEnable) {
      this.btnSave.removeAttribute('disabled');
      this.btnSaveEnable = true;
      this.onWindowReload();
    }
  }

  checkTemplate(val) {
    try {mustache.parse(val);}
    catch (e) {
      f.showMsg(`Ошибка в шаблоне (${e.message})` , 'warning');
    }
  }
  checkSavedTableChange(e) {
    if (this.btnSaveEnable && !confirm('Изменения будут потеряны, продолжить?')) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      return false;
    }
    return true;
  }

  query(data = new FormData()) {
    if (!this.action || !this.tableName) return false;

    data.set('mode', 'DB');
    data.set('dbAction', this.action);
    data.set('tableName', this.tableName);

    return f.Post({data});
  }

  // Event function
  //--------------------------------------------------------------------------------------------------------------------
  tableNameClick(e) {
    //e.preventDefault();
    let node = e.target, name = node.value || node.innerText;
    if(name.includes('.csv')) f.qS('#btnLoadCSV').classList.remove('fade');
    else f.qS('#btnLoadCSV').classList.add('fade');
  }

  clickDocument(e) {
    let target = e.target,
        checkedTarget = target.closest('#sideLeft, nav.navbar');
    checkedTarget && this.checkSavedTableChange(e);
  }
  clickShowLegend() {
    let m = f.initModal({showDefaultButton: false}),
        legend = f.qS('#dataTableLegend');

    legend && m.show('Описание таблицы', legend.content.children[0].cloneNode(true));
  }

  // DB event bind
  //--------------------------------------------------------------------------------------------------------------------
  onBtnEvent() {
    // Остальные кнопки
    f.qA('input[data-dbaction]').forEach(n => n.addEventListener('click', e => this.dbAction(e)));

    // Загрузить файл
    //node = f.qS('#DBTables');
    //node && node.addEventListener('click', e => admindb.tableNameClick(e), {passive: true});

    // Добавлен файл
    //let node = f.qS('#btnAddFileCsv');
    //node && node.addEventListener('change', checkAddedFile);

    // Проверка перехода
    document.onclick = e => this.clickDocument(e);
    f.qA('nav.navbar [data-action]').forEach(n => n.onclick = e => this.clickDocument(e));

    // Легенда
    f.qS('#legend').addEventListener('click', this.clickShowLegend);
  }
  onWindowReload() {
    window.onbeforeunload = (e) => {
      if (this.btnSaveEnable) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        return false;
      }
      return true;
    };
  }
  disWindowReload() {
    window.onbeforeunload = () => {};
  }
}
