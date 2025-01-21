(function ( $ ) {
    'use strict';
    $(document).ready(
        function ($) {

            if(!ExtendWooCommerce || !ExtendShippingIntegration) { return;
            }

            function initShippingOffers()
            {
                // Deconstructs ExtendProductIntegration variables
                const { env, items, enable_helloextend_sp, ajax_url, update_order_review_nonce } = ExtendShippingIntegration;
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
                                        }
                                    }
                                );
                            }
                        }
                    );
                }
            }

            initShippingOffers();

        }
    );

    function formatPrice(price)
    {
        return  price.toFixed(2);
    }
})(jQuery);