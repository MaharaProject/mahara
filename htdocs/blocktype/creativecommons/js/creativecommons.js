/**
 * Automatic display of the Free Culture seal
 *
 * @package    mahara
 * @subpackage blocktype-creativecommons
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

var toggle_seal = jQuery(function($) {
  return function() {
      freeculture = true;
      sealimage = $("#freecultureseal");

      if (!$('#instconf_noncommercial_container input').first().prop('checked')) {
          freeculture = false;
      }

      if ($('#instconf_noderivatives_container input').eq(2).prop('checked')) {
          freeculture = false;
      }

      if (freeculture) {
          sealimage.removeClass("d-none");
      }
      else {
          sealimage.addClass("d-none");
      }
  };
});
