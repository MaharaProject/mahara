/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
function rewriteTaskTitles(blockid, planid) {
    jQuery('tasklist_' + blockid + '_plan' + planid + ' a.task-title').each(function() {
        jQuery(this).off();
        jQuery(this).on('click', function(e) {
            e.preventDefault();
            var description = jQuery(this).parent().find('div.task-desc');
            description.toggleClass('d-none');
        });
    });
}
function TaskPager(blockid, planid) {
    var self = this;
    paginatorProxy.addObserver(self);
    jQuery(self).on('pagechanged', rewriteTaskTitles.bind(null, blockid, planid));
}

function changeCheckBox(taskid, state) {
    if (state == 1) {

        $('.task' + taskid).next('span').removeClass('text-danger').addClass('text-success');
        $('.task' + taskid).removeClass('text-midtone icon-regular icon-square icon-times text-danger').addClass('icon-regular icon-check-square text-success');
    }
    else if (state == -1) {
        $('.task' + taskid).next('span').removeClass('text-success').addClass('text-danger');
        $('.task' + taskid).removeClass('icon-check-square icon-regular icon-square text-success').addClass('icon-times text-danger');
    }
    else {
        $('.task' + taskid).next('span').removeClass('text-success text-danger');
        $('.task' + taskid).removeClass('icon-check-square icon-times text-success text-danger').addClass('icon-regular icon-square text-midtone');
    }
}

function saveCheckBoxChange(taskid) {
    var params = {};
    params.taskid = taskid;
    sendjsonrequest(config.wwwroot + 'artefact/plans/block/checktask.json.php', params, 'POST', function(data) {
        if (data.data) {
            changeCheckBox(data.data.artefact, data.data.state);
        }
    });
}

function enableCheckBoxes() {
    jQuery('.plan-task-icon').off('click');
    jQuery('.plan-task-icon').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        var taskid = jQuery(e.target).data('task');
        saveCheckBoxChange(taskid);
    });
}

// Wire up the checkboxes
jQuery(document).on('pageupdated', function(e, data) {
    // When using pagination
    enableCheckBoxes();
});

jQuery(window).on('blocksloaded', {}, function() {
"use strict";
    // after all blocks are loaded
    enableCheckBoxes();
});

findButtonDataUrls();
