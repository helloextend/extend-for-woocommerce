describe('Process Order In WooCommerce & Set to Completed', () => {
    before(() => {
        // Log in to WordPress Admin
        cy.visit('https://woocommerce.woodys.extend.com/wp-login.php');

        cy.wait(1000);

        cy.get('#user_login').type(Cypress.env('WP_ADMIN_USERNAME'));
        cy.get('#user_pass').type(Cypress.env('WP_ADMIN_PASSWORD'));
        cy.get('#wp-submit').click();

        // Ensure login was successful
        cy.url().should('include', '/wp-admin/');
    });

    it('Process Order', () => {
        // Visit Orders page
        cy.visit('https://woocommerce.woodys.extend.com/wp-admin/edit.php?post_type=shop_order');

        // Click on the latest order
        cy.get('a.order-view').first().click();

        // wait 1 second
        cy.wait(1000);

        // hide .woocommerce-layout__header-heading  element
        cy.get('.woocommerce-layout__header-heading').invoke('hide');

        // Click select option[value="wc-completed"] with force true
        cy.get('#order_status').select('Completed', { force: true });

        // Click on "Save Order"
        cy.get('.save_order').click();

        // Verify that order was saved
        cy.contains('Order updated.').should('exist');

        // Wait 1 second
        cy.wait(1000);
    });

});
