'use strict';

import {Section} from "./Section";
import {Elements} from "./Elements";
import {Options} from "./Options";
import {Search} from "./Search";

export const catalog = {
  init() {
    const db = {
      units: JSON.parse(f.qS('#dataUnits').value),
      money: JSON.parse(f.qS('#dataMoney').value),
      lang : JSON.parse(f.qS('#dataDbLang').value),
    };
    const tmp = {
      tHead   : f.gT('#itemsTableHead'),
      checkbox: f.gT('#itemsTableRowsCheck'),
      imgCell : f.gTNode('#imageTableCell'),
      img     : f.gT('#imageTableItem'),
    };


    this.section  = new Section();
    this.elements = new Elements({db, tmp});
    this.options  = new Options({db, tmp});
    this.serch    = new Search();

    this.section.loadSection();
    return this;
  },
}
