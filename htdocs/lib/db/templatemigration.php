<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage core
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();
require_once('view.php');
safe_require('blocktype', 'textbox'); // need this for labels, always

/**
 * Performs the template migration, from the old view template style of 0.8 to 
 * the new views format for 0.9
 */
function upgrade_template_migration() {
    log_debug('upgrade_template_migration()');

    if (!$views = get_records_array('view', '', '', 'id')) {
        log_debug('no views to migrate');
        return;
    }
    log_debug('we have ' . count($views) . ' views to migrate...');


    //
    // Static data needed by various templates when they are migrated
    //

    // blogreflection
    $blogreflection_numbers = array(1 => 'One', 2 => 'One', 3 => 'Two', 4 => 'Two', 5 => 'Three', 6 => 'Three');

    // ppae
    $ppae_text = array(
        '<h4>Group Name</h4>', 
        '<h4>Student Names</h4>', 
        '<h4>Mission and Vision Statement (Concept and Concept Outline)</h4>', 
        '<h4>Physical Design of the space</h4><p>Explain, using theorists, how you created ambeince and support of holistic learning for each child</p>', 
        '<h4>Schedule or timetable of tasks and events prior to opening</h4>', 
        '<h4>Curriculum Matrix for first six weeks, specific activities for beginners, intermediates, end-game</h4><p>provide NZCF AO/level cross-reference</p>', 
        '<h4>Yearlong Teaching and Learning Topics plan for beginners, intermediates and end-game.</h4><p>Justify your choices by citing educationalists, psychologists and other theorists</p>', 
        '<h4>Priorised table of equipment, materials and supplies</h4>'
    );


    // The main view migration loop
    $count = 0;
    foreach ($views as $view) {
        $count++;
        log_debug("Migrating view $view->id ($count)");
        if ($view->template == 'test') {
            // We are NOT migrating test templates. There's not much point. The 
            // only real data loss from this is the labels, which are only one 
            // line text inputs. If this annoys someone they could always have 
            // a go at writing a migration. There's no technical reason why one 
            // couldn't be done.
            log_debug("View $view->id is a test view. Deleting instead");
            $view = new View($view->id);
            delete_records('view_content', 'view', $view->get('id'));
            $view->delete();
            continue;
        }

        //
        // $viewcolumns is a datastructure representing the new structure of a 
        // view. It's an array of columns, which are arrays of label titles etc.
        // All the values are null.
        //
        // We are trying to turn the nulls into blockinstances
        //
        if (!$viewcolumns = upgrade_template_get_structure($view->template)) {
            log_warn("Unsupported custom template $view->template! Deleting!");
            $view = new View($view->id);
            delete_records('view_content', 'view', $view->get('id'));
            $view->delete();
            continue;
        }
        $numcolumns = count($viewcolumns);

        // Temporary, testing the migration of certain templates only
        if ($view->template != 'PPAE') {
            //log_debug('skipping template, it is not blogreflection');
            continue;
        }

        // Handle all label blocks, by converting them to WYSIWYG blocks
        if ($lblocks = get_records_array('view_content', 'view', $view->id)) {
            foreach ($lblocks as $block) {
                upgrade_template_insert_block($viewcolumns, $block->block, upgrade_template_create_wysiwyg($block->content, $view->id));
            }
        }

        // Get all artefact blocks in the view
        if ($ablocks = get_records_sql_array('
            SELECT va.*, a.artefacttype, a.title
            FROM {view_artefact} va
            INNER JOIN {artefact} a ON (va.artefact = a.id)
            WHERE "view" = ?', array($view->id))) {
            foreach ($ablocks as $block) {
                if (!upgrade_template_block_exists($viewcolumns, $block->oldblock)) {
                    // There's no block here. We can make one and insert it
                    $bi = upgrade_template_convert_block_to_blockinstance($block, $view->id);
                    // note: the location to insert the block is known as 
                    // 'oldblock', as the column as been renamed in the database 
                    // in preparation for its removal
                    upgrade_template_insert_block($viewcolumns, $block->oldblock, $bi);
                }
                else {
                    // We're dealing with multiple artefacts in the same block... just need to append
                    upgrade_template_update_block($viewcolumns, $block);
                }
            }
        }

        //
        // Special case code for each template, to make sure the labels and 
        // hard coded text are migrated nicely
        //
        if ($view->template == 'blogreflection') {
            // There are six labels in this view - the three reflection titles 
            // and three 'last date available' fields
            for ($i = 1; $i <= 6; $i++) {
                // The labels were badly numbered, 2, 4 and 5 are the right 
                // hand side (and thus the 'last date available' ones)
                if (in_array($i, array(2, 4, 5))) {
                    $column = 1;
                    $text = '<h4>Reflection ' . $blogreflection_numbers[$i] . ': Last Date Available</h4>';
                }
                else {
                    $column = 0;
                    $text = '<h3>Reflection ' . $blogreflection_numbers[$i] . ' Title</h3>'; 
                }

                // The label migration would have put WYSIWYG blocks in where 
                // there was content. If there is none, let's ignore this block
                if (empty($viewcolumns[$column]['tpl_label' . $i])) {
                    continue;
                }

                $labelkey = 'tpl_label' . $i;
                $replace = $text . '<p>' . upgrade_template_get_wysiwyg_content($viewcolumns, $column, 'tpl_label' . $i) . '</p>';

                // Update the WYSIWYG blocks to have the titles they need
                if (in_array($i, array(2, 4, 5))) {
                    upgrade_template_update_wysiwyg($viewcolumns, $column, $labelkey, null, $replace);
                }
                else {
                    upgrade_template_update_wysiwyg($viewcolumns, $column, 'tpl_label' . $i, null, $replace);
                }
            }
        }
        else if ($view->template == 'PPAE') {
            if (!empty($viewcolumns[0]['tpl_label1'])) {
                log_debug('tpl_label1 is not empty, assuming it is a wysiwyg and updating its content');
                upgrade_template_update_wysiwyg($viewcolumns, 0, 'tpl_label1', null, '<h4>' . $ppae_text[0] . '</h4>' . upgrade_template_get_wysiwyg_content($viewcolumns, 0, 'tpl_label1'));
            }
            if (!empty($viewcolumns[0]['tpl_label2']) || !empty($viewcolumns[0]['tpl_label3']) || !empty($viewcolumns[0]['tpl_label4']) || !empty($viewcolumns[0]['tpl_label5'])) {
                // mash it all into the first one and unset the rest 
                log_debug('assuming tpl_label2 is a wysiwyg');
                $label2_text = '<h4>' . $ppae_text[1] . '</h4>'
                    . upgrade_template_get_wysiwyg_content($viewcolumns, 0, 'tpl_label2') . '<br>' 
                    . upgrade_template_get_wysiwyg_content($viewcolumns, 0, 'tpl_label3') . '<br>' 
                    . upgrade_template_get_wysiwyg_content($viewcolumns, 0, 'tpl_label4') . '<br>'
                    . upgrade_template_get_wysiwyg_content($viewcolumns, 0, 'tpl_label5');

                $label2_text = preg_replace('#(<br>)+$#', '', $label2_text);

                if (upgrade_template_block_exists($viewcolumns, 'tpl_label2')) {
                    upgrade_template_update_wysiwyg($viewcolumns, 0, 'tpl_label2', null, $label2_text);
                }
                else {
                    upgrate_template_insert_block($viewcolumns, 'tpl_label2', upgrade_template_create_wysiwyg($label2_text, $view->id));
                }
                unset($viewcolumns[0]['tpl_label3']);
                unset($viewcolumns[0]['tpl_label4']);
                unset($viewcolumns[0]['tpl_label5']);
            }
            if (!empty($viewcolumns[0]['tpl_blog1'])) {
                $viewcolumns[0]['tpl_converted1'] = upgrade_template_create_wysiwyg($ppae_text[2], $view->id);
            }
            if (!empty($viewcolumns[0]['tpl_files1']) || !empty($viewcolumns[0]['tpl_blog2'])) {
                $viewcolumns[0]['tpl_converted2'] = upgrade_template_create_wysiwyg($ppae_text[3], $view->id);
            }
            if (!empty($viewcolumns[0]['tpl_files2']) || !empty($viewcolumns[0]['tpl_blog3'])) {
                $viewcolumns[0]['tpl_converted3'] = upgrade_template_create_wysiwyg($ppae_text[4], $view->id);
            }
            if (!empty($viewcolumns[0]['tpl_files3']) || !empty($viewcolumns[0]['tpl_blog4'])) {
                $viewcolumns[0]['tpl_converted4'] = upgrade_template_create_wysiwyg($ppae_text[5], $view->id);
            }
            if (!empty($viewcolumns[0]['tpl_files4']) || !empty($viewcolumns[0]['tpl_blog5'])) {
                $viewcolumns[0]['tpl_converted5'] = upgrade_template_create_wysiwyg($ppae_text[6], $view->id);
            }
            if (!empty($viewcolumns[0]['tpl_files5']) || !empty($viewcolumns[0]['tpl_blog6'])) {
                $viewcolumns[0]['tpl_converted6'] = upgrade_template_create_wysiwyg($ppae_text[7], $view->id);
            }
        }
        
        // Clean up empty columns 
        foreach ($viewcolumns as $c => $col) {
            $empty = true;
            foreach ($col as $key => $guff) {
                if (!empty($guff)) {
                    $empty = false;
                }
            }
            if ($empty) {
                log_debug("Column $c is empty");
                $numcolumns--;
                unset($viewcolumns[$c]);
            }
        }
        log_debug("Final number of columns in view: $numcolumns");

        // Make all the block instances have the correct column and order 
        foreach ($viewcolumns as $c => $col) {
            $order = 1;
            foreach ($col as $key => $block) {
                if ($block instanceof BlockInstance) {
                    $block->set('column', ($c + 1));
                    $block->set('order', $order);
                    $block->commit();
                    $order++;
                }
            }
        }

        // Work out what layout to set the view to
        $layout = upgrade_template_get_view_layout($view->template);

        // Commit the view!
        $view = new View($view->id);
        $view->set('numcolumns', $numcolumns);
        if ($layout) {
            $view->set('layout', $layout);
        }
        $view->commit();
    } // End of the view loop
}

/**
* Puts blockinstances into the appropriate place in the deeply nested array
*
* @param array (reference) $columns column structure
* @param string            $key     key to insert blockinstance at
* @param BlockInstance     $bi      blockinstance to insert
*/
function upgrade_template_insert_block(&$columns, $key, BlockInstance $bi) {
    log_debug('upgrade_template_insert_block() for key ' . $key);
    foreach ($columns as &$c) {
        if (array_key_exists($key, $c)) {
            if (!empty($c[$key])) {
                $e = new TemplateBlockExistsAlreadyException();
                $e->set_block_data($c[$key]);
                throw $e;
            }
            $c[$key] = $bi;
            return;
        }
    }
}

/**
 * Takes a block that, according to its oldblock setting, wants to be inserted 
 * somewhere where there is an existing blockinstance.  With this information, 
 * establishes how to change the existing blockinstance, or what to replace it 
 * with, so that both artefacts are in the same blockinstance.
 *
 * @param array (reference) $columns column structure
 * @param stdClass          $block   the new block data
 */
function upgrade_template_update_block(&$columns, $block) {
    // $block->oldblock is where the existing blockinstance is
    // Then we need to establish what to put in $columns, or in the blockinstance
    $bi = null;
    foreach ($columns as &$c) {
        if (array_key_exists($block->oldblock, $c)) {
            if (!empty($c[$block->oldblock])) {
                $bi = $c[$block->oldblock];
            }
        }
    }

    if (empty($bi)) {
        log_debug("WTF: tried to update a block when there was nothing there to update");
        return;
    }

    // If the blockinstance is a filedownload block and we have a file or image 
    // to add, add it directly to the blockinstance
    if ($bi->get('blocktype') == 'filedownload') {
        if ($block->artefacttype == 'file' || $block->artefacttype == 'image') {
            $configdata = $bi->get('configdata');
            $configdata['artefactids'][] = $block->artefact;
            $bi->set('configdata', $configdata);
        }
    }

}

/**
 * Determines whether a block already exists at the given location
 *
 * @param array (reference) $columns column structure
 * @param string            $key     key to check for existance
 */
function upgrade_template_block_exists(&$columns, $key) {
    foreach ($columns as &$c) {
        if (array_key_exists($key, $c)) {
            if (!empty($c[$key])) {
                return true;
            }
        }
    }
    return false;
}

/**
 * Creates a WYSIWYG blockinstance
 *
 * @param string $content The content for the wysiwyg block
 * @param int $view The view the new block will be in
 */
function upgrade_template_create_wysiwyg($content, $view) {
    $b = new BlockInstance(0, array(
        'title'      => '',
        'blocktype'  => 'textbox',
        'configdata' => serialize(array('text' => $content)),
        'view'       => $view,
    ));

    return $b;
}

/**
 * Given a location with a WYSIWYG blockinstance, either appends or replaces its content
 */
function upgrade_template_update_wysiwyg(&$columns, $column, $key, $appendcontent=null, $replacecontent=null) {
    $block = $columns[$column][$key];
    $data = $block->get('configdata');
    if (!empty($appendcontent)) {
        $data['text'] .= $appendcontent;
    }
    else {
        $data['text'] = $replacecontent;
    }
    $block->set('configdata', $data);
}

/**
 * Gets content of an existing WYSIWYG blockinstance, or an empty string if the 
 * block is empty
 */
function upgrade_template_get_wysiwyg_content($columns, $column, $key) {
    $block = $columns[$column][$key];
    if (empty($block)) {
        return '';
    }
    $data = $block->get('configdata');
    return $data['text'];
}

/**
 * Get the new view structure for the given template.
 *
 * @param string $template The template to get the structure for
 * @return array
 */
function upgrade_template_get_structure($template) {

    static $columnstructure;
    if (empty($columnstructure)) {

        $columnstructure = array(
            'blogandprofile' => array(
                // First column
                array(
                    // Each thing in the column, from top to bottom
                    'tpl_blogslabel' => null,
                    'tpl_blog1'      => null,
                    'tpl_blog2'      => null
                ),
                array(
                    'tpl_profilelabel' => null,
                    'tpl_profile'      => null,
                ),
            ),
            'blogreflection' => array( 
                array(
                    'tpl_label1' => null,
                    'tpl_blog1'  => null,
                    'tpl_label3' => null, 
                    'tpl_blog2'  => null,
                    'tpl_label6' => null,
                    'tpl_blog3'  => null,
                ), 
                array(
                    'tpl_label2' => null,
                    'tpl_label4' => null,
                    'tpl_label5' => null,
                ), // I know it looks like 5 and 6 are backwards, it's in the template html
            ),
            'filelist' => array(
                array(
                    'tpl_fileslabel1' => null,
                    'tpl_files1'      => null,
                    'tpl_fileslabel2' => null,
                    'tpl_files2'      => null,
                    'tpl_fileslabel3' => null,
                    'tpl_files3'      => null,
                ),
                array(
                    'tpl_freelabel' => null
                ),
            ),
            'gallery' => array(
                array(
                    'tpl_image1' => null,
                    'tpl_label1' => null,
                    'tpl_image6' => null,
                    'tpl_label6' => null,
                ),
                array(
                    'tpl_image2' => null,
                    'tpl_label2' => null, 
                    'tpl_image7' => null,
                    'tpl_label7' => null,
                ),
                array(
                    'tpl_image3' => null,
                    'tpl_label3' => null,
                    'tpl_image8' => null,
                    'tpl_label8' => null,
                ),
                array(
                    'tpl_image4' => null,
                    'tpl_label4' => null,
                    'tpl_image9' => null,
                    'tpl_label9' => null,
                ),
                array(
                    'tpl_image5'  => null,
                    'tpl_label5'  => null,
                    'tpl_image10' => null,
                    'tpl_label10' => null,
                ),
            ), 
            'generaltemplate' => array(
                array(
                    'tpl_label'    => null,
                    'tpl_generic1' => null,
                    'tpl_generic2' => null,
                    'tpl_generic3' => null,
                    'tpl_generic4' => null,
                ),
            ),
            'professionalprofile' => array( 
                array(
                    'tpl_firstname' => null,
                    'tpl_generic4'  => null,
                    'tpl_generic1'  => null,
                    'tpl_label1'    => null,
                    'tpl_generic2'  => null,
                ),
                array(
                    'tpl_lastname' => null,
                    'tpl_image'    => null,
                    'tpl_label2'   => null,
                    'tpl_generic3' => null,
                ),
            ),
            'PPAE' => array( 
                array(
                    // Free text here (Group Name)
                    'tpl_label1' => null,
                    // Free text here (Student Names)
                    'tpl_label2' => null,
                    'tpl_label3' => null,
                    'tpl_label4' => null, 
                    'tpl_label5' => null,
                    // Free text here (Mission.. )
                    'tpl_converted1' => null,
                    'tpl_blog1'  => null,
                    // Free text here (Physical Design.. )
                    'tpl_converted2' => null,
                    'tpl_files1' => null,
                    'tpl_blog2'  => null, 
                    // Free text here (Schedule or timetable.. )
                    'tpl_converted3' => null,
                    'tpl_files2' => null,
                    'tpl_blog3'  => null, 
                    // Free text here (Curriculum Matrix.. )
                    'tpl_converted4' => null,
                    'tpl_files3' => null,
                    'tpl_blog4'  => null,
                    // Free text here (Yearlong teaching.. )
                    'tpl_converted5' => null,
                    'tpl_files4' => null,
                    'tpl_blog5'  => null,
                    // Free text here (Prioritised table)
                    'tpl_converted6' => null,
                    'tpl_files5' => null,
                    'tpl_blog6'  => null
                ),
            ),
        );
    }

    if (!array_key_exists($template, $columnstructure)) {
        return false;
    }
    return $columnstructure[$template];
}

/**
 * Select what view layout the template should be migrated to
 */
function upgrade_template_get_view_layout($template) {
    if ($template == 'blogreflection') {
        return 2; // 67/33
    }
    return null;
}

/**
 * Given a record from the view_artefact table (otherwise known as a "block"), 
 * try and establish a blockinstance that it could be under the new system and 
 * return it
 */
function upgrade_template_convert_block_to_blockinstance($block, $view) {
    safe_require('artefact', 'resume');

    if ($block->artefacttype == 'blogpost') {
        $bi = new BlockInstance(0, array(
            'title' => $block->title,
            'blocktype' => 'blogpost',
            'configdata' => serialize(array('artefactid' => $block->artefact)),
            'view' => $view,
        ));
        return $bi;
    }
    else if ($block->artefacttype == 'blog') {
        $bi = new BlockInstance(0, array(
            'title' => $block->title,
            'blocktype' => 'blog',
            'configdata' => serialize(array('artefactid' => $block->artefact)),
            'view' => $view,
        ));
        return $bi;
    }
    else if ($block->artefacttype == 'image') {
        $bi = new BlockInstance(0, array(
            'title' => $block->title,
            'blocktype' => 'image',
            'configdata' => serialize(array('artefactid' => $block->artefact)),
            'view' => $view,
        ));
        return $bi;
    }
    else if ($block->artefacttype == 'file') {
        $bi = new BlockInstance(0, array(
            'title' => $block->title,
            'blocktype' => 'filedownload',
            'configdata' => serialize(array('artefactids' => array($block->artefact))),
            'view' => $view,
        ));
        return $bi;
    }
    else if (in_array($block->artefacttype, PluginArtefactResume::get_artefact_types())) {
        $bi = new BlockInstance(0, array(
            'title' => $block->title,
            'blocktype' => 'resumefield',
            'configdata' => serialize(array('artefactid' => $block->artefact)),
            'view' => $view,
        ));
        return $bi;
    }

    $bi = new BlockInstance(0, array(
        'title' => 'TODO - correct blocktype',
        'blocktype' => 'textbox',
        'configdata' => serialize(array('text' => 'TODO - correct blocktype')),
        'view' => $view,
    ));
    return $bi;
}

class TemplateBlockExistsAlreadyException extends MaharaException {
    
    private $blockdata;

    public function set_block_data($data) {
        $this->blockdata = $data;
    }

    public function get_block_data() {
        return $this->blockdata;
    }
}

?>
