/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

$(function () {

    function setButtonAccessibility($button, $selectionBox) {
        if ($selectionBox.val()) {
            $button.attr("disabled", null);
        }
        else {
            $button.attr("disabled", "disabled");
        }
    }

    function getOutcomePortfolioTypeAndId(selectValue) {
        let portfolioType = {'v': 'view', 'c': 'collection'};
        let portfolioTypeAndId = selectValue.split(':', 2);

        return {type: portfolioType[portfolioTypeAndId[0]], id: portfolioTypeAndId[1]}
    }

    // view
    $("#edittask_view").change(function () {
        setButtonAccessibility($("#view_button"), $("#edittask_view"));
    });

    setButtonAccessibility($("#view_button"), $("#edittask_view"));

    if ($("#edittask_view_container .picker").length) {
        $("#view_button").removeClass('d-none').insertAfter("#edittask_view_container .picker");
    }
    else {
        $("#view_button").addClass('d-none');
    }

    // Hook up 'click to preview' links
    $("#view_button").click(function (event) {
        event.preventDefault();

        var viewId = $("#edittask_view").val();
        if (viewId) {
            var params = {
                'id': viewId
            };
            sendjsonrequest(config.wwwroot + 'view/viewcontent.json.php', params, 'POST', showPreview.bind(null, 'big'));
        }
    });

    // outcome
    $("#edittask_outcome").change(function () {
        setButtonAccessibility($("#outcome_button"), $("#edittask_outcome"));
    });

    // main
    setButtonAccessibility($("#outcome_button"), $("#edittask_outcome"));

    if ($("#edittask_outcome_container .picker").length) {
        $("#outcome_button").removeClass('d-none').insertAfter("#edittask_outcome_container .picker");
    }
    else {
        $("#outcome_button").addClass('d-none');
    }

    // Hook up 'click to preview' links
    $("#outcome_button").click(function (event) {
        event.preventDefault();

        var outcome = getOutcomePortfolioTypeAndId($("#edittask_outcome").val());
        if (outcome.id) {
            var params = {
                'id' : outcome.id
            };

            var url = '';
            if (outcome.type === 'view') {
                url = 'view/viewcontent.json.php';
            }
            else if (outcome.type === 'collection') {
                url = 'artefact/plans/collection/viewcontent.json.php';
            }
            sendjsonrequest(config.wwwroot + url, params, 'POST', showPreview.bind(null, 'big'));
        }
    });

});

