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

class EvaluationTools {

    /**
     * @param Submission $submission
     * @return Evaluation
     * @throws \SystemException
     * @throws \SQLException
     */
    public static function createAndCommitEvaluationForNewSubmission(Submission $submission) {
        $evaluation = new Evaluation();

        $evaluation->set('submissionId', $submission->get('id'));
        $evaluation->commit();

        return $evaluation;
    }

    /**
     * @param array $recordsArray
     * @return array
     * @throws \SystemException
     */
    public static function createEvaluationArrayFromRecordArray(array $recordsArray) {
        $evaluationArray = [];
        foreach ($recordsArray as $record) {
            $evaluation = new Evaluation(null, $record);
            $evaluationArray[] = $evaluation;
        }
        return $evaluationArray;
    }
}