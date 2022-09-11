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

namespace Submissions\Tools;


use Submissions\Models\Evaluation;
use Submissions\Models\Submission;
use Submissions\Repository\SubmissionRepository;

class SubmissionTools {
    // region public functions submission handling
    /**
     * @param \View|\Collection $portfolioElement
     * @return string
     * @throws \SystemException
     */
    public static function getPortfolioElementType($portfolioElement) {
        switch (get_class($portfolioElement)) {
            case 'View':
                return 'view';
                break;
            case 'Collection':
                return 'collection';
                break;
            default:
                throw new \SystemException(get_string('unsupportedportfoliotype','artefact.plans'));
        }
    }

    /**
     * @param \View|\Collection $portfolioElement
     * @return string
     * @throws \SystemException
     */
    public static function getPortfolioElementTitle($portfolioElement) {
        switch (get_class($portfolioElement)) {
            case 'View':
                return $portfolioElement->get('title');
                break;
            case 'Collection':
                return $portfolioElement->get('name');
                break;
            default:
                throw new \SystemException(get_string('unsupportedportfoliotype','module.submissions'));
        }
    }

    /**
     * @param Submission $submission
     * @param \View|\Collection $portfolioElement
     * @throws \AuthUnknownUserException
     * @throws \SystemException
     */
    public static function setOwnerFieldsForSubmissionFromPortfolioElement(Submission $submission, $portfolioElement) {
        switch (true) {
            case $portfolioElement->get('owner'):
                $submission->set('ownerType', 'user');
                $submission->set('ownerId', $portfolioElement->get('owner'));
                break;

            case $portfolioElement->get('group'):
                $submission->set('ownerType', 'group');
                $submission->set('ownerId', $portfolioElement->get('group'));
                break;

            default:
                throw new \SystemException(get_string('unsupportedsubmissionownertype','module.submissions'));
        }
    }

    /**
     * @param Submission $submission
     * @throws \SystemException
     */
    public static function setTaskFieldsForSubmission(Submission $submission) {
        if (is_plugin_active('plans', 'artefact')) {
            if (empty($submission->get('portfolioElementType')) || empty($submission->get('portfolioElementId'))) {
                throw new \SystemException(get_string('portfoliofieldsmustbesetforsettingtaskfields', 'module.submissions'));
            }

            $rootGroupTask = SubmissionRepository::findAssignedRootGroupTaskForSubmission($submission);

            if (!is_null($rootGroupTask)) {
                $submission->set('taskId', $rootGroupTask->get('id'));
                $submission->set('taskTitle', $rootGroupTask->get('title'));
            }
        }
    }

    /**
     * @param \View|\Collection $portfolioElement
     * @param \stdClass $group
     * @return Submission
     * @throws \AuthUnknownUserException
     * @throws \SystemException
     */
    public static function createNewSubmissionByPortfolioElementAndGroup($portfolioElement, \stdClass $group) {
        $submission = new Submission();
        $submission->set('groupId', $group->id);
        $submission->set('portfolioElementType', self::getPortfolioElementType($portfolioElement));
        $submission->set('portfolioElementId', $portfolioElement->get('id'));
        $submission->set('portfolioElementTitle', self::getPortfolioElementTitle($portfolioElement));
        $submission->set('submissionDate', time());
        $submission->set('status', Submission::StatusSubmitted);
        self::setOwnerFieldsForSubmissionFromPortfolioElement($submission, $portfolioElement);
        self::setTaskFieldsForSubmission($submission);

        return $submission;
    }

    /**
     * Check if submission portfolio is assigned as outcome to a user (selection) task and if yes, set it's status to completed
     *
     * @param Submission $submission
     * @throws \SQLException
     */
    public static function setEventuallyAssignedPrivateTaskToCompletedForSubmission(Submission $submission) {
        if (is_plugin_active('plans', 'artefact')) {
            $ownerTask = SubmissionRepository::findAssignedOwnerTaskForSubmission($submission);

            if (!is_null($ownerTask)) {
                $ownerTask->set('completed', true);
                $ownerTask->commit();
            }
        }
    }
    // endregion

