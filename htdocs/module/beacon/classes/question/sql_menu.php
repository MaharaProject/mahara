<?php
/**
 * SQL_MENU question type.
 *
 * @package     module_beacon
 * @copyright   2020 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace module_beacon\question;
defined('INTERNAL') || die();
use module_beacon\model\beacon_row_kv;

class sql_menu extends question {
    use sql_question_trait;

    protected function query() {

        $results = [];
        $query = $this->params->sql;

        // Check if the query is not safe and return the error as the response if so.
        if ($this->contains_blocked_word($query)) {
            $records = [
                'did_not_query' => 'ERROR: Query contains a blocked word so it was not executed.'
            ];
        } else {
            $records = get_records_sql_menu($query);
        }

        foreach ($records as $key => $value) {
            $results[] = new beacon_row_kv(
                $this->domain,
                $this->timestamp,
                $this->type,
                $this->questionid,
                $query,
                $key,
                $value
            );
        }

        return $results;
    }
}
