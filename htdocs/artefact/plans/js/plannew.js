/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

function fillFormFromTemplatePlan(templatePlan) {

    $('#addplan_title').val(templatePlan.title);
    $('#addplan_description').val(templatePlan.description);
    $('#addplan_template').prop('checked', templatePlan.template);

    // We use the server set disabled property as indicator for the optional setting the selection plan flag
    if (!$('#addplan_selectionplan').prop('disabled')) {
        $('#addplan_selectionplan').prop('checked', templatePlan.selectionplan);
    }

    var $select = $('#addplan_tags');

    // Clear tag selection element
    $select.val(null);

    // Fill tag selection element
    templatePlan.tags.forEach(function (tag) {
        $select.append(new Option(tag, tag, false, true));
    });
    $select.trigger('change');
}

$(function () {
    // If we would set the switchbox disabled property in Pieforms as default to readonly,
    // the user wouldn't be able to change it's value anymore if applicable, so we have to set the disabled property here
    if ($('#addplan_groupid').length) {
        $('#addplan_selectionplan').prop('disabled', false);
    }
    else {
        $('#addplan_selectionplan').prop('disabled', !$('#addplan_template').prop('checked'));
    }

    $('.templatelist').click(function () {
        var $this = $(this);
        if ($this.val()) {
            var params = {
                'planid': $this.val()
            }
            sendjsonrequest(config.wwwroot + "artefact/plans/groupplans/get_plan_template.json.php", params, 'POST', function (data) {
                if (data.error) {
                    alert(data.message);
                }
                else {
                    fillFormFromTemplatePlan(data);
                    $('[name="createfromuserplantemplate"]').val($this.val());
                }
            });
        }
        else {
            $('[name="createfromuserplantemplate"]').val($this.val());
        }
        $('#template_selection_dialog').modal('hide');
    });

    $('#template_selection_button').click(function () {
        $('#template_selection_dialog').modal('show');
    });

    $('#addplan_template').change(function() {
        var checked = $(this).prop('checked');
        $('#addplan_selectionplan').prop('checked', checked);
        $('#addplan_selectionplan').prop('disabled', !checked);
    });
});