    //region submissions table
    /**
     * @param array $recordsArray
     * @return array
     * @throws \SystemException
     */
    public static function createSubmissionArrayFromRecordArray(array $recordsArray) {
        $submissionArray = [];
        foreach ($recordsArray as $record) {
            $submission = new Submission(null, $record);
            $submissionArray[] = $submission;
        }
        return $submissionArray;
    }

    /**
     * @param int $groupId
     * @param bool $showNameAsLastnameFirstname
     * @return string[]
     * @throws \AuthUnknownUserException
     */
    public static function createAdminTutorUserIdUserNameArrayForPiertableSelectionBoxByGroupId(int $groupId, bool $showNameAsLastnameFirstname) {
        $userArray = ['' => get_string('unassignedselectboxitem', 'module.submissions')];
        $users = SubmissionRepository::findAdminsAndTutorsByGroupId($groupId);

        /** @var \User $user */
        foreach ($users as $user) {
            $userArray[$user->get('id')] = ($showNameAsLastnameFirstname ? self::getEvaluatorNameAsLastNameFirstName($user) : self::getEvaluatorNameAsFirstNameLastName($user));
        }
        asort($userArray);

        return $userArray;
    }

    /**
     * @param int $groupId
     * @param bool $showNameAsLastnameFirstname
     * @return string[]
     * @throws \AuthUnknownUserException
     */
    public static function createAdminTutorValueTextArrayForPiertableRowSelectionBoxByGroupId(int $groupId, bool $showNameAsLastnameFirstname) {
        $userArray[] = ['value' => '', 'text' => get_string('unassignedselectboxitem', 'module.submissions')];
        $users = SubmissionRepository::findAdminsAndTutorsByGroupId($groupId);

        /** @var \User $user */
        foreach ($users as $user) {
            $userArray[] = ['value' => $user->get('id'), 'text' => ($showNameAsLastnameFirstname ? self::getEvaluatorNameAsLastNameFirstName($user) : self::getEvaluatorNameAsFirstNameLastName($user))];
        }
        asort($userArray);

        return $userArray;
    }

    /**
     * @param bool $showNameAsLastnameFirstname
     * @return array
     * @throws \SQLException
     */
    public static function createAdminTutorUserIdUserNameArrayForPiertableQuickfilterByLiveUser(bool $showNameAsLastnameFirstname) {
        global $USER;

        $userArray = [null => get_string('unassignedselectboxitem', 'module.submissions')];

        $sql = "SELECT u.id, u.firstname, u.lastname FROM {group_member} AS gm
                    INNER JOIN {group} AS g ON g.id = gm.group
                    INNER JOIN {grouptype_roles} AS gr ON (gr.grouptype = g.grouptype AND gr.role = gm.role AND gr.see_submitted_views = '1')
                    INNER JOIN {group_member} AS lugm ON lugm.group = gm.group
                    INNER JOIN {usr} AS u ON u.id = gm.member
                WHERE lugm.member = ?";

        $users = get_records_sql_array($sql, [$USER->get('id')]);

        if ($users) {
            foreach ($users as $user) {
                $userArray[$user->id] = ($showNameAsLastnameFirstname ? self::concatLastAndFirstName($user->id) : self::concatFirstAndLastName($user->id));
            }
            asort($userArray);
        }
        return $userArray;
    }

    /**
     * @param int $groupId
     * @return array
     * @throws \SQLException
     */
    public static function createTaskIdTaskTitleArrayForPiertableSelectionBoxByGroupId(int $groupId) {
        $taskArray = [null => get_string('unassignedselectboxitem', 'module.submissions')];
        $tasks = SubmissionRepository::findPlansAndTasksByGroupId($groupId);

        /** @var \stdClass $task */
        foreach ($tasks as $task) {
            $taskArray[$task->id] = $task->tasktitle;
        }

        return $taskArray;
    }

    /**
     * @param string $string
     * @param int $maxLength
     * @param string $ellipses
     * @return string
     */
    public static function limitStringLengthByInsertingEllipses(string $string, int $maxLength = 40, string $ellipses = '..') {
        $stringLength = strlen($string);
        $ellipsesLength = strlen($ellipses);
        $overflow = $stringLength - $maxLength;

        if ($overflow > 0 && $ellipsesLength < $maxLength) {
            $start = intval($maxLength * 2 / 3) - intval($ellipsesLength / 2);
            $string = substr_replace($string, $ellipses, $start, $overflow + $ellipsesLength);
        }

        return $string;
    }

