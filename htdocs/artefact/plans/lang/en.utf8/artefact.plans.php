<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Catalyst IT Ltd, Alexander Del Ponte
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/* Plans */
$string['pluginname'] = 'Plans';
$string['groupplans'] = 'Plans';

$string['canteditdontownplan'] = 'You cannot edit this plan because you do not own it.';
$string['description'] = 'Description';
$string['deleteplanconfirm'] = 'Are you sure you wish to delete this plan? Deleting this plan will also remove any tasks it contains.';
$string['deleteplan'] = 'Delete plan';
$string['deletethisplan'] = 'Delete plan: \'%s\'';
$string['editplan'] = 'Edit plan';
$string['editingplan'] = 'Editing plan';
$string['managetasks'] = 'Manage tasks';
$string['managetasksspecific'] = 'Manage tasks in "%s"';
$string['newplan'] = 'New plan';
$string['noplansaddone'] = 'No plans yet. %sAdd one%s.';
$string['noplans'] = 'No plans to display';
$string['Plan'] = 'Plan';
$string['Plans'] = 'Plans';
$string['plan'] = 'plan';
$string['plans'] = 'plans';
$string['plandeletedsuccessfully'] = 'Plan deleted successfully.';
$string['plannotdeletedsuccessfully'] = 'There was an error deleting this plan.';
$string['plannotsavedsuccessfully'] = 'There was an error submitting this form. Please check the marked fields and try again.';
$string['plansavedsuccessfully'] = 'Plan saved successfully.';
$string['planstasks1'] = 'Plan %s \'%s\' tasks';
$string['templateplan'] = 'template';
$string['planstasksdescription'] = 'Add tasks below or use the "%s" button to begin building your plan.';
$string['saveplan'] = 'Save plan';
$string['title'] = 'Title';
$string['titledesc'] = 'The title will be used to display each task in the plans blocktype.';

/* Tasks */
$string['addtask'] = 'Add task';
$string['addtaskspecific'] = 'Add task to "%s"';
$string['alltasks'] = 'All tasks';
$string['canteditdontowntask'] = 'You cannot edit this task because you do not own it.';
$string['completed'] = 'Completed';
$string['incomplete'] = 'Incomplete';
$string['overdue'] = 'Overdue';
$string['completiondate'] = 'Completion date';
$string['completiondatedescription'] = 'Use the format %s. For a template task, the default completion date will be taken from the group\'s editability end date.';
$string['completeddesc'] = 'Mark your task completed.';
$string['deletetaskconfirm'] = 'Are you sure you wish to delete this task?';
$string['deletetask'] = 'Delete task';
$string['deletethistask'] = 'Delete task: \'%s\'';
$string['edittask'] = 'Edit task';
$string['editingtask'] = 'Editing task';
$string['editthistask'] = 'Edit task: \'%s\'';
$string['mytasks'] = 'My tasks';
$string['newtask'] = 'New task';
$string['notasks'] = 'No tasks to display.';
$string['notasksaddone'] = 'No tasks yet. %sAdd one%s.';
$string['savetask'] = 'Save task';

$string['Task'] = 'Task';
$string['Tasks'] = 'Tasks';
// For counting tasks results, e.g. tasklist
$string['task'] = 'task';
$string['tasks'] = 'tasks';

$string['Grouptasks'] = 'Group tasks';
$string['taskdeletedsuccessfully'] = 'Task deleted successfully.';
$string['tasksavedsuccessfully'] = 'Task saved successfully.';
$string['selectionstatechangedpagereload'] = 'The selection status changed in the meantime. Reload this page to show the current status.';
$string['completedstatechangedpagereload'] = 'The completion status changed in the meantime. Reload this page to show the current status.';
$string['noselectiontask'] = 'This is not an assignment task.';
$string['ownerfieldsnotset'] = 'The owner fields are not set.';
$string['unsupportedportfoliotype'] = 'This is an unsupported portfolio type.';
$string['viewandoutputcontainsameelement'] = 'Task and assignment portfolio cannot contain the same page or page from within a collection.';
$string['grouptaskselected'] = 'The plan and selected tasks were transferred to your personal portfolio area. If applicable, assignment portfolios were copied as well.';
$string['grouptaskunselected'] = 'The plan and / or selected tasks were removed from your personal portfolio area. If applicable, any unedited assignment portfolios were removed as well.';
$string['unselecttaskconfirm'] = 'Do you really want to remove this task from your plan?';
$string['wrongfunctionrole'] = 'You cannot complete this action because your role does not allow it.';

