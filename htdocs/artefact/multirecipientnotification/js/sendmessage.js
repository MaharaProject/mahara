/**
 * Multi-Recipient Notification Send Message
 * @source: http://gitorious.org/mahara/mahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

function initializeAutocomplete() {

    jQuery( "#sendmessage_addnewrecipient" ).catcomplete({
        delay: 0,
        source: function(request, callback) {
            getAutocompleteSource(request, callback);
        },
        select: function(event, ui) {
            var userid = ui.item.userid;
            var username = ui.item.label;
            var userspan = document.createElement('span');
            var jUserspan = jQuery(userspan);

            var inputhidden = document.createElement('input');
            var jInputhidden = jQuery(inputhidden);
            jInputhidden.attr('type', 'hidden').attr('name', 'recipients[]').attr('value', userid);
            jUserspan.append(jInputhidden);

            var label = document.createElement('a');
            var jLabel = jQuery(label);
            jLabel.attr('href', config.wwwroot + 'admin/users/edit.php?id=' + userid);
            jLabel.text(username);
            jUserspan.append(jLabel);

            var link = document.createElement('a');
            var jLink = jQuery(link);
            jLink.attr('onclick', 'removeRecipient("' + username + '")');
            var img = document.createElement('img');
            var jImg = jQuery(img);
            jImg.attr('src', get_themeurl('images/btn_deleteremove.png'));
            jLink.append(jImg);
            jUserspan.append(jLink);

            jQuery('#sendmessage_addnewrecipient').before(jUserspan);
            var jOnclicktext = jQuery('#sendmessage_addnewrecipient');
            jOnclicktext.attr('value', '');
            return false;
        }
    });

    jQuery('#sendmessage_addnewrecipient').each(function() {
        var default_value = this.value;
        var jInput = jQuery(this);
        jInput.focus(function () {
            if (this.value === default_value) {
                this.value = '';
            }
        });
        jInput.blur(function() {
            if (jQuery.trim(this.value) === '') {
                this.value = default_value;
            }
        });
    });
}

function removeRecipient(name) {
    var onclicktext = jQuery('#sendmessage_addnewrecipient');
    var list = onclicktext.parent();
    list.children('div :contains("' + name + '")').remove();

    var jOnclicktext = jQuery('#sendmessage_addnewrecipient');
    jOnclicktext.attr('value', '');
}

function getAutocompleteSource(request, callback) {
    var jOnclicklist = jQuery('#sendmessage_addnewrecipient').parent();
    var ignoreusers = [];
    jOnclicklist.find('input:hidden').each(function() {
        ignoreusers.push(jQuery(this).val());
    });

    sendjsonrequest('sendmessage.json.php',
        {
            getuserlist : 1,
            ignoreusers : JSON.stringify(ignoreusers),
            request     : request.term
        },
        'GET',
        function (data) {
            callback(data.data.autocompleteusers);
        }
    );
}
