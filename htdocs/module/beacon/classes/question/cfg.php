<?php
/**
 * CFG question type - returns values stored in $CFG.
 *
 * @package    tool_beacon
 * @author     Kevin Pham <kevinpham@catalyst-au.net>
 * @copyright  Catalyst IT, 2021
 */

namespace module_beacon\question;
defined('INTERNAL') || die();
use module_beacon\model\beacon_row_kv;

class cfg extends question {

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array  $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    private  function get($array, $key, $default = null) {
        if (is_null($key)) {
            return $array;
        }
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        if (strpos($key, '.') === false) {
            return isset($array[$key]) ? $array[$key] : $default;
        }
        foreach (explode('.', $key) as $segment) {
            if (array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }
        return $array;
    }

    protected function query() {
        global $cfg;
        $result = [];

        $names = $this->params->names;

        foreach ($names as $id => $key) {
            $cfgasarray = json_decode(json_encode($cfg), true);
            $value = json_encode($this->get($cfgasarray, $key));

            if (!isset($value)) {
                continue;
            }

            $result[] = new beacon_row_kv(
                $this->domain,
                $this->timestamp,
                $this->type,
                $this->questionid,
                $key,
                $id,
                $value
            );
        }

        return $result;
    }
}
