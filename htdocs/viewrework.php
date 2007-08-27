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

define('INTERNAL', 1);
define('PUBLIC', 1);
define('MENUITEM', 'viewrework');
require('init.php');
define('TITLE', 'Views Rework [DANGER construction site]');

require_once('artefact.php');
rebuild_artefact_parent_cache_complete();
$smarty = smarty(array('views'), array('<link rel="stylesheet" href="views.css" type="text/css">'));

// Categories
$categories = array(
    'cats' => array(
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
    ),
    'current' => 'aboutme',
);

$flag = false;
foreach ($categories['cats'] as &$category) {
    if (!$flag) {
        $flag = true;
        $category['classes'] = 'first';
    }
    if ($categories['current'] == $category['name']) {
        $category['classes'] = (isset($category['classes'])) ? $category['classes'] . ' current' : 'current';
        // certainly done now
        break;
    }
}

$smarty->assign('categories', $categories);


// Block types for the selected category
$blocktypes = array(
    array(
        'id'             => 1,
        'name'           => 'blocktype1',
        'title'          => 'Block Type 1',
        'description'    => 'This is the description for block type 1',
        'thumbnail_path' => 'theme/default/static/images/no_thumbnail.gif',
    ),
    array(
        'id'             => 2,
        'name'           => 'blocktype2',
        'title'          => 'Block Type 2',
        'description'    => 'This is the description for block type 2',
        'thumbnail_path' => 'theme/default/static/images/no_thumbnail.gif',
    ),
    array(
        'id'             => 3,
        'name'           => 'blocktype3',
        'title'          => 'Block Type 3',
        'description'    => 'This is the description for block type 3',
        'thumbnail_path' => 'theme/default/static/images/no_thumbnail.gif',
    ),
    array(
        'id'             => 4,
        'name'           => 'blocktype4',
        'title'          => 'Block Type 4',
        'description'    => 'This is the description for block type 4',
        'thumbnail_path' => 'theme/default/static/images/no_thumbnail.gif',
    ),
);

$smarty->assign('blocktypes', $blocktypes);



// Column 3 blocks (temporary!)
$columns = array(
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

$smarty->assign('columns', $columns);


$smarty->display('viewrework.tpl');

?>
