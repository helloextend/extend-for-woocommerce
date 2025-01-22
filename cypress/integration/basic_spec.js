describe('Basic WooCommerce Test', () => {
    it('Visits the test site', () => {
        cy.visit('https://woocommerce.woodys.extend.com/');
        cy.contains('WooCommerce').should('exist');
    });
});
