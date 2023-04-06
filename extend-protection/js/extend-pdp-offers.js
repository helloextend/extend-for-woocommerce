(function( $ ) {
    'use strict';
    $(document).ready(function($) {

        // if(!ExtendWooCommerce || !ExtendProductIntegration) return;
        if(!ExtendProductIntegration) return;

        window.Extend.config({storeId: '6caaf44e-0410-4529-9674-4dc6a4e0e800', environment: 'production'});

        // Deconstructs ExtendProductIntegration variables
        const { type: product_type, id: product_id, sku,  env, extend_enabled, extend_pdp_offers_enabled, extend_modal_offers_enabled } = ExtendProductIntegration;

        console.log("product_type", product_type);
        console.log("product_id", product_id);
        console.log("sku", sku);
        console.log("env", env);
        console.log("extend_enabled", extend_enabled);
        console.log("extend_pdp_offers_enabled", extend_pdp_offers_enabled, typeof extend_pdp_offers_enabled);
        console.log("extend_modal_offers_enabled", extend_modal_offers_enabled, typeof extend_modal_offers_enabled);

        // TODO: If PDP offers are not enabled, hide Extend offer div
        if(extend_pdp_offers_enabled === '0'){
            const extendOffer = document.querySelector('.extend-offer')
            extendOffer.style.display = 'none';
        }

        // TODO: product_type ==='simple' — Find out why this matters

        // TODO: Render offers
        if(product_type ==='simple'){
            Extend.buttons.render('.extend-offer', {
                referenceId: product_id,
            })
        } else if (product_type ==='variable') {

            Extend.buttons.render('.extend-offer', {
                referenceId: product_id,
            });

            setTimeout(function(){
                let variation_id = jQuery('[name="variation_id"]').val();
                console.log("variation_id: ", variation_id);
                if(variation_id ) {
                    let comp = Extend.buttons.instance('.extend-offer');
                    comp.setActiveProduct(variation_id)
                }
            }, 600);

            jQuery( ".single_variation_wrap" ).on( "show_variation", function ( event, variation )  {
                let component = Extend.buttons.instance('.extend-offer');
                let variation_id = variation.variation_id;
                console.log("variation_id changed: ", variation_id);
                if(variation_id && component) {
                    component.setActiveProduct(variation.variation_id)
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
                    plan: planCopy
                }
                // TODO: Function that adds plan data to cart
                console.log("Extend Plan to be added to cart: ", data);
                // ExtendWooCommerce.addPlanToCart(data)
                //     .then(() => {
                        triggerAddToCart();
                //     })
            } else{
                if(extend_modal_offers_enabled === '1'){
                    Extend.modal.open({
                        referenceId: product.id,
                        onClose: function(plan, product) {
                            if (plan && product) {
                                var planCopy = { ...plan, covered_product_id: product.id }
                                var data = {
                                    quantity: 1,
                                    plan: planCopy
                                }
                                // TODO: Function that adds plan data to cart
                                console.log("Extend Plan to be added to cart: ", data);
                                // ExtendWooCommerce.addPlanToCart(data)
                                //     .then(() => {
                                        triggerAddToCart();
                                //     })
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



