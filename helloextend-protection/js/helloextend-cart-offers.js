// This script is used to handle the rendering and functionality of Extend offers in a WooCommerce cart.
(($) => {

    const SELECTORS = {
        CART_ITEM: '.cart_item',
        TITLE: '.product-name',
        IMAGE: '.product-thumbnail',
        PRICE: '.product-price',
        QUANTITY: 'input.qty',
        EXTEND_OFFER: '.cart-extend-offer',
        UPDATE_CART: "[name='update_cart']"
    }
    
    /**
     * Renders the cart offer for a given offer container element and params
     * @param {HTMLElement} element Offer container element
     * @param {object} params Offer params: referenceId, price, category
     * @param {number} quantity Quantity of warranties to be added
     */
    function renderExtendOffer(element, params, quantity) {
        
        Extend.buttons.renderSimpleOffer(element, {
            ...params,
            onAddToCart: ({plan, product}) => {
                if (!plan || !product) {
                    ExtendWooCommerce.extendAjaxLog('error', 'SimpleOffer onAddToCart failed: plan or product missing');
                    return;
                }
                
                const data = {
                    quantity,
                    plan: {
                        ...plan,
                        covered_product_id: product.id
                    }
                };
                
                if (ExtendWooCommerce.debugLogEnabled)
                    ExtendWooCommerce.extendAjaxLog('debug', 'SimpleOffer add to cart with data: ', JSON.stringify(data));
                
                // Add the plan to the cart.
                ExtendWooCommerce.addPlanToCart(data).then(() => {
                    // After adding the plan, enable the 'Update cart' button and trigger a click on it.
                    $(SELECTORS.UPDATE_CART).removeAttr('disabled');
                    $(SELECTORS.UPDATE_CART).trigger("click");
                });
            }
        }); 
    }
    
    /**
     * Initializes cart offers
     * @param {Object} cart Cart object
     */
    function initCartOffers(cart) {
        $(SELECTORS.CART_ITEM).each((index, lineItemElement) => {
            const $lineItemElement = $(lineItemElement);
            const $title = $lineItemElement.find(SELECTORS.TITLE);
            const $image = $lineItemElement.find(SELECTORS.IMAGE);
            
            if ($title.text().toLowerCase().includes('extend protection plan')) {
                $image.css('pointer-events', 'none');
            } else {
                const $offer = $lineItemElement.find(SELECTORS.EXTEND_OFFER);
                
                const referenceId = $offer.data('covered');
                
                if (ExtendWooCommerce.warrantyAlreadyInCart(referenceId, cart ? cart : window.ExtendCartIntegration.cart)
                    || ExtendCartIntegration.helloextend_enable_cart_offers !== '1') {
                    return;
                }
                
                const category = $offer.data('category');
                const quantity = $lineItemElement.find(SELECTORS.QUANTITY).val();
                
                const [ dollars, cents = '00' ] = $lineItemElement.find(SELECTORS.PRICE).text().trim().replace(/[$,]/g, '').split('.');
                const normalizedCents = cents.padEnd(2, '0');
                const price = `${dollars}${normalizedCents}`;
                
                renderExtendOffer($offer[0], { referenceId, category, price }, quantity);
            }
        });
    }
    
    // Wait until the document is fully loaded before running the script.
    $(document).ready(() => {
        // If necessary objects (ExtendWooCommerce and ExtendCartIntegration) do not exist, stop the execution of the script.
        if (!ExtendWooCommerce || !ExtendCartIntegration) {
            return;
        }
        
        initCartOffers();
    });
    
    // When the cart totals are updated, re-render the Extend offers.
    $(document.body).on('updated_cart_totals', function () {
        ExtendWooCommerce.getCart().then(initCartOffers);
    });

})(jQuery);