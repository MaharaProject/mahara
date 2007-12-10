<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Alastair Pharo <alastair@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

$enc_wwwroot = json_encode(get_config('wwwroot'));
$enc_id = json_encode($this->id);
$enc_options = json_encode(json_encode($options));

return <<<EOJAVASCRIPT

var blog_renderfull{$blockid} = new TableRenderer(
    'blog_renderfull{$blockid}',
    {$enc_wwwroot} + 'artefact/blog/render/blog_renderfull.json.php',
    [
        function(r) {
            var td = TD();
            td.innerHTML = r.content.html;
            return td;
        }
    ]
);

blog_renderfull{$blockid}.statevars.push('id');
blog_renderfull{$blockid}.id = {$enc_id};
blog_renderfull{$blockid}.statevars.push('options');
blog_renderfull{$blockid}.options = {$enc_options};

blog_renderfull{$blockid}.updateOnLoad();

EOJAVASCRIPT;

?>
