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

namespace Submissions\Models;

use Submissions\Tools\SubmissionTools;

class Submission extends AbstractModel {
    const StatusIncomplete = 0;
    const StatusSubmitted = 1;
    const StatusReleasing = 2;
    const StatusRevision = 3;
    const StatusEvaluated = 4;

    protected $id;

    protected $groupId;
    protected $groupName;

    protected $ownerType;
    protected $ownerId;
    protected $ownerName;
    protected $ownerEmail;

    protected $portfolioElementType;
    protected $portfolioElementId;
    protected $portfolioElementTitle;

    protected $submissionDate;
    protected $status;    // Values are above defined status constants

    protected $taskId;
    protected $taskTitle;

    protected $exportArchiveId;

    protected static $dbTable = 'module_submissions';

    protected static $internalDbFieldsToProperties = [
        'id' => 'id',
        'groupid' => 'groupId',
        'ownertype' => 'ownerType',
        'ownerid' => 'ownerId',
        'portfolioelementtype' => 'portfolioElementType',
        'portfolioelementid' => 'portfolioElementId',
        'portfolioelementtitle' => 'portfolioElementTitle',
        'submissiondate' => 'submissionDate',
        'status' => 'status',
        'taskid' => 'taskId',
        'tasktitle' => 'taskTitle',
        'exportarchiveid' => 'exportArchiveId',
    ];

    // The related DB tables must have the corresponding alias in the SQL command ('group' resp. 'owner', also see function getSqlFromForCompleteSubmissionAndEvaluationData)
    protected static $externalDbFieldsToProperties = [
        '{group}.name' => 'groupName',
        'owner.firstname' => 'ownerFirstName',
        'owner.lastname' => 'ownerLastName',
        'owner.preferredname' => 'ownerPreferredName',
        'owner.email' => 'ownerEmail',
    ];

    protected static $allDbFieldsToProperties = [];     // Is set in constructor by merging both arrays above

    protected static $dateProperties = ['submissionDate'];

    protected $dirty;

    /**
     * @param int $statusToCheck
     * @return bool
     */
    public function hasStatus($statusToCheck) {
        return (int)$this->status === $statusToCheck;
    }

    /**
     * @return bool
     */
    public function isEditable() {
        return (int)$this->status < self::StatusReleasing;
    }

    /**
     * @return bool
     */
    public function isNotReleased() {
        return ((int)$this->status === self::StatusSubmitted || (int)$this->status === self::StatusReleasing);
    }

    /**
     * @return string
     */
    public static function getSqlSelectForCompleteSubmissionAndEvaluationDataForSubmissionTable() {
        $pgBooleanConversion = (is_postgres() ? '::int' : '');

        $allSubmissionAndEvaluationDbFieldsToProperties = array_merge(self::getAllDbFieldsToProperties(), Evaluation::getAllDbFieldsToProperties());
        unset($allSubmissionAndEvaluationDbFieldsToProperties['id']);

        foreach ($allSubmissionAndEvaluationDbFieldsToProperties as $dbField => $property) {
            $selectArray[] = $dbField . ' AS "' . $property . '"';
        }
        $selectArray[] = 'evaluation.id AS "evaluationId"';
        $selectArray[] = '(owner.deleted = 1)' . $pgBooleanConversion . ' AS "ownerDeleted"';
        $selectArray[] = '(evaluator.deleted = 1) ' . $pgBooleanConversion . ' AS "evaluatorDeleted"';
        $selectArray[] = '(grouptyperoles.see_submitted_views = 1)' . $pgBooleanConversion . ' AS "liveUserIsAssessor"';

        return 'SELECT ' . implode(', ', $selectArray);
    }

    /**
     * @return string
     */
    public static function getSqlFromForCompleteSubmissionAndEvaluationDataForSubmissionTable() {
        global $USER;

        return "FROM {module_submissions} AS submission
                    INNER JOIN {module_submissions_evaluation} AS evaluation ON evaluation.submissionid = submission.id
                    INNER JOIN {group} AS {group} ON {group}.id = submission.groupid
                    INNER JOIN {group_member} AS groupmember ON groupmember.group = {group}.id AND groupmember.member = " . $USER->get('id') . "
                    INNER JOIN {grouptype_roles} AS grouptyperoles ON grouptyperoles.grouptype = {group}.grouptype AND grouptyperoles.role = groupmember.role
                    INNER JOIN {usr} AS owner ON submission.ownertype = 'user' AND owner.id = submission.ownerid AND (grouptyperoles.see_submitted_views = 1 OR owner.id = " . $USER->get('id') . ")
                    LEFT JOIN {usr} AS evaluator ON evaluator.id = evaluation.evaluatorid";
    }
}