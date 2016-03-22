/**
 * Show badges details in a modal dialog
 *
 * @package    mahara
 * @subpackage blocktype-openbadgedisplayer
 * @author     Discendum Oy
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */


        function shorten(str) {
            var n = 40;
            return str.substr(0, n - 1) + (str.length > n ? '...' : '');
        }

        function formatDate(date) {
            if (!date) {
                return '-';
            }
            if (date.toString().match(/^[0-9]+$/)) {
                var d = new Date(0);
                d.setUTCSeconds(date);
                return d.toLocaleDateString();
            }
            return date;
        }

        function urlElement(url) {
            if (!url) {
                return '-';
            }
            return jQuery('<a/>').attr({ href: url, title: url }).text(shorten(url));
        }

        function buildBadgeContent(assertion) {
            var el = jQuery('.badge-template').clone().removeClass('badge-template');

            el.find('img.badge-image').attr('src', assertion.badge.image);
            el.find('tr.issuer-name td.value').text(assertion.badge.issuer.name);
            el.find('tr.issuer-url td.value').html(urlElement(assertion.badge.issuer.origin));
            el.find('tr.issuer-organization td.value').text(assertion.badge.issuer.org || '-');

            el.find('tr.badge-name td.value').text(assertion.badge.name);
            el.find('tr.badge-description td.value').text(assertion.badge.description);
            el.find('tr.badge-criteria td.value').html(urlElement(assertion.badge.criteria));

            el.find('tr.issuance-evidence td.value').html(urlElement(assertion.evidence));
            el.find('tr.issuance-issuedon td.value').text(formatDate(assertion.issued_on));
            el.find('tr.issuance-expires td.value').text(formatDate(assertion.expires));

            return el.prop('outerHTML');
        }

        function showBadgeContent(data) {
            /* Add a modal dialog if not exists */
            if (jQuery('div#content').length == 1 && jQuery('div#content #badge-content-dialog').length == 0) {
                jQuery('div#content').append(
'<div id="badge-content-dialog" class="modal fade page-modal js-page-modal" role="dialog">' +
'  <div class="modal-dialog">' +
'    <div class="modal-content">' +
'      <div class="modal-body"></div>' +
'      <div class="modal-footer">' +
'        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>' +
'      </div>' +
'    </div>' +
'  </div>' +
'</div>');
            }

            jQuery('#badge-content-dialog .modal-body').html(data.html);
            jQuery('#badge-content-dialog').modal('show');

        }
