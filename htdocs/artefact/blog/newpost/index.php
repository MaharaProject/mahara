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
 * @subpackage artefact-blog
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

$id = param_integer('id');

$form = pieform(array(
    'name' => 'newpost',
    'method' => 'post',
    'action' => '',
    'elements' => array(
        'id' => array(
            'type' => 'hidden',
            'value' => $id,
            'rules' => array(
                'required' => true
            )
        ),
        'title' => array(
            'type' => 'text',
            'title' => get_string('posttitle', 'artefact.blog'),
            'description' => get_string('posttitledesc', 'artefact.blog'),
            'rules' => array(
                'required' => true
            )
        ),
        'description' => array(
            'type' => 'textarea',
            'rows' => 5,
            'cols' => 80,
            'title' => get_string('postbody', 'artefact.blog'),
            'description' => get_string('postbodydesc', 'artefact.blog'),
            'rules' => array(
                'required' => true
            )
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('newpost', 'artefact.blog')
        )
    )
));

$smarty =& smarty();
$smarty->assign_by_ref('newpostform', $form);
$smarty->display('artefact:blog:newpost.tpl');
exit;

/**
 * This function gets called to create a new blog post.
 *
 * @param array
 */
function newpost_submit(array $values) {
    global $USER;

    ArtefactTypeBlogPost::new_post($USER, $values);
    redirect(get_config('wwwroot') . '/artefact/blog/view/?id=' . $values['id']);
}

?>
