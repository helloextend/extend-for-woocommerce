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

        cy.wait(1000);

    });

    it('Ensures Cart Normalization and Simple Offers Are working', () => {
        cy.visit('https://woocommerce.woodys.extend.com/product/15in-dell-laptop/');

        cy.get('[name="add-to-cart"]').then(($button) => {
            let productDetails = {
                productId: '104'// Check for different attributes
                // ... other product details you need ...
            };

            cy.log("Product Details:", productDetails);
        });

        // 2. Add to Cart via API (Recommended)
        // This is the most reliable way to add to cart in tests.  It bypasses
        // the UI and directly interacts with WooCommerce's backend.
        cy.request({
            method: 'POST',
            url: 'https://woocommerce.woodys.extend.com/?wc-ajax=add_to_cart', // Adjust if needed
            body: {
                product_id: '104', // Use the extracted product ID
                quantity: 1,  // Or whatever quantity you want
                // ... any other required parameters (variation IDs, etc.) ...
            },
            form: true // Important for form data
        }).then((response) => {
            expect(response.status).to.eq(200); // Check for success
            // Optionally, you can log the response to see what data it returns
            cy.log('Add to cart response:', response.body);

        });

        // 4. Visit the cart page to set the cookie.
        cy.visit('https://woocommerce.woodys.extend.com/cart/');
        cy.get('h1.entry-title').should('contain', 'Cart');

        // Check if cart is not empty
        cy.get('tr.cart_item').should('have.length.at.least', 1);

        // find cart-extend-offer div
        cy.get('.extend-simple-offer').should('exist');

        // Inside the cart-extend-offer div, find the iframe
        cy.get('.extend-simple-offer > iframe').should('exist');

        // click on the .simple-offer button inside the iframe
        cy.get('.extend-simple-offer > iframe').then(($iframe) => {
            cy.wrap($iframe)
                .its('0.contentDocument.body')
                .should('not.be.empty')
                .then(cy.wrap)
                .within(() => {
                    cy.get('.simple-offer').click();
                });
        });

        // Find iframe.extend-offers-modal-iframe
        cy.get('iframe#extend-offers-modal-iframe').should('exist');

        // click on data-cy="offerModal_submit_button" button inside the iframe
        cy.get('iframe#extend-offers-modal-iframe').then(($iframe) => {
            cy.wrap($iframe)
                .its('0.contentDocument.body')
                .should('not.be.empty')
                .then(cy.wrap)
                .within(() => {
                    cy.get('[data-cy="offerModal_submit_button"]').click();
                    cy.wait(1000);
                });
        });

        // find the first input.qty element and clear it, then type 2

        cy.get('input.qty').first().clear();

        cy.get('input.qty').first().type('2');
        cy.wait(1000)

        // click on the update cart button
        cy.get('[name="update_cart"]').click();

        cy.wait(1000)

        // Remove first item from cart
        cy.get('a[class="remove"]').first().click();

    });

    it('Validates Checkout Flow and Shipping Protection', () => {
        cy.visit('https://woocommerce.woodys.extend.com/product/15in-dell-laptop/');

        // 1. Get Product Details (Important!)
        // You'll need to extract the product ID and any other relevant info
        // to properly add it to the cart via the API.  Inspect the page
        // to see how WooCommerce structures its product data.  Here's an example:
        cy.get('[name="add-to-cart"]').then(($button) => {
            let productDetails = {
                productId: '104'// Check for different attributes
                // ... other product details you need ...
            };

            cy.log("Product Details:", productDetails);
        });

        // 2. Add to Cart via API (Recommended)
        // This is the most reliable way to add to cart in tests.  It bypasses
        // the UI and directly interacts with WooCommerce's backend.
        cy.request({
            method: 'POST',
            url: 'https://woocommerce.woodys.extend.com/?wc-ajax=add_to_cart', // Adjust if needed
            body: {
                product_id: '104', // Use the extracted product ID
                quantity: 1,  // Or whatever quantity you want
                // ... any other required parameters (variation IDs, etc.) ...
            },
            form: true // Important for form data
        }).then((response) => {
            expect(response.status).to.eq(200); // Check for success
            // Optionally, you can log the response to see what data it returns
            cy.log('Add to cart response:', response.body);

        });

        // 4. Visit the cart page to set the cookie.
        cy.visit('https://woocommerce.woodys.extend.com/cart/');
        cy.get('h1.entry-title').should('contain', 'Cart');

        // Check if cart is not empty
        cy.get('tr.cart_item').should('have.length.at.least', 1);

        // find cart-extend-offer div
        cy.get('.extend-simple-offer').should('exist');

        // Inside the cart-extend-offer div, find the iframe
        cy.get('.extend-simple-offer > iframe').should('exist');

        // click on the .simple-offer button inside the iframe
        cy.get('.extend-simple-offer > iframe').then(($iframe) => {
            cy.wrap($iframe)
                .its('0.contentDocument.body')
                .should('not.be.empty')
                .then(cy.wrap)
                .within(() => {
                    cy.get('.simple-offer').click();
                });
        });

        // Find iframe.extend-offers-modal-iframe
        cy.get('iframe#extend-offers-modal-iframe').should('exist');

        // click on data-cy="offerModal_submit_button" button inside the iframe
        cy.get('iframe#extend-offers-modal-iframe').then(($iframe) => {
            cy.wrap($iframe)
                .its('0.contentDocument.body')
                .should('not.be.empty')
                .then(cy.wrap)
                .within(() => {
                    cy.get('[data-cy="offerModal_submit_button"]').click();
                    cy.wait(1000);
                });
        });

        // find the first input.qty element and clear it, then type 2

        // scroll to the top of the page
        cy.scrollTo('top')

        cy.wait(1000)

        // clicks on a.checkout-button
        cy.get('a.checkout-button').click();

        cy.wait(1000)


        // fill in form[name="checkout"] details
        cy.get('form[name="checkout"]').within(() => {
            cy.get('#billing_first_name').type('Santibot');
            cy.get('#billing_last_name').type('Automatison');
            cy.get('#billing_address_1').type('123 Cypress St');
            cy.get('#billing_city').type('Beverly Hills');
            // select value="TX" from #billing_state
            // cy.get('#billing_state').select('TX');
            cy.get('#billing_postcode').type('90210');
            cy.get('#billing_phone').type('1234567890');
            cy.get('#billing_email').type('santiago.enciso+cypress@extend.com');
            cy.get('#order_comments').type('Order Placed by Cypress');
        });

        // wait 3 seconds
        cy.wait(1000);

        // Check for tr[class="fee"] > th labeled 'Extend Shipping Protection'
        cy.get('tr[class="fee"] > th ').should('contain', 'Extend Shipping Protection');

        // Inside the iframe, find the input[type=checkbox] and click it
        cy.get('iframe#extend-shipping-offers-iframe').then(($iframe) => {
            cy.wrap($iframe)
                .its('0.contentDocument.body')
                .should('not.be.empty')
                .then(cy.wrap)
                .within(() => {
                    cy.get('input[type="checkbox"]').click();
                });
        });

        // wait 3 seconds
        cy.wait(1000);

        // Ensure fee element is not visible
        cy.get('tr[class="fee"]').should('not.exist');

        // Inside the iframe, find the input[type=checkbox] and click it
        cy.get('iframe#extend-shipping-offers-iframe').then(($iframe) => {
            cy.wrap($iframe)
                .its('0.contentDocument.body')
                .should('not.be.empty')
                .then(cy.wrap)
                .within(() => {
                    cy.get('input[type="checkbox"]').click();
                });
        });

        // wait 3 seconds
        cy.wait(1000);

        // check for iframe #extend-shipping-offers-iframe
        cy.get('iframe#extend-shipping-offers-iframe').should('exist');


        // click on button[name="woocommerce_checkout_place_order"]
        cy.get('button[name="woocommerce_checkout_place_order"]').click();

        cy.wait(1000);

        // check for h1.entry-title
        cy.get('h1.entry-title').should('contain', 'Order received');

        // Get order number from .woocommerce-order-overview__order and save it for later
        cy.get('.woocommerce-order-overview__order').invoke('text').then((orderNumber) => {
            cy.log('Order Number:', orderNumber);

            // Save order number to use in later tests
            Cypress.env('orderNumber', orderNumber);
        });

    });
});
