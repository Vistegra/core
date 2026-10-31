'use strict';

export class Search {
  constructor() {
    this.setParam();

    this.loader = new f.LoaderIcon(this.node.search, false);

    this.onEvent();
  }

  setParam() {
    this.node = Object.create(null);
    this.node.main = f.qS('#searchField');
    this.node.search = this.node.main.querySelector('[data-field="search"]');

    this.queryParam = Object.create(null);
    this.sortParam  = Object.create(null);
  }

  query() {
    let data = new FormData();

    data.set('mode', 'DB');
    data.set('dbAction', 'searchElements');

    Object.entries(Object.assign({}, this.queryParam, this.sortParam))
          .map(param => data.set(param[0], param[1]));

    return f.Post({data});
  }

  searchInput(e) {
    this.timeOut && clearTimeout(this.timeOut);
    this.timeOut = setTimeout(() => {
      const value = e.target.value;
      if (value.length > 1) {
        this.queryParam.searchValue = value;
        this.query().then(data => f.observer.fire('searchInput', {data, value}));
      } else {
        f.observer.fire('searchInput', {}, true);
      }
    }, 500);
  }

  onEvent() {
    this.node.search.addEventListener('input', e => this.searchInput(e));
  }
}
