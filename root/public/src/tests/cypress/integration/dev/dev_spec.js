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

import { p } from '../../../utils';

const oConfig = Cypress['originalConfig'],
      url = Cypress.env('external') ? oConfig['baseUrlExternal'] : oConfig['baseUrlLocal'];


describe('My First Test', {
  //viewportWidth: viewportWidth,
  //viewportHeight: viewportHeight,
}, () => {
  beforeEach(() => {
    cy.visit(url + 'public');
    cy.request({
      url: `${url}index.php?mode=auth&authAction=login&login=${oConfig.authLogin}&password=${oConfig.authPass}`
    })
      .then(() => {});
  })

  it('should start', () => {
    cy.visit(url + 'public').wait(100);

    //cy.contains('Коммерческое предложение').click();
    //cy.contains('Спецификация').click();
    //cy.get('.cypress1').click()
    //cy.get('.cypress2').click()
    //cy.scrollTo('bottom');
  })

});
