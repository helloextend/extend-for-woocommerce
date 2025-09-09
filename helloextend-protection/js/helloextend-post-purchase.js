(($) => {
    function initAftermarket() {
        // Get params from URL
        const params = (new URL(document.location)).searchParams;
                
        // Check if leadToken or leadtoken is in the URL
        const leadToken = params.get('leadToken') || params.get('leadtoken');
        if (!leadToken) return;

        Extend.aftermarketModal.open({
            leadToken,
            onClose: (plan, product, quantity) => {
                if (plan && product) {
                    plan = {
                        ...plan,
                        covered_product_id: product.id
                    }
                    ExtendWooCommerce.addPlanToCart({
                        plan,
                        product,
                        quantity,
                        leadToken
                    })
                    .then(() => {
                        const cartUrl =
                          (ExtendWooCommerce && ExtendWooCommerce.cart_url)
                          || (window.wc_cart_params && wc_cart_params.cart_url)
                          || '/cart';
                        window.location = cartUrl;
                    })
                    .catch((e) => {
                        if (ExtendWooCommerce && ExtendWooCommerce.extendAjaxLog) {
                            ExtendWooCommerce.extendAjaxLog('error', 'post-purchase addPlanToCart failed', e && e.message ? e.message : e);
                        } else {
                            // eslint-disable-next-line no-console
                            console.error('post-purchase addPlanToCart failed', e);
                        }
                    });
                    
                }
            }
        });
    }

    $(document).off('integration.extend.aftermarket').on('integration.extend.aftermarket', () => {
        if (typeof Extend === 'undefined' || typeof ExtendWooCommerce === 'undefined') {
            return;
        }
    
        initAftermarket();
    });

})(jQuery);