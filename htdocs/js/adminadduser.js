/**
 * Support file for the adduser admin page in Mahara
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

var current;

jQuery(function($) {
  function move_step(i) {
      var selected = $(i).closest('td.step')[0];
      if (selected != current) {
          $(selected).addClass('current');
          if (current) {
              $(current).removeClass('current');
          }
          current = selected;
      }
  }

  function change_quota(input) {
      var quota = document.getElementById('adduser_quota');
      var quotaUnits = document.getElementById('adduser_quota_units');
      var params = {};
      params.instid = $(input).val();
      if (quotaUnits == null) {
          params.disabled = true;
      }
      sendjsonrequest('quota.json.php', params, 'POST', function(data) {
          if (quotaUnits == null) {
              quota.value = data.data;
          }
          else {
              quota.value = data.data.number;
              quotaUnits.value = data.data.units;
          }
      });
  }

    var step1_spans = $('.step1').find('span.requiredmarker');
    var step1_inputs = $('.step1').find('input.required');
    var leap2a_input = $('#adduser_leap2afile');
    var leap2a_label = $('#leap2a_label');

    leap2a_input.disabled = true;
    $('.step').addClass('current');

    /**
     * state = true if the user selects the leap2a radio button, else false
     */
    function set_step1_requiredfields(state) {
        if (state) {
            $(step1_spans).each(function() {
              $(this).css('visibility', 'hidden');
            });
            $(step1_inputs).each(function() {
              $(this).removeClass('required');
            });
        }
        else {
          $(step1_spans).each(function() {
            $(this).css('visibility', 'visible');
          });
          $(step1_inputs).each(function() {
            $(this).addClass('required');
          });
        }

        $('#adduser_firstname').prop('disabled',state);
        $('#adduser_lastname').prop('disabled',state);
        $('#adduser_email').prop('disabled',state);
        $('#adduser_leap2afile').disabled = !state;
    }


    $('#adduser input.ic').each(function() {
      $(this).on('click', function(e) {
          set_step1_requiredfields($(this).prop('id') == 'uploadleap');
      });
      if ($(this).prop('checked')) {
          set_step1_requiredfields($(this).prop('id') == 'uploadleap');
      }
    });


    current = $('#adduser td.step1')[0];
    $('#adduser input').each(function() {
      $(this).on('focus', move_step.bind(null, this));
      $(this).on('click', move_step.bind(null, this));
    });

    select = document.getElementById('adduser_authinstance');
    if (select != null) {
        $(select).on('change', change_quota.bind(null, select));
    }
    else {
        select = document.getElementsByName('authinstance')[0];
    }
    change_quota(select);
});
