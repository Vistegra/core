'use strict';

import {methods as section} from './sections.js';
import {methods as elements} from './elements.js';
import {methods as options} from './options.js';

const common = {
  setReloadQueryParam() {
    delete this.reloadAction.callback;
    this.queryParam = Object.assign(this.queryParam, this.reloadAction);
    this.reloadAction = false;
  },

  query(action = '') {
    let data = new FormData();

    Object.entries(Object.assign({}, this.queryParam))
          .map(param => data.set(param[0], param[1]));
    //action && data.set('dbAction', action);

    Object.entries(this.queryFiles).forEach(([id, file]) => {
      if (file instanceof File) data.append('files' + id, file, file.name);
      else data.set('files' + id, id);
    });
    data.delete('files');

    this.queryFiles = Object.create(null);

    return f.Post({data}).then(async data => {
      if (this.reloadAction) {
        let cbFunc = this.reloadAction.callback || false;
        this.setReloadQueryParam();
        let cbData = await this.query();
        data.status && cbFunc && cbFunc(data, cbData);
      }

      if (data.status === false && data.error) f.showMsg(data.error, 'error');
      return data;
    });
  },
}

export default Object.assign(common, section, elements, options);
