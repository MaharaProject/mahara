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
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'blog');
define('SECTION_PAGE', 'index');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('myblogs','artefact.blog'));
safe_require('artefact', 'blog');

// This is the wwwroot.
$wwwroot = get_config('wwwroot');
$enc_delete = json_encode(get_string('delete', 'artefact.blog'));
$enc_confirmdelete = json_encode(get_string('deleteblog?', 'artefact.blog'));

// This JavaScript creates a table to display the blog entries.
$js = <<<EOF

var bloglist = new TableRenderer(
    'bloglist',
    'index.json.php',
    [
        function(r) {
            return TD(
              null,
              A({'href':'{$wwwroot}artefact/blog/view/?id=' + r.id}, r.title)
            );
        },
        function(r) {
            var td = TD();
            td.innerHTML = r.description;
            return td;
        },
        function (r) {
            var deleteButton = BUTTON(null, {$enc_delete});
            connect(deleteButton, 'onclick', function (e) {
                if (!confirm({$enc_confirmdelete})) {
                    return;
                }
                sendjsonrequest(
                    'index.json.php',
                    {
                        'action': 'delete',
                        'id': r.id
                    },
                    'POST',
                    function (data) {
                        bloglist.doupdate();
                    }
                );
            });
            return TD(null, deleteButton);
        }
    ]
);

bloglist.updateOnLoad();

EOF;

$smarty = smarty(array('tablerenderer'));
$smarty->assign_by_ref('INLINEJAVASCRIPT', $js);
$smarty->assign_by_ref('blogs', $blogs);
$smarty->display('artefact:blog:list.tpl');

?>
