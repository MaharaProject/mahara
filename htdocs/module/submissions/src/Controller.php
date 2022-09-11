<?php
/**
 *
 * @package    Mahara
 * @subpackage module-submissions
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information, please see the README file distributed with this software.
 *
 */

namespace Submissions;

use AccessDeniedException;
use Submissions\Models\Evaluation;
use Submissions\Models\Submission;
use Submissions\Repository\EvaluationRepository;
use Submissions\Repository\SubmissionRepository;
use Submissions\Tools\SubmissionTools;
use PluginModuleSubmissions;

abstract class Context {
    const ContentUser = 0;
    const GroupAssessor = 1;
    const GroupSubmitter = 2;
}

abstract class Data {
    const Available = 1;
}

class Controller {
    private $requestMethod;
    private $command;

    private $user;
    private $group;

    private $context;

    private $settings;

    /**
     * @return bool
     */
    private function isInGroupContext() {
        return !is_null($this->group);
    }

    /**
     * Controller constructor.
     * @param string $command
     * @throws \AccessDeniedException
     * @throws \ParameterException
     */
    function __construct($command = 'index') {
        global $USER;

        $this->checkPlugInActivation();

        $this->initSettings();

        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->command = $command;

        $this->user = $USER;

        $groupId = param_integer('group', 0);
        if ($groupId) {
            $this->group = get_group_by_id($groupId);
        }

        if ($this->group) {
            // User is group assessor (role admin or tutor)
            if (group_user_can_assess_submitted_views($this->group->id, $this->user->get('id'))) {
                $this->context = Context::GroupAssessor;
            }
            else {
                // User is regular group member (role member)
                if (!group_user_access($this->group->id, $this->user->get('id'))) {
                    throw new \AccessDeniedException(get_string('notamember', 'group'));
                }
                // We use Context::ContentUser here until Context::GroupSubmitter functionality is implemented
                $this->context = Context::ContentUser; // Context::GroupSubmitter;
                $this->group = null;
            }
        }
        else {
            $this->context = Context::ContentUser;
        }

        $this->defineMaharaConstants($command);
    }

