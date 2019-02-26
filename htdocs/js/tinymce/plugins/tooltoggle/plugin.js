/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/*global tinymce:true */

tinymce.PluginManager.add('tooltoggle', function(editor) {
    var tooltoggleState = false, DOM = tinymce.DOM;

    editor.addButton('toolbar_toggle', {
        icon: 'icon tooltoggle-icon',
        tooltip: get_string('toggletoolbarson'),
        onclick: function(e) {
            jQuery(e.target).closest('.mce-toolbar').siblings().toggleClass('d-none');
            tooltoggleState = !tooltoggleState;
            editor.fire('ToolToggleStateChanged', {state: tooltoggleState});
        },
        onPostRender: function() {
            var self = this;
            editor.on('ToolToggleStateChanged', function(e) {
                if (e.state) {
                    e.target.buttons.toolbar_toggle.tooltip = get_string('toggletoolbarsoff');
                }
                else {
                    e.target.buttons.toolbar_toggle.tooltip = get_string('toggletoolbarson');
                }
                self.active(e.state);
            });
        }
    });
});
