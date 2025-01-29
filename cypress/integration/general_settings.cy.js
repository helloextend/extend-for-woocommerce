describe('Update Plugin Settings in WP-Admin', () => {
    before(() => {

        // Log in to WordPress Admin
        cy.visit(`${process.env.SITE_URL}/wp-login.php`);

        cy.get('#user_login').type(process.env.WP_ADMIN_USERNAME);
        cy.get('#user_pass').type(process.env.WP_ADMIN_PASSWORD);
        cy.get('#wp-submit').click();

        // Ensure login was successful
        cy.url().should('include', '/wp-admin/');
    });

    it('Navigates to plugin settings, adds credentials and enables Extend debug log', () => {
        // Visit Plugin Settings Page
        cy.visit(`${process.env.SITE_URL}/wp-admin/admin.php?page=helloextend-protection-settings`);

        // Select "Live" environment
        cy.get('#helloextendenvironment').select('live');

        // Fill Store ID
        cy.get('#helloextendlive_store_id')
            .clear()
            .type(process.env.STORE_ID);

        // Fill Client ID
        cy.get('#helloextendlive_client_id')
            .clear()
            .type(process.env.CLIENT_ID);

        // Fill Client Secret
        cy.get('#helloextendlive_client_secret')
            .clear()
            .type(process.env.CLIENT_SECRET);

        // Enable Debug Mode (Check the box)
        cy.get('#enable_helloextend_debug').check();

        // Save the settings
        cy.get('input[type="submit"]').click();

        // Verify settings saved
        cy.contains('Settings saved').should('exist');
    });
});
