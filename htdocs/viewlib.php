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

/**
 * Returns HTML for the category list
 *
 * @param string $defaultcategory The currently selected category
 * @param bool   $javascript Set to true if the caller is a json script, 
 *                           meaning that nothing for the standard HTML version 
 *                           alone should be output
 */
function views_build_category_list($defaultcategory, $javascript=false) {
    // TODO: This data structure needs to be sourced from the database
    $categories = array(
        array(
            'name' => 'aboutme',
            'title' => 'About Me',
        ),
        array(
            'name' => 'blogs',
            'title' => 'Blogs',
        ),
        array(
            'name' => 'filesandfolders',
            'title' => 'Files and Folders',
        ),
        array(
            'name' => 'general',
            'title' => 'General',
        ),
        array(
            'name' => 'system',
            'title' => 'System Blocks',
        ),
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
function views_build_blocktype_list($category, $javascript=false) {
    // TODO: This data structure needs to be sourced from the database
    $blocktypes = array(
        array(
            'id'             => 1,
            'name'           => 'blocktype1',
            'title'          => 'Block Type ' . $category,
            'description'    => 'This is the description for block type 1',
            'thumbnail_path' => 'theme/default/static/images/no_thumbnail.gif',
        ),
        array(
            'id'             => 2,
            'name'           => 'blocktype2',
            'title'          => 'Block Type ' . $category,
            'description'    => 'This is the description for block type 2',
            'thumbnail_path' => 'theme/default/static/images/no_thumbnail.gif',
        ),
        array(
            'id'             => 3,
            'name'           => 'blocktype3',
            'title'          => 'Block Type ' . $category,
            'description'    => 'This is the description for block type 3',
            'thumbnail_path' => 'theme/default/static/images/no_thumbnail.gif',
        ),
        array(
            'id'             => 4,
            'name'           => 'blocktype4',
            'title'          => 'Block Type ' . $category,
            'description'    => 'This is the description for block type 4',
            'thumbnail_path' => 'theme/default/static/images/no_thumbnail.gif',
        ),
    );

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
        $radio = ($javascript) ? '' : '<input type="radio" class="blocktype-radio" name="blocktype" value="' . $blocktype['id'] . '">';
        $blocktypehtml = str_replace('{RADIO}', $radio, $blocktypehtml);

        $result .= $blocktypehtml;
    }
    $result .= "\n</ul>";

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
