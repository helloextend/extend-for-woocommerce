/**
 * Editor registration for the helloextend/buttons block.
 *
 * The block is registered server-side from block.json, so the editor already
 * knows its title, category and description — WordPress bootstraps that
 * metadata into the editor for every block type registered in PHP. This file
 * supplies only the parts that have to live in JavaScript: an edit component
 * for the canvas, and a save that returns null because the front-end markup is
 * produced by render.php on every request.
 *
 * Without this file the block still renders correctly wherever it is placed,
 * but it is absent from the inserter — the editor builds that list from blocks
 * registered on the client, not from the PHP registry.
 *
 * No build step: this is plain ES5-compatible JS against the wp.* globals,
 * matching the rest of the plugin's JavaScript.
 *
 * Generated with assistance from Claude Code.
 */

(function ( blocks, element, blockEditor, i18n ) {
    'use strict';

    const el = element.createElement;
    const useBlockProps = blockEditor.useBlockProps;
    const __ = i18n.__;

    blocks.registerBlockType('helloextend/buttons', {
        /**
         * Placeholder rendered on the editor canvas.
         *
         * This is a static label rather than a live preview of render.php. If
         * the PHP output grows beyond a placeholder and the preview needs to
         * match it, swap this for wp.serverSideRender and add
         * 'wp-server-side-render' to the dependencies in index.asset.php.
         *
         * @return {Object} Element to render in the editor.
         */
        edit: function () {
            return el(
                'div',
                useBlockProps(),
                __('Extend Buttons', 'helloextend-protection')
            );
        },

        /**
         * Dynamic block: nothing is written into post content, so the saved
         * markup is just the block comment delimiter.
         *
         * @return {null} Nothing to serialize.
         */
        save: function () {
            return null;
        }
    });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.i18n);
