import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { createElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ReactComponent as ExtendX } from '../../../assets/extend-x-24x24.svg';

/* Must match the name in block.json. This block declares a `parent` list, so
   it is only offered inside the WooCommerce cart and checkout blocks named
   there — it will not appear in the top-level inserter of a normal post. */
registerBlockType( 'helloextend/shipping-protection', {
	icon: createElement(ExtendX),
	/**
	 * Placeholder rendered on the editor canvas.
	 *
	 * This is a static label rather than a live preview of render.php. If the
	 * PHP output grows beyond a placeholder and the preview needs to match it,
	 * import ServerSideRender from '@wordpress/server-side-render' and return
	 * that instead — the dependency list updates itself on the next build.
	 *
	 * @return {Object} Element to render in the editor.
	 */
	edit: function Edit() {
		return createElement(
			'div',
			useBlockProps(),
			__( 'Extend Shipping Protection', 'helloextend-protection' )
		);
	},

	/**
	 * Dynamic block: nothing is written into post content, so the saved markup
	 * is just the block comment delimiter.
	 *
	 * @return {null} Nothing to serialize.
	 */
	save: function Save() {
		return null;
	},
} );
