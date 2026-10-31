// Caching session when logging in via page visit
cy.get('#authForm').then($form => {
  if ($form.length) {
    cy.get('#orangeForm-name').type('admin')
    cy.get('#orangeForm-pass').type('1234')
    cy.get('#authForm .btn.btn-info').click()
  }
});

cy.contains('Completed').click()

/* тест вход */
const loginForm = cy.get('#authForm').then($form => {
  if ($form.length) {
    cy.get('#orangeForm-name').type('admin')
    cy.get('#orangeForm-pass').type('1234')
    cy.get('#authForm .btn.btn-info').click()
    cy.get('.notification-container').should('have.text', 'Неправильный логин или пароль');
    cy.get('#orangeForm-pass').type('{backspace}')
    cy.get('#authForm .btn.btn-info').click()
  }
});
