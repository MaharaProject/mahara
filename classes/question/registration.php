<?php

/**
 * REGISTRATION question type.
 * @package mahara
 * @subpackage     module_beacon
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace module_beacon\question;
defined('INTERNAL') || die();
use module_beacon\model\beacon_row_kv;

class registration extends question {

    protected function query() {
        require_once(get_config('libroot').'/registration.php');
        $siteinfo = [];
        $registrationdata = registration_data();
        foreach ($registrationdata as $key => $value) {
            $siteinfo[] = new beacon_row_kv(
                $this->domain,
                $this->timestamp,
                $this->type,
                $this->questionid,
                'site',
                $key,
                $value
            );
        }

        return $siteinfo;
    }
}
