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

    it('Intercepts Offers API Response Checks for Plans In ADH or Base', () => {
        cy.intercept('GET', 'https://api.helloextend.com/offers*').as('offersResponse');

        // Visit the product page
        cy.visit('https://woocommerce.woodys.extend.com/product/15in-dell-laptop/');

        // Wait for the API response
        cy.wait('@offersResponse').then((interception) => {
            if (interception && interception.response) {
                const responseBody = interception.response.body;

                // Ensure "plans" object exists
                expect(responseBody).to.have.property('plans');

                // Extract plans
                const { adh, base } = responseBody.plans;

                // Log plans data for debugging
                console.log('Plans:', responseBody.plans);

                // Ensure either `adh` or `base` has a length of 3
                const adhLength = Array.isArray(adh) ? adh.length : 0;
                const baseLength = Array.isArray(base) ? base.length : 0;

                expect(adhLength === 3 || baseLength === 3).to.be.true;
            } else {
                throw new Error('API response was not intercepted.');
            }
        });
    });

    it('Validates Extend PDP Offers & Modal Are Working', () => {
        // Visit product page
        cy.visit('https://woocommerce.woodys.extend.com/product/15in-dell-laptop/');

        // Check if the Extend Offer iFrame exists
        cy.get('div.helloextend-offer > div.extend-product-offer > iframe')
            .should('exist')
            .then(($iframe) => {
                // Switch to the iFrame context
                cy.wrap($iframe)
                    .its('0.contentDocument.body')
                    .should('not.be.empty')
                    .then(cy.wrap)
                    .within(() => {
                        // Check for div.button-group inside iframe
                        cy.get('div.button-group').should('exist');

                        // Check for the three offer buttons inside button-group
                        cy.get('[data-cy="offerModal_offer_0_button"]').should('exist');
                        cy.get('[data-cy="offerModal_offer_1_button"]').should('exist');
                        cy.get('[data-cy="offerModal_offer_2_button"]').should('exist');

                        // Click on the first offer button
                        cy.get('[data-cy="offerModal_offer_0_button"]').click();
                    });
            });

        // Click on Add to Cart button outside the iframe
        cy.get('[name="add-to-cart"]').click();

        // Wait 1 second
        cy.wait(1000);

        // Click on Add to Cart button again
        cy.get('[name="add-to-cart"]').click();

        // Wait 1 second
        cy.wait(1000);

        // Check if Extend Offers modal iframe is visible
        cy.get('#extend-offers-modal-iframe').should('be.visible');

        // Interact with the modal inside the iframe
        cy.get('#extend-offers-modal-iframe')
            .should('exist')
            .then(($modalIframe) => {
                cy.wrap($modalIframe)
                    .its('0.contentDocument.body')
                    .should('not.be.empty')
                    .then(cy.wrap)
                    .within(() => {
                        // Click the close button inside the modal iframe
                        cy.get('[name="close"]').click();
                    });
            });

        // Capture WooCommerce session & cart cookies
        cy.getCookies().then((cookies) => {
            cookies.forEach((cookie) => {
                cy.setCookie(cookie.name, cookie.value);
            });

            // Save the WooCommerce session cookie for future tests
            Cypress.env('WC_SESSION_ID', cookies.find(cookie => cookie.name.includes('wp_woocommerce_session'))?.value);
        });

    });

    beforeEach(() => {
        // Restore WooCommerce session before each test
        cy.getCookies().then((cookies) => {
            cookies.forEach((cookie) => {
                cy.setCookie(cookie.name, cookie.value);
            });
        });
    });

    it('Ensures Protection Plans Were Added to Cart', () => {
        // Go to cart page
        cy.visit('https://woocommerce.woodys.extend.com/cart/');

        // Check if the cart page loaded
        cy.get('h1.entry-title').should('contain', 'Cart');

        // Ensure there are two tr elements with class .cart_item
        cy.get('tr.cart_item').should('have.length', 2);

        // Inside the first tr.cart_item, find the input.qty and set it to 1
        cy.get('tr.cart_item').first().within(() => {
            cy.get('input.qty').clear().type('1');
        });

        // Click the Update Cart button
        cy.get('button[name="update_cart"]').click();

        // Wait 1 second for the update to take effect
        cy.wait(1000);

        // Check that the .qty element is now 2
        cy.get('input.qty').should('have.value', '2');

        // Find and click the first "remove" button (a.remove)
        cy.get('a.remove').first().click();

        // Wait for cart to update
        cy.wait(1000);

        // Look for div.cart-extend-offer, inside it, find an iframe
        cy.get('div.cart-extend-offer iframe').should('exist').then(($iframe) => {
            cy.wrap($iframe)
                .its('0.contentDocument.body')
                .should('not.be.empty')
                .then(cy.wrap)
                .within(() => {
                    // Click the button with class .simple-offer inside the iframe
                    cy.get('.simple-offer').click();
                });
        });

        // Check for the Extend Offers modal iframe and interact with it
        cy.get('#extend-offers-modal-iframe')
            .should('exist')
            .then(($modalIframe) => {
                cy.wrap($modalIframe)
                    .its('0.contentDocument.body')
                    .should('not.be.empty')
                    .then(cy.wrap)
                    .within(() => {
                        // Click the submit button inside the modal
                        cy.get('[data-cy="offerModal_submit_button"]').click();
                    });
            });

        // Ensure there are two input.qty elements and both have a value of 2
        cy.get('input.qty').should('have.length', 2).and('have.value', '2');

        // Find and click the first "remove" button again
        cy.get('a.remove').first().click();

        // Ensure message 'Your cart is currently empty.' appears
        cy.contains('Your cart is currently empty.').should('exist');
    });

});
