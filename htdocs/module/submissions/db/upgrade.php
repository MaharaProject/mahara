<?php
/**
 *
 * @package    Mahara
 * @subpackage module-submissions
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_module_submissions_upgrade($oldversion=0) {
    if ($oldversion < 2020102700) {
        $configs = array(
            array(
                'entry' => array(
                    'plugin' => 'submissions',
                    'field' =>  'shownameaslastnamefirstname'
                ),
                'value' => '0'
            ),
            array(
                'entry' => array(
                    'plugin' => 'submissions',
                    'field' =>  'showportfoliobuttons'
                ),
                'value' => '0'
            ),
            array(
                'entry' => array(
                    'plugin' => 'submissions',
                    'field' =>  'retentionperiod'
                ),
                'value' => '0'
            ),
        );

        foreach ($configs as $config) {
            ensure_record_exists('module_config', (object)$config['entry'], (object)array_merge($config['entry'], array('value' => $config['value'])));
        }
    }

    return true;
}
