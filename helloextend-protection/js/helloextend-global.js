(function ( $ ) {
    'use strict';
    $(document).ready(
        function ($) {
            if(!ExtendWooCommerce) { return;
            }

            const { store_id: storeId, ajaxurl, environment  } = ExtendWooCommerce;

            Extend.config(
                {
                    storeId,
                    environment
                }
            );

            window.ExtendWooCommerce = {
                ...ExtendWooCommerce,
                addPlanToCart,
                getCart,
                warrantyAlreadyInCart,
                extendAjaxLog
            }

            async function addPlanToCart(opts)
            {
                return await jQuery.post(
                    ajaxurl, {
                        action: "add_to_cart_helloextend",
                        quantity: opts.quantity,
                        extendData: opts.plan
                    }
                ).promise()
            }

            async function getCart()
            {
                return JSON.parse(
                    await jQuery.post(
                        ajaxurl, {
                            action: "get_cart_helloextend"
                        }
                    ).promise()
                );
            }

            function warrantyAlreadyInCart(variantId, cart)
            {
                let cartContents = cart['cart_contents'];
                if (!cartContents) {
                    cartContents = cart;
                }
                const cartItems = Object.values(cartContents);
                const extendWarranties = cartItems.filter(
                    function (lineItem) {
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
                    }
                );
                return extendWarranties.length > 0;
            }

            function extendAjaxLog(message , method)
            {

                /* Now use an ajax call to write logs from js files... */
                $.ajax(
                    {
                        type: 'POST',
                        url: ajaxurl,

                        data: {
                            action: 'helloextend_logger_ajax_call',
                            message: message,
                            method: method,
                        },
                        success: function (xhr, x, checkStatus) {
                            return null;
                        },
                        error: function (e) {
                            console.error("helloextendAjaxLog error: ", e.statusText)
                        }
                    }
                );
            }

        }
    )
})(jQuery);



