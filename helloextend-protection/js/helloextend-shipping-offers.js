(function ( $ ) {
    'use strict';
    $(document).off('integration.extend.shipping').on('integration.extend.shipping', function () {
        if(!ExtendWooCommerce || !ExtendShippingIntegration) { return;
        }

        function initShippingOffers()
        {
            // Deconstructs ExtendProductIntegration variables
            const { env, items, enable_helloextend_sp, ajax_url, update_order_review_nonce, helloextend_sp_add_sku } = ExtendShippingIntegration;
            let items_array = eval(items);

            // If Extend shipping protection offers are not enabled, hide Extend offer div
            if(enable_helloextend_sp === '0') {
                const extendShippingOffer = document.querySelector('.helloextend-sp-offer')
                if (extendShippingOffer) {
                    extendShippingOffer.style.display = 'none';
                }

            }
            //const isShippingProtectionInCart = ExtendShippingIntegration.shippingProtectionInCart(items);
            const isShippingProtectionInCart = false;

            //If Extend shipping  protection is enabled, render offers
            if (enable_helloextend_sp === '1') {
                Extend.shippingProtection.render(
                    {
                        selector: '#helloextend-shipping-offer',
                        items: items_array,
                        // isShippingProtectionInCart: false,
                        onEnable: function (quote) {
                            // Update totals and trigger WooCommerce cart calculations
                            $.ajax(
                                {
                                    type: 'POST',
                                    url: ajax_url,
                                    data: {
                                        action: 'add_shipping_protection_fee',
                                        fee_amount: quote.premium,
                                        fee_label: 'Shipping Protection',
                                        shipping_quote_id: quote.id
                                    },
                                    success: function () {
                                        $('body').trigger('update_checkout');

                                        // Need to trigger again for SP line item settings to get correct total
                                        if (helloextend_sp_add_sku) {
                                            setTimeout(() => {
                                                $('body').trigger('update_checkout');
                                            }, 50);
                                        }
                                    }
                                }
                            );
                        },
                        onDisable: function (quote) {
                            // Update totals and trigger WooCommerce cart calculations
                            $.ajax(
                                {
                                    type: 'POST',
                                    url: ajax_url,
                                    data: {
                                        action: 'remove_shipping_protection_fee',
                                    },
                                    success: function () {
                                        $('body').trigger('update_checkout');

                                        // Need to trigger again for SP line item settings to get correct total
                                        if (helloextend_sp_add_sku) {
                                            setTimeout(() => {
                                                $('body').trigger('update_checkout');
                                            }, 50);
                                        }
                                    }
                                }
                            );
                        },
                        onUpdate: function (quote) {

                            // Update totals and trigger WooCommerce cart calculations
                            $.ajax(
                                {
                                    type: 'POST',
                                    url: ajax_url,
                                    data: {
                                        action: 'add_shipping_protection_fee',
                                        fee_amount: quote.premium,
                                        fee_label: 'Shipping Protection',
                                        shipping_quote_id: quote.id
                                    },
                                    success: function () {
                                        $('body').trigger('update_checkout');

                                        // Need to trigger again for SP line item settings to get correct total
                                        if (helloextend_sp_add_sku) {
                                            setTimeout(() => {
                                                $('body').trigger('update_checkout');
                                            }, 50);
                                        }
                                    }
                                }
                            );
                        }
                    }
                );
            }
        }

        initShippingOffers();

    });

    function formatPrice(price)
    {
        return  price.toFixed(2);
    }
})(jQuery);