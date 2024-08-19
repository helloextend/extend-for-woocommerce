(function( $ ) {
    'use strict';
    $(document).ready(function($) {

        if (!ExtendWooCommerce || !ExtendProductIntegration) return;

        // Deconstructs ExtendProductIntegration variables
        const { type: product_type, id: product_id, sku, first_category, price, extend_pdp_offers_enabled, extend_modal_offers_enabled, extend_use_skus, atc_button_selector } = ExtendProductIntegration;

        const $atcButton = jQuery(atc_button_selector)

        let supportedProductType = true;
        let reference_id = '';

        // If PDP offers are not enabled, hide Extend offer div
        if (extend_pdp_offers_enabled === '0') {
            const extendOffer = document.querySelector('.extend-offer')
            extendOffer.style.display = 'none';
        }

        if (extend_use_skus == '1') {
            reference_id = sku;
        } else {
            reference_id = product_id;
        }

        function handleAddToCartLogic(variation_id)  {

            $atcButton.on('click', function extendHandler(e) {
                e.preventDefault()

                let isDisabled = $atcButton.hasClass("disabled");

                if (isDisabled) return;

                function triggerAddToCart() {
                    $atcButton.off('click', extendHandler);
                    $atcButton.trigger('click');
                    $atcButton.on('click', extendHandler);
                }

                const component = Extend.buttons.instance('.extend-offer');

                /** get the users plan selection */
                const plan = component.getPlanSelection();
                const product = component.getActiveProduct();

                if (plan) {
                    var planCopy = { ...plan, covered_product_id: variation_id }
                    var data = {
                        quantity: 1,
                        plan: planCopy,
                        price: (plan.price / 100).toFixed(2)
                    }
                    ExtendWooCommerce.addPlanToCart(data)

                        .then(() => {
                            triggerAddToCart();
                        })
                } else {
                    if(extend_modal_offers_enabled === '1') {
                        Extend.modal.open({
                            referenceId: variation_id,
                            price: price,
                            category: first_category,
                            onClose: function(plan, product) {
                                if (plan && product) {
                                    var planCopy = { ...plan, covered_product_id: variation_id }
                                    var data = {
                                        quantity: 1,
                                        plan: planCopy,
                                        price: (plan.price / 100).toFixed(2)
                                    }
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

        }

        // If Extend PDP is enabled, render offers
        if (extend_pdp_offers_enabled === '1') {
            if(product_type ==='simple'){

                Extend.buttons.render('.extend-offer', {
                    referenceId: reference_id,
                    price: price,
                    category: first_category
                });

                // TODO: initalize cart offers
                handleAddToCartLogic(reference_id);

            } else if (product_type === 'variable') {

                jQuery( ".single_variation_wrap" ).on( "show_variation", function ( event, variation )  {

                    setTimeout(function(){
                        let component = Extend.buttons.instance('.extend-offer');
                        let variation_id = variation.variation_id;
                        let variationPrice = variation.display_price * 100

                        if (component) {

                            if(variation_id) {

                                Extend.setActiveProduct('.extend-offer',
                                    {
                                        referenceId: variation_id,
                                        price: variationPrice,
                                        category: first_category
                                    }
                                );


                            }
                        } else {
                            Extend.buttons.render('.extend-offer', {
                                referenceId: variation_id,
                                price: variationPrice,
                                category: first_category
                            });
                        }

                        handleAddToCartLogic(variation_id);

                    }, 1000);
                });
            } else if (product_type === 'composite') {

                Extend.buttons.render('.extend-offer', {
                    referenceId: reference_id,
                    price: price,
                    category: first_category
                });

                handleAddToCartLogic();

                // These two variables need to be settings in the plugin
                let compositeProductOptionsSelector = '.dd-option'
                let priceSelector = '.summary > .price > .woocommerce-Price-amount'

                jQuery(compositeProductOptionsSelector).on("click", function() {
                    const compositeProductPrice = parseFloat(document.querySelector(priceSelector).textContent.replace("$", "")) * 100;
                    if (compositeProductOptionsSelector && priceSelector) {
                        Extend.setActiveProduct('.extend-offer', {
                            referenceId: reference_id,
                            price: compositeProductPrice,
                            category: first_category
                        });
                    }
                });

            } else {
                console.warn("extend-pdp-offers.js error: Unsupported product type: ", product_type);
                supportedProductType = false;
            }


        }
    });
})( jQuery );