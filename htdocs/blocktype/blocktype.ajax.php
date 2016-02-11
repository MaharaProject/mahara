<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('JSON', 1);
define('NOSESSKEY', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require($CFG->docroot.'/blocktype/lib.php');

// Close the session to prevent session locking.
session_write_close();

$blockid = param_integer('blockid');
$block = new BlockInstance($blockid);
if (!can_view_view($block->get('view'))) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

safe_require_plugin('blocktype', $block->get('blocktype'));
echo call_static_method(generate_class_name('blocktype', $block->get('blocktype')), 'render_instance', $block);
