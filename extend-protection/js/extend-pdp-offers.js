(function( $ ) {
    'use strict';
    $(document).ready(function($) {

        // if(!ExtendWooCommerce || !ExtendProductIntegration) return;
        if (!ExtendWooCommerce || !ExtendProductIntegration) return;

        // Deconstructs ExtendProductIntegration variables
        const { type: product_type, id: product_id, sku, first_category, price, extend_pdp_offers_enabled, extend_modal_offers_enabled, extend_use_skus, atc_button_selector } = ExtendProductIntegration;

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

        // If Extend PDP is enabled, render offers
        if (extend_pdp_offers_enabled === '1') {
            if(product_type ==='simple'){
                Extend.buttons.render('.extend-offer', {
                    referenceId: reference_id,
                    price: price,
                    category: first_category
                })
            } else if (product_type === 'variable') {
                Extend.buttons.render('.extend-offer', {
                    referenceId: reference_id,
                    price: price,
                    category: first_category
                });

                setTimeout(function(){
                    let variation_id = jQuery('[name="variation_id"]').val();
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
            } else if (product_type === 'composite') {
                Extend.buttons.render('.extend-offer', {
                    referenceId: reference_id,
                    price: price,
                    category: first_category
                });

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

            if (supportedProductType) {

                // Clone ATC Button
                const atc_button_clone = document.createElement('button')
                atc_button_clone.textContent = document.querySelector(atc_button_selector).textContent;

                // copy styles from original button to clone
                const atc_button_styles = window.getComputedStyle(document.querySelector(atc_button_selector));
                for (let i = 0; i < atc_button_styles.length; i++) {
                    const prop = atc_button_styles[i];
                    atc_button_clone.style.setProperty(prop, atc_button_styles.getPropertyValue(prop), atc_button_styles.getPropertyPriority(prop));
                }

                // Append clone button and hide original
                jQuery(atc_button_selector).after(atc_button_clone);
                jQuery(atc_button_selector).hide();

                // Add click handler to clone button
                atc_button_clone.addEventListener('click', function extendHandler(e) {
                    e.preventDefault()

                    function triggerAddToCart() {
                        jQuery(atc_button_selector).trigger('click');
                    }

                    // /** get the component instance rendered previously */
                    const component = Extend.buttons.instance('.extend-offer');

                    /** get the users plan selection */
                    const plan = component.getPlanSelection();
                    const product = component.getActiveProduct();

                    if (plan) {
                        var planCopy = { ...plan, covered_product_id: reference_id }
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
                                referenceId: reference_id,
                                price: price,
                                category: first_category,
                                onClose: function(plan, product) {
                                    if (plan && product) {
                                        var planCopy = { ...plan, covered_product_id: reference_id }
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
        }
    });
})( jQuery );