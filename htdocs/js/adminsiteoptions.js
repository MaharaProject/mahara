/**
 * Forces full reload of the page if certain site options have been
 * changed
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

  // Add here as appropriate
var forceReloadElements = ['sitename', 'lang', 'theme',
                           'defaultaccountlifetime_units',
                           'defaultaccountlifetimeupdate'];
var isReloadRequired = false;

// if strict privacy is enabled, disables multiple institutions per user
function multipleinstitutionscheckallowed() {
    var target = jQuery('#siteoptions_usersallowedmultipleinstitutions');
    if (jQuery('#siteoptions_institutionstrictprivacy').is(':checked')) {
        target.prop('disabled', true);
        target.prop('checked', false);
    }
    else {
        target.prop('disabled', false);
    }
}

// if multiple institution per user is enabled, disables strict privacy
function strictprivacycheckallowed() {
    if (!usersinmultipleinstitutions) {
        var target = jQuery('#siteoptions_institutionstrictprivacy');
        if (jQuery('#siteoptions_usersallowedmultipleinstitutions').is(':checked')) {
            target.prop('disabled', true);
            target.prop('checked', false);
        }
        else {
            target.prop('disabled', false);
        }
    }
}

var checkReload = (function($) {
  // Disconnects the pieform submit handler and changes the form target back to
  // the page itself (rather than pieform's hidden iframe), so a full post/reload
  // cycle will happen when the form is submitted
  function reloadRequired() {
      isReloadRequired = true;
      $('#siteoptions').off();
      $('#siteoptions')[0].target = '';
  }

  // Wires up appropriate elements to cause a full page reload if they're changed
  function connectElements() {
      $(forceReloadElements).each(function(id, element) {
          if ($('#siteoptions_' + element).length) {
            $('#siteoptions_' + element).on('change', reloadRequired);
          }
      });

      $('#siteoptions_allowpublicviews').on('click', update_allowpublicprofiles);
  }



  function update_allowpublicprofiles() {
      if ($('#siteoptions_allowpublicviews').prop('checked')) {
          $('#siteoptions_allowpublicprofiles').prop('checked', true);
          $('#siteoptions_allowpublicprofiles').prop('disabled', 'disabled');
      }
      else {
          $('#siteoptions_allowpublicprofiles').removeAttr('disabled');
      }
  }

  connectElements();


  // Javascript success handler for the form. Re-wires up the elements
  return function(form, data) {
      update_allowpublicprofiles();

      isReloadRequired = false;
      connectElements();

      jQuery('#siteoptions_institutionstrictprivacy').click(function() {
          multipleinstitutionscheckallowed();
      });
      jQuery('#siteoptions_usersallowedmultipleinstitutions').click(function() {
          strictprivacycheckallowed();
      });
      multipleinstitutionscheckallowed();
      strictprivacycheckallowed();

      formSuccess(form, data);
  };
}(jQuery));