    /**
     * @param string $htmlString
     * @param int $maxLength
     * @param string $ellipses
     * @return string
     */
    public static function limitHtmlStringLengthByInsertingEllipses(string $htmlString, int $maxLength = 40, string $ellipses = '..') {
        $decodedString = html_entity_decode($htmlString);
        $limitedString = self::limitStringLengthByInsertingEllipses($decodedString, $maxLength, $ellipses);

        return htmlentities($limitedString);
    }

    /**
     * @param string $link
     * @param string $title
     * @param string $hoverText
     * @param string $classes
     * @param string $style
     * @return string
     */
    public static function createHtmlLinkWithTitle(string $link, string $title = '', string $hoverText = '', string $classes = '', string $style = '') {
        return '<a class="' . $classes . '" href="' . $link . '" title="' . $hoverText . '" style="' . $style .'">' . $title . '</a>';
    }

    /**
     * @param string $link
     * @param string $title
     * @param string $hoverText
     * @param string $portfolioElementType
     * @param bool $showPortfolioButtons
     * @return string
     */
    public static function createPortfolioTitleHtml(string $link, string $title, string $hoverText, string $portfolioElementType, bool $showPortfolioButtons) {
        $containerClasses = '';
        $titleClasses = 'portfolio-element';
        $previewClasses = 'portfolio-type-' . $portfolioElementType . ' portfolio-element-preview icon icon-regular icon-eye btn btn-sm btn-secondary ms-1';

        if ($showPortfolioButtons) {
            $containerClasses = 'portfolio-element-container btn-group btn-group-sm';
            $titleClasses .= ' btn btn-sm btn-primary';
            $previewClasses = 'portfolio-type-' . $portfolioElementType . ' portfolio-element-preview icon icon-regular icon-eye btn btn-sm btn-secondary';
        }

        $viewTitleLink = self::createHtmlLinkWithTitle($link, $title, $hoverText, $titleClasses);
        $viewPreviewLink = self::createHtmlLinkWithTitle($link, '', 'Preview', $previewClasses);

        return '<div class="' . $containerClasses . '" role="group">' . $viewTitleLink . $viewPreviewLink . '</div>';
    }

    /**
     * @param int $portfolioElementId
     * @param string $portfolioElementTitle
     * @param string $portfolioElementType
     * @param int|null $exportArchiveId
     * @param bool $isNotReleased
     * @param bool $showPortfolioButtons
     * @return string
     * @throws \SQLException
     * @throws \SystemException
     */
    public static function createPortfolioElementTitleHtmlForTable(int $portfolioElementId, string $portfolioElementTitle, string $portfolioElementType, $exportArchiveId, bool $isNotReleased, bool $showPortfolioButtons) {
        $limitedTitle = self::limitHtmlStringLengthByInsertingEllipses($portfolioElementTitle);
        $portfolioElementTitleHtml = '';
        if ($isNotReleased) {
            $viewLink = '';
            $deleted = false;
            switch ($portfolioElementType) {
                case 'view':
                    $viewLink = get_config('wwwroot') . 'view/view.php?id=' . $portfolioElementId;
                    $deleted = !get_field('view', 'id', 'id', $portfolioElementId);
                    break;
                case 'collection':
                    require_once(get_config('docroot') . 'lib/collection.php');
                    try {
                        $collection = new \Collection($portfolioElementId);
                        $viewLink = get_config('wwwroot') . 'view/view.php?id=' . $collection->first_view()->get('id');
                    }
                        // Should not happen, but who knows... :) To be sure, that the user gets the table
                    catch (\Exception $e) {
                        $portfolioElementTitleHtml = 'Collection is empty';
                        $deleted = !get_field('collection', 'id', 'id', $portfolioElementId);
                    }
                    break;
                default:
                    throw new \SystemException(get_string('unsupportedportfoliotype','module.submissions'));
            }
            if ($deleted) {
                $portfolioElementTitleHtml = $limitedTitle;
            }
            else {
                $portfolioElementTitleHtml = self::createPortfolioTitleHtml($viewLink, $limitedTitle, $portfolioElementTitle, $portfolioElementType, $showPortfolioButtons);
            }
        }
        else if (!is_null($exportArchiveId)) {
            $exportArchive = get_record('export_archive', 'id', $exportArchiveId);
            if ($exportArchive) {
                $exportArchiveLink = get_config('wwwroot') . 'downloadarchive.php?id=' . $exportArchive->id;
                $portfolioElementTitleHtml = self::createHtmlLinkWithTitle($exportArchiveLink,
                    $limitedTitle,
                    $portfolioElementTitle,
                    '');
            }
        }
        else {
            $portfolioElementTitleHtml = $limitedTitle;
        }
        return $portfolioElementTitleHtml;
    }

