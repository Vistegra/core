'use strict';

/**
 * Global variables and simple functions
 */
export const c = {
  DEBUG        : window['DEBUG'] || false,
  CSV_DEVELOP  : !!window['CSV_DEVELOP'] || false,
  OUTSIDE      : window['CL_OUTSIDE'],
  SITE_PATH    : window['SITE_PATH'] || '/',
  MAIN_PHP_PATH: (window['SITE_PATH'] || '/') + 'index.php',
  PUBLIC_PAGE  : (window['PUBLIC_PAGE'] || 'calculator'),
  PATH_IMG     : (window['PATH_IMG'] || 'public/images/'),
  AUTH_STATUS  : !!(window['AUTH_STATUS'] || false),

  PHONE_MASK: '+7 (___) ___ __ __',

  // Global IDs
  // ------------------------------------------------------------------------------------------------
  ID: {
    AUTH_BLOCK: 'authBlock',
    POPUP: {
      title: 'popup_title',
    },
    PUBLIC_PAGE: 'publicPageLink'
  },

  CLASS_NAME: {
    // css класс который добавляется активным элементам
    ACTIVE: 'active',
    // css класс который добавляется кнопкам сортировки
    SORT_BTN_CLASS: 'btn-light',
    // css класс который добавляется скрытым элементам
    HIDDEN_NODE: 'd-none',
    // css класс который добавляется неактивным элементам
    DISABLED_NODE: 'disabled',
    // css класс который добавляется при загрузке
    LOADING: 'loading-st1',
  },

  // Пробное
  calcWrap: document.querySelector('#wrapCalcNode'),
};
