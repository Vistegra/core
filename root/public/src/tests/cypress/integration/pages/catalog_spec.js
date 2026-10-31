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
    cy.get('a[href="/catalog"]').click().wait(100);
  });
}

