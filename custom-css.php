<?php
/**
 * Plugin Name: Enhanced CSS Editor
 * Plugin URI: https://burostaal.nl
 * Description: Enhances the WordPress CSS editor with syntax highlighting and better editing capabilities.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Buro Staal
 * Author URI: https://burostaal.nl
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: enhanced-css-editor
 * 
 * @package BuroStaal
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Add after ABSPATH check
if ( version_compare( $GLOBALS['wp_version'], '5.8', '<' ) ) {
    return;
}

/**
 * Initialize the CodeMirror CSS editor enhancement
 *
 * @since 1.0.0
 * @return void
 */
function burostaal_initialize_css_editor() {
    // Check if we're in the admin area
    if (!is_admin()) {
        return;
    }

    // Enqueue required scripts and styles
    wp_enqueue_script('wp-codemirror');
    wp_enqueue_style('wp-codemirror');

    add_action('admin_footer', 'burostaal_render_css_editor');
}
add_action('admin_init', 'burostaal_initialize_css_editor');

/**
 * Render the enhanced CSS editor
 *
 * @since 1.0.0
 * @return void
 */
function burostaal_render_css_editor() {
    ?>
    <style>
        .burostaal-codemirror {
            height: auto !important;
            min-height: 300px;
            width: 100% !important;
        }
        .burostaal-codemirror .CodeMirror-scroll {
            min-height: 300px;
            max-height: 70vh;
            overflow-y: auto !important;
        }
        .burostaal-codemirror .CodeMirror-sizer {
            margin-bottom: 20px !important;
        }
        .burostaal-css-textarea-hidden {
            display: block !important;
            position: absolute;
            top: -99999px;
        }
    </style>

    <script>
    (function($) {
        'use strict';

        $(document).ready(function() {
            var editorInitialized = false;

            function initializeCodeMirror(editor) {
                if (editor.data('codemirror-initialized')) {
                    return;
                }

                editor.data('codemirror-initialized', true);
                editor.addClass('burostaal-css-textarea-hidden');

                var codeEditor = wp.CodeMirror.fromTextArea(editor[0], {
                    lineNumbers: true,
                    mode: 'css',
                    indentUnit: 2,
                    lineWrapping: true,
                    viewportMargin: Infinity
                });

                // Add our custom class to CodeMirror
                $(codeEditor.getWrapperElement()).addClass('burostaal-codemirror');

                codeEditor.setSize("100%", "auto");
                codeEditor.refresh();

                codeEditor.on('change', function() {
                    var cssContent = codeEditor.getValue();
                    var textarea = editor[0];
                    var nativeInputValueSetter = Object.getOwnPropertyDescriptor(
                        window.HTMLTextAreaElement.prototype,
                        'value'
                    ).set;
                    nativeInputValueSetter.call(textarea, cssContent);
                    textarea.dispatchEvent(new Event('input', { bubbles: true }));
                });

                editorInitialized = true;
            }

            var observer = new MutationObserver(function(mutations) {
                var cssEditors = $([
                    '.edit-site-global-styles-screen-css label.components-base-control__label:contains("Extra CSS")',
                    '.block-editor-global-styles-advanced-panel__custom-css-input label.components-base-control__label:contains("Extra CSS")'
                ].join(','));

                cssEditors.each(function() {
                    var label = $(this);
                    var textareaId = label.attr('for');
                    var editor = $('#' + textareaId);

                    if (editor.length > 0) {
                        initializeCodeMirror(editor);
                    }
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    })(jQuery);
    </script>
    <?php
}