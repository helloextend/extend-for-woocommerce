(function( $ ) {
    'use strict';
    $(document).ready(function() {

        if (!ExtendWooCommerce || !ExtendProductIntegration) return;

        // Deconstructs ExtendProductIntegration variables
        const { type: product_type, id: product_id, sku, first_category, price, helloextend_pdp_offers_enabled, helloextend_modal_offers_enabled, atc_button_selector } = ExtendProductIntegration;

        const $atcButton = $(atc_button_selector);

        const quantity = parseInt($('input[name="quantity"]').val() || 1)

        let reference_id = product_id;

        // If PDP offers are not enabled, hide Extend offer div
        if (helloextend_pdp_offers_enabled === '0') {
            $('.helloextend-offer').hide();
        }

        function handleAddToCartLogic()  {

            $atcButton.off('click.extend').on('click.extend', function extendHandler(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                let isDisabled = $atcButton.hasClass("disabled");

                if (isDisabled) return;

                function triggerAddToCart() {
                    $atcButton.off('click.extend', extendHandler);
                    $atcButton.trigger('click');
                    $atcButton.on('click.extend', extendHandler);
                }

                const component = Extend.buttons.instance('.helloextend-offer');
                
                /** get the users plan selection */
                const plan = component.getPlanSelection();
                const referenceId = component.getActiveProduct().id;

                if (plan) {
                    let planCopy = { ...plan, covered_product_id: referenceId };
                    let data = {
                        quantity: quantity,
                        plan: planCopy,
                        price: (plan.price / 100).toFixed(2)
                    };

                    ExtendWooCommerce.addPlanToCart(data)
                        .then(() => {
                            triggerAddToCart();
                        });
                } else {
                    if(helloextend_modal_offers_enabled === '1') {
                        Extend.modal.open({
                            referenceId,
                            price: price,
                            category: first_category,
                            onClose: function(plan, product) {
                                if (plan && product) {
                                    let planCopy = { ...plan, covered_product_id: referenceId };
                                    let data = {
                                        quantity: quantity,
                                        plan: planCopy,
                                        price: (plan.price / 100).toFixed(2)
                                    };

                                    ExtendWooCommerce.addPlanToCart(data)
                                        .then(() => {
                                            triggerAddToCart();
                                        });
                                } else {
                                    triggerAddToCart();
                                }
                            },
                        });
                    } else {
                        triggerAddToCart();
                    }
                }

            });

        }

        // If Extend PDP is enabled, render offers
        if (helloextend_pdp_offers_enabled === '1') {
            if(product_type ==='simple'){

                Extend.buttons.render('.helloextend-offer', {
                    referenceId: reference_id,
                    price: price,
                    category: first_category
                });

                // TODO: initalize cart offers
                handleAddToCartLogic();

            } else if (product_type === 'variable') {

                $( ".single_variation_wrap" ).on( "show_variation", function ( event, variation )  {

                        let component = Extend.buttons.instance('.helloextend-offer');
                        let variation_id = variation.variation_id;
                        let variationPrice = variation.display_price * 100

                        if (component) {
                            if(variation_id) {
                                Extend.setActiveProduct('.helloextend-offer',
                                    {
                                        referenceId: variation_id,
                                        price: variationPrice,
                                        category: first_category
                                    }
                                );
                            }
                        } else {
                            Extend.buttons.render('.helloextend-offer', {
                                referenceId: variation_id,
                                price: variationPrice,
                                category: first_category
                            });
                        }

                    });

                    handleAddToCartLogic();
            } else if (product_type === 'composite') {

                Extend.buttons.render('.helloextend-offer', {
                    referenceId: reference_id,
                    price: price,
                    category: first_category
                });

                handleAddToCartLogic();

                // These two variables need to be settings in the plugin
                let compositeProductOptionsSelector = '.dd-option';
                let priceSelector = '.summary > .price > .woocommerce-Price-amount';

                $(compositeProductOptionsSelector).on("click", function() {
                    const compositeProductPrice = parseFloat($(priceSelector).text().replace("$", "")) * 100;
                   
                    Extend.setActiveProduct('.helloextend-offer', {
                        referenceId: reference_id,
                        price: compositeProductPrice,
                        category: first_category
                    });

                });

            } else {
                console.warn("helloextend-pdp-offers.js error: Unsupported product type: ", product_type);
            }
        }
    });
})( jQuery );