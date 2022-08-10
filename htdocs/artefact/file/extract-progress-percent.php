<?php
/**
 * Helper to adjust manage the state when extracting a file artefact
 *
 * @package    mahara
 * @subpackage artefact-file
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
$data['progress'] = $SESSION->get('unzipprogress');
if ($data['progress'] === 'done') {
    $data['finished'] = true;
    $SESSION->set('unzipprogress', false);
}
json_reply(false, array('data' => $data));
