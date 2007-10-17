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

function upgrade_template_migration() {

    if (!$views = get_records_array('view')) {
        return;
    }


    $numbers = array(1 => 'One', 3 => 'Two', 6 => 'Three');
    $ppaetext = array(
        'Group Name', 
        'Student Names', 
        'Mission and Vision Statement (Concept and Concept Outline)', 
        'Physical Design of the space (explain, using theorists, how you created ambeince and support of holistic learning for each child)', 
        'Schedule or timetable of tasks and events prior to opening', 
        'Curriculum Matrix for first six weeks, specific activities for beginners, intermediates, end-game (provide NZCF AO/level cross-reference)', 
        'Yearlong Teaching and Learning Topics plan for beginners, intermediates and end-game.<br>(Justify your choices by citing educationalists, psychologists and other theorists)', 
        'Priorised table of equipment, materials and supplies'
    );

    foreach ($views as $view) {
        if (!$viewcolumns = update_template_get_structure($view->template)) {
            log_warn("Unsupported custom template $view->template! Skipping!");
            // @TODO do something else
            continue;
        }

        $numcolumns = count($viewcolumns);

        $ablocks = get_records_array('view_artefact', 'view', $view->id);
        $lblocks = get_records_array('view_content', 'view', $view->id);

        foreach ($lblocks as $block) {
            // @TODO make a block instance (wysiwyg)
            upgrade_template_insert_block($viewcolumns, $block->block, $block->content);
        }

        foreach ($ablocks as $block) {
            // @TODO make appropriate block instance
            try {
                upgrade_template_insert_block($viewcolumns, $block->block, $block);
            }
            catch (TemplateBlockExistsAlreadyException $e) {
                // @TODO  we're dealing with multiple artefacts in the same block... just need to append
            }
        }

        if ($view->template == 'blogreflection') {
            // we're going to end up with three new labels, made of 6
            $newlabels = array();
            for ($i = 1; $i <= 6; $i++) {
                $fromcol = 0;
                if (in_array($i, array(2, 4, 5))) {
                    $fromc = 1;
                    $text = 'Last Date Available';
                }
                else {
                    $fromc = 0;
                    $text = 'Reflection ' . $numbers[$i] . ' Title'; 
                }

                if (empty($viewcolumns[$fromc]['tpl_label' . $i])) {
                    continue;
                }

                // @TODO prepend $text . "\n" to the label contents (wysiwyg configdata?) 
                // - at the moment we're prepending it to something that's actually a block instance 
                // (or will be - see up a few lines foreach ($lblocks as $block section))
                if ($i == 2 || $i == 4) {
                    $viewcolumns[0]['tpl_label' . ($i-1)].= $text . "\n" . $viewcolumns[$fromc]['tpl_label' . $i];
                }
                else if ($i == 5) {
                    $viewcolumns[0]['tpl_label6'].= $text . "\n" . $viewcolumns[$fromc]['tpl_label' . $i];
                }
                else {
                    $viewcolumns[$fromc]['tpl_label' . $i] = $text . "\n" . $viewcolumns[$fromc]['tpl_label'] . $i;
                }
            }
        }
        else if ($view->template == 'PPAE') {
            if (!empty($viewcolumns[0]['tpl_label1'])) {
                $viewcolumns[0]['tpl_label1'] = $ppaetext[0] . $viewcolumns[0]['tpl_label1'];
            }
            if (!empty($viewcolumns[0]['tpl_label2']) || !empty($viewcolumns[0]['tpl_label3']) || !empty($viewcolumns[0]['tpl_label4']) || !empty($viewcolumns[0]['tpl_label5'])) {
                // mash it all into the first one and unset the rest // @TODO maybe "\n" should be <br> here, we'll know when we actually start running this and fix all the TODOs.
                $viewcolumns[0]['tpl_label2'] = $ppaetext[1] . "\n" . $viewcolumns[0]['tpl_label3'] . "\n" . $viewcolumns[0]['tpl_label4'] . "\n" . $viewcolumns[0]['tpl_label5'];
                unset($viewcolumns[0]['tpl_label3']);
                unset($viewcolumns[0]['tpl_label4']);
                unset($viewcolumns[0]['tpl_label5']);
            }
            if (!empty($viewcolumns[0]['tpl_blog1'])) {
                $viewcolumns[0]['tpl_converted1'] = $ppaetext[2]; // @TODO wysiwyg block instance
            }
            if (!empty($viewcolumns[0]['tpl_files1']) || !empty($viewcolumns[0]['tpl_blog2'])) {
                $viewcolumns[0]['tpl_converted2'] = $ppaetext[3]; // @TODO wysiwyg block instance
            }
            if (!empty($viewcolumns[0]['tpl_files2']) || !empty($viewcolumns[0]['tpl_blog3'])) {
                $viewcolumns[0]['tpl_converted3'] = $ppaetext[4]; // @TODO wysiwyg block instance
            }
            if (!empty($viewcolumns[0]['tpl_files3']) || !empty($viewcolumns[0]['tpl_blog4'])) {
                $viewcolumns[0]['tpl_converted4'] = $ppaetext[5]; // @TODO wysiwyg block instance
            }
            if (!empty($viewcolumns[0]['tpl_files4']) || !empty($viewcolumns[0]['tpl_blog5'])) {
                $viewcolumns[0]['tpl_converted5'] = $ppaetext[6]; // @TODO wysiwyg block instance
            }
            if (!empty($viewcolumns[0]['tpl_files5']) || !empty($viewcolumns[0]['tpl_blog6'])) {
                $viewcolumns[0]['tpl_converted6'] = $ppaetext[7]; // @TODO wysiwyg block instance
            }
        }
        
        // clean up empty columns 
        foreach ($viewcolumns as $c => $col) {
            $empty = true;
            foreach ($blocks as $key => $guff) {
                if (!empty($guff)) {
                    $empty = false;
                }
            }
            if ($empty) {
                $numcolumns--;
                unset($viewcolumns[$c]);
            }
        }
        // make all the block instances have the correct column and order (danger!)
        foreach ($viewcolumns as $c => $col) {
            $count = 0;
            foreach ($blocks as $key => $data) {
                $block->set('column', $c);
                $block->set('order', $count);
                $block->commit();
                $count++;
            }
        }

        $view = new View($view->id);
        $view->set('numcolumns', $numcolumns);
        $view->commit();
    }
}

function upgrade_template_insert_block(&$columns, $key, $data) {
    foreach ($columns as &$c) {
        if (array_key_exists($key, $c)) {
            if (!empty($c[$key])) {
                $e = new TemplateBlockExistsAlreadyException();
                $e->set_block_data($c[$key]);
                throw $e;
            }
            $c[$key] = $data;
            return;
        }
    }
}

function update_template_get_structure($template) {

    static $columnstructure;
    if (empty($columnstructure)) {

        $columnstructure = array(
            'blogandprofile' => array(
                array(
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
