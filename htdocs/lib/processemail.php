<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Andrew Nicols <andrew.nicols@luns.net.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2009 Lancaster University Network Services Limited
 *                      http://www.luns.net.uk
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('TITLE', '');

require(dirname(dirname(__FILE__)).'/init.php');

$address = getenv('RECIPIENT');

log_debug('---------- started  processing email at ' . date('r', time()) . ' ----------');
log_debug('-- mail from ' . $address );

$email = process_email($address);

log_debug('---------- finished processing email at ' . date('r', time()) . ' ----------');
