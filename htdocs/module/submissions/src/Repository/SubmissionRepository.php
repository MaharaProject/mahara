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

namespace Submissions\Repository;


use Submissions\Models\Evaluation;
use Submissions\Models\Submission;
use Submissions\Tools\SubmissionTools;

class SubmissionRepository {

    /**
     * @return array
     * @throws \SQLException
     */
    public static function findCompleteSubmissionAndEvaluationRecordsbyLiveUserAsArray() {
        $sql = Submission::getSqlSelectForCompleteSubmissionAndEvaluationDataForSubmissionTable() . ' ' .
            Submission::getSqlFromForCompleteSubmissionAndEvaluationDataForSubmissionTable();
        $submissionRecordArray = get_records_sql_array($sql);

        if ($submissionRecordArray) {
            return $submissionRecordArray;
        }
        return [];

    }

    /**
     * @param \stdClass $group
     * @return array of \stdClass
     * @throws \SQLException
     */
    public static function findCompleteSubmissionAndEvaluationRecordsByGroupAsArray(\stdClass $group) {
        $sql = Submission::getSqlSelectForCompleteSubmissionAndEvaluationDataForSubmissionTable() . ' ' .
                Submission::getSqlFromForCompleteSubmissionAndEvaluationDataForSubmissionTable() . ' ' .
                'WHERE submission.groupid = ?';
        $submissionRecordArray = get_records_sql_array($sql, [$group->id]);

        if ($submissionRecordArray) {
            return $submissionRecordArray;
        }
        return [];
    }

    /**
     * @param int $submissionId
     * @return \stdClass|null
     * @throws \SQLException
     */
    public static function findCompleteSubmissionAndEvaluationRecordBySubmissionId(int $submissionId) {
        $sql = Submission::getSqlSelectForCompleteSubmissionAndEvaluationDataForSubmissionTable() . ' ' .
                Submission::getSqlFromForCompleteSubmissionAndEvaluationDataForSubmissionTable() . ' ' .
                'WHERE submission.id = ?';
        $submissionRecord = get_record_sql($sql, [$submissionId]);

        if ($submissionRecord) {
            return $submissionRecord;
        }
        return null;
    }

    /**
     * @param int $groupId
     * @param string $portfolioElementType
     * @param int $portfolioElementId
     * @return array of Submission
     * @throws \SystemException
     * @throws \SQLException
     */
    public static function findSubmissionsByGroupAndPortfolioElementOrderByIdDesc($groupId, $portfolioElementType, $portfolioElementId) {
        $sqlWhere = 'groupid = ? AND portfolioelementtype = ? AND portfolioelementid = ?';
        $sqlWhereValues = [$groupId, $portfolioElementType, $portfolioElementId];
        $submissionRecordArray = get_records_select_array('module_submissions', $sqlWhere, $sqlWhereValues, 'id DESC');

        if ($submissionRecordArray) {
            return SubmissionTools::createSubmissionArrayFromRecordArray($submissionRecordArray);
        }
        return [];
    }

    /**
     * @param \View|\Collection $portfolioElement
     * @return array|bool
     * @throws \SQLException
     * @throws \SystemException
     */
    public static function findCurrentSubmissionAndAssignedEvaluationByPortfolioElement($portfolioElement) {
        $sql = 'SELECT id FROM {module_submissions} WHERE groupid = ? AND portfolioelementtype = ? AND portfolioelementid = ? AND status = 1';

        $portfolioElementType = SubmissionTools::getPortfolioElementType($portfolioElement);
        $record = get_record_sql($sql, [$portfolioElement->get('submittedgroup'), $portfolioElementType, $portfolioElement->get('id')]);

        if ($record) {
            return self::findSubmissionAndAssignedEvaluationBySubmissionId($record->id);
        }
        return false;
    }

    /**
     * @param int $groupId
     * @param string $portfolioElementType
     * @param int $portfolioElementId
     * @return mixed|null
     * @throws \SQLException
     * @throws \SystemException
     */
    public static function findLatestReleasingSubmissionByGroupAndPortfolioElement(int $groupId, string $portfolioElementType, int $portfolioElementId) {
        $sourceSubmissions = self::findSubmissionsByGroupAndPortfolioElementOrderByIdDesc($groupId, $portfolioElementType, $portfolioElementId);
        $resultSubmissions = [];

        //Get last matching submission to avoid any difficulties when we may have more than one matching submission
        /** @var Submission $submission */
        foreach($sourceSubmissions as $submission) {
            if ($submission->isNotReleased()) {     // If status is Submitted (1) or Releasing (2) it is not released
                $resultSubmissions[] = $submission;
            }
        }

        if (!empty($resultSubmissions)) {
            return $resultSubmissions[0];
        }
        return null;
    }

