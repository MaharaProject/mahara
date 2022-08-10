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
use Submissions\Tools\EvaluationTools;

class EvaluationRepository {

    /**
     * @param Submission $submission
     * @return \stdClass|null
     * @throws \SQLException
     */
    public static function findEvaluationBySubmissionAsStdClass(Submission $submission) {
        $evaluationRecord = get_record('module_submissions_evaluation', 'submissionid', $submission->get('id'));

        if ($evaluationRecord) {
            return $evaluationRecord;
        }
        return null;
    }

    /**
     * @param Submission $submission
     * @return null|Evaluation
     * @throws \SystemException
     * @throws \SQLException
     */
    public static function findEvaluationBySubmission(Submission $submission) {
        $evaluationRecord = self::findEvaluationBySubmissionAsStdClass($submission);

        if ($evaluationRecord) {
            return new Evaluation(null, $evaluationRecord);
        }
        return null;
    }

    /**
     * @param Submission $submission
     * @return Evaluation
     * @throws \SystemException
     * @throws \SQLException
     */
    public static function findOrCreateEvaluationBySubmission(Submission $submission) {
        $evaluationRecord = self::findEvaluationBySubmissionAsStdClass($submission);

        if ($evaluationRecord) {
            $evaluation = new Evaluation(null, $evaluationRecord);
        }
        else {
            $evaluation = EvaluationTools::createAndCommitEvaluationForNewSubmission($submission);
        }
        return $evaluation;
    }

    /**
     * @param Submission $submission
     * @return array of \stdClass
     * @throws \SQLException
     */
    public static function findEvaluationsBySubmissionAsStdClass(Submission $submission) {
        $evaluationRecordArray = get_records_array('module_submissions_evaluation', 'submissionid', $submission->get('id'));

        if ($evaluationRecordArray) {
            return $evaluationRecordArray;
        }
        return [];
    }

    /**
     * @param Submission $submission
     * @return array of Evaluation
     * @throws \SystemException
     * @throws \SQLException
     */
    public static function findEvaluationsBySubmission(Submission $submission) {
        $evaluationRecordArray = self::findEvaluationsBySubmissionAsStdClass($submission);

        return EvaluationTools::createEvaluationArrayFromRecordArray($evaluationRecordArray);
    }
}