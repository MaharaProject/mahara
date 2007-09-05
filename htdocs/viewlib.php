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
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * NOTE:
 *
 * This file contains functions for the new views interface. It is intended to 
 * be merged with/moved to lib/views.php eventually
 */
defined('INTERNAL') || die;

//
// This is a testing data structure for this library. Certain functions need to 
// have database calls in them instead. Just look for $COLUMNS in this file.
//
$COLUMNS = array(
    'columns' => array(
        // First column
        1 => array(
            'blockinstances' => array(
                array(
                    'id'           => 1,
                    'title'        => 'Block Instance 1',
                    'content'      => '
                    <div class="fl" style="margin-right: 1em;">
                        <img src="theme/default/static/images/no_userphoto100x100.gif" alt="Profile Image">
                    </div>
                    <h4>Nigel McNie</h4>
                    <ul style="list-style-type: none;">
                        <li><strong>Personal Website:</strong> <a href="http://nigel.mcnie.name/">nigel.mcnie.name</a></li>
                        <li><strong>City:</strong> Wellington</li>
                        <li><strong>Occupation:</strong> Engineer</li>
                    </ul>
                    <div style="clear:both;"></div>',
                    'canmoveleft'  => false,
                    'canmoveright' => true,
                    'canmoveup'    => false,
                    'canmovedown'  => true,
                ),
                array(
                    'id'           => 2,
                    'title'        => 'Block Instance 2',
                    'content'      => '
                    <div class="fl" style="margin-right: 1em;">
                        <img src="theme/default/static/images/no_userphoto100x100.gif" alt="Profile Image">
                    </div>
                    <h4>Nigel McNie</h4>
                    <ul style="list-style-type: none;">
                        <li><strong>Personal Website:</strong> <a href="http://nigel.mcnie.name/">nigel.mcnie.name</a></li>
                        <li><strong>City:</strong> Wellington</li>
                        <li><strong>Occupation:</strong> Engineer</li>
                    </ul>
                    <div style="clear:both;"></div>',
                    'canmoveleft'  => false,
                    'canmoveright' => true,
                    'canmoveup'    => true,
                    'canmovedown'  => false,
                ),
            ),
        ),
        // Second column
        2 => array(
            'blockinstances' => array(
                array(
                    'id'           => 3,
                    'title'        => 'Block Instance 3',
                    'content'      => '
                                        <h4>Recent Blog Posts for \'My Holiday in Scotland\'</h4>
                                        <ul>
                                            <li><a href="">Edinburgh is a dangerous place</a></li>
                                            <li><a href="">I thought I saw Gordon!</a></li>
                                            <li><a href="">There\'s no monster at Loch Ness</a></li>
                                            <li><a href="">Synchronicity II</a></li>
                                            </ul>',
                    'canmoveleft'  => true,
                    'canmoveright' => true,
                    'canmoveup'    => false,
                    'canmovedown'  => true,
                ),
                array(
                    'id'           => 4,
                    'title'        => 'Block Instance 4',
                    'content'      => '
                                        <h4>Recent Blog Posts for \'My Holiday in Scotland\'</h4>
                                        <ul>
                                            <li><a href="">Edinburgh is a dangerous place</a></li>
                                            <li><a href="">I thought I saw Gordon!</a></li>
                                            <li><a href="">There\'s no monster at Loch Ness</a></li>
                                            <li><a href="">Synchronicity II</a></li>
                                            </ul>',
                    'canmoveleft'  => true,
                    'canmoveright' => true,
                    'canmoveup'    => true,
                    'canmovedown'  => true,
                ),
                array(
                    'id'           => 5,
                    'title'        => 'Block Instance 5',
                    'content'      => '
                                        <h4>Recent Blog Posts for \'My Holiday in Scotland\'</h4>
                                        <ul>
                                            <li><a href="">Edinburgh is a dangerous place</a></li>
                                            <li><a href="">I thought I saw Gordon!</a></li>
                                            <li><a href="">There\'s no monster at Loch Ness</a></li>
                                            <li><a href="">Synchronicity II</a></li>
                                            </ul>',
                    'canmoveleft'  => true,
                    'canmoveright' => true,
                    'canmoveup'    => true,
                    'canmovedown'  => false,
                ),
            ),
        ),
        // Third column
        3 => array(
            'blockinstances' => array(
                array(
                    'id'           => 6,
                    'title'        => 'Block Instance 6',
                    'content'      => 'The time is now <strong>' . date('h:i:s a') . '</strong>',
                    'canmoveleft'  => true,
                    'canmoveright' => false,
                    'canmoveup'    => false,
                    'canmovedown'  => true,
                ),
                array(
                    'id'           => 7,
                    'title'        => 'Block Instance 7',
                    'content'      => 'The date is now <strong>' . date('d/m/Y') . '</strong>',
                    'canmoveleft'  => true,
                    'canmoveright' => false,
                    'canmoveup'    => true,
                    'canmovedown'  => false,
                ),
            ),
        ),
    ),
    'count' => 3
);



