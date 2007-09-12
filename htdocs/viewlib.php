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
        // @TODO fix this url (apart from anything else it needs the view id!)
        // and probably should be in a smarty template
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
 * @param View $view       The view to build the columns for
 * @param bool $javascript Set to true if the caller is a json script, 
 *                         meaning that nothing for the standard HTML version 
 *                         alone should be output
 */
function view_build_columns(View $view, $javascript=false) {
    $numcols = $view->get('numcolumns');

    $result = '';
    for ($i = 1; $i <= $numcols; $i++) {
        $result .= view_build_column($view, $i, $javascript);
    }

    return $result;
}

/**
 * Returns the HTML for a particular view column
 *
 * @param View $view    The view to build the column for
 * @param int  $column     The column to build
 * @param bool $javascript Set to true if the caller is a json script, 
 *                         meaning that nothing for the standard HTML version 
 *                         alone should be output
 */
function view_build_column(View $view, $column, $javascript=false) {
    // FIXME: TEMPORARY. Just so if we're adding a new column, we can insert a blank one
    if ($javascript) {
        $data = array('blockinstances' => array());
    }
    else {
        $data = $view->get_column_datastructure($column);
    }

    $result = '';

    $result = '<div id="column_' . $column . '" class="column columns' . $view->get('numcolumns') . '">
    <div class="column-header">';

    if ($column == 1) {
        $result .= '    <div class="add-column-left">
        <input type="submit" class="submit addcolumn" name="action_addcolumn_before_1" value="Add Column">
    </div>';
    }

    $result .= '    <div class="remove-column">
        <input type="submit" class="submit removecolumn" name="action_removecolumn_column_' . $column . '" value="Remove Column">
    </div>';

    if ($column == $view->get('numcolumns')) {
        $result .= '    <div class="add-column-right">
        <input type="submit" class="submit addcolumn" name="action_addcolumn_before_' . ($column + 1) . '" value="Add Column">
    </div>';
    }
    else {
        $result .= '    <div class="add-column-center">
        <input type="submit" class="submit addcolumn" name="action_addcolumn_before_' . ($column + 1) . '" value="Add Column">
    </div>';
    }

    $result .= '
    </div>
    <div class="column-content">';
    if (!$javascript) {
        $result .= '        <div class="add-button">
            <input type="submit" class="submit newblockhere" name="action_addblocktype_column_' . $column . '_order_1" value="Add new block here">
        </div>';
    }

    // Blocktype loop here
    foreach($data['blockinstances'] as $blockinstance) {
        $result .= '    <div class="blockinstance" id="blockinstance_' . $blockinstance->get('id') . '">
    <div class="blockinstance-header">
        <h4>' . hsc($blockinstance->get('title')) . '</h4>
    </div>
    <div class="blockinstance-controls">';

        if (!$javascript) {
            // FIXME loop pls!
            $movestart = '<input type="submit" class="submit movebutton" name="action_moveblockinstance_id_' . $blockinstance->get('id');
            if ($blockinstance->get('canmoveleft')) {
                $result .= $movestart . '_column_' . ($column - 1) . '_order_' . $blockinstance->get('order') . '" value="&larr;">';
            }
            if ($blockinstance->get('canmovedown')) {
                $result .= $movestart . '_column_' . $column . '_order_' . ($blockinstance->get('order') + 1) . '" value="&darr;">';
            }
            if ($blockinstance->get('canmoveup')) {
                $result .= $movestart . '_column_' . $column . '_order_' . ($blockinstance->get('order') - 1) . '" value="&uarr;">';
            }
            if ($blockinstance->get('canmoveright')) {
                $result .= $movestart . '_column_' . ($column + 1) . '_order_' . $blockinstance->get('order') . '" value="&rarr;">';
            }
        }
        $result .= '<input type="submit" class="submit deletebutton" name="action_removeblockinstance_id_' . $blockinstance->get('id') .'" value="X">';

        $result .= '        </div>
        <div class="blockinstance-content">
            ' . $blockinstance->render() . '
        </div>
    </div>';
        if (!$javascript) {
            $result .= '
    <div class="add-button">
        <input type="submit" class="submit newblockhere" name="action_addblocktype_column_' . $column . '_order_' . ($blockinstance->get('order') + 1) . '" value="Add new block here">
    </div>';
        }
    }

    $result .= '    </div>
</div>';

    return $result;
}


/**
 *
 * process view changes 
 * this function is used both by the json stuff and by normal posts
 *
 */
function view_process_changes() {
    global $SESSION;

    if (!count($_POST)) {
        return;
    }
    $view = param_integer('view');
    $category = param_alpha('category', null);
    $view = new View($view);

    $action = '';
    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 7) == 'action_') {
            $action = substr($key, 7);
        }
    }

    $actionstring = $action;
    $action = substr($action, 0, strpos($action, '_'));
    $actionstring  = substr($actionstring, strlen($action) + 1);
    
    $values = view_get_values_for_action($actionstring);

    $result = null;
    switch ($action) {
        // the view class method is the same as the action,
        // but I've left these here in case any additional
        // parameter handling has to be done.
        case 'addblocktype': // requires action_addblocktype  (blocktype in separate parameter)
            $values['blocktype'] = param_alpha('blocktype', null);
        break;
        case 'moveblockinstance': // requires action_moveblockinstance_id_\d_column_\d_order_\d
        case 'removeblockinstance': // requires action_removeblockinstance_id_\d
        //case 'configureblockinstance': // later
        case 'addcolumn': // requires action_addcolumn_before_\d
        case 'removecolumn': // requires action_removecolumn_column_\d
        break;
        default:
            throw new InvalidArgumentException(get_string('noviewcontrolaction', 'error', $action));
    }
   
    $message = '';
    $success = false;
    try {
        $values['returndata'] = defined('JSON');
        $returndata = $view->$action($values);
        $message = $view->get_viewcontrol_ok_string($action);
        $success = true;
    }
    catch (Exception $e) {
        // if we're in ajax land, just throw it
        // the handler will deal with the message.
        if (defined('JSON')) {
            throw $e;
        }
        $message = $view->get_viewcontrol_err_string($action) . ': ' . $e->getMessage();
    }
    if (!defined('JSON')) {
        // set stuff in the session and redirect
        $fun = 'add_ok_msg';
        if (!$success) {
            $fun = 'add_err_msg';
        }
        $SESSION->{$fun}($message);
        // TODO fix this url
        redirect('/viewrework.php?view=' . $view->get('id') . '&category=' . $category);
    }
    return array('message' => $message, 'data' => $returndata);
}


/** 
 * parses the string and returns a hash of values
 * @param string $action expects format name_value_name_value
 *                       where values are all numeric
 * @return array associative
*/
function view_get_values_for_action($action) {
    $values = array();
    $bits = explode('_', $action);
    if ((count($bits) % 2) == 1) {
        throw new ParamOutOfRangeException(get_string('invalidviewaction', 'error', $action));
    }
    $lastkey = null;
    foreach ($bits as $index => $bit) {
        if ($index % 2 == 0) { 
            $lastkey = $bit;
        }
        else {
            $values[$lastkey] = $bit;
        }
    }
    return $values;
}



?>
