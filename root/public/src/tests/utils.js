/**
 * @var Cypress - global object of cypress
 */
/**
 * @var cy - global object of cypress
 */

export const oConfig = Cypress['originalConfig'],
url = Cypress.env('external') ? oConfig['baseUrlExternal'] : oConfig['baseUrlLocal'],
vueRenderWait = 200;

//
export const p = () => cy.pause();
export const log = (...arg) => cy.log(arg);


export let test = function (name, template, callback, handleExpectedErrors = false) {
  it(name, () => {
    injectHtmlAndBootAlpine(cy, template, callback, undefined, handleExpectedErrors)
  })
}

export const node = function (cy) {
  cy.then(sNode => {
    console.log(sNode);
    debugger
  })
}

/**
 *
 * @param link
 * @param config
 */
export const entryToCms = function (link = '', config = oConfig) {
  const {
    authLogin: authLogin = 'admin',
    authPass: authPass = '123',
  } = config;
  link = link || url;

  cy.visit(link);
  cy.request({
    url: `${link}index.php?mode=auth&authAction=login&login=${authLogin}&password=${authPass}`})
    .then(() => {});
  cy.visit(link);
}

const selectLastOrder = () => {
  cy.intercept({ pathname: '/index.php' }).as('request');

  cy.get('a[href*="orders"]').click();
  cy.get('input[data-column="lastEditDate"]').click();
  cy.wait('@request');
  cy.get('input[data-column="lastEditDate"]').click();
  cy.wait('@request').wait(100); // Подождать анимацию
  cy.get('#commonTable tbody tr').first().click();
}

/**
 * Загрузить последний заказ
 * */
export const loadLastSavesOrder = () => {
  selectLastOrder();
  cy.get('input[data-action="openOrder"]').click();

  return 'request';
};


/**
 * Удалить последний заказ
 * */
export const deleteLastSavesOrder = (count = 1, returnToCalc = true) => {
  while (count) {
    selectLastOrder();
    cy.get('input[data-action="delOrders"]').click();
    cy.get('#confirmField input[data-action="confirmYes"]').click();
    cy.wait('@request');
    cy.get('.notification-container').should('be.visible');
    count--;
  }

  if (returnToCalc) cy.visit(url + '/')

  return 'request';
};
