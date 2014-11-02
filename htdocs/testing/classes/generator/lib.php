<?php
/**
 * @package    mahara
 * @subpackage test/generator
 * @author     Son Nguyen, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  portions from Moodle 2012, Petr Skoda {@link http://skodak.org}
 *
 */

/**
 * Adds data generator support
 *
 */

// NOTE: MOODLE_INTERNAL is not verified here because we load this before setup.php!

require_once(__DIR__ . '/TestingDataGenerator.php');
require_once(__DIR__ . '/DataGeneratorBase.php');
