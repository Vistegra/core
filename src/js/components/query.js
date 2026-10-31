import {c} from "./const.js";

// Query Object -----------------------------------------------------------------------------------------------------------------

const checkJSON = data => {
  try {
    const response = JSON.parse(data);
    if (response['error']) throw response['error'];
    return response;
  }
  catch (e) { f.showMsg(e['xdebug_message'] || e.message || data, 'error', false); return {status: false}; }
};

const downloadBody = async data => {
  const fileName = JSON.parse(data.headers.get('fileName')),
        reader   = data.body.getReader();
  let chunks    = [],
      countSize = 0;

  while (true) {
    // done становится true в последнем фрагменте
    // value - Uint8Array из байтов каждого фрагмента
    const {done, value} = await reader.read();

    if (done) break;

    chunks.push(value);
    countSize += value.length;
  }
  return Object.assign(new Blob(chunks), {fileName});
}

const query = (url, data, type = 'json') => {
  type === 'file' && (type = 'body');
  return fetch(url, {method: 'post', body: data})
    .then(res => type === 'json' ? res.text() : res).then(
      data => {
        if (type === 'json') return checkJSON(data, type);
        else if (type === 'body') return downloadBody(data);
        else return data[type]();
      },
      error => console.log(error),
    );
};

/**
 * @type {{Post: (function({url?: *, data?: *, type?: *}): Promise),
 * Get: (function({url: *, data: *, type?: *}): Promise)}}
 */
export const q = {

  /**
   * @param {string} url
   * @param {object} data
   * @param {string: 'json'} type
   * @return {*}
   * @constructor
   */
  Get: ({url = c.MAIN_PHP_PATH, data, type = 'json'}) => query(url + '?' + data, '', type),

  /**
   * Fetch Post function
   * @param {string} url
   * @param {object} data
   * @param {string: 'json'} type
   * @returns {Promise<Response>}
   */
  Post: ({url = c.MAIN_PHP_PATH, data, type = 'json'}) => query(url, data, type),

};
