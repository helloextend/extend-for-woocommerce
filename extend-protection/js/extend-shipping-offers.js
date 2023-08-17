(function( $ ) {
    'use strict';
    $(document).ready(function($) {

        if(!ExtendWooCommerce || !ExtendShippingIntegration) return;

        // Deconstructs ExtendProductIntegration variables
        const { env, items, enable_extend_sp } = ExtendShippingIntegration;
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
                        console.log('call back to add SP plan to cart ' +quote.premium, quote);
                        // Add a custom fee line to the total
                        // var feeAmount = formatPrice(quote.premium/100) ; // Set your desired fee amount here
                        // var feeLabel = 'Shipping Protection';
                        // $('.woocommerce-cart-totals .woocommerce-table__footer').before('<tr class="fee"><th>' + feeLabel + '</th><td data-title="' + feeLabel + '">' + feeAmount + '</td></tr>');


                        // Update totals and trigger WooCommerce cart calculations
                        // JM : to fix
                        $('body').trigger('update_checkout', [ true, { fee: quote.premium, fee_label: 'Shipping Protection' } ]);

                    },
                    onDisable: function (quote) {
                        console.log('call back to remove sp plan from cart', quote);

                        // Update totals and trigger WooCommerce cart calculations
                        $('body').trigger('update_checkout');
                    },
                    onUpdate: function (quote) {
                        console.log('call back to update sp plan in cart', quote);
                        // Update totals and trigger WooCommerce cart calculations
                        $('body').trigger('update_checkout');

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
        var currencySymbol = wc_cart_fragments_params.currency_symbol;
        return currencySymbol + price.toFixed(2);
    }
})( jQuery );