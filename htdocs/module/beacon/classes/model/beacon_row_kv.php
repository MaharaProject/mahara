<?php
/**
 * Model for a beacon row.
 *
 * @package     tool_beacon
 * @copyright   2020 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace module_beacon\model;
defined('INTERNAL') || die();
class beacon_row_kv {
    public $domain;
    public $timestamp;
    public $type;
    public $questionid;
    public $id;
    public $key;
    public $value;

    public function __construct($domain, $timestamp, $type, $questionid, $id, $key, $value) {
        $this->domain = $domain;
        $this->timestamp = $timestamp;
        $this->type = $type;
        $this->questionid = $questionid;
        $this->id = $id;
        $this->key = $key;
        $this->value = $value;
    }
}
