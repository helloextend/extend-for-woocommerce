describe('Frontend Page and API Validation', () => {
    it('Checks Homepage for 500 Errors', () => {
        cy.visit('https://woocommerce.woodys.extend.com/', { failOnStatusCode: false });

        // Ensure the page loaded without 500 errors
        cy.document().then((doc) => {
            expect(doc.statusCode).to.not.equal(500);
        });
    });

    it('Checks Product Page for 500 Errors', () => {
        cy.visit('https://woocommerce.woodys.extend.com/product/15in-dell-laptop/', { failOnStatusCode: false });

        // Ensure the page loaded without 500 errors
        cy.document().then((doc) => {
            expect(doc.statusCode).to.not.equal(500);
        });
    });

    it('Intercepts Offers API Request and Validates Query Parameters', () => {
        cy.intercept('GET', 'https://api.helloextend.com/offers*').as('offersRequest');

        // Visit the product page
        cy.visit('https://woocommerce.woodys.extend.com/product/15in-dell-laptop/');

        // Wait for the intercepted API request
        cy.wait('@offersRequest').then((interception) => {
            if (interception && interception.request) {
                const requestUrl = new URL(interception.request.url);
                const queryParams = Object.fromEntries(requestUrl.searchParams.entries());

                // Log query parameters
                console.log('Query Parameters:', queryParams);

                // Validate expected query parameters
                expect(queryParams.storeId).to.equal('0e03bcd1-8a00-4a2a-bf2d-7c4d336c07e9');
                expect(queryParams.productId).to.equal('104');
                expect(queryParams.category).to.equal('Electronics');
                expect(queryParams.price).to.equal('69900');
            } else {
                throw new Error('API request was not intercepted.');
            }
        });
    });
});
