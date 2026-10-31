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

import * as ut from '../../../utils';
import {log, p} from "../../../utils";

/**
 *
 * @param testStep
 */
export default function (testStep) {
  it('Test calendar page', () => {
    cy.intercept({ pathname: 'index.php' }).as('request');

    cy.wait(100);
    cy.get('a[href="/calendar"]').click().wait(100);

    cy.get('.fc-prev-button').first().click();
    cy.wait('@request');
    cy.get('.fc-prev-button').first().click().click();
    cy.wait('@request');
    cy.contains('Сегодня').click();
    cy.wait('@request');
    cy.contains('Месяц').click();
    cy.wait('@request');
    cy.contains('Неделя').click();
    cy.wait('@request');
    cy.contains('День').click();
    cy.wait('@request');
  });
}

