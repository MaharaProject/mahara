/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/*global tinymce:true */

tinymce.PluginManager.add('tooltoggle', function(editor) {
    var tooltoggleState = false, DOM = tinymce.DOM;
    var firstrow;

    editor.ui.registry.addToggleButton('toolbar_toggle', {
        icon: 'chevron-up',
        tooltip: get_string('toggletoolbarson'),
        onAction: function(api) {
            firstrow.siblings().toggleClass('d-none');
            firstrow.find('button').first().toggleClass('flipicon');
            api.setActive(!api.isActive());
            tooltoggleState = !tooltoggleState;
            editor.fire('ToolToggleStateChanged', {state: tooltoggleState});
        },
        onSetup: function(api) {
            var self = this;
            firstrow = jQuery(editor.editorContainer).find('.tox-toolbar-overlord').children().first();
            firstrow.siblings().addClass('d-none');
            firstrow.find('button').first().addClass('flipicon');
            editor.on('ToolToggleStateChanged', function(api) {
                if (api.state) {
                    api.target.formElement[2].title = get_string('toggletoolbarsoff');
                }
                else {
                    api.target.formElement[2].title = get_string('toggletoolbarson');
                }
            });
            api.setActive(self.state);
        }
    });
});
