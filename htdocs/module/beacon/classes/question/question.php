<?php
/**
 * Schedule tasks definition.
 *
 * @package     module_beacon
 * @copyright   2020 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace module_beacon\question;
defined('INTERNAL') || die();
use module_beacon\model\beacon_row_kv;

abstract class question {
    protected $params;

    protected $questionid;

    protected $domain;

    protected $timestamp;

    protected $type;

    /**
     * @var beacon_row_kv $results - Collection of results, used when query has already been run before
     */
    protected $results;

    abstract protected function query();

    /**
     * Ensure multiple calls to 'answer' will return the same results
     * @return beacon_row_kv rows
     */
    private function answer() {
        if (empty($this->results)) {
            $this->results = $this->query();
        }
        return $this->results;
    }

    public function __construct($question) {
        global $CFG;

        $this->timestamp = time();
        $this->domain = rtrim($CFG->wwwroot, '/');
        $this->type = (new \ReflectionClass($this))->getShortName();

        if (isset($question->params)) {
            $this->params = $question->params;
        }

        if (isset($question->id)) {
            $this->questionid = $question->id;
        }
    }

    public function get_structured_data() {
        $data = [
            'timestamp'     => $this->timestamp,
            'domain'        => $this->domain,
            'type'          => $this->type,
            'questionid'    => $this->questionid,
        ];

        $result = [];

        $starttime = microtime(true);

        /** @var beacon_row_kv $row **/
        foreach ($this->answer() as $row) {
            $result[$row->id][$row->key] = $row->value;
        }

        $data['timetoanswer'] = microtime(true) - $starttime;

        $data['result'] = $result;
        return $data;
    }
}
