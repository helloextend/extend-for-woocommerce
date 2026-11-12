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

?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes the attribute string it returns. ?>
data-block-name="helloextend/buttons">
<?php
/*
 * data-helloextend-offer marks this as the block's container. A product
 * template can render an offer container twice — once here and once from the
 * classic PDP hook in class-helloextend-protection-pdp-offer.php, which still
 * fires on many block themes. helloextend-pdp-offers.js reads this attribute
 * to decide which one to keep.
 */
?>
<div class='helloextend-offer' data-helloextend-offer='block'></div>
</div>