    /**
     * @throws AccessDeniedException
     */
    private function checkPlugInActivation() {
        safe_require('module', 'submissions');
        if (!PluginModuleSubmissions::is_active()) {
            throw new \AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('pluginname','module.submissions')));
        }
    }

    private function initSettings() {
        $this->settings = new \stdClass();
        $this->settings->showPortfolioButtons = (bool)get_config_plugin('module', 'submissions', 'showportfoliobuttons');
        $this->settings->showNameAsLastnameFirstname = (bool)get_config_plugin('module', 'submissions', 'shownameaslastnamefirstname');
        $this->settings->retentionPeriod = get_config_plugin('module', 'submissions', 'retentionperiod');
    }

    /**
     * @param string $command
     */
    private function defineMaharaConstants($command = 'index') {
        switch ($command) {
            case 'index':
                if ($this->isInGroupContext()) {
                    define('TITLE', get_string("submissionstitlegroup", "module.submissions", $this->group->name));
                    define('SUBSECTIONHEADING', hsc(get_string("Submissions", "module.submissions")));
                    define('GROUP', $this->group->id);
                    define('MENUITEM', 'engage/index');
                    define('MENUITEM_SUBPAGE', 'groupsubmissions');
                    define('SECTION_PAGE', 'groupindex');
                }
                else {
                    define('TITLE', get_string("Submissions", "module.submissions"));
                    define('MENUITEM', 'share/submissions');
                    define('SECTION_PAGE', 'index');
                }
                break;
            case 'index.json':
                // No additional defined constants
        }
    }

    /**
     * @param Submission $submission
     * @throws \SystemException
     */
    private function checkUserRightsAndSynchronizedSubmissionStatusForEditing(Submission $submission) {
        if (!group_user_can_assess_submitted_views($submission->get('groupId'), $this->user->get('id'))) {
            throw new \SystemException(get_string('notallowedtoassesssubmission','module.submissions'));
        }
        // If Submission has status Incomplete, we don't have to synchronize 'cause the portfolio can't be released with
        // this status and so we don't have to care about a double submission
        if (!$submission->hasStatus(Submission::StatusIncomplete)) {
            try {
                SubmissionTools::synchronizeSubmissionStatusWithPortfolioElement($submission);
            }
            catch (\Exception $e) {
                throw new \SystemException($e->getMessage() . get_string('dependingonstatusmayeditupdatedfields','module.submissions'), Data::Available);
            }
        }
        if (!$submission->isEditable()) {
            throw new \SystemException(get_string('submissionreadonlynotupdated','module.submissions'), Data::Available);
        }
    }

    // Command: Index

    /**
     * @param array $options
     * @param array $js
     */
    private function displayIndex($options = [], $js = []) {
        $headers[] = '<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'js/DataTables/datatables.min.css">';
        $piertables_css = file_exists(get_config('wwwroot') . 'local/theme/piertables.css') ? get_config('wwwroot') . 'local/theme/piertables.css' : get_config('wwwroot') . 'module/submissions/js/piertables.css';
        $headers[] = '<link rel="stylesheet" type="text/css" href="' . $piertables_css . '">';
        $pagestrings = array('module.submissions' => array('quickfilter', 'quickfiltertooltip'));
        $smarty = smarty($js, $headers, $pagestrings);
        setpageicon($smarty, 'icon-hand-holding');
        $smarty->assign('language', strstr(current_language(), '.', true));
        $smarty->assign('options', $options);
        $smarty->assign('SIDEBLOCKS', []);
        $smarty->assign('SIDEBARS', []);
        $smarty->assign('tooltip', array('question' => get_string('tooltip_question', 'module.submissions'),
                                         'refresh'  => get_string('tooltip_refresh', 'module.submissions'),
                                         'remove'   => get_string('tooltip_remove', 'module.submissions'),
                                         'success'  => get_string('tooltip_success', 'module.submissions')));
        $smarty->display('module:submissions:index.tpl');
    }

    /**
     * @param $options
     */
    private function setRatingIconOptions(&$options) {
        $ratingIcon = 'star';
        if (get_config_plugin('artefact', 'comment', 'ratingicon')) {
            $ratingIcon =  get_config_plugin('artefact', 'comment', 'ratingicon');
        }
        $options['ratingIcon'] = $ratingIcon;
        $ratingIconColour = '#DBB80E';
        if (get_config_plugin('artefact', 'comment', 'ratingcolour')) {
            $ratingIconColour =  get_config_plugin('artefact', 'comment', 'ratingcolour');
        }
        $options['ratingIconColour'] = $ratingIconColour;
    }

    /**
     * @param \stdClass $record
     * @return \stdClass
     * @throws \CollectionNotFoundException
     * @throws \MaharaException
     * @throws \SQLException
     * @throws \SystemException
     */
    private function createDataTableRowFromSubmissionAndEvaluationRecord(\stdClass $record) {
        static $cachedGroupEvaluatorSelections = [];

        $record->portfolioElementTitleHtml = SubmissionTools::createPortfolioElementTitleHtmlForTableBySubmissionRecord($record, $this->settings->showPortfolioButtons);
        $record->isEditable = (bool)$record->liveUserIsAssessor && (int)$record->status === Submission::StatusSubmitted;
        $record->isFixable = (bool)$record->liveUserIsAssessor && (int)$record->status === Submission::StatusIncomplete;

        $record->ownerName = (bool)$record->ownerDeleted ? get_string('deleteduser1') : SubmissionTools::createOwnerName($record, $this->settings->showNameAsLastnameFirstname);
        if ($record->evaluatorId) {
            $record->evaluatorName = (bool)$record->evaluatorDeleted ? get_string('deleteduser1') : SubmissionTools::createEvaluatorName($record, $this->settings->showNameAsLastnameFirstname);
        }
        else {
            $record->evaluatorName = null;
        }

        $record->userElementTitleHtml = (bool)$record->ownerDeleted ? $record->ownerName : SubmissionTools::createHtmlLinkWithTitle(get_config('wwwroot') . 'user/view.php?id=' . $record->ownerId, $record->ownerName, '', '');
        if ($record->evaluatorId) {
            $record->evaluatorElementTitleHtml = SubmissionTools::createHtmlLinkWithTitle(get_config('wwwroot') . 'user/view.php?id=' . $record->evaluatorId, $record->evaluatorName, '', '');
        }
        if ($record->groupId) {
            $record->portfolioElementGroupHtml = SubmissionTools::createHtmlLinkWithTitle(get_config('wwwroot') . 'group/view.php?id=' . $record->groupId, $record->groupName, '', '');
        }

        // If submission is not released yet
        if ((bool)$record->liveUserIsAssessor && ((int)$record->status === Submission::StatusSubmitted || (int)$record->status === Submission::StatusReleasing)) {
            // If submission has not been released yet and so the comment field in the evaluation table is empty, get last comment of currently set evaluator or live user
            $comment = SubmissionRepository::findLatestEvaluatorComment($record->portfolioElementType, $record->portfolioElementId, $record->evaluatorId, $record->submissionDate);
            if (!is_null($comment)) {
                $record->feedback = $comment->get('description');
                $record->rating = $comment->get('rating');
            }

            // Because we now use the new Mahara collection preview functionality, we provide the portfolio view id(s)
            // in the row data to the frontend
            if ($record->portfolioElementType === 'collection') {
                $collection = new \Collection($record->portfolioElementId);

                $record->editOptions['viewIds'] = $collection->get_viewids();
            } else {
                $record->editOptions['viewIds'] = [$record->portfolioElementId];
            }
        }

        // In context ContentUser evaluator select options are row specific because data possibly is collected from multiple groups
        if ($this->context === Context::ContentUser && $record->isEditable) {
            if (array_key_exists($record->groupId, $cachedGroupEvaluatorSelections)) {
                $evaluatorSelection = $cachedGroupEvaluatorSelections[$record->groupId];
            }
            else {
                $evaluatorSelection = SubmissionTools::createAdminTutorValueTextArrayForPiertableRowSelectionBoxByGroupId($record->groupId, $this->settings->showNameAsLastnameFirstname);
                $cachedGroupEvaluatorSelections[$record->groupId] = $evaluatorSelection;
            }
            $record->editOptions['evaluatorName']['arrayValueText'] = $evaluatorSelection;
        }
        $record->submissionDateFormat = format_date(strtotime($record->submissionDate), 'strftimedate');
        return $record;
    }

    /**
     * @param Submission $submission
     * @param Evaluation $evaluation
     * @throws \AuthUnknownUserException
     * @throws \ParameterException
     * @throws \SQLException
     */
    private function setValuesForSubmissionAndAssignedEvaluationAndCommit(Submission $submission, Evaluation $evaluation) {

        // Only on status Submitted, the fields are editable
        switch (true) {
            case $submission->hasStatus(Submission::StatusSubmitted):
                $evaluatorId = SubmissionTools::param_integer_or_null('evaluatorId');
                if ($evaluation->get('evaluatorId') != $evaluatorId) {
                    if ($evaluatorId) {
                        $evaluator = new \User();
                        $evaluator->find_by_id(param_integer('evaluatorId'));
                    }
                    $evaluation->set('evaluatorId', $evaluatorId);
                }
            case $submission->hasStatus(Submission::StatusIncomplete):
                $evaluation->set('success', SubmissionTools::param_integer_or_null('success'));
        }
        $submission->commit();
        $evaluation->commit();

        return;
    }

    // Handler

    /**
     * @throws AccessDeniedException
     * @throws \AuthUnknownUserException
     * @throws \CollectionNotFoundException
     * @throws \SQLException
     * @throws \SystemException
     */
    public function handleRequest() {
        switch ($this->command) {

            case 'index':
                switch ($this->context) {
                    case Context::GroupAssessor:
                        $options['evaluatorselection'] = SubmissionTools::createAdminTutorUserIdUserNameArrayForPiertableSelectionBoxByGroupId($this->group->id, $this->settings->showNameAsLastnameFirstname);
                        $options['evaluatorfilterselection'] = $options['evaluatorselection'];
                        $options['groupid'] = $this->group->id;
                        break;
                    case Context::ContentUser:
                        // We create only the evaluatorfilterselection because here the evaluatorselection is row specific and so it's created in function createDataTableRowFromSubmissionAndEvaluationRecord below
                        $options['evaluatorfilterselection'] = SubmissionTools::createAdminTutorUserIdUserNameArrayForPiertableQuickfilterByLiveUser($this->settings->showNameAsLastnameFirstname);
                        break;
                }
                $this->setRatingIconOptions($options);
                $piertables_js = file_exists(get_config('wwwroot') . 'local/theme/piertables.js') ? get_config('wwwroot') . 'local/theme/piertables.js' : get_config('wwwroot') . 'module/submissions/js/piertables.js';
                $js = [
                    'js/preview.js',
                    //'js/export.js',
                    'js/gridstack/gridstack_modules/gridstack-h5.js',
                    'js/gridlayout.js',
                    'js/collection-navigation.js',
                    'js/DataTables/datatables.min.js',
                    $piertables_js,
                    'module/submissions/js/UrlFlashback.js'
                ];
                $this->displayIndex($options, $js);
                break;

            case 'index.json':
                if ($this->requestMethod == 'GET') {
                    try {
                        $reply['data'] = [];

                        switch ($this->context) {
                            case Context::ContentUser:
                                $submissionRecords = SubmissionRepository::findCompleteSubmissionAndEvaluationRecordsbyLiveUserAsArray();
                                break;
                            case Context::GroupAssessor:
                                $submissionRecords = SubmissionRepository::findCompleteSubmissionAndEvaluationRecordsByGroupAsArray($this->group);
                                break;
                            //case Context::GroupSubmitter:
                            //    $submissions = SubmissionRepository::findCompleteSubmissionAndEvaluationRecordsBySubmitterAsArray($this->group, $this->user);
                            //    break;
                            default:
                                throw new \AccessDeniedException();
                        }

                        foreach ($submissionRecords as $submissionRecord) {
                            $reply['data'][] = $this->createDataTableRowFromSubmissionAndEvaluationRecord($submissionRecord);
                        }
                        json_reply(false, $reply);
                    }
                    catch (\Exception $e) {
                        $reply['message'] = $e->getMessage();
                        json_reply(true, $reply);
                    }
                }

                if ($this->requestMethod == 'POST') {
                    $submission = null;
                    try {
                        /** @var Submission $submission */
                        /** @var Evaluation $evaluation */
                        list($submission, $evaluation) = SubmissionRepository::findSubmissionAndAssignedEvaluationBySubmissionId(param_integer('submissionId'));
                        $this->checkUserRightsAndSynchronizedSubmissionStatusForEditing($submission);

                        db_begin();
                        $this->setValuesForSubmissionAndAssignedEvaluationAndCommit($submission, $evaluation);
                        db_commit();
                        // Read complete submission and evaluation from db so that for correct filtering this table row is returned
                        // identically in it's var type structure with the already displayed ones in the frontend
                        // (After reading from db using function get_record all fields are var type string)
                        $submissionRecord = SubmissionRepository::findCompleteSubmissionAndEvaluationRecordBySubmissionId($submission->get('id'));
                        $reply['data'] = $this->createDataTableRowFromSubmissionAndEvaluationRecord($submissionRecord);
                    }
                    catch (\Exception $e) {
                        db_rollback();
                        $reply['message'] = $e->getMessage();
                        if ($e->getCode() === Data::Available) {
                            $submissionRecord = SubmissionRepository::findCompleteSubmissionAndEvaluationRecordBySubmissionId($submission->get('id'));
                            $reply['data'] = $this->createDataTableRowFromSubmissionAndEvaluationRecord($submissionRecord);
                        }
                        json_reply(true, $reply);
                    }
                    json_reply(false, $reply);
                }
                break;

            case 'release.json':
                if ($this->requestMethod == 'POST') {
                    $messageAction = get_string('actionreleased','module.submissions');
                    try {
                        $submission = null;

                        /** @var Submission $submission */
                        /** @var Evaluation $evaluation */
                        list($submission, $evaluation) = SubmissionRepository::findSubmissionAndAssignedEvaluationBySubmissionId(param_integer('submissionId'));
                        $this->checkUserRightsAndSynchronizedSubmissionStatusForEditing($submission);

                        // Status Submitted is the only one on which we initiate a submission
                        if ($submission->hasStatus(Submission::StatusSubmitted)) {
                            SubmissionTools::standardReleaseSubmission($submission, $this->user);
                        }
                        // Having status Incomplete some fields are still editable - Until now we only have Evaluation result editable
                        else if ($submission->hasStatus(Submission::StatusIncomplete)) {
                                $submission->set('status', SubmissionTools::concludeSubmissionStatusFromEvaluation($evaluation));
                                if ($submission->hasStatus(Submission::StatusIncomplete)) {
                                    throw new \SystemException(get_string('submissionnotfixedmissingevaluationresult','module.submissions'), Data::Available);
                                }
                            $messageAction = get_string('actionfixed','module.submissions');
                            db_begin();
                            $this->setValuesForSubmissionAndAssignedEvaluationAndCommit($submission, $evaluation);

                            if ($submission->hasStatus(Submission::StatusRevision)) {
                                SubmissionTools::removeCompletedFlagOfAssignedTaskBySubmissionAndCommit($submission);
                            }
                            db_commit();
                        }
                        else {
                            throw new \SystemException(get_string('submissionnotreleasedorfixedwrongsubmissionstatus','module.submissions'), Data::Available);
                        }
                        $submissionRecord = SubmissionRepository::findCompleteSubmissionAndEvaluationRecordBySubmissionId($submission->get('id'));
                        $reply['data'] = $this->createDataTableRowFromSubmissionAndEvaluationRecord($submissionRecord);
                        $reply['message'] = get_string('submissionreleased','module.submissions',
                            $submissionRecord->portfolioElementTitle,
                            $submissionRecord->ownerName,
                            $messageAction
                        );
                    }
                    catch (\Exception $e) {
                        db_rollback();
                        $reply['message'] = $e->getMessage();
                        if ($e->getCode() === Data::Available) {
                            $submissionRecord = SubmissionRepository::findCompleteSubmissionAndEvaluationRecordBySubmissionId($submission->get('id'));
                            $reply['data'] = $this->createDataTableRowFromSubmissionAndEvaluationRecord($submissionRecord);
                        }
                        json_reply(true, $reply);
                    }
                    json_reply(false, $reply);
                }
                break;

            default:
                throw new \SystemException(get_string('missingcontrollerhandler','module.submissions'));
        }
    }
}
