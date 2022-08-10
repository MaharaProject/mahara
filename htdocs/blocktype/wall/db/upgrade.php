<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-wall
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_blocktype_wall_upgrade($oldversion=0) {

    if ($oldversion < 2009021801) {
        set_config_plugin('blocktype', 'wall', 'defaultpostsizelimit', 1500); // 1500 characters
    }

    if ($oldversion < 2016101700) {
        $posts = get_records_select_array('blocktype_wall_post',"text LIKE '%artefact/file/download.php%'");
        require_once('embeddedimage.php');
        if ($posts) {
            foreach ($posts as $post) {
                $newtext = EmbeddedImage::prepare_embedded_images($post->text, 'wallpost', $post->id, null, $post->from);
                if ($post->text != $newtext) {
                      $updatedwallpost = new stdClass();
                      $updatedwallpost->id = $post->id;
                      $updatedwallpost->text = $newtext;
                      update_record('blocktype_wall_post', $updatedwallpost, 'id');
                }
            }
        }
    }

    return true;
}
