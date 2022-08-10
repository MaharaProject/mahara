<?php
/**
 *
 * @package    Mahara
 * @subpackage module-submissions
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

use Submissions\Models\Evaluation;
use Submissions\Models\Submission;
use Submissions\Repository\EvaluationRepository;
use Submissions\Repository\SubmissionRepository;
use Submissions\Tools\EvaluationTools;
use Submissions\Tools\SubmissionTools;

defined('INTERNAL') || die();

require(dirname(__FILE__) . '/vendor/autoload.php');

class PluginModuleSubmissions extends PluginModule {

    /**
     * Fetch the human readable name for the plugin
     *
     * @return string
     */
    public static function get_plugin_display_name() {
        return get_string('pluginname', 'module.submissions');
    }

    public static function get_plugin_name() {
        return 'submissions';
    }

    public static function is_active() {
        return is_plugin_active('submissions', 'module');
    }

    public static function menu_items() {
        return [
            'share/submissions' => [
                'path' => 'share/submissions',
                'url'  => 'module/submissions/index.php',
                'title' => get_string('Submissions', 'module.submissions'),
                'weight' => 50,
            ],
        ];
    }

    public static function group_tabs($groupid, $role) {
        global $USER;

        $groupTab = [];
        if (group_user_can_assess_submitted_views($groupid, $USER->get('id'))) {
            $groupTab = [
                'groupsubmissions' => [
                    'path' => 'groups/groupsubmissions',
                    'url' => 'module/submissions/index.php?group=' . $groupid,
                    'title' => get_string('Submissions', 'module.submissions'),
                    'weight' => 80,
                ],
            ];
        }
        return $groupTab;
    }

    public static function get_event_subscriptions() {
        $subscription = new stdClass();

        $subscription->plugin = 'submissions';
        $subscription->event = 'deleteuser';
        $subscription->callfunction = 'delete_user_event';

        return [$subscription];
    }

    public static function delete_user_event($event, $data) {
        if (empty($retentionPeriod = get_config_plugin('module', 'submissions', 'retentionperiod'))) {
            $userId = $data['id'];

            if (!empty($userId)) {
                SubmissionTools::deleteArchivedSubmissionsByUserId($userId);
            }
        }
    }

    public static function get_cron() {
        return [
            (object) [
                'callfunction' => 'cron_check_retention_period',
                'minute'    => 0,
                'hour'      => 0,
                'day'       => 1,
                'month'     => 1
            ]
        ];
    }

    public static function cron_check_retention_period() {
        $retentionPeriod = get_config_plugin('module', 'submissions', 'retentionperiod');

        if ($retentionPeriod) {
            SubmissionTools::deleteArchivedSubmissionsByRetentionPeriod($retentionPeriod);
        }
    }


    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        $showNameAsLastnameFirstname = get_config_plugin('module', 'submissions', 'shownameaslastnamefirstname');
        $showPortfolioButtons = get_config_plugin('module', 'submissions', 'showportfoliobuttons');
        $retentionPeriod = get_config_plugin('module', 'submissions', 'retentionperiod');

        return array(
            'elements' => array(
                'shownameaslastnamefirstname' => array(
                    'type'          => 'switchbox',
                    'title'         => get_string('shownameaslastnamefirstname', 'module.submissions'),
                    'description'   => get_string('shownameaslastnamefirstnamedescription', 'module.submissions'),
                    'switchtext'    => 'yesno',
                    'defaultvalue'  => $showNameAsLastnameFirstname
                ),
                'showportfoliobuttons' => array(
                    'type'          => 'switchbox',
                    'title'         => get_string('showportfoliobuttons', 'module.submissions'),
                    'description'   => get_string('showportfoliobuttonsdescription', 'module.submissions'),
                    'switchtext'    => 'yesno',
                    'defaultvalue'  => $showPortfolioButtons
                ),
                'retentionperiod' => array(
                    'type'          => 'text',
                    'title'         => get_string('retentionperiod', 'module.submissions'),
                    'description'   => get_string('retentionperioddescription', 'module.submissions'),
                    'rules'         => array(
                        'integer'   => true,
                        'maxlength' => 2,
                        'minvalue'  => 0,
                    ),
                    'defaultvalue'  => $retentionPeriod
                ),
            )
        );
    }

    public static function save_config_options(Pieform $form, $values) {
        set_config_plugin('module', 'submissions', 'shownameaslastnamefirstname', $values['shownameaslastnamefirstname']);
        set_config_plugin('module', 'submissions', 'showportfoliobuttons', $values['showportfoliobuttons']);
        set_config_plugin('module', 'submissions', 'retentionperiod', $values['retentionperiod']);
    }

    /**
     * @param \View|\Collection $portfolioElement
     * @param \stdClass $group
     * @throws MaharaException
     * @throws SQLException
     * @throws SystemException
     */
    public static function add_submission($portfolioElement, $group) {
        $submission = SubmissionTools::createNewSubmissionByPortfolioElementAndGroup($portfolioElement, $group);

        // Check if submission portfolio is already evaluated
        $alreadyEvaluatedSubmission = SubmissionRepository::findNonRevisionSubmissionBySubmission($submission);

        if (!is_null($alreadyEvaluatedSubmission)) {
            switch ($alreadyEvaluatedSubmission->get('status')) {
                case Submission::StatusIncomplete:
                    $errorMsg = get_string('lastsubmissionnoevaluationresultcontactinstructor','module.submissions');
                    break;
                case Submission::StatusEvaluated:
                    $errorMsg = get_string('portfolioortaskalreadyevaluated','module.submissions');
                    break;
                case Submission::StatusReleasing:
                    $errorMsg = get_string('portfoliocurrentlybeingreleased','module.submissions');
                    break;
                case Submission::StatusSubmitted:
                    $errorMsg = get_string('portfolioalreadysubmitted','module.submissions');
                    break;
                default:
                    $errorMsg = get_string('unknownportfoliostatus','module.submissions');
            }
            // Remove if clause when rollback is available for MySQL in Mahara
            if (is_mysql()) {
                try {
                    db_begin();
                    $viewIds = array();
                    switch (SubmissionTools::getPortfolioElementType($portfolioElement)) {
                        case 'view':
                            $viewIds = [$portfolioElement->get('id')];
                            break;
                        case 'collection':
                            $viewIds = $portfolioElement->get_viewids();
                            execute_sql('
                            UPDATE {collection}
                            SET submittedgroup = NULL,
                                submittedhost = NULL,
                                submittedtime = NULL,
                                submittedstatus = ' . Collection::UNSUBMITTED . '
                            WHERE id = ?',
                                [$portfolioElement->get('id')]
                            );

                            break;
                    }
                    View::_db_release($viewIds, $portfolioElement->get('owner'), $group->id);
                }
                catch (Exception $e) {
                    throw new SystemException($e->getMessage());
                }
            }
            throw new SubmissionException($errorMsg);
        }

        try {
            db_begin();
            $submission->commit();
            $evaluation = EvaluationTools::createAndCommitEvaluationForNewSubmission($submission);
            SubmissionTools::setEventuallyAssignedPrivateTaskToCompletedForSubmission($submission);
            db_commit();
        }
        catch (\Exception $e) {
            db_rollback();
            throw new \SystemException(get_string('submissionnotcreated','module.submissions') . $e->getMessage());
        }
    }

    /**
     * @param \View|\Collection $portfolioElement
     * @param mixed $releaseUserData
     * @throws CollectionNotFoundException
     * @throws MaharaException
     * @throws SQLException
     * @throws SystemException
     */
    public static function pending_release_submission($portfolioElement, $releaseUserData) {
        self::release_submission($portfolioElement, $releaseUserData, true);
    }

    /**
     * @param \View|\Collection $portfolioElement
     * @param mixed $releaseUserData
     * @param bool $pendingRelease
     * @throws CollectionNotFoundException
     * @throws MaharaException
     * @throws SQLException
     * @throws SystemException
     */
    public static function release_submission($portfolioElement, $releaseUserData, $pendingRelease = false) {
        $releaseUser = SubmissionTools::getReleaseUserFromReleaseUserData($releaseUserData);
        $submission = SubmissionRepository::findLatestReleasingSubmissionByGroupAndPortfolioElement($portfolioElement->get('submittedgroup'),
                                                                                                    SubmissionTools::getPortfolioElementType($portfolioElement),
                                                                                                    $portfolioElement->get('id'));
        if (is_null($submission)) { // Portfolio was submitted to external host or when Submissions plugin was inactive
            return;
        }
        $evaluation = EvaluationRepository::findEvaluationBySubmission($submission);

        // If submission has status releasing (waiting for cron archive export), all fields within the if clause were already set
        if (!$submission->hasStatus(Submission::StatusReleasing)) {
            // Set evaluation fields
            $evaluation->set('evaluationDate', time());

            if ($releaseUser && $releaseUser->get('id') !== $evaluation->get('evaluatorId')) {
                $evaluation->set('evaluatorId', $releaseUser->get('id'));
            }

            $evaluatorComment = SubmissionRepository::findLatestEvaluatorCommentBySubmissionAndEvaluation($submission, $evaluation);
            if (!is_null($evaluatorComment)) {
                $evaluation->set('feedback', $evaluatorComment->get('description'));
                $evaluation->set('rating', $evaluatorComment->get('rating'));
            }
        }

        // Set submission status
        if ($pendingRelease) {
            $submission->set('status', Submission::StatusReleasing);
        }
        else {
            // We use the Evaluation success field to determine whether it was released through the submissions plugin
            // and so we are sure that the evaluator has had the option to set a success value
            $submission->set('status', SubmissionTools::concludeSubmissionStatusFromEvaluation($evaluation));

            // Set archive
            $submittedGroup = get_group_by_id($submission->get('groupId'));
            if (is_object($submittedGroup) && $submittedGroup->allowarchives) {
                $exportArchiveId = SubmissionRepository::findLatestMatchingArchiveIdBySubmission($submission);
                if (!is_null($exportArchiveId)) {
                    $submission->set('exportArchiveId', $exportArchiveId);
                }
            }
        }

        try {
            db_begin();
            $submission->commit();
            $evaluation->commit();

            if ($submission->hasStatus(Submission::StatusRevision)) {
                SubmissionTools::removeCompletedFlagOfAssignedTaskBySubmissionAndCommit($submission);
            }
            db_commit();
        }
        catch (\Exception $e) {
            db_rollback();
            throw new \SystemException(get_string('submissionnotcreatedorupdated', 'module.submissions') . $e->getMessage());
        }
    }

    /**
     * Called post install and after every upgrade.
     * @param string $prevversion the previously installed version of this module.
     */
    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            $configs = array(
                array(
                    'entry' => array(
                        'plugin' => 'submissions',
                        'field' =>  'shownameaslastnamefirstname'
                    ),
                    'value' => '0'
                ),
                array(
                    'entry' => array(
                        'plugin' => 'submissions',
                        'field' =>  'showportfoliobuttons'
                    ),
                    'value' => '0'
                ),
                array(
                    'entry' => array(
                        'plugin' => 'submissions',
                        'field' =>  'retentionperiod'
                    ),
                    'value' => '0'
                ),
            );

            foreach ($configs as $config) {
                ensure_record_exists('module_config', (object)$config['entry'], (object)array_merge($config['entry'], array('value' => $config['value'])));
            }
            // Clear the cache so that the new menu item appears
            clear_menu_cache();
        }
        return true;
    }
}

class SubmissionException extends UserException {
    public function strings() {
        return array_merge(
            parent::strings(),
            array(
                'title' => get_string('submissionexceptiontitle', 'module.submissions'),
                'message' => get_string('submissionexceptionmessage', 'module.submissions'),
            )
        );
    }
}