    /**
     * @param \stdClass $submissionRecord
     * @param bool $showPortfolioButtons
     * @return string
     * @throws \SQLException
     * @throws \SystemException
     */
    public static function createPortfolioElementTitleHtmlForTableBySubmissionRecord(\stdClass $submissionRecord, bool $showPortfolioButtons) {
        return self::createPortfolioElementTitleHtmlForTable($submissionRecord->portfolioElementId,
            $submissionRecord->portfolioElementTitle,
            $submissionRecord->portfolioElementType,
            $submissionRecord->exportArchiveId,
            (int)$submissionRecord->status === Submission::StatusSubmitted || (int)$submissionRecord->status === Submission::StatusReleasing,
            $showPortfolioButtons);
    }

    /**
     * @param Submission $submission
     * @param bool $showPortfolioButtons
     * @return string
     * @throws \SystemException
     */
    public static function createPortfolioElementTitleHtmlForTableBySubmission(Submission $submission, bool $showPortfolioButtons) {
        return self::createPortfolioElementTitleHtmlForTable($submission->get('portfolioElementId'),
            $submission->get('portfolioElementTitle'),
            $submission->get('portfolioElementType'),
            $submission->get('exportArchiveId'),
            $submission->isNotReleased(),
            $showPortfolioButtons);
    }

    /**
     * @param int $exportArchiveId
     * @param string $filePath
     * @throws \SQLException
     */
    public static function deleteArchivedSubmissionByExportArchiveIdAndFilePath(int $exportArchiveId, string $filePath) {
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $sql = 'UPDATE {module_submissions} SET exportarchiveid = NULL WHERE exportarchiveid = ?';
        execute_sql($sql, [$exportArchiveId]);

        $sql = 'DELETE FROM {archived_submissions} WHERE archiveid = ?';
        execute_sql($sql, [$exportArchiveId]);

        $sql = 'DELETE FROM {export_archive} WHERE id = ?';
        execute_sql($sql, [$exportArchiveId]);
    }
    //endregion

