"use strict";

const addSlashes = value => value.replaceAll('\n', '\\n').replaceAll('\r', '\\r');
const removeSlashes = value => value.replaceAll('\\n', '\n').replaceAll('\\r', '\r');

const changeRowCol = that => !that.tableChanged && (that.tableChanged = true) && that.admindb.enableBtnSave();

export const handson = {
  optionCommon: {
    rowHeaders        : true,
    colHeaders        : true, //filters   : true,
    columnSorting     : false,
    dropdownMenu      : true,
    contextMenu       : true,
    manualColumnResize: true,
    manualRowResize   : true,
    stretchH          : 'all',
    width             : '100%',
    height            : window.innerHeight * 0.8,
    licenseKey        : 'non-commercial-and-evaluation',

    afterChange(changes) {
      if (changes) {
        for (const [row, column, oldValue, newValue] of changes) {
          if (oldValue !== newValue) {
            if (this.getColHeader(column).includes('template')) this.admindb.checkTemplate(newValue);
            this.admindb.enableBtnSave();
            !this.tableChanged && (this.tableChanged = true);
          }
        }
      }
    },

    afterCreateCol() { changeRowCol(this) },
    afterCreateRow() { changeRowCol(this) },
    afterRemoveCol() { changeRowCol(this) },
    afterRemoveRow() { changeRowCol(this) },
  },


  optionDb: (() => f.CSV_DEVELOP
  /**
   * Настройки DataBase для разработки
   */
  ? {}
  /**
   * Настройки DataBase продакшн
   * */
  : {})(),


  optionCsv: (() => f.CSV_DEVELOP
  /**
   * Настройки СSV для разработки
   */
  ? {}
  /**
   * Настройки СSV продакшн
   */
  : {
    hiddenRows: {rows: [0]}, // Не показывать заголовок

    beforeRemoveCol(ind, count, columns) {
      for (const cIndex of columns) {
        const important = this.getDataAtCol(cIndex).find(i => /^(c_|d_)/i.test(i));
        if (important) {
          f.showMsg('Ключевые значения нельзя удалить', 'error');
          throw new Error('try to delete important values!');
        }
      }
    },
    beforeRemoveRow(ind, count, rows) {
      for (const rIndex of rows) {
        const important = this.getDataAtRow(rIndex).find(i => /^(c_|d_)/i.test(i));
        if (important) {
          f.showMsg('Ключевые значения нельзя удалить', 'error');
          throw new Error('try to delete important values!');
        }
      }
    },

    // Перебор всех ячеек
    cells(row, col) {
      if (row === 0 || this.hasOwnProperty('readOnly')) return; // Первую строку пропускаем
      const cell = this.instance.getDataAtCell(row, col), res = {readOnly: false};

      if (!cell) return res;

      res.readOnly = /^(c_|d_)/i.test(cell);
      if (cell === '+' || cell === '-') {
        res.type = 'checkbox';
        res.checkedTemplate = '+';
        res.uncheckedTemplate = '-';
      }
      else res.type = isFinite(cell.replace(',', '.')) ? 'numeric' : 'text';

      return res;
    },
  })(),

  contextDb: {
    contextMenu: {
      items: {
        "row_above" : {name: 'Добавить строку выше'},
        "row_below" : {name: 'Добавить строку ниже'},
        "hsep1"     : "---------",
        "remove_row": {name: 'Удалить строку'},
        "hsep3"     : "---------",
        "undo"      : {name: 'Отменить'},
        "redo"      : {name: 'Вернуть'},
      },
    },
  },
  contextCsv: {
    contextMenu: {
      items: {
        "row_above" : {name: 'Добавить строку выше'},
        "row_below" : {name: 'Добавить строку ниже'},
        "hsep1"     : "---------",
        "col_left"  : {name: 'Добавить колонку слева'},
        "col_right" : {name: 'Добавить колонку справа'},
        "hsep2"     : "---------",
        "remove_row": {name: 'Удалить строку'},
        "remove_col": {name: 'Удалить колонку'},
        "hsep3"     : "---------",
        "undo"      : {name: 'Отменить'},
        "redo"      : {name: 'Вернуть'}
      },
    },
  },

  removeSlashesData(data) {
    data.length && (data = data.map(row => row.map(cell => cell && removeSlashes(cell))));
    return data;
  },

  addSlashesData(data) {
    data.length && (data = data.map(row => row.map(cell => typeof cell === 'string' ? addSlashes(cell) : cell)));
    return data;
  },
};
