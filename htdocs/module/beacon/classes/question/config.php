<?php
/**
 * CONFIG question type.
 *
 * @package     module_beacon
 * @copyright   2020 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace module_beacon\question;

use module_beacon\model\beacon_row_kv;
defined('INTERNAL') || die();

class config extends question {

    protected function query() {
        $result = [];

        $plugin = $this->params->plugin;
        $names = $this->params->names;

        foreach ($names as $name) {
            $value = get_config($plugin, $name);

            if ($value === false) {
                continue;
            }

            $result[] = new beacon_row_kv(
                $this->domain,
                $this->timestamp,
                $this->type,
                $this->questionid,
                $plugin,
                $name,
                $value
            );
        }

        return $result;
    }
}
