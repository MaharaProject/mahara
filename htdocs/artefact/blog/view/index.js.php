<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage artefact-blog
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

$bloglocked = (int) $blog->get('locked');
$enc_id = json_encode($id);

$enc_wwwroot = json_encode(get_config('wwwroot'));

$enc_draft = json_encode(get_string('draft', 'artefact.blog'));
$enc_published = json_encode(get_string('published', 'artefact.blog'));
$enc_publish = json_encode(get_string('publish', 'artefact.blog'));
$enc_publish_confirm = json_encode(get_string('publishblogpost?', 'artefact.blog'));
$enc_nopublish = json_encode(get_string('publishfailed', 'artefact.blog'));
$enc_edit = json_encode(get_string('edit', 'artefact.blog'));
$enc_error = json_encode(get_string('unknownerror'));
$enc_files = json_encode(get_string('attachedfiles', 'artefact.blog'));
$enc_delete = json_encode(get_string('delete', 'artefact.blog'));
$enc_delete_confirm = json_encode(get_string('deleteblogpost?', 'artefact.blog'));
$enc_postedon = json_encode(get_string('postedon', 'artefact.blog'));
$enc_cannotdeleteblogpost = json_encode(get_string('cannotdeleteblogpost', 'artefact.blog'));
$enc_submitted = json_encode(get_string('submittedforassessment', 'view'));


$enc_publish_help = json_encode(get_help_icon('artefact', 'blog', null, null, null, 'publish'));

$limit = ArtefactTypeBlog::pagination;

return <<<EOJAVASCRIPT

var postlist = new TableRenderer(
    'postlist',
    {$enc_wwwroot} + 'artefact/blog/view/index.json.php',
    [undefined, undefined, undefined]
);
postlist.limit = $limit;

postlist.rowfunction = function(d, n, gd) {
    
    var status = TH({'id':'poststatus'+d.id});
    if (d.published == 1) {
        status.innerHTML = {$enc_published};
    }
    else {
        status.innerHTML = {$enc_draft};
    }

    var controls = [];
    if ({$bloglocked} || d.locked == 1) {
        controls.push({$enc_submitted});
    }
    else {
        if (d.published != 1) {
            status.innerHTML = {$enc_draft};
            pub = INPUT(
                { 'type' : 'button' , 'class' : 'button publish', 'value' : {$enc_publish}}
            );
            pubhelp = SPAN(null); pubhelp.innerHTML = {$enc_publish_help};

            connect(pub, 'onclick', function(e) {
                if (!confirm({$enc_publish_confirm})) {
                    return;
                }
                sendjsonrequest({$enc_wwwroot} + 'artefact/blog/view/publish.json.php', { 'id': d.id }, 'GET',
                                function (response) {
                                    if (!response.error) {
                                        $('poststatus'+d.id).innerHTML = {$enc_published};
                                        hideElement(pub);
                                        hideElement(pubhelp);
                                    }
                                });
            });
            controls.push(pub, pubhelp);
        }

        var edit = FORM(
            {
                'method' : 'get',
                'style' : 'display: inline;',
                'action' : {$enc_wwwroot} + 'artefact/blog/post.php'
            },
            INPUT(
                {
                    'type'  : 'hidden',
                    'name'  : 'blogpost',
                    'value' : d.id
                }
            ),
            INPUT(
                { 'type' : 'submit', 'class' : 'submit edit',
                  'value' : {$enc_edit}
                }
            )
        );
        var del = INPUT(
            { 'type' : 'button', 'class' : 'button delete', 'value': {$enc_delete} }
        );
        controls.push(edit, ' ', del);
    }

    var desctd = TD({'colSpan':3});
    desctd.innerHTML = d.description;
  
    var rows = [
        TR(
            null,
            TH(null, d.title),
            status,
            TH({'class': 'controls'}, controls)
        ),
        TR(null, desctd)
    ];

    if (d.files) {
        var filerows = [TR(null, TH({'colSpan':3}, {$enc_files}))];
        for (var i = 0; i < d.files.length; i++) {
            filerows.push(TR({'class':'r'+((i+1)%2)}, 
                             TD(null, IMG({'src':d.files[i].icon})),
                             TD(null, A({'href':config.wwwroot+'artefact/file/download.php?file='+d.files[i].attachment},
                                        d.files[i].title)),
                             TD(null, d.files[i].description)));
        }
        rows.push(TR(null, TD({'colSpan':3}, 
                              TABLE({'class': 'attachments fullwidth'},
                                    createDOM('col', {'width':'5%'}),
                                    createDOM('col', {'width':'40%'}),
                                    createDOM('col', {'width':'55%'}),
                                    TBODY(null, filerows)))));
    }

    rows.push(TR(null, TD({'colspan':2, 'class': 'postdetails'}, {$enc_postedon}, ' ', d.ctime)));

    if (del) {
        connect(del, 'onclick', function(e) {
            if (!confirm({$enc_delete_confirm})) {
                return;
            }
            sendjsonrequest({$enc_wwwroot} + 'artefact/blog/view/delete.json.php', { 'id' : d.id }, 'GET', function(response) {
                if (!response.error) {
                    for (row in rows) {
                        rows[row].parentNode.removeChild(rows[row]);
                    }
                }
            });
        });
    }

    return rows;
};
postlist.statevars.push('id');
postlist.id = {$enc_id};

postlist.updateOnLoad();

EOJAVASCRIPT;

?>
