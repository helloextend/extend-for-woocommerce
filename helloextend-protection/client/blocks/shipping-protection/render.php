<?php

/**
 * Server-side render callback for the helloextend/buttons block.
 *
 * Referenced from block.json via the `render` field, so WordPress includes this
 * file on every front-end render of the block. Three variables are in scope:
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner block content.
 * @var WP_Block $block      The block instance.
 *
 * @since   1.2.11
 * @package HelloExtend_Protection
 *
 * Generated with assistance from Claude Code.
 */

// If this file is accessed directly, exit.
if (!defined('ABSPATH')) {
    exit;
}

if (is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST )) {
    return $content;
}

if ( ! function_exists( 'WC' ) || WC()->cart == null ) {
    return $content;
}

// add offer element
$cart_items = WC()->cart->get_cart();

if ( ! isset( $cart_items ) || $cart_items == null ) {
    return $content;
}

$items      = array_values(
    array_filter(
        array_map(function($item) {
            /** @var WC_Product */
            $product = $item['data'];

            if ( !$product->is_virtual() ) {
                $referenceId = $product->get_id();
                $categories = get_the_terms($item['product_id'], 'product_cat');
                $category    = HelloExtend_Protection_Global::helloextend_get_first_valid_category(is_wp_error($categories) ? [] : (array) $categories);

                return [
                    'referenceId'   => $referenceId,
                    'quantity'      => $item['quantity'],
                    'category'      => $category,
                    'purchasePrice' => (int) floatval($product->get_price() * 100),
                    'productName'   => $product->get_name()
                ];
            }
        }, $cart_items)
    )
);

/*
 * JSON_HEX_AMP escapes every ampersand in the JSON, so nothing that looks
 * like an HTML entity can survive into the attribute. esc_attr() does not
 * double-encode, so without it a product name containing the literal text
 * "&quot;" reaches the browser as an entity, decodes to a bare quote and
 * breaks the JSON — leaving jQuery's .data() to hand the offer a string
 * instead of the item array.
 */
?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes the attribute string it returns. ?>
data-block-name="helloextend/shipping-protection">
<div id="helloextend-block-shipping-offer" style="max-height: 100px; padding-right: 1rem;" data-items="<?php echo esc_attr(wp_json_encode($items, JSON_HEX_AMP)); ?>"></div>
</div>
