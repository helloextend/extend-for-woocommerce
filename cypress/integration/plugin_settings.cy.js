describe('Update Plugin Settings in WP-Admin > Extend', () => {
    before(() => {
        // Log in to WordPress Admin
        cy.visit('https://woocommerce.woodys.extend.com/wp-login.php');

        cy.get('#user_login').type(Cypress.env('WP_ADMIN_USERNAME'));
        cy.get('#user_pass').type(Cypress.env('WP_ADMIN_PASSWORD'));
        cy.get('#wp-submit').click();

        // Ensure login was successful
        cy.url().should('include', '/wp-admin/');
    });

    it('Updates General Settings', () => {
        // Visit Plugin Settings Page
        cy.visit('https://woocommerce.woodys.extend.com/wp-admin/admin.php?page=helloextend-protection-settings');

        // Select "Live" environment
        cy.get('#helloextendenvironment').select('live');

        // Fill Store ID
        cy.get('#helloextendlive_store_id').clear().type(Cypress.env('STORE_ID'));

        // Fill Client ID
        cy.get('#helloextendlive_client_id').clear().type(Cypress.env('CLIENT_ID'));

        // Fill Client Secret
        cy.get('#helloextendlive_client_secret').clear().type(Cypress.env('CLIENT_SECRET'));

        // Enable Debug Mode (Check the box)
        cy.get('#enable_helloextend_debug').check();

        // Save the settings
        cy.get('input[value="Save Changes"]').click();

        // Verify settings saved
        cy.contains('Settings saved').should('exist');
    });

    it('Updates Product Protection Settings', () => {
        // Visit Product Protection Settings Page
        cy.visit('https://woocommerce.woodys.extend.com/wp-admin/admin.php?page=helloextend-protection-settings&tab=product_protection');

        // Check and enable required checkboxes
        ['#enable_helloextend', '#helloextendenable_cart_offers', '#helloextendenable_cart_balancing',
            '#helloextendenable_pdp_offers', '#helloextendproduct_protection_contract_create'].forEach(selector => {
            cy.get(selector).then(($el) => {
                if (!$el.is(':checked')) {
                    cy.wrap($el).check();
                }
            });
        });

        // Select "Fulfillment" for Contract Create Event
        cy.get('#helloextendproduct_protection_contract_create_event').select('Fulfillment');

        // Click Save Changes
        cy.get('input[value="Save Changes"]').click();

        // Verify that settings were saved
        cy.contains('Settings saved.').should('exist');
    });

    it('Updates Shipping Protection Settings', () => {
        // Visit Shipping Protection Settings Page
        cy.visit('https://woocommerce.woodys.extend.com/wp-admin/admin.php?page=helloextend-protection-settings&tab=shipping_protection');

        // Check and enable Shipping Protection checkbox
        cy.get('#enable_helloextend_sp').then(($el) => {
            if (!$el.is(':checked')) {
                cy.wrap($el).check();
            }
        });

        // Select "woocommerce_review_order_before_payment" for Offer Location
        cy.get('#helloextendsp_offer_location').select('woocommerce_review_order_before_payment');

        // Click Save Changes
        cy.get('input[value="Save Changes"]').click();

        // Verify that settings were saved
        cy.contains('Settings saved.').should('exist');
    });
});
