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
    $(window).on("keyup", function(e) {
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
                case 'a': // Administration
                    document.location.href = config.wwwroot + 'admin/index.php';
                    break;
                case 'h': // Homepage
                    document.location.href = config.wwwroot;
                    break;
                case 'b': // User journal
                    document.location.href = config.wwwroot + 'artefact/blog/index.php';
                    break;
                case 'p': // User Profile
                    document.location.href = config.wwwroot + 'artefact/internal/index.php';
                    break;
                case 'f': // User files
                    document.location.href = config.wwwroot + 'artefact/file/index.php';
                    break;
                case 'g': // Groups
                    document.location.href = config.wwwroot + 'group/index.php';
                    break;
                case 'v': // Pages and collections
                    document.location.href = config.wwwroot + 'view/index.php';
                    break;
                case 's': // Pages and collections access
                    document.location.href = config.wwwroot + 'view/share.php';
                    break;
                case 'l': // Plans
                    document.location.href = config.wwwroot + 'artefact/plans/index.php';
                    break;
                case 'e': // Export
                    document.location.href = config.wwwroot + 'export/index.php';
                    break;
                case '/':
                    $('#usf_query').focus();
                    break;
            }
            config.commandMode = false;
        }
        else {
            if (e.key == 'g') {
                config.commandMode = true;
            }
        }
    });
});
