<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/*
 * Saves blocks with new layout data into the database
 */
function save_blocks_in_new_layout($viewid) {
    if ($viewid) {
        // check the view hasn't been translated yet
        $sql = " SELECT * FROM {block_instance} bi
            INNER JOIN {block_instance_dimension} bd
            ON bi.id = bd.block
            WHERE bi.view = ? ";

        if (!record_exists_sql($sql, array($viewid))) {
            // check if the view has a description,
            // then the blocks should start in row 1
            require_once('view.php');
            $view = new View($viewid);

            $newdescriptionblock = 0;
            if ($description = $view->get('description')) {
                $simpletextdescription = can_extract_description_text($description);
                if ($simpletextdescription) {
                    $description = $simpletextdescription;
                }
                else {
                    $newdescriptionblock = 1;
                }
            };

            $oldlayoutcontent = get_blocks_in_old_layout($viewid);
            $newlayoutcontent = translate_to_new_layout($oldlayoutcontent, $newdescriptionblock);

            if ($newlayoutcontent) {
                foreach ($newlayoutcontent as $block) {
                    insert_record('block_instance_dimension', (object) $block);
                }
                // if there's a page description, we need to add extra block
                if ($newdescriptionblock) {
                    require_once('view.php');
                    $view = new View($viewid);

                    $view->description_to_block();
                    //remove description from view
                    $view->set('description', '');
                    $view->commit();
                }
            }
        }
        else {
            log_debug('Grid dimensions already in DB');
        }
    }
}


/*
 * This function will take the blocks in the old layout
 * and create a structure with blocks in the new gridstack layout
 */

function translate_to_new_layout($blocks, $y=0) {
    $gridblocks = array();
    foreach ($blocks as $row) {
        $x = 0;
        $maxorder = 0;
        foreach ($row as $column) {
            if (isset($column['blocks'])) {
                foreach ($column['blocks'] as $order => $block) {
                    $gridblock = array(
                        'positionx' => $x,
                        'positiony' => $y + $order - 1,
                        'width'     => $column['width'],
                        'height'    => 1,
                    );
                    if (is_array($block)) {
                        $gridblock = array_merge($block, $gridblock);
                    }
                    else {
                        $gridblock['block'] = $block;
                    }
                    $gridblocks[] = $gridblock;
                    $maxorder = max($maxorder, $order);
                }
            }
            $x += $column['width'];
        }
        $y += $maxorder;
    }
    return $gridblocks;
}

/* Helper function to translate the column widths*/
function get_column_widths($viewid, $row) {

    // get the view's layout
    $view = new View($viewid);
    $layout = $view->get_layout();
    $widths = explode(",", $layout->rows[$row]['widths']);
    return (array_map(function($col) { return round(12 * $col/100);}, $widths));
}


/*
 * Creates a data structure to use when translating old layout to new gridstack layout
 * data structure contains the blocks from a view that still uses old layout
 */
function get_blocks_in_old_layout($viewid) {

    // get old layout structure
    $sql = "SELECT " . db_quote_identifier('row') . ", columns
        FROM {view_rows_columns}
        WHERE view = ? ORDER BY " . db_quote_identifier('row');
    $oldlayout = get_records_sql_array($sql, array($viewid));

    // get blocks in old layout
    $sql = "SELECT id, " . db_quote_identifier('row') . ", " . db_quote_identifier('column') . ", " . db_quote_identifier('order') . "
        FROM {block_instance}
        WHERE view = ?
        ORDER BY " . db_quote_identifier('row') . ", " . db_quote_identifier('column') . ", " . db_quote_identifier('order');
    $oldblocks = get_records_sql_array($sql, array($viewid));
    if ($oldlayout) {
        foreach ($oldlayout as $row) {
            $columnwidths = get_column_widths($viewid, $row->row);
            for ($i=1; $i <= $row->columns ; $i++) {
                $content[$row->row][$i]['width'] = $columnwidths[$i-1];
            }
        }
    }
    if ($oldblocks) {
        foreach ($oldblocks as $b) {
            $content[$b->row][$b->column]['blocks'][$b->order] = $b->id;
        }
    }

    return $content;
}
