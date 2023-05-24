(function( $ ) {
    'use strict';
    $(document).ready(function($) {
        if(!ExtendWooCommerce) return;

        const { store_id: storeId, ajaxurl, environment  } = ExtendWooCommerce;

        Extend.config({
            storeId,
            environment
        });

        window.ExtendWooCommerce = {
            ...ExtendWooCommerce,
            addPlanToCart,
            getCart,
            warrantyAlreadyInCart
        }

        async function addPlanToCart (opts) {
            return await jQuery.post(ajaxurl, {
                action: "add_to_cart_extend",
                quantity: opts.quantity,
                extendData: opts.plan
            }).promise()
        }

        async function getCart() {
            return JSON.parse(
                await jQuery.post(ajaxurl, {
                    action: "get_cart_extend"
                }).promise()
            );
        }

        function warrantyAlreadyInCart (variantId, cart) {
            console.log("warrantyAlreadyInCart cart", cart)
            console.log("cart_contents", cart['cart_contents'])
            let cartContents = cart['cart_contents'];
            if (!cartContents) {
                cartContents = cart;
            }
            const cartItems = Object.values(cartContents);
            console.log("Final cartContents", cartContents)
            const extendWarranties = cartItems.filter(function (lineItem) {
                //filter through the customAttributes and grab the referenceId
                let extendData = lineItem.extendData;
                if (extendData && extendData['covered_product_id']) {
                    let referenceId = extendData['covered_product_id'];
                    return (
                        extendData &&
                        !extendData.leadToken &&
                        referenceId &&
                        referenceId.toString() === variantId.toString()
                    );
                }
            });
            return extendWarranties.length > 0;
        }

    })
})( jQuery );



