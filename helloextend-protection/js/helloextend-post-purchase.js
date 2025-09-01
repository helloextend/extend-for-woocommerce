(($) => {
    function initAftermarket() {
        // Get params from URL
        const params = (new URL(document.location)).searchParams;
                
        // Check if leadToken or leadtoken is in the URL
        const leadToken = params.get('leadToken') ? params.get('leadToken') : params.get('leadtoken');

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
                    }).then(() => {
                        window.location = '/cart'; // Is there a standard WC cart path? Does this need to be a setting?
                    });
                    
                }
            }
        });
    }

    $(document).ready(() => {
        if (!Extend || !ExtendWooCommerce) {
            return;
        }
    
        initAftermarket();
    });

})(jQuery);