    //region submissions table backend tools to process json requests on inline editing
    /**
     * @param Submission $submission
     * @return bool
     */
    public static function isToBePendingReleased(Submission $submission) {
        $submittedGroup = get_group_by_id($submission->get('groupId'));

        if (is_object($submittedGroup) && $submittedGroup->allowarchives) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * @param Submission $submission
     * @throws \CollectionNotFoundException
     * @throws \SQLException
     * @throws \SystemException
     * @throws \ViewNotFoundException
     */
    public static function synchronizeSubmissionStatusWithPortfolioElement(Submission $submission) {
        $portfolioElement = SubmissionTools::getPortfolioElementBySubmission($submission);

        switch ($portfolioElement->get('submittedstatus')) {
            // Portfolio element is editable
            case $portfolioElement::SUBMITTED:
                $submission->set('status', Submission::StatusSubmitted);
                break;
            // Release of portfolio element has already been initiated and element currently is in archive queue
            case $portfolioElement::PENDING_RELEASE:
                $submission->set('status', Submission::StatusReleasing);
                break;
            // Portfolio element has already been completely released
            case $portfolioElement::UNSUBMITTED:
                    if ($submission->isNotReleased()) {
                        // Something must have been went wrong in a previous release:
                        // Better, a person checks this than automatically set the status
                        $submission->set('status', Submission::StatusIncomplete);
                    }
                break;
        }
        if ($submission->get('dirty')) {
            $submission->commit();
            throw new \SystemException(get_string('submissionstatuschangedexternally','module.submissions'));
        }
    }

    /**
     * @param Evaluation $evaluation
     * @return int
     */
    public static function concludeSubmissionStatusFromEvaluation(Evaluation $evaluation) {
        switch ((int)$evaluation->get('success')) {
            case Evaluation::NoResult:
                return Submission::StatusRevision;
            case Evaluation::Fail:
            case Evaluation::Success:
                return Submission::StatusEvaluated;
            default:
                return Submission::StatusIncomplete;
        }
    }

    /**
     * @param Submission $submission
     * @param \User|null $releaseUser
     * @throws \CollectionNotFoundException
     * @throws \ParameterException
     * @throws \SystemException
     * @throws \ViewNotFoundException
     */
    public static function standardReleaseSubmission(Submission $submission, \User $releaseUser = null) {
        $portfolioElement = self::getPortfolioElementBySubmission($submission);

        if (self::isToBePendingReleased($submission)) {
            $portfolioElement->pendingrelease($releaseUser);
        }
        else {
            $portfolioElement->release($releaseUser);
        }
        return;
    }
    //endregion

    //region events
    /**
     * @param int $userId
     * @throws \SQLException
     */
    public static function deleteArchivedSubmissionsByUserId(int $userId) {
        $sql = "SELECT s.id AS submissionid, ea.id AS exportarchiveid, ea.filename, ea.filepath FROM {module_submissions} AS s
                    INNER JOIN {module_submissions_evaluation} AS e ON e.submissionid = s.id
                    INNER JOIN {export_archive} AS ea ON ea.id = s.exportarchiveid
                    WHERE s.ownertype = 'user' AND s.ownerid = ?";

        $results = get_records_sql_array($sql, [$userId]);

        if ($results) {
            foreach ($results as $result) {
                self::deleteArchivedSubmissionByExportArchiveIdAndFilePath($result->exportarchiveid, $result->filepath . $result->filename);
            }
        }
    }
    //endregion

    //region cron jobs
    /**
     * @param int $retentionPeriod
     * @throws \SQLException
     */
    public static function deleteArchivedSubmissionsByRetentionPeriod(int $retentionPeriod) {
        $where = 'EXTRACT(YEAR FROM NOW()) - EXTRACT(YEAR FROM e.evaluationdate) > ?';

        if (is_mysql()) {
            $where = 'YEAR(NOW()) - YEAR(e.evaluationdate) > ?';
        }

        $sql = "SELECT s.id AS submissionid, ea.id AS exportarchiveid, ea.filename, ea.filepath FROM {module_submissions} AS s
                    INNER JOIN {module_submissions_evaluation} AS e ON e.submissionid = s.id
                    INNER JOIN {export_archive} AS ea ON ea.id = s.exportarchiveid
                    WHERE s.ownertype = 'user' AND " . $where;

        $results = get_records_sql_array($sql, [$retentionPeriod]);

        if ($results) {
            foreach ($results as $result) {
                self::deleteArchivedSubmissionByExportArchiveIdAndFilePath($result->exportarchiveid, $result->filepath . $result->filename);
            }
        }
    }
    //endregion

    //region common functions
    /**
     * @param string $name
     * @return int|null
     * @throws \ParameterException
     */
    public static function param_integer_or_null($name) {
        if (param_exists($name) && empty(param_variable($name))) {
            return null;
        }
        return param_integer($name);
    }

    /**
     * @param int $id
     * @param bool $fullname
     * @return string
     */
    public static function concatFirstAndLastName(int $id, bool $fullname = false) {
        $user = get_user_for_display($id);
        if ($fullname) {
            return full_name($user);
        }
        return empty($user->preferredname) ? full_name($user) : $user->preferredname;
    }

    /**
     * @param int $id
     * @param bool $fullname
     * @param string $divider
     * @return string
     */
    public static function concatLastAndFirstName(int $id, bool $fullname = false, string $divider = ', ') {
        $user = get_user_for_display($id);
        if ($fullname) {
            return full_name($user, $divider);
        }
        return empty($user->preferredname) ? full_name($user, $divider) : $user->preferredname;
    }

    /**
     * @param \stdClass $record
     * @param bool $showNameAsLastnameFirstname
     * @return string
     */
    public static function createOwnerName(\stdClass $record, bool $showNameAsLastnameFirstname) {
        if ($showNameAsLastnameFirstname) {
            return self::concatLastAndFirstName($record->ownerId, true);
        }
        return self::concatFirstAndLastName($record->ownerId, true);
    }

    /**
     * @param \stdClass $record
     * @param bool $showNameAsLastnameFirstname
     * @return string
     */
    public static function createEvaluatorName(\stdClass $record, bool $showNameAsLastnameFirstname) {
        if ($record->evaluatorPreferredName) {
            return $record->evaluatorPreferredName;
        }
        if ($showNameAsLastnameFirstname) {
            return self::concatLastAndFirstName($record->evaluatorId);
        }
        return self::concatFirstAndLastName($record->evaluatorId);
    }

    /**
     * @param \User $user
     * @return string
     */
    public static function getEvaluatorNameAsLastNameFirstName(\User $user) {
        return self::concatLastAndFirstName($user->get('id'));
    }

    /**
     * @param \User $user
     * @return string
     */
    public static function getEvaluatorNameAsFirstNameLastName(\User $user) {
        return self::concatFirstAndLastName($user->get('id'));
    }

    /**
     * Gets the releaseUser as User class
     *
     * If it's called directly we have the release user as class LiveUser
     *
     * If it's called from cronjob (pending release) and
     * if the portfolio is a collection we have the releaseuser as integer userId
     * respectively if the portfolio is a view we have the releaseuser as stdClass user
     *
     * @param mixed $sourceUserData
     * @return null|\User
     */
    public static function getReleaseUserFromReleaseUserData($sourceUserData) {
        $releaseUser = null;

        // @ to suppress php warning if $sourceUserData is not an object
        switch (@get_class($sourceUserData)) {
            case 'LiveUser':
            case 'User':
                return $sourceUserData;
            case 'stdClass':
                $sourceUserData = $sourceUserData->id;
            default:
                if (is_numeric($sourceUserData)) {
                    try {
                        $releaseUser = new \User;
                        $releaseUser->find_by_id((int)$sourceUserData);
                    }
                    catch(\Exception $e) {
                    }
                }
        }
        return $releaseUser;
    }

    /**
     * @param Submission $submission
     * @return \Collection|\View
     * @throws \CollectionNotFoundException
     * @throws \SystemException
     * @throws \ViewNotFoundException
     */
    public static function getPortfolioElementBySubmission(Submission $submission) {
        switch ($submission->get('portfolioElementType')) {
            case 'view':
                require_once(get_config('docroot') . 'lib/view.php');
                return new \View($submission->get('portfolioElementId'));
            case 'collection':
                require_once(get_config('docroot') . 'lib/collection.php');
                return new \Collection($submission->get('portfolioElementId'));
            default:
                throw new \SystemException(get_string('unsupportedportfoliotype','module.submissions'));
        }
    }

    /**
     * @param Submission $submission
     */
    public static function removeCompletedFlagOfAssignedTaskBySubmissionAndCommit(Submission $submission) {
        // If plans plugin is active find the assigned owner task and if the portfolio is assigned as outcome adjust completed flag
        if (is_plugin_active('plans', 'artefact')) {
            try {
                $ownerTask = SubmissionRepository::findAssignedOwnerTaskForSubmission($submission);

                if (!is_null($ownerTask)) {
                    $ownerTask->set('completed', 0);
                    $ownerTask->commit();
                }
            }
            catch (\Exception $e) {
                // Try catch only to ensure, that this non important functionality doesn't interrupt the calling function (Release event or fix submission)
            }
        }
    }

    /**
     * @param array $values
     * @return string
     */
    public static function createSQLEqualityWhereClauseFromFieldValueArray(array &$values) {
        $whereArray = [];
        foreach($values as $field => $value) {
            if (is_null($value)) {
                $whereArray[] = $field . ' IS NULL';
                unset($values[$field]);
            }
            else {
                $whereArray[] = $field . ' = ?';
            }
        }
        return implode(' AND ', $whereArray);
    }
    //endregion
}