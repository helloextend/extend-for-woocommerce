describe('Update Plugin Product Protection Settings in WP-Admin > Extend', () => {
    before(() => {
        // Log in to WordPress Admin
        cy.visit('https://woocommerce.woodys.extend.com/wp-login.php');

        cy.get('#user_login').type(Cypress.env('WP_ADMIN_USERNAME'));
        cy.get('#user_pass').type(Cypress.env('WP_ADMIN_PASSWORD'));
        cy.get('#wp-submit').click();

        // Ensure login was successful
        cy.url().should('include', '/wp-admin/');
    });

    it('Navigates to product protection settings and updates fields', () => {
        // Visit Product Protection Settings Page
        cy.visit('https://woocommerce.woodys.extend.com/wp-admin/admin.php?page=helloextend-protection-settings&tab=product_protection');

        // Check #enable_helloextend if unchecked
        cy.get('#enable_helloextend').then(($el) => {
            if (!$el.is(':checked')) {
                cy.wrap($el).check();
            }
        });

        // Check #helloextendenable_cart_offers if unchecked
        cy.get('#helloextendenable_cart_offers').then(($el) => {
            if (!$el.is(':checked')) {
                cy.wrap($el).check();
            }
        });

        // Check #helloextendenable_cart_balancing if unchecked
        cy.get('#helloextendenable_cart_balancing').then(($el) => {
            if (!$el.is(':checked')) {
                cy.wrap($el).check();
            }
        });

        // Check #helloextendenable_pdp_offers if unchecked
        cy.get('#helloextendenable_pdp_offers').then(($el) => {
            if (!$el.is(':checked')) {
                cy.wrap($el).check();
            }
        });

        // Check #helloextendproduct_protection_contract_create if unchecked
        cy.get('#helloextendproduct_protection_contract_create').then(($el) => {
            if (!$el.is(':checked')) {
                cy.wrap($el).check();
            }
        });

        // Select "Fulfillment" for #helloextendproduct_protection_contract_create_event
        cy.get('#helloextendproduct_protection_contract_create_event').select('Fulfillment');

        // Click Save Changes
        cy.get('input[value="Save Changes"]').click();

        // Verify that settings were saved
        cy.contains('Settings saved.').should('exist');
    });
});
