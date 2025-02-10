describe('Update Plugin Settings in WP-Admin > Extend', () => {
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

        // wait 1 second
        cy.wait(1000);

    });

    it('Navigates to product protection settings and updates fields', () => {
        // Visit Product Protection Settings Page
        cy.visit('https://woocommerce.woodys.extend.com/wp-admin/admin.php?page=helloextend-protection-settings&tab=product_protection');

        cy.wait(1000);

        // if it goes back to wp-login, then login again
        if (cy.url().should('include', '/wp-login.php')) {
            cy.get('#user_login').type(Cypress.env('WP_ADMIN_USERNAME'));
            cy.get('#user_pass').type(Cypress.env('WP_ADMIN_PASSWORD'));
            cy.get('#wp-submit').click();
        }

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

        // wait 1 second
        cy.wait(1000);

    });

    it('Navigates to Shipping Protection settings and updates fields', () => {
        // Visit Shipping Protection Settings Page
        cy.visit('https://woocommerce.woodys.extend.com/wp-admin/admin.php?page=helloextend-protection-settings&tab=shipping_protection');

        cy.wait(1000);

        // if it goes back to wp-login, then login again
        if (cy.url().should('include', '/wp-login.php')) {
            cy.get('#user_login').type(Cypress.env('WP_ADMIN_USERNAME'));
            cy.get('#user_pass').type(Cypress.env('WP_ADMIN_PASSWORD'));
            cy.get('#wp-submit').click();
        }

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

        // wait 1 second
        cy.wait(1000);

    });
});
