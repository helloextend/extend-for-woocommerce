// cypress/integration/example_spec.cy.js

describe('Cypress Test', () => {
    it('Visits the Extend Warranty site', () => {
        cy.visit('https://woocommerce.woodys.extend.com');
        cy.contains('WooCommerce').should('be.visible');
    });
});