$string['ntasks'] = [
        '1 task',
        '%s tasks',
];
$string['duplicatedplan'] = 'Duplicated plan';
$string['existingplans'] = 'Existing plans';
$string['duplicatedtask'] = 'Duplicated task';
$string['existingtasks'] = 'Existing tasks';

$string['progress_plan'] = [
    'Add a plan',
    'Add %s plans',
];
$string['progress_task'] = [
    'Add a task to a plan',
    'Add %s tasks to a plan',
];
$string['showassignedview'] = 'Task preview';
$string['showassignedoutcome'] = 'Portfolio preview';
$string['editassignedoutcome'] = 'Open portfolio';
$string['submitassignedoutcome'] = 'Submit for assessment';
$string['outcomeiscurrentlysubmitted'] = 'This portfolio is currently submitted for assessment.';

// New plan fields
$string['template'] = 'Template';
$string['templatedescription'] = 'Use this plan as a template for the creation of group plans.';

// New task fields
$string['startdate'] = 'Start date';
$string['startdatedescription'] = 'Use the format %s. For a template task, the default start date will be taken from the group\'s editability start date.';
$string['reminder'] = 'Reminder';
$string['reminderdescription'] = 'Send a reminder notification relative to the completion date.';
$string['taskview'] = 'Task page';
$string['taskviewdescription'] = 'Connect a page with a detailed task description to this task.';
$string['outcome'] = 'Assignment portfolio';
$string['outcomedescription'] = 'Connect a portfolio to complete this task. Note: This portfolio can only be used in one task.';


// Selectionplan field
$string['selectionplan'] = 'Assignment tasks';
$string['selectionplandescription'] = 'Group members can select the tasks of this plan to complete assignments after a plan was created in a group based on this template plan. This option is only available when a template is created.';

$string['none'] = 'None';
$string['startdatemustbebeforecompletiondate'] = 'The start date has to be before the completion date.';
$string['completiondatemustbeinfuture'] = 'The completion date must be in the future.';
$string['completiondatemustbesetforreminder'] = 'You need to enter a completion date to set the reminder.';

// New plan from template modal form
$string['choosetemplate'] = 'Choose template';
$string['notemplate'] = 'No template';
$string['close'] = 'Close';
$string['fromtemplate'] = 'From template';
$string['taskviewsfortemplateplan'] = 'Pages containing details of the tasks for the plan "%s"';
$string['templatedialogdescription'] = 'Select a template plan from your personal plans as basis for this group plan. All associated tasks, task pages, and assignment portfolios are copied into this group automatically.';

$string['targetgroupplancollectiontitleprefix'] = 'Plan tasks: ';
// check_reminders message
$string['remindersubject'] = 'Reminder to complete tasks in a plan';
$string['URL'] = 'Link to task';
$string['reminderinfo'] = 'Info';
$string['reminderinfonearlydue'] = [
    0 => 'One day to go',
    1 => '%s days to go'
];
$string['reminderinfodue'] = [
    0 => 'One day overdue',
    1 => '%s days overdue'
];
$string['emailfooter'] = 'This is an auto-generated notification from %s.';

$string['youhaveremindertasksmessagetext'] = "Hello %s,

We would like to remind you to complete the following plan tasks:

%s

Regards,
The %s team
";

$string['visitplans'] = 'Reminder to complete tasks';
