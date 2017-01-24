/**
 * Javascript for the switchbox
 *
 * @package    mahara
 * @subpackage core
 * @author     Gilles-Philippe Leblanc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  2017 Université de Montréal
 *
 */

// Self executing function.
(function(Switchbox, $) {
    "use strict";
    Switchbox.computeWidth = function(id) {
      var switchbox = $('#' + id).parent(),
          // Use a temporary copy to be sure that the switch is not hidden and therefore has an outerWidth.
          tempform = switchbox.parent().clone().appendTo('body'),
          labels = tempform.find('.switch .state-label');
      labels.css('width', 'auto');
      var maxWidth = Math.max.apply(null, labels.map(function() {
          return $(this).outerWidth();
      }).get());
      tempform.remove();
      switchbox.css('width', (Math.ceil(maxWidth) + 1) + 'px');
    };
  }(window.Switchbox = window.Switchbox || {}, jQuery));
