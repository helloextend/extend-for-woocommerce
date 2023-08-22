(function( $ ) {
    'use strict';
    $(document).ready(function($) {

        if(!ExtendWooCommerce || !ExtendShippingIntegration) return;

        // Deconstructs ExtendProductIntegration variables
        const { env, items, enable_extend_sp, ajax_url } = ExtendShippingIntegration;
        let items_array = eval(items);

        // If Extend shipping protection offers are not enabled, hide Extend offer div
        if(enable_extend_sp === '0'){
            const extendShippingOffer = document.querySelector('#extend-shipping-offer')
            extendShippingOffer.style.display = 'none';
        }

        //const isShippingProtectionInCart = ExtendShippingIntegration.shippingProtectionInCart(items);
        const isShippingProtectionInCart = false;

        //If Extend shipping  protection is enabled, render offers
        console.log('enable_extend_sp : '+enable_extend_sp);
        if (enable_extend_sp === '1') {
            Extend.shippingProtection.render(
                {
                    selector: '#extend-shipping-offer',
                    items: items_array,
                   // isShippingProtectionInCart: false,
                    onEnable: function (quote) {
                        console.log('call back to add SP plan to cart , quote.premium = ' +quote.premium);
                        // Add a custom fee line to the total
                        // var feeAmount = formatPrice(quote.premium/100) ;
                        // var feeLabel = 'Shipping Protection';
                        // $('.woocommerce-cart-totals .woocommerce-table__footer').before('<tr class="fee"><th>' + feeLabel + '</th><td data-title="' + feeLabel + '">' + feeAmount + '</td></tr>');
                       // console.log('call back to add SP plan to cart , amount: ' + feeAmount +', label: '+feeLabel);

                        // Update totals and trigger WooCommerce cart calculations
                        $.ajax({
                            type: 'POST',
                            url: ajax_url,
                            data: {
                                action: 'add_shipping_protection_fee',
                                fee_amount: quote.premium,
                                fee_label: 'Shipping Protection'
                            },
                            success: function() {
                                console.log('updating cart after success ajax call');
                                //$('body').trigger('update_checkout', [true, { fee: { label: 'Shipping Protection', amount: 10 }}]);
                                $('body').trigger('update_checkout');
                            }
                        });
                        // JM : to fix
                        // console.log('updating cart after fee');

                        //$('body').trigger('update_checkout', [ true, { fee: 30 } ]);

                    },
                    onDisable: function (quote) {
                        console.log('call back to remove sp plan from cart', quote);

                        // Update totals and trigger WooCommerce cart calculations
                        $('body').trigger('update_checkout');
                    },
                    onUpdate: function (quote) {
                        console.log('call back to update sp plan in cart', quote);
                        // Update totals and trigger WooCommerce cart calculations
                        $('body').trigger('update_checkout', [true, { fee: { label: 'Shipping Protection', amount: 10 }}]);

                        // var feeAmount = formatPrice(quote.premium/100) ; // Set your desired fee amount here
                        // var feeLabel = 'Shipping Protection';
                        // $('.woocommerce-cart-totals .woocommerce-table__footer').before('<tr class="fee"><th>' + feeLabel + '</th><td data-title="' + feeLabel + '">' + feeAmount + '</td></tr>');

                    }
                }
            );
        }
    });
// Format price
    function formatPrice(price) {
        //console.log('fragment : ', wc_cart_fragments_params);

        //var currencySymbol = wc_cart_fragments_params.currency_symbol;
       // var currencySymbol = $('.woocommerce-Price-currencySymbol').text();
        return  price.toFixed(2);
    }
})( jQuery );