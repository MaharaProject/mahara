<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-text
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_blocktype_text_upgrade($oldversion = 0) {

  $status = true;

  if ($oldversion < 2022101900) {
    log_debug("Re-save text blocks so their instructions are now textinstructions");
    // require_once(get_config('docroot') . 'blocktype/lib.php');
    require_once(get_config('libroot') . 'embeddedimage.php');
    $count = 0;
    $limit = 500;
    // Update the resume blocks for this person
    $blocks = get_column_sql("
      SELECT id
      FROM {block_instance}
      WHERE blocktype = ?
      AND configdata like ?
      ", array('text', '%instructions=%'));
    $total = count($blocks);
    if ($blocks) {
      safe_require('blocktype', 'text');
      foreach ($blocks as $blockid) {
        $bi = new BlockInstance($blockid);
        $configdata = $bi->get('configdata');
        // Strip out all instances of instructions=nnn
        $instructions = preg_replace('/&amp;instructions=[0-9]+/', '', $configdata['instructions']);

        $configdata['instructions'] = EmbeddedImage::prepare_embedded_images(
          $instructions,
          'textinstructions',
          $blockid
        );
        $bi->set('configdata', $configdata);
        $bi->commit();
        $count++;
        if (($count % $limit) == 0 || $count == $total) {
            log_debug("$count/$total");
            set_time_limit(30);
        }
      }
      // Clean up the old artefact_file_embedded records for 'instructions'
      $sql = '
        SELECT DISTINCT afe.id
        FROM {artefact_file_embedded} afe
        INNER JOIN {artefact_file_embedded} afe2
            ON afe2.resourceid = afe.resourceid
            AND afe2.fileid = afe.fileid
            AND afe2.resourcetype = ?
        WHERE afe.resourcetype = ?
        ';
      $ids = get_column_sql($sql, array('textinstructions', 'instructions'));
      if ($ids) {
        delete_records_select('artefact_file_embedded', 'id IN (' . join(',', $ids) . ')');
      }
    }
  }

  return $status;

}