    /**
     * @param Submission $submission
     * @return array of Submission
     * @throws \SystemException
     * @throws \SQLException
     */
    public static function findSubmissionsByGroupSubmitterAndPortfolioElementTaskOrderByIdDescBySubmission(Submission $submission) {
        $sqlWhereValues = [
            'groupid' => $submission->get('groupId'),
            'ownertype' => $submission->get('ownerType'),
            'ownerid' => $submission->get('ownerId'),
        ];
        // If we have a taskId check if an evaluated or incomplete submission already exists for this task and the submitter in this group
        // else check if an evaluated or incomplete submission already exists for this portfolio element and the submitter in this group
        if (!is_null($submission->get('taskId'))) {
            $sqlWhereValues['taskid'] = $submission->get('taskId');
        }
        else {
            $sqlWhereValues['portfolioelementtype'] = $submission->get('portfolioElementType');
            $sqlWhereValues['portfolioelementid'] = $submission->get('portfolioElementId');
        }
        $sqlWhere = SubmissionTools::createSQLEqualityWhereClauseFromFieldValueArray($sqlWhereValues);
        $submissionRecordArray = get_records_select_array('module_submissions', $sqlWhere, $sqlWhereValues, 'id DESC');

        if ($submissionRecordArray) {
            return SubmissionTools::createSubmissionArrayFromRecordArray($submissionRecordArray);
        }
        return [];
    }

    /**
     * @param Submission $newSubmission
     * @return null|Submission
     * @throws \SystemException
     * @throws \SQLException
     */
    public static function findNonRevisionSubmissionBySubmission(Submission $newSubmission) {
        $sourceSubmissions = self::findSubmissionsByGroupSubmitterAndPortfolioElementTaskOrderByIdDescBySubmission($newSubmission);

        /** @var Submission $submission */
        foreach($sourceSubmissions as $submission) {

            // Check if Portfolio element has already been evaluated in this group or
            // the submitter has already an evaluated submission for it's eventually assigned grouptask
            // We can do this by only checking if one result has not submission status revision
            if (!$submission->hasStatus(Submission::StatusRevision)) {
                return $submission;
            }
        }
        return null;
    }

    /**
     * @param int $submissionId
     * @return array
     * @throws \SystemException
     * @throws \SQLException
     */
    public static function findSubmissionAndAssignedEvaluationBySubmissionId(int $submissionId) {
        $submission = new Submission($submissionId);
        $evaluation = EvaluationRepository::findOrCreateEvaluationBySubmission($submission);

        return [$submission, $evaluation];
    }

