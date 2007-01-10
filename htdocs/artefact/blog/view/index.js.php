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

defined('INTERNAL') || die();

$enc_id = json_encode($id);

$enc_wwwroot = json_encode(get_config('wwwroot'));

$enc_published = json_encode(get_string('published', 'artefact.blog'));
$enc_publish = json_encode(get_string('publish', 'artefact.blog'));
$enc_nopublish = json_encode(get_string('nopublish', 'artefact.blog'));
$enc_error = json_encode(get_string('jsonerror', 'artefact.blog'));
$enc_edit = json_encode(get_string('edit', 'artefact.blog'));
$enc_delete = json_encode(get_string('delete', 'artefact.blog'));

return <<<EOJAVASCRIPT

var postlist = new TableRenderer(
    'postlist',
    'index.json.php',
    [undefined, undefined]
);

postlist.rowfunction = function(d, n, gd) {
    
    var pub;
    if (d.published == 1) {
      
        pub = {$enc_published};
    }
    else {
        pub = INPUT(
            { 'type' : 'button' },
            {$enc_publish}
        );

        connect(pub, 'onclick', function(e) {
            var def = loadJSONDoc('publish.json.php', { 'id': d.id });
            def.addCallbacks(
                function (response) {
                    if (response.success) {
                        swapDOM(pub, document.createTextNode({$enc_published}));
                    }
                    else {
                        alert({$enc_nopublish});
                    }
                },
                function (error) {
                    alert({$enc_error});
                }
            );
        });
    }

    var edit = FORM(
        {
            'method' : 'get',
            'style' : 'display: inline;',
            'action' : {$enc_wwwroot} + 'artefact/blog/editpost.php'
        },
        INPUT(
            {
                'type'  : 'hidden',
                'name'  : 'blogpost',
                'value' : d.id
            }
        ),
        INPUT(
            { 'type' : 'submit',
              'value' : {$enc_edit}
            }
        )
    );

    var del = INPUT(
        { 'type' : 'button', 'value': {$enc_delete} }
    );

    var desctd = TD({'colSpan':2});
    desctd.innerHTML = d.description;
  
    var rows = [
        TR(
            null,
            TH(null, d.title),
            TD(null, [pub, ' ', edit, ' ', del])
        ),
        TR(null, desctd),
        TR(null, TD({'colSpan':2}, d.ctime))
    ];

    connect(del, 'onclick', function(e) {
        var def = loadJSONDoc('delete.json.php', { 'id' : d.id });
        def.addCallbacks(
            function (response) {
                if (response.success) {
                    for (row in rows) {
                        rows[row].parentNode.removeChild(rows[row]);
                    }
                }
                else {
                    alert('yay');
                }
            },
            function (error) {
                alert('blah');
            }
        );
    });

    return rows;
};
postlist.statevars.push('id');
postlist.id = {$enc_id};

postlist.updateOnLoad();

EOJAVASCRIPT;

?>
