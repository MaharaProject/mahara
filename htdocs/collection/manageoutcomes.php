<?php

/**
 * Provides support for Outcomes in Collections.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('PUBLIC_ACCESS', 1);
define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'collection');
define('SECTION_PAGE', 'outcomes');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(dirname(dirname(__FILE__)). '/group/outcomes.php');
require_once('collection.php');

$collectionid = param_integer('id');
$collection = new Collection($collectionid);

if (!$collection->get('outcomeportfolio')) {
    throw new AccessDeniedException();
}

// check if user admin
if (!($collection->get('group') && group_user_access($collection->get('group')) === 'admin')) {
    throw new AccessDeniedException();
}

// get outcomes from db
$outcomes = get_outcomes($collection->get('id'));

$outcomeforms = [];
if (!$outcomes) {
    $name           = 'outcome0';
    $title          = get_string('outcometitle', 'collection', 1);
    $outcomeforms[]['html'] = create_outcome_form($name, $title, $collection);
}
else {
    $formcount = count($outcomes);
    for ($i = 0; $i < $formcount; $i++) {
        $name           = 'outcome' . $i;
        $title          = get_string('outcometitle', 'collection', $i + 1);
        $outcomeforms[$i]['html'] = create_outcome_form($name, $title, $collection, false, $outcomes[$i]);
        $outcomeforms[$i]['shorttitle'] = $outcomes[$i]->short_title;
    }
}

$strings= get_outcome_lang_strings();

$js = <<< EOJS
{$strings}
jQuery(function($) {

    /**
    * Prevent short title input element to submit form on 'enter' krey pressed
    */
    function removeSubmitOnEnter() {
        $("#outcome_forms input[name='short_title']").on('keypress', function(e){
            if (e.keyCode == 13) {
                e.preventDefault();
                e.stopPropagation();
            }
        })
    }

    /**
     * Set maxlength attribute on textareas
     */
    function setMaxlength() {
        $("textarea").attr('maxlength', 255);
    }

    /*
     * Validate short_title field not empty
     */
    function validateForms() {
        var errors = false;
        // Clear previous error messages
        $(".errmsg").remove();
        // Add new messages if needed
        $(`input[name="short_title"]`).each((i, e)=>{
            if (!$(e).val()) {
                var parent = $(e).parent();
                if (!parent.find(".errmsg").length) {
                    const requiredmsg = get_string('rule.required.required', 'pieforms');
                    parent.append(`<div class="errmsg"><span id="short_title_` + i + `_error">` + requiredmsg + `</span></div>`);
                }
                errors = true;
            }
        });

        // Scroll to first error
        if (errors) {
            var errmsg = get_string("errorprocessingform", "mahara");
            $("#messages").replaceWith('<div id="messages"><div class="alert alert-danger">'
            + errmsg +
            '</div></div>');
            $($(".errmsg")[0]).parent()[0].scrollIntoView();
        }
        return errors;
    }

    /*
     * Saves all outcome forms to the DB
     */
    function saveOutcomesForm() {
        const forms = $("form.outcomeform");
        const errors = validateForms();

        if (!errors) {
            // outcome loop
            var data = [];
            forms.map((i,form) => {
                // Get values from form
                const id =  $(form).find(`input[name="id"]`).val();
                const short_title = $(form).find(`input[name="short_title"]`).val();
                const full_title = $(form).find(`textarea[name="full_title"]`).val();
                const outcome_type = $(form).find(`select[name="outcome_type"]`).val();

                // Add to network request params
                data.push({
                    "short_title": short_title,
                    "full_title": full_title,
                    "outcome_type": outcome_type || '',
                    "id": id,
                });
            });
            sendjsonrequest(config.wwwroot + 'json/outcomes.php', {
                collection: {$collection->get('id')},
                outcomes: data,
            },
            "POST",
            function(data) {
                formchangemanager.reset();
                const id = new URL(location.href).searchParams.get('id');
                window.location.href= config.wwwroot + 'collection/outcomesoverview.php?id=' + id;
                if (data) {
                    $("#messages div").html(data);
                }
            },
            function(error) {
                $("#messages")[0].scrollIntoView(true);
            }
            );
        }
    }

    /**
     * Gets a new outcome form to be added to the DOM
     */
    function addOutcomeForm(e) {
        console.log("addOutcomeForm");
        e.preventDefault();
        var formscount = $("form.outcomeform").length;
        sendjsonrequest(config.wwwroot + 'json/outcomesnewform.php', {
            collection: {$collection->get('id')},
            group: {$collection->get('group')},
            formscount: formscount
        },
        "POST",
        function (data) {
            if (data.html) {
                $("#outcome_forms").append(data.html);
                $("#outcome_forms .requiredmarkerdesc").remove();
                removeSubmitOnEnter();
                setMaxlength();
                $("#outcome_forms .delete-outcome a").last().on("click", removeForms);
                $("#outcome_buttons_container")[0].scrollIntoView({block: "end"});
            }
        });
    }

    /**
     * Update the outcome numbering
     */
    function updateoutcomenumbering() {
        // reset form outcome numbers on DOM
        $("#outcome_forms div h3").map((i,heading) => {
            const id = i+1;
            const title = get_string('outcome') + ' ' + id;
            $(heading).html(title);
        });
    }

    /*
    * Deletes outcome form in DOM and outcome on DB
    */
    function deleteOutcome(e) {
        e.preventDefault();
        const deleteform = $(e.target).closest('div');
        const outcomeform = deleteform.next();
        // get hidden db id
        const dynamicindex = parseInt(outcomeform.attr('id').replace(/[^0-9]/g,'')) + 1;
        const id = outcomeform.find(`input[name="id"]`).val();
        if (confirm(get_string('confirmdeleteoutcomedb'))) {
            sendjsonrequest('deleteoutcome.json.php', {
                collection: {$collection->get('id')},
                outcomeid:id,
                dynamicindex
            },
            'POST',
            (data) => {
                // remove form element from DOM on successful deletion
                deleteform.remove();
                outcomeform.remove();
                updateoutcomenumbering();
            });
        }
    }

    /*
     * Deletes outcome form in DOM only
     * Used on forms added by ajax, there is no outcome on DB for them
     */
    function removeForms(e) {
        e.preventDefault();
        if (confirm(get_string('confirmdeleteoutcome', 'collection'))) {
            const deleteform = $(e.target).closest('div');
            const outcomeform = deleteform.next();
            deleteform.remove();
            outcomeform.remove();
            updateoutcomenumbering();
        }
    }

    $("#submit_save").on("click", saveOutcomesForm);
    $("#add_outcome").on("click",   addOutcomeForm);
    $(".delete-outcome a").on("click", deleteOutcome);
    $("#outcome_forms .requiredmarkerdesc").remove();
    removeSubmitOnEnter();
    // Somehow maxlength is not being set on the textareas
    // need to add it using jquery
    setMaxlength();
});
EOJS;
$cancelredirecturl = get_config('wwwroot') . 'view/groupviews.php?group=' . $collection->get('group');
$smarty = smarty();

$smarty->assign('outcomeforms', $outcomeforms);
$smarty->assign('cancelredirecturl', $cancelredirecturl);
$smarty->assign('PAGETITLE', get_string('manageoutcomes', 'collection'));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('collection/manageoutcomes.tpl');

/**
 * Get all strings that will be needed on js script
 */
function get_outcome_lang_strings() {
    $jsstrings = array(
        array('confirmdeleteoutcomedb', 'collection'),
        array('confirmdeleteoutcome', 'collection'),
        array('rule.required.required', 'pieforms'),
        array("errorprocessingform", "mahara"),
        array('outcome', 'collection')
    );
    $strings = '';
    foreach ($jsstrings as $stringdata) {
        list($tag, $section) = $stringdata;
        $strings .= '    strings["' . $tag . '"] = ' . json_encode(get_raw_string($tag, $section)) . ";\n";
    }
    return $strings;
}