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
 * @subpackage artefact-internal
 * @author     Alastair Pharo <alastair@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myblogs');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'blog');

$form = pieform(array(
    'name' => 'newblog',
    'method' => 'post',
    'action' => '',
    'elements' => array(
        'title' => array(
            'type'        => 'text',
            'title'       => get_string('blogtitle', 'artefact.blog'),
            'description' => get_string('blogtitledesc', 'artefact.blog'),
            'rules' => array(
                'required'    => true
            )
        ),
        'description' => array(
            'type'        => 'textarea',
            'rows'        => 10,
            'cols'        => 80,
            'title'       => get_string('blogdesc', 'artefact.blog'),
            'description' => get_string('blogdescdesc', 'artefact.blog'),
            'rules' => array(
                'required'    => true
            )
        ),
        'submit' => array(
            'type'  => 'submit',
            'value' => get_string('newblog', 'artefact.blog')
        )
    )
));

$smarty =& smarty();
$smarty->assign_by_ref('newblogform', $form);
$smarty->display('artefact:blog:new.tpl');
exit;

/**
 * This function gets called to submit the new blog.
 *
 * @param array
 */
function newblog_submit($values) {
    global $USER;

    ArtefactTypeBlog::new_blog($USER, $values);
    redirect(get_config('wwwroot') . '/artefact/blog/list/');
}

?>
