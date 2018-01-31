/**
 * General javascript routines for Mahara
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

function checkActiveTab(activeTab) {
    $('a[href="#' + activeTab + '"]').tab('show');
    showTab('#' + activeTab);
}

function showTab(el) {
    var i;
    var x = $(".tab");
    for (i = 0; i < x.length; i++) {
        $(x[i]).addClass("js-hidden");
    }
    $(el).removeClass("js-hidden");
    $(el + '-text').removeClass("js-hidden");
}
