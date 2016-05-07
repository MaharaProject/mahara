/**
 * Asynchronous loading badges
 *
 * @package    mahara
 * @subpackage blocktype-openbadgedisplayer
 * @author     Discendum Oy
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

/* pieform_element_checkboxes_get_headdata() includes the javascript
 needed by the "Select all/none" -links. That function isn't called
 when the config form is rendered, so let's just copy the code here
 and add it to window scope.*/
if (typeof pieform_element_checkboxes_update === 'undefined') {
    window.pieform_element_checkboxes_update = function (p, v) {
        forEach(getElementsByTagAndClassName('input', 'checkboxes', p), function(e) {
            if (!e.disabled) {
                e.checked = v;
            }
        });
        if (typeof formchangemanager !== 'undefined') {
            var form = jQuery('div#' + p).closest('form')[0];
            formchangemanager.setFormState(form, FORM_CHANGED);
        }
    };
}

var badgegroups_hosts = JSON.parse(jQuery("input#instconf_hosts").val());
var badgegroups_emails = JSON.parse(jQuery("input#instconf_emails").val());
var selectedbadgegroups = JSON.parse(jQuery("input#instconf_selectedbadgegroups").val());

if ((badgegroups_hosts instanceof Array && badgegroups_hosts.length >= 1)
    && (badgegroups_emails instanceof Array && badgegroups_emails.length >= 1)) {
    var count=0;
    jQuery("div#instconf_loadinginfo_container > p.alert").removeClass('hidden');
    for (var i=0; i < badgegroups_hosts.length; i++) {
        var h = badgegroups_hosts[i];
        for (var j=0; j < badgegroups_emails.length; j++) {
            var e = badgegroups_emails[j];
            var params = {'host': h, 'email': e};
            count++;
            /* Fetching the badge info via ajax and render the pieform checkbox element */
            sendjsonrequest(config['wwwroot'] + '/blocktype/openbadgedisplayer/badgegroupnames.json.php', params, 'POST', function(data) {
                var icon = jQuery('<span class="icon icon-lg icon-exclamation-triangle left" aria-hidden="true" role="presentation"></span>');
                var container = jQuery('<div class="alert alert-warning" role="alert">');
                if (!data.uid) {
                    var msg = jQuery('<span>').text(data.nobackpackmsg);
                    container.append(icon).append(msg);
                    jQuery("div#instconf_loadinginfo_container > div").append(container);
                }
                else if (!data.badgegroups || data.badgegroups.length === 0) {
                    var msg = jQuery('<span>').text(data.nobadgegroupsmsg);
                    container.append(icon).append(msg);
                    jQuery("div#instconf_loadinginfo_container > div").append(container);
                }
                else {
                    var htmlstr =
                        '<div id="instconf_' + data.host + '_container" class="checkboxes form-group">' +
                            '<span class="pseudolabel">' + data["hosttitle"] + '</span>' +
                            '<div class="btn-group">' +
                                '<a href="" class="btn btn-default btn-xs" onclick="pieform_element_checkboxes_update(\'instconf_' + data["host"] + '_container\', true); return false;">' + get_string_ajax('selectall','blocktype.openbadgedisplayer') + '</a>' +
                                '<a href="" class="btn btn-default btn-xs" onclick="pieform_element_checkboxes_update(\'instconf_' + data["host"] + '_container\', false); return false;">' + get_string_ajax('selectnone','blocktype.openbadgedisplayer') + '</a>&nbsp;' +
                            '</div>';
                    for (var badgegroupid in data.badgegroups) {
                        var badgegroupname = data.badgegroups[badgegroupid];
                        var checkboxvalue = data["host"] + ':' + data["uid"] + ':' + badgegroupid;
                        var checkboxid = data["host"] + '_' + data["uid"] + '_' + badgegroupid;
                        var selected = '';
                        if (jQuery.inArray(checkboxvalue, selectedbadgegroups) != -1) {
                            selected = 'checked';
                        }
                        htmlstr +=
                            '<div class="checkboxes-option checkbox">' +
                                '<input type="checkbox" id="instconf_' + checkboxid + '"name="' + data["host"] + '[]" value="' + checkboxvalue + '" ' + selected + ' class="checkboxes">' +
                                    '<label class="checkbox" for="instconf_' + checkboxid + '">' +
                                        '<span class="accessible-hidden sr-only">' + data["hosttitle"] + ': </span>' +
                                         badgegroupname +
                                    '</label>' +
                            '</div>';
                    }
                    htmlstr +=
                        '<div class="cl"></div>' +
                        '</div>';
                    jQuery("div#instconf_loadinginfo_container > div").append(htmlstr);
                }

                count--;
                if (count == 0) {
                    jQuery("div#instconf_loadinginfo_container > p.alert").addClass('hidden');
                }
            });
        }
    }
}