/**
 * Returns HTML for the category list
 *
 * @param string $defaultcategory The currently selected category
 * @param bool   $javascript Set to true if the caller is a json script, 
 *                           meaning that nothing for the standard HTML version 
 *                           alone should be output
 */
function view_build_category_list($defaultcategory, $javascript=false) {
    require_once(get_config('docroot') . '/blocktype/lib.php');
    $cats = get_records_array('blocktype_category');
    $categories = array_map(
        create_function(
            '$a', 
            '$a = $a->name;
            return array(
                "name" => $a, 
                "title" => call_static_method("PluginBlockType", "category_title_from_name", $a),
            );'
        ),
        $cats
    );

    $result = "<ul>\n";
    $flag = false;
    foreach ($categories as &$cat) {
        $classes = '';
        if (!$flag) {
            $flag = true;
            $classes[] = 'first';
        }
        if ($defaultcategory == $cat['name']) {
            $classes[] = 'current';
        }

        $result .= '<li';
        if ($classes) {
            $result .= ' class="' . hsc(implode(' ', $classes)) . '"';
        }
        $result .= '<a href="viewrework.php?category=' . hsc($cat['name']) . '">' . hsc($cat['title']) . "</a></li>\n";
    }
    $result .= "</ul>\n";

    return $result;
}

/**
 * Returns HTML for the blocktype list for a particular category
 *
 * @param string $category   The category to build the blocktype list for
 * @param bool   $javascript Set to true if the caller is a json script, 
 *                           meaning that nothing for the standard HTML version 
 *                           alone should be output
 */
function view_build_blocktype_list($category, $javascript=false) {

    require_once(get_config('docroot') . 'blocktype/lib.php');
    if (!$blocktypes = PluginBlockType::get_blocktypes_for_category($category)) {
        return '';
    }

    $template =<<<EOF
    <li>
        <img src="{THUMBNAIL_PATH}" alt="Preview">
        <h3>{TITLE}</h3>
        <p>{DESCRIPTION}</p>
        {RADIO}
    </li>
EOF;

    $result = '<ul>';
    foreach ($blocktypes as $blocktype) {
        $blocktypehtml = $template;
        //$blocktypehtml = str_replace('{ID}', $blocktype['id'], $blocktypehtml);
        $blocktypehtml = str_replace('{TITLE}', hsc($blocktype['title']), $blocktypehtml);
        $blocktypehtml = str_replace('{DESCRIPTION}', format_whitespace(hsc($blocktype['description'])), $blocktypehtml);
        $blocktypehtml = str_replace('{THUMBNAIL_PATH}', hsc($blocktype['thumbnail_path']), $blocktypehtml);
        $radio = ($javascript) ? '' : '<input type="radio" class="blocktype-radio" name="blocktype" value="' . $blocktype['name'] . '">';
        $blocktypehtml = str_replace('{RADIO}', $radio, $blocktypehtml);

        $result .= $blocktypehtml;
    }
    $result .= "\n</ul>";

    return $result;
}

/**
 * Returns the HTML for the columns of a particular views
 *
 * @param int  $view       The view to build the columns for
 * @param bool $javascript Set to true if the caller is a json script, 
 *                         meaning that nothing for the standard HTML version 
 *                         alone should be output
 */
function view_build_columns($view, $javascript=false) {
    global $COLUMNS;
    $numcols = $COLUMNS['count'];

    $result = '';
    for ($i = 1; $i <= $numcols; $i++) {
        $result .= view_build_column($view, $i, $javascript);
    }

    return $result;
}

/**
 * Returns the HTML for a particular view column
 *
 * @param int  $view       The view to build the column for
 * @param int  $column     The column to build
 * @param bool $javascript Set to true if the caller is a json script, 
 *                         meaning that nothing for the standard HTML version 
 *                         alone should be output
 */
