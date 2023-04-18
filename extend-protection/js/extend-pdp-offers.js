(function( $ ) {
    'use strict';
    $(document).ready(function($) {

        // if(!ExtendWooCommerce || !ExtendProductIntegration) return;
        if(!ExtendWooCommerce || !ExtendProductIntegration) return;

        // Deconstructs ExtendProductIntegration variables
        const { type: product_type, id: product_id, sku, first_category, price, env, extend_enabled, extend_pdp_offers_enabled, extend_modal_offers_enabled } = ExtendProductIntegration;

        // If PDP offers are not enabled, hide Extend offer div
        if(extend_pdp_offers_enabled === '0'){
            const extendOffer = document.querySelector('.extend-offer')
            extendOffer.style.display = 'none';
        }

        if(product_type ==='simple'){
            Extend.buttons.render('.extend-offer', {
                referenceId: product_id,
                price: price,
                category: first_category
            })
        } else if (product_type ==='variable') {

            Extend.buttons.render('.extend-offer', {
                referenceId: product_id,
                price: price,
                category: first_category
            });

            setTimeout(function(){
                let variation_id = jQuery('[name="variation_id"]').val();
                console.log("variation_id: ", variation_id);
                if(variation_id ) {
                    Extend.setActiveProduct('.extend-offer', {
                        referenceId: variation_id,
                        price: price,
                        category: first_category
                        }
                    );
                }
            }, 600);

            jQuery( ".single_variation_wrap" ).on( "show_variation", function ( event, variation )  {
                let component = Extend.buttons.instance('.extend-offer');
                let variation_id = variation.variation_id;
                let productPrice = parseFloat(document.querySelector('.woocommerce-variation-price > .price > .woocommerce-Price-amount').textContent.replace("$", "")) * 100
                console.log("variation_id changed: ", variation_id);
                console.log("productPrice: ", productPrice);
                if(variation_id && component) {
                    Extend.setActiveProduct('.extend-offer',
                        {
                            referenceId: variation_id,
                            price: productPrice,
                            category: first_category
                        }
                    );
                }
            });
        } else {
            console.error("extend-pdp-offers.js error: Product is neither simple nor variable");
        }

        jQuery('button.single_add_to_cart_button').on('click', function extendHandler(e) {
            e.preventDefault()

            function triggerAddToCart() {
                jQuery('button.single_add_to_cart_button').off('click', extendHandler);
                jQuery('button.single_add_to_cart_button').trigger('click');
                jQuery('button.single_add_to_cart_button').on('click', extendHandler);
            }

            // /** get the component instance rendered previously */
            const component = Extend.buttons.instance('.extend-offer');

            /** get the users plan selection */
            const plan = component.getPlanSelection();
            const product = component.getActiveProduct();

            if (plan) {
                var planCopy = { ...plan, covered_product_id: product.id }
                var data = {
                    quantity: 1,
                    plan: planCopy,
                    price: (plan.price / 100).toFixed(2)
                }
                ExtendWooCommerce.addPlanToCart(data)
                    .then(() => {
                        triggerAddToCart();
                    })
            } else{
                if(extend_modal_offers_enabled === '1'){
                    Extend.modal.open({
                        referenceId: product.id,
                        onClose: function(plan, product) {
                            if (plan && product) {
                                var planCopy = { ...plan, covered_product_id: product.id }
                                var data = {
                                    quantity: 1,
                                    plan: planCopy,
                                    price: (plan.price / 100).toFixed(2)
                                }
                                // TODO: Function that adds plan data to cart
                                console.log("Extend Plan to be added to cart: ", data);
                                ExtendWooCommerce.addPlanToCart(data)
                                    .then(() => {
                                        triggerAddToCart();
                                    })
                            } else {
                                triggerAddToCart()
                            }
                        },
                    });
                } else {
                    triggerAddToCart()
                }
            }
        });

    });
})( jQuery );



