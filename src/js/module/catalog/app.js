'use strict';

import Tree from 'primevue/tree';


import {data as sectionData, watch as sectionWatch} from "./sections";
import {data as elementsData, watch as elementsWatch, computed as elementsComputed} from "./elements";
import {data as optionsData, watch as optionsWatch, computed as optionsComputed} from "./options";

import methods from './v_methods.js';

const setData = selector => {
  const node = f.qS(selector),
        res  = node && node.value ? JSON.parse(node.value) : false;
  node.remove();
  return res;
}

export default {
  components: {
    Tree,
  },
  data: () => Object.assign({
    search: '',
    searchShow : true,
    sectionShow: true,
    elementShow: true,
    reloadAction: Object.create(null),
    queryParam  : Object.create(null),
    queryFiles  : Object.create(null),
    temp: false,

    codes: [],

  }, sectionData, elementsData, optionsData),
  computed: Object.assign({}, elementsComputed, optionsComputed),
  watch: Object.assign({}, sectionWatch, elementsWatch, optionsWatch),
  methods: methods,
  mounted() {
    this.queryParam.mode = 'DB';
    this.codes = setData('#dataCodes');
    this.units = setData('#dataUnits');
    this.money = setData('#dataMoney');
    this.properties = setData('#dataProperties');
    this.optionsColumnsSelected = this.optionsColumns;
    this.loadSections();
  },
}
