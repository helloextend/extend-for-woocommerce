// cypress/integration/shipping_protection.cy.js
describe('Update Extend Plugin "Shipping Protection" Tab', () => {
    before(() => {
        // Log in to WordPress Admin
        cy.visit('https://woocommerce.woodys.extend.com/wp-login.php');

        cy.get('#user_login').type(Cypress.env('WP_ADMIN_USERNAME'));
        cy.get('#user_pass').type(Cypress.env('WP_ADMIN_PASSWORD'));
        cy.get('#wp-submit').click();

        // Ensure login was successful
        cy.url().should('include', '/wp-admin/');
    });

    it('Navigates to Shipping Protection settings and updates fields', () => {
        // Visit Product Protection Settings Page
        cy.visit('https://woocommerce.woodys.extend.com/wp-admin/admin.php?page=helloextend-protection-settings&tab=shipping_protection');

        // Check #enable_helloextend if unchecked
        cy.get('#enable_helloextend_sp').then(($el) => {
            if (!$el.is(':checked')) {
                cy.wrap($el).check();
            }
        });

        // Select "Fulfillment" for #helloextendproduct_protection_contract_create_event
        cy.get('#helloextendsp_offer_location').select('woocommerce_review_order_before_payment');

        // Click Save Changes
        cy.get('input[value="Save Changes"]').click();

        // Verify that settings were saved
        cy.contains('Settings saved.').should('exist');
    });


});