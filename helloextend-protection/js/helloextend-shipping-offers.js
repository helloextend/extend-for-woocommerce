(function ( $ ) {
    'use strict';
    $(document).off('integration.extend.shipping').on('integration.extend.shipping', function () {
        if(!ExtendWooCommerce || !ExtendShippingIntegration) { return;
        }

        function initShippingOffers()
        {
            // Deconstructs ExtendProductIntegration variables
            const { env, items, helloextend_enabled, enable_helloextend_sp, ajax_url, update_order_review_nonce } = ExtendShippingIntegration;
            let items_array = eval(items);

            if (helloextend_enabled == 0 || enable_helloextend_sp == 0)  return;

            const isShippingProtectionInCart = false;

            // The cart is already updated server-side (fee or SP product), so one refresh gets the right total
            function refreshCheckout()
            {
                $('body').trigger('update_checkout');
            }

            //If Extend shipping  protection is enabled, render offers
            if (enable_helloextend_sp == '1') {
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
                                    success: refreshCheckout
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
                                    success: refreshCheckout
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
                                    success: refreshCheckout
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