    /**
     * @param Submission $submission
     * @return \ArtefactTypeTask|null
     */
    public static function findAssignedOwnerTaskForSubmission(Submission $submission) {
        $sql = 'SELECT id FROM {artefact} AS a INNER JOIN {artefact_plans_task} AS p ON a.id = p.artefact ' .
            'WHERE p.outcome = ? AND p.outcometype = ?';

        try {
            $record = get_record_sql($sql, [$submission->get('portfolioElementId'), $submission->get('portfolioElementType')]);
            if (!$record) {
                return null;
            }
            require_once(get_config('docroot') . 'artefact/lib.php');
            require_once(get_config('docroot') . 'artefact/plans/lib.php');

            return new \ArtefactTypeTask($record->id);
        }
        catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param Submission $submission
     * @return \ArtefactTypeTask|null
     */
    public static function findAssignedRootGroupTaskForSubmission(Submission $submission) {

        $ownerTask = self::findAssignedOwnerTaskForSubmission($submission);
        if (is_null($ownerTask)) {
            return null;
        }

        try {
            require_once(get_config('docroot') . 'artefact/lib.php');
            require_once(get_config('docroot') . 'artefact/plans/lib.php');

            $sourceTask = new \ArtefactTypeTask($ownerTask->get('id'));
            $rootGroupTaskId = $sourceTask->get('rootgrouptask');
            if (empty($rootGroupTaskId)) {
                return null;
            }

            $rootGroupTask = new \ArtefactTypeTask($rootGroupTaskId);
            return $rootGroupTask;
        }
        catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param int $groupId
     * @return array
     * @throws \AuthUnknownUserException
     */
    public static function findAdminsAndTutorsByGroupId(int $groupId) {
        $users = [];
        $userIds = group_get_member_ids($groupId, ['admin', 'tutor']);

        if ($userIds) {
            foreach ($userIds as $userId) {
                $user = new \User();
                $user->find_by_id($userId);
                $users[] = $user;
            }
        }
        return $users;
    }

    /**
     * @param int $groupId
     * @return array|mixed
     * @throws \SQLException
     */
    public static function findPlansAndTasksByGroupId(int $groupId) {
        $sql = 'SELECT art.id, art.title AS tasktitle, arp.title AS plantitle FROM {artefact} AS arp
                  INNER JOIN {artefact} AS art ON art.parent = arp.id
                  WHERE arp.artefacttype = ? AND arp.group = ? ORDER BY arp.title, art.title';
        $plansTasksArray = get_records_sql_array($sql, ['plan', $groupId]);

        if ($plansTasksArray) {
            return $plansTasksArray;
        }
        return [];
    }

    /**
     * Used on release_submission event to get the matching archive - (Currently there is no Mahara process_export_queue event)
     *
     * @param Submission $submission
     * @return null|\stdClass
     * @throws \SQLException
     */
    public static function findLatestMatchingArchiveIdBySubmission(Submission $submission) {

        $sql = 'SELECT archiveid FROM {export_archive} AS e INNER JOIN {archived_submissions} AS a ON e.id = a.archiveid ' .
            'WHERE a.group = ? AND e.usr = ? AND e.filetitle = ? ' .
            'ORDER BY e.id DESC';

        $archiveRecords = get_records_sql_array($sql, [$submission->get('groupId'), $submission->get('ownerId'), $submission->get('portfolioElementTitle')]);

        if ($archiveRecords) {
            return $archiveRecords[0]->archiveid;
        }

        // ToDo: The export queue has not been executed for a longer time and/or we have 2 submissions with the same title from the same user in the same group

        return null;
    }

    /**
     * @param string $portfolioElementType
     * @param int $portfolioElementId
     * @param int|null $evaluatorId
     * @param string $submissionDate
     * @return \ArtefactTypeComment|null
     * @throws \CollectionNotFoundException
     * @throws \SQLException
     * @throws \SystemException
     */
    public static function findLatestEvaluatorComment(string $portfolioElementType, int $portfolioElementId, $evaluatorId, string $submissionDate) {
        global $USER;

        switch ($portfolioElementType) {
            case 'view':
                break;
            case 'collection':
                require_once(get_config('libroot') . 'collection.php');

                $collection = new \Collection($portfolioElementId);
                $portfolioElementId = $collection->get_viewids()[0];
                break;
            default:
                throw new \SystemException(get_string('unsupportedportfoliotype'), 'module.submissions');
        }

        if (is_null($evaluatorId)) {
            $evaluatorId = $USER->get('id');
        }

        $sql = 'SELECT id FROM {artefact} AS a INNER JOIN {artefact_comment_comment} AS acc ON acc.artefact = a.id ' .
            'WHERE acc.onview = ? AND a.ctime > ? AND (a.author = ? OR a.owner = ?) ORDER BY acc.threadedposition DESC';
        $submissionDate = db_format_timestamp($submissionDate);
        $commentIdsArray = get_records_sql_array($sql, [$portfolioElementId, $submissionDate, $evaluatorId, $evaluatorId]);

        if (!empty($commentIdsArray)) {
            safe_require('artefact', 'comment');
            return new \ArtefactTypeComment($commentIdsArray[0]->id);
        }
        return null;
    }

    /**
     * @param Submission $submission
     * @param Evaluation $evaluation
     * @return \ArtefactTypeComment|null
     * @throws \SystemException
     */
    public static function findLatestEvaluatorCommentBySubmissionAndEvaluation(Submission $submission, Evaluation $evaluation) {
        return self::findLatestEvaluatorComment($submission->get('portfolioElementType'), $submission->get('portfolioElementId'), $evaluation->get('evaluatorId'), $submission->get('submissionDate'));
    }
}