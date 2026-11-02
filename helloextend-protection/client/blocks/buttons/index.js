import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { createElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/* wp-scripts chains @svgr/webpack with url-loader for .svg imports, so the
   DEFAULT export is a data URL and the React component is the named
   `ReactComponent` export — importing the default here would hand
   createElement() a string and produce a bogus element.

   The icon has to be supplied here rather than in block.json: that file's
   `icon` field only accepts a Dashicon slug and cannot reference a file. */
import { ReactComponent as ExtendX } from '../../../assets/extend-x-24x24.svg';

/* Must match the name in block.json — the editor merges the server-side
   metadata (title, category) in by name, and a mismatch leaves the block
   untitled and out of the inserter. */
registerBlockType( 'helloextend/buttons', {
	icon: createElement( ExtendX ),

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
			__( 'Extend Buttons', 'helloextend-protection' )
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
	}
} );
