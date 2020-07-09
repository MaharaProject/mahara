<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-file
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
$data['progress'] = $SESSION->get('exportprogress');
if ($data['progress'] === 'done') {
    $data['finished'] = true;
    $SESSION->set('exportprogress', false);
}
json_reply(false, array('data' => $data));