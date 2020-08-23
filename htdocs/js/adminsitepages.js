/**
 * Automatically populates the WYSIWYG box on the site pages screen
 * with the content of the appropriate page
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

var oldPageContent = '';
var oldPageName = 'home';
var changedCheckbox = false;
var checkOldContent = false;

function updateWYSIWYGText() {
    if (((checkOldContent && oldPageContent != tinyMCE.activeEditor.getContent()) || changedCheckbox) && !confirm(get_string('discardpageedits', 'admin'))) {
        jQuery('#editsitepage_pagename').val(oldPageName);
        return;
    }
    if (!tinyMCE.Env.ie) {
        // Disable changed content check for IE (see below)
        checkOldContent = true;
    }
    sendjsonrequest(
        config['wwwroot'] + 'admin/site/editchangecontent.json.php',
        {'contentname' : jQuery('#editsitepage_pagename').val(),
         'institution' : jQuery('#editsitepage_pageinstitution').val()
        },
        'POST',
        function(data) {
            if (!data.error) {
                tinyMCE.activeEditor.setContent(data.content);
                oldPageContent = tinyMCE.activeEditor.getContent();
                oldPageName = jQuery('#editsitepage_pagename').val();
                if (jQuery('#editsitepage_pageusedefault')) {
                    jQuery('#editsitepage_pageusedefault').prop('checked', (data.pageusedefault) ? true : false);
                    updateSiteDefault(false);
                }
            }
        }
    );
}

function updateSiteDefault(changed) {
    changedCheckbox = (changed) ? true : false;
    var editor = jQuery('#editsitepage_pagetext_container .mce-tinymce');
    if (jQuery('#editsitepage_pageusedefault')[0] && jQuery('#editsitepage_pageusedefault').prop('checked') === true) {
        tinyMCE.activeEditor.getBody().setAttribute('contenteditable', false);
        jQuery('#changecheckboxdiv').css({
          'display': 'block',
          'zIndex': '1',
          'position': 'absolute',
          'width': editor.outerWidth() + 'px',
          'height': editor.outerHeight() + 'px',
          'top': editor.offset().top + 'px',
          'left': editor.offset().left + 'px'
        });
    }
    else {
        tinyMCE.activeEditor.getBody().setAttribute('contenteditable', true);
        jQuery('#changecheckboxdiv').css({
          'display': 'none',
          'width': '1px',
          'height': '1px'
        });
    }
}

function connectElements() {
    jQuery('#editsitepage_pagename').on('change', updateWYSIWYGText);
    jQuery('#editsitepage_pageinstitution').on('change', updateWYSIWYGText);
    if (jQuery('#editsitepage_pageusedefault').length) {
        jQuery('#editsitepage_pageusedefault').on('change', updateSiteDefault);
    }
    // create hidden div to place over tinymce to 'show' when it is disabled from editing
    var changeboxdiv = jQuery('<div></div>', {'id':'changecheckboxdiv','style':'display:none;background-color: rgba(200,200,200,0.5)'});
    jQuery(document.body).append(changeboxdiv);
}

function contentSaved(form, data) {
    connectElements();
    changedCheckbox = false;
    if (!tinyMCE.Env.ie) {
        // Disabling changed content check for IE; Need to work out
        // why the getBody() call in getContent fails to return the
        // body element.
        oldPageContent = tinyMCE.activeEditor.getContent();
    }
    // For the 'sitedefault' overlay to be positioned correctly we
    // need to stop old messages from disappearing after page load
    data.hideprevmsg = false;
    formSuccess(form, data);
    updateSiteDefault(false);
}

jQuery(window).on('load', function() {
  connectElements();
  // need to wait until tinyMCE editor is loaded before updating editor's text
  var checkExists = setInterval(function() {
      if (tinyMCE.activeEditor != "null") {
          updateWYSIWYGText();
          clearInterval(checkExists);
      }
  }, 500);
});
