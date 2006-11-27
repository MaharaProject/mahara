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
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

function template_parse($templatename) {

    $t = array();
    
    $template = template_locate($templatename);
    $template = $template['fragment']; // we don't care about css or thumbnails right now

    $fragment = file_get_contents($template);

    preg_match_all('/(.*?)\{\{(.*?)\}\}/xms', $fragment, $matches, PREG_SET_ORDER);
    
    $strlen = 0;

    foreach ($matches as $m) {
        $temp = array('type'    => 'html',
                      'content' => $m[1],
                      );
        $t[] = $temp;
        $temp = array('type'    => 'block', 
                      'data'    => template_parse_block($m[2]),
                      );
        $t[] = $temp;

        $strlen += strlen($m[0]);
           
    }
   
    $temp = array('type'    => 'html',
                  'content' => substr($fragment, $strlen),
                  );

    $t[] = $temp;

    return $t;
}

function template_parse_block($blockstr) {
    $data = array();
    $bits = explode(' ', $blockstr);

    // the first bit should be 'block'
    if ($bits[0] != 'block') {
        throw new InvalidArgumentException("Invalid block section $blockstr");
    }

    array_shift($bits);
    foreach ($bits as $b) {
        $keyvalue = explode('=', $b);
        $data[$keyvalue[0]] = substr($keyvalue[1], 1, -1);
    }
    
    if (!isset($data['id']) || empty($data['id'])) {
        throw new InvalidArgumentException("Invalid block $blockstr. Must have id");
    }
    // everything else can theoretically be optional....

    return $data;
        
}

function template_locate($templatename) {

    // check dataroot first for custom templates
    $templatedir = 'templates/' . $templatename . '/';
    $fragment = $templatedir . 'fragment.template';
    $css = $templatedir . 'fragment.css';

    $template = array();

    $thumbnails = array('jpg', 'jpeg', 'png', 'gif');

    if ($path = realpath(get_config('dataroot') . $fragment)) {
        $template['fragment'] = $path;
        if (is_readable(get_config('dataroot') . $css)) {
            $template['css'] = get_config('dataroot') . $css;
        }
        foreach ($thumbnails as $t) {
            if (is_readable(get_config('dataroot') . $templatedir . 'thumbnail.' . $t)) {
                $template['thumbnail'] = get_config('dataroot') . $templatedir . 'thumbnail.' . $t;
                break;
            }
        }
        return $template;
    }

    if ($path = realpath(get_config('libroot') . $fragment)) {
        $template['fragment'] = $path;
        if (is_readable(get_config('libroot') . $css)) {
            $template['css'] = get_config('libroot') . $css;
        }
        foreach ($thumbnails as $t) {
            if (is_readable(get_config('libroot') . $templatedir . 'thumbnail.' . $t)) {
                $template['thumbnail'] = get_config('libroot') . $templatedir . 'thumbnail.' . $t;
                break;
            }
        }
        return $template;
    }

    throw new InvalidArgumentException("Invalid template name $templatename, couldn't find");
}


?>
