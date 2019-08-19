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

    editor.ui.registry.addToggleButton('toolbar_toggle', {
        icon: 'chevron-up',
        tooltip: get_string('toggletoolbarson'),
        onAction: function(api) {
            jQuery(editor.editorContainer.childNodes[0].childNodes[0].childNodes[0]).siblings().toggleClass('d-none')
            jQuery(editor.editorContainer.childNodes[0].childNodes[0].childNodes[0].childNodes[0].childNodes[0]).toggleClass('flipicon');
            api.setActive(!api.isActive());
            tooltoggleState = !tooltoggleState;
            editor.fire('ToolToggleStateChanged', {state: tooltoggleState});
        },
        onSetup: function(api) {
            var self = this;
            jQuery(editor.editorContainer.childNodes[0].childNodes[0].childNodes[0]).siblings().toggleClass('d-none');
            jQuery(editor.editorContainer.childNodes[0].childNodes[0].childNodes[0].childNodes[0].childNodes[0]).addClass('flipicon');
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
