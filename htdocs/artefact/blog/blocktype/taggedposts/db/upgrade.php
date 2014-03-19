<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-blog
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_blocktype_taggedposts_upgrade($oldversion=0) {

    if ($oldversion < 2014121700) {
        // if we have existing taggedposts blocks we will need to to update them
        if ($taggedblockids = get_column('block_instance', 'id', 'blocktype', 'taggedposts')) {
            require_once(get_config('docroot') . 'blocktype/lib.php');
            foreach ($taggedblockids as $blockid) {
                $bi = new BlockInstance($blockid);
                $configdata = $bi->get('configdata');
                if (isset($configdata['tagselect']) && !is_array($configdata['tagselect'])) {
                    $configdata['tagselect'] = array($configdata['tagselect'] => 1);
                    $bi->set('configdata', $configdata);
                    $bi->commit();
                }
            }
        }
    }

    return true;
}
