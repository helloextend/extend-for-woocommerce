(function( $ ) {
    'use strict';
    $(document).ready(function($) {
        console.log("extend-cart-offers.js is working!");

        console.log("ExtendWooCommerce", ExtendWooCommerce);
        console.log("ExtendCartIntegration", ExtendCartIntegration);

        jQuery('.cart-extend-offer').each(function(ix, val){
            let ref_id =  jQuery(val).data('covered');
            let qty = jQuery(val).parents('.cart_item').find('input.qty').val();
            let price = jQuery(val).parents('.cart_item').find('.product-price').text().trim().replace(/[$,\.]/g, '')
            let extendPrice = parseFloat(price) * 100
            console.log("ref_id", ref_id)
            console.log("qty", qty)
            console.log("price", price)
            console.log("extendPrice", extendPrice);

            // TODO: might need to destroy Extend buttons instance

            ExtendWooCommerce.getCart().then(cart => {
                console.log("cart", cart);

                // TODO: check warrantyAlreadyInCart

                // Render simple offer
                Extend.buttons.renderSimpleOffer('.cart-extend-offer', {
                    referenceId: ref_id,
                    onAddToCart:
                        function({ plan, product }) {
                            if (plan && product) {

                                var planCopy = { ...plan, covered_product_id: ref_id }

                                var data = {
                                    quantity: qty,
                                    plan: planCopy
                                };

                                ExtendWooCommerce.addPlanToCart(data)
                                    .then(() => {
                                        jQuery("[name='update_cart']").removeAttr('disabled');
                                        jQuery("[name='update_cart']").trigger("click");
                                    })
                            }
                        }
                });

            });

        });


        $( document.body ).on( 'updated_cart_totals', function(){
            console.log("updated_cart_totals");
        });

    });
})( jQuery );



