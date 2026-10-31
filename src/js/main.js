"use strict";

const importModuleFunc = async moduleName => {
  let link;
  if (moduleName === 'public') {
    link = `${f.SITE_PATH}public/js/${f.PUBLIC_PAGE}.js`;
    moduleName = f.PUBLIC_PAGE;
  } else link = `./module/${moduleName}.js`;

  try {
    let importModule = await new Promise((resolve, reject) => {
      import(/* webpackIgnore: true */ link)
        .then(module => resolve(module[moduleName]))
        .catch(err => reject(err));
    });
    return importModule.init() || false;
  } catch (e) { console.error(e); f.showMsg(e, 'error', false); return false; }
}

const init = (moduleName = 'default') => {
  let module = importModuleFunc(moduleName);
  if (!module) initIndex();
  f.relatedOption();
  return module;
}

const setLinkMenu = page => {
  let menu = f.qS('#sideMenu');
  if (!menu) return;

  let target = menu.querySelector('.nav-item.active');
  while (target) {
    let wrap = target.closest('[data-role="link"]');
    if (!wrap) return;
    target = wrap.previousElementSibling;
    target.click();
  }

  for (let n of [...menu.querySelectorAll('a')]) {
    let href = n.getAttribute('href') || '';
    if (href.includes(page)) { n.parentNode.classList.add('active'); break; }
  }
}

const cancelFormSubmit = () => {
  f.qA('form', 'keypress', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      return false;
    }
  });
}

const dictionaryInit = () => {
  const d = Object.create(null),
        node = f.qS('#dictionaryData');

  if (!node) return;
  d.data = JSON.parse(node.value);
  node.remove();

  d.getTitle = key => d.data[key] || key;

  /**
   * Template string can be param (%1, %2)
   * @param key - array, first item must be string
   * @returns {*}
   */
  d.translate = (...key) => {
    if (key.length === 1) return d.getTitle(key[0]);

    let str = d.getTitle(key[0]);
    for (let i = 1; i< key.length; i++) {
      key[i] && (str = str.replace(`%${i}`, key[i]));
    }
    return str;
  };
  window._ = d.translate;
}

const stopPreloader = () => {
  f.gI('preloader').remove();
  f.gI('mainWrapper').classList.add('show');
}

const setParentHeight = (target, height) => {
  const n = target.closest("ul[aria-expanded=\"false\"]");
  if (n) {
    n.style.height = (n.offsetHeight + height) + 'px';
    setParentHeight(n.parentNode, height);
  }
}

// Event function
// ---------------------------------------------------------------------------------------------------------------------

const cmsEvent = function() {
  let action = this.dataset.action;

  let select = {
    'menuToggle': () => {
      f.gI('mainWrapper').classList.toggle('menu-toggle');
      setTimeout(() => window.dispatchEvent(new Event('resize')), 200);
    },
    'exit': () => {
      location.href = f.SITE_PATH + `?mode=auth&authAction=exit`;
    },
  };

  select[action] && select[action]();
};

const sideMenuExpanded = (e, node) => {
  e.preventDefault();
  if (node.getAttribute('aria-expanded') === 'true') {
    node.setAttribute('aria-expanded', 'false');
    f.hide(node.nextElementSibling);
  } else {
    node.setAttribute('aria-expanded', 'true');
    f.show(node.nextElementSibling);
  }
}

// Event bind
// -------------------------------------------------------------------------------------------------------------------

const onEvent = () => {
  // Block Authorization
  let node = f.gI(f.ID.AUTH_BLOCK);
  node && node.querySelectorAll('[data-action]')
              .forEach(n => n.addEventListener('click', cmsEvent));

  // Menu Action
  f.qA('#sideMenu a[href^="#"]').forEach(n =>
    n.addEventListener('click', (e) => sideMenuExpanded(e, n)));
}

// Entrance function
(() => {
  let page = location.pathname.replace(f.SITE_PATH, '').match(/(\w+)/);
  page = (page && !f.OUTSIDE) ? page[1] : 'public';

  if (f.gI('authForm')) return;
  cancelFormSubmit();
  dictionaryInit();
  onEvent();

  setLinkMenu(page || '/');
  page && init(page);

  setTimeout(() => { // todo разобраться с синхронизацией
    f.initShadow(); // todo убрать отсюда
  }, 100);
})();