function view_build_column($view, $column, $javascript=false) {
    global $COLUMNS;
    // FIXME: TEMPORARY. Just so if we're adding a new column, we can insert a blank one
    if ($javascript) {
        $data = array('blockinstances' => array());
    }
    else {
        $data = $COLUMNS['columns'][$column];
    }

    $result = '';

    $result = '<div id="column_' . $column . '" class="column columns' . $COLUMNS['count'] . '">
    <div class="column-header">';

    if ($column == 1) {
        $result .= '    <div class="add-column-left">
        <input type="submit" class="submit addcolumn" name="action_add_column_before_1" value="Add Column">
    </div>';
    }

    $result .= '    <div class="remove-column">
        <input type="submit" class="submit removecolumn" name="action_remove_column_' . $column . '" value="Remove Column">
    </div>';

    if ($column == $COLUMNS['count']) {
        $result .= '    <div class="add-column-right">
        <input type="submit" class="submit addcolumn" name="action_add_column_before_' . ($column + 1) . '" value="Add Column">
    </div>';
    }
    else {
        $result .= '    <div class="add-column-center">
        <input type="submit" class="submit addcolumn" name="action_add_column_before_' . ($column + 1) . '" value="Add Column">
    </div>';
    }

    $result .= '
    </div>
    <div class="column-content">';
    if (!$javascript) {
        $result .= '        <div class="add-button">
            <input type="submit" class="submit newblockhere" name="action_blocktype_add_top_' . $column . '" value="Add new block here">
        </div>';
    }

    // Blocktype loop here
    foreach($data['blockinstances'] as $blockinstance) {
        $result .= '    <div class="blockinstance" id="blockinstance_' . $blockinstance['id'] . '">
    <div class="blockinstance-header">
        <h4>' . hsc($blockinstance['title']) . '</h4>
    </div>
    <div class="blockinstance-controls">';

        if (!$javascript) {
            // FIXME loop pls!
            if ($blockinstance['canmoveleft']) {
                $result .= '<input type="submit" class="submit movebutton" name="blockinstance_' . $blockinstance['id'] . '_moveleft" value="&larr;">';
            }
            if ($blockinstance['canmovedown']) {
                $result .= '<input type="submit" class="submit movebutton" name="blockinstance_' . $blockinstance['id'] . '_movedown" value="&darr;">';
            }
            if ($blockinstance['canmoveup']) {
                $result .= '<input type="submit" class="submit movebutton" name="blockinstance_' . $blockinstance['id'] . '_moveup" value="&uarr;">';
            }
            if ($blockinstance['canmoveright']) {
                $result .= '<input type="submit" class="submit movebutton" name="blockinstance_' . $blockinstance['id'] . '_moveright" value="&rarr;">';
            }
        }
        $result .= '<input type="submit" class="submit deletebutton" name="blockinstance_' . $blockinstance['id'] .'_delete" value="X">';

        $result .= '        </div>
        <div class="blockinstance-content">
            ' . $blockinstance['content'] . '
        </div>
    </div>';
        if (!$javascript) {
            $result .= '
    <div class="add-button">
        <input type="submit" class="submit newblockhere" name="action_blocktype_add_after_' . $blockinstance['id'] . '" value="Add new block here">
    </div>';
        }
    }

    $result .= '    </div>
</div>';

    return $result;
}

function view_process_changes() {
    global $SESSION;

    if (!count($_POST)) {
        return;
    }
    log_debug($_POST);
    $view = param_integer('view');

    $action = '';
    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 7) == 'action_') {
            $action = substr($key, 7);
        }
        else {
            $data[$key] = $value;
        }
    }

    $value = view_get_value_for_action($action);

    $result = null;
    if (starts_with($action, 'blocktype_add_top')) {
        // Done as "add_top" so that block instances can be added to columns with nothing in them
        $blocktype = param_integer('blocktype', 0);
        if (!$blocktype) {
            $SESSION->add_info_msg('Please select a block type to add first');
            return;
        }

        $result = view_blocktype_add_top($view, $blocktype, $value);
        $okmsg  = 'Added block type successfully';
        $errmsg = 'Could not add the block to your view';
    }
    else if (starts_with($action, 'blocktype_add_after')) {
        $blockinstance = view_get_value_for_action($action);
        $blocktype = param_integer('blocktype', 0);
        if (!$blocktype) {
            $SESSION->add_info_msg('Please select a block type to add first');
            return;
        }

        $result = view_blocktype_add_after($view, $blocktype, $value);
        $okmsg  = 'Added block type successfully';
        $errmsg = 'Could not add the block to your view';
    }
    else if (starts_with($action, 'add_column_before')) {
        $result = false;
        $okmsg  = '';
        $errmsg = 'Not implemented yet';
    }
    else if (starts_with($action, 'remove_column')) {
        $column = view_get_value_for_action($action);

        log_debug("Remove column " . $column);
        if (view_remove_column($view, $column)) {
            $SESSION->add_ok_msg('Removed column successfully');
        }
        else {
            $SESSION->add_ok_msg('Failed to remove column');
        }
        return;
    }

    if (!is_null($result)) {
        if ($result) {
            $SESSION->add_ok_msg($okmsg);
        }
        else {
            $SESSION->add_error_msg($errmsg);
        }
        redirect('/viewrework.php');
    }

    throw new UserException('No valid action found');
}

function starts_with($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) == $needle;
}

function view_get_value_for_action($action) {
    $value = intval(substr($action, strrpos($action, '_') + 1));
    if ($value == 0) {
        throw new UserException('Value for action is not valid');
    }
    return $value;
}

function view_assert_data($data, $key) {
    if (!isset($data[$key])) {
        throw new UserException('The value for "' . $key . '" is not available for this action');
    }
}



function view_blocktype_add_top($view, $blocktype, $column) {
    // Stub
    log_debug("Add block type " . $blocktype . ' to the top of column ' . $column);
    return true;
}

function view_blocktype_add_after($view, $blocktype, $blockinstance) {
    // Stub
    log_debug("Add block type " . $blocktype . ' below blockinstance ' . $blockinstance);
    return true;
}

function view_add_column($view, $column) {
    // Stub
    log_debug('Adding column before current column ' . $column);
    return true;
}

function view_remove_column($view, $column) {
    // Stub
    log_debug('Removing column ' . $column . ' from view ' . $view);
    return true;
}

?>
