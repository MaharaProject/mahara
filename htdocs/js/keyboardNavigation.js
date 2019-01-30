/**
 * Adds keystroke navigation to Mahara.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

jQuery(function($) {
    $(window).on("keypress", function(e) {
        var targetType = e.target.nodeName;

        if (
            targetType == 'INPUT'
            || targetType == 'TEXTAREA'
            || targetType == 'SELECT'
            || targetType == 'BUTTON'
        ) {
            return;
        }

        if (config.commandMode) {
            switch(e.key) {
                case 'a':
                    document.location.href = config.wwwroot + 'admin/';
                    break;
                case 'h':
                    document.location.href = config.wwwroot;
                    break;
                case 'b':
                    document.location.href = config.wwwroot + 'artefact/blog/';
                    break;
                case 'p':
                    document.location.href = config.wwwroot + 'artefact/internal/';
                    break;
                case 'f':
                    document.location.href = config.wwwroot + 'artefact/file/';
                    break;
                case 'g':
                    document.location.href = config.wwwroot + 'group/index.php';
                    break;
                case 'v':
                    document.location.href = config.wwwroot + 'view';
                    break;
                case 'c':
                    document.location.href = config.wwwroot + 'collection';
                    break;
                case 'l':
                    document.location.href = config.wwwroot + 'artefact/plans';
                    break;
                case '/':
                    $(document.usf.query).trigger("focus");
                    break;
            }
            config.commandMode = false;
        }
        else {
            if (e.key.string == 'g') {
                config.commandMode = true;
            }
        }
    });
});
