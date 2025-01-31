// cypress/e2e/shipping_protection.cy.js

describe('Cypress Test', () => {
    it('Visits the Extend Warranty site', () => {
        cy.visit('https://woocommerce.woodys.extend.com');
        cy.contains('WooCommerce').should('be.visible');
    });
});