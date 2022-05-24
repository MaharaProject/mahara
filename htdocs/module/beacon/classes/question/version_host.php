<?php
/**
 * VERSION_HOST question type.
 * @package    mahara
 * @subpackage module_beacon
 * @copyright   2020 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace module_beacon\question;
defined('INTERNAL') || die();
use module_beacon\model\beacon_row_kv;

class version_host extends question {

    protected function query() {
        //Mahara stores maturity data in release string. For example: "20.22.4dev".
        $query = [
            'version' => get_config('version'),
            'release' => get_config('release'),
            'branch' => get_config('series')
        ];

        foreach ($query as $key => $value) {
            $siteinfo[] = new beacon_row_kv(
                $this->domain,
                $this->timestamp,
                $this->type,
                $this->questionid,
                'version_host',
                $key,
                $value
            );
        }

        return $siteinfo;
    }
}
