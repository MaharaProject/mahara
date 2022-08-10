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

class Evaluation extends AbstractModel {
    const NoResult = 1;
    const Fail = 2;
    const Success = 3;

    protected $id;

    protected $submissionId;

    protected $evaluatorId;
    protected $evaluatorName;
    protected $evaluatorEmail;

    protected $evaluationDate;
    protected $feedback;
    protected $rating;
    protected $success;

    protected static $dbTable = 'module_submissions_evaluation';

    protected static $internalDbFieldsToProperties = [
        'id' => 'id',
        'submissionid' => 'submissionId',
        'evaluatorid' => 'evaluatorId',
        'evaluationdate' => 'evaluationDate',
        'feedback' => 'feedback',
        'rating' => 'rating',
        'success' => 'success',
    ];

    protected static $externalDbFieldsToProperties = [
        'evaluator.firstname' => 'evaluatorFirstName',
        'evaluator.lastname' => 'evaluatorLastName',
        'evaluator.preferredname' => 'evaluatorPreferredName',
        'evaluator.email' => 'evaluatorEmail',
    ];

    protected static $allDbFieldsToProperties = [];     // Will be set in constructor by merging both arrays above

    protected static $dateProperties = ['evaluationDate'];

    protected $dirty;
}