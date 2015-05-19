/**
 * Automatic display of the Free Culture seal
 *
 * @package    mahara
 * @subpackage blocktype-creativecommons
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

function toggle_seal() {
    freeculture = true;
    sealimage = $("freecultureseal");

    nc_checkboxes = getElementsByTagAndClassName("input", null, $("instconf_noncommercial_container"));
    if (!nc_checkboxes[0].checked) {
        freeculture = false;
    }

    nd_checkboxes = getElementsByTagAndClassName("input", null, $("instconf_noderivatives_container"));
    if (nd_checkboxes[2].checked) {
        freeculture = false;
    }

    if (freeculture) {
        removeElementClass(sealimage, "hidden");
    }
    else {
        addElementClass(sealimage, "hidden");
    }
}
