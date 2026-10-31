/**
 * @var describe function
 */
/**
 * @var it function
 */
/**
 * @var Cypress - global object of cypress
 */
/**
 * @var cy - global object of cypress
 */

import * as ut from '../../utils';
import calendar from "./pages/calendar_spec";
import catalog from "./pages/catalog_spec";
import adminDb from "./pages/adminDb_spec";

const testPage = [
  'calendar',
];

describe('Cms Test',
  {
    //viewportWidth: viewportWidth,
    //viewportHeight: viewportHeight,
  },
  () => {
    beforeEach(() => {
      ut.entryToCms();
    });

    // Страница календарь
    testPage.includes('calendar') && calendar();

    // Страница Каталог
    testPage.includes('catalog') && catalog();

    // Администрироание
    testPage.includes('adminDb') && adminDb();

    // Заказы
    testPage.includes('orders') && orders();
    // Клиенты
    testPage.includes('customers') && customers();
    // Пользователи
    testPage.includes('users') && users();

    // statistic
    //testPage.includes('statistic') && statistic();
    // statistic
    //testPage.includes('fileManager') && fileManager();
  }
);
