/**
 * Forces full reload of the page if certain site options have been
 * changed
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

  // Add here as appropriate
var forceReloadElements = ['sitename', 'lang', 'theme',
                           'defaultaccountlifetime_units',
                           'defaultaccountlifetimeupdate',
                           'defaultregistrationexpirylifetime_units',
                           'defaultaccountinactiveexpire_units',
                           'defaultaccountinactivewarn_units',
                           'searchplugin', 'remoteavatars']; // Changes that are visible on site options page
var isReloadRequired = false;

// if strict privacy is enabled, disables multiple institutions per user
function multipleinstitutionscheckallowed(isolated) {
    var target = jQuery('#siteoptions_usersallowedmultipleinstitutions');
    if (jQuery('#siteoptions_institutionstrictprivacy').is(':checked')) {
        target.prop('disabled', true);
        target.prop('checked', false);
    }
    else if (!isolated) {
        target.prop('disabled', false);
    }
}

// if multiple institution per user is enabled, disables strict privacy
function strictprivacycheckallowed(isolated) {
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

// we need to toggle the homepageredirecturl field depending on state of homepageredirect
function homepageredirect() {
    var target = jQuery('#siteoptions_homepageredirecturl');
    if (jQuery('#siteoptions_homepageredirect').is(':checked')) {
        target.parent().removeClass('hidden');
        target.prop('disabled', false);
    }
    else {
        target.parent().addClass('hidden');
        target.prop('disabled', true);
    }
}

function update_allowpublicprofiles() {
    if (jQuery('#siteoptions_allowpublicviews').prop('checked')) {
        jQuery('#siteoptions_allowpublicprofiles').prop('checked', true);
        jQuery('#siteoptions_allowpublicprofiles').prop('disabled', 'disabled');
    }
    else {
        jQuery('#siteoptions_allowpublicprofiles').prop('disabled', false);
    }
}

var checkReload = (function($) {
  // Checks to see if we need to refresh the page after form is saved
  // Normally we only load back in the form but for things like language/theme
  // changes we want to see them right away.
  function reloadRequired() {
      isReloadRequired = true;
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

  jQuery(function() {
      connectElements();
  });

  // Javascript success handler for the form. Re-wires up the elements
  return function(form, data) {
      update_allowpublicprofiles();
      if (isReloadRequired == true) {
          isReloadRequired = false;
          jQuery('#siteoptions_applying').removeClass('d-none');
          setTimeout(function() {
              window.location.href = data.goto;
          }, 1000);
      }
      connectElements();

      jQuery('#siteoptions_institutionstrictprivacy').on("click", function() {
          multipleinstitutionscheckallowed(isolated);
      });
      jQuery('#siteoptions_usersallowedmultipleinstitutions').on("click", function() {
          strictprivacycheckallowed(isolated);
      });
      jQuery('#siteoptions_homepageredirect').on("click", function() {
          homepageredirect();
      });
      multipleinstitutionscheckallowed(isolated);
      strictprivacycheckallowed(isolated);
      homepageredirect();

      formSuccess(form, data);
  };
}(jQuery));

jQuery(function($) {
    $('#siteoptions_allowpublicviews').on('click', update_allowpublicprofiles);
});
