describe('Update Plugin General Settings in WP-Admin > Extend', () => {
    before(() => {
        // Log in to WordPress Admin
        cy.visit('https://woocommerce.woodys.extend.com/wp-login.php');

        cy.get('#user_login').type(Cypress.env('WP_ADMIN_USERNAME'));
        cy.get('#user_pass').type(Cypress.env('WP_ADMIN_PASSWORD'));
        cy.get('#wp-submit').click();

        // Ensure login was successful
        cy.url().should('include', '/wp-admin/');
    });

    it('Navigates to plugin settings and updates fields', () => {
        // Visit Plugin Settings Page
        cy.visit('https://woocommerce.woodys.extend.com/wp-admin/admin.php?page=helloextend-protection-settings');

        // Select "Live" environment
        cy.get('#helloextendenvironment').select('live');

        // Fill Store ID
        cy.get('#helloextendlive_store_id')
            .clear()
            .type(Cypress.env('STORE_ID'));

        // Fill Client ID
        cy.get('#helloextendlive_client_id')
            .clear()
            .type(Cypress.env('CLIENT_ID'));

        // Fill Client Secret
        cy.get('#helloextendlive_client_secret')
            .clear()
            .type(Cypress.env('CLIENT_SECRET'));

        // Enable Debug Mode (Check the box)
        cy.get('#enable_helloextend_debug').check();

        // Save the settings
        cy.get('input[type="submit"]').click();

        // Verify settings saved
        cy.contains('Settings saved').should('exist');
    });
});