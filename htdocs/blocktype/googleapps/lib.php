<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2010 Catalyst IT Ltd and others; see:
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
 * @subpackage blocktype-googleapps
 * @author     Gregor Anželj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2010 Gregor Anželj, gregor.anzelj@gmail.com
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeGoogleApps extends SystemBlocktype {

    private static $default_height = 500;

    public static function get_title() {
        return get_string('title', 'blocktype.googleapps');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.googleapps');
    }

    public static function get_categories() {
        return array('external');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');
        if (!isset($configdata['appsid'])) {
            return;
        }
        $apps = self::make_apps_url($configdata['appsid']);
        $url    = hsc($apps['url']);
        $type   = hsc($apps['type']);
        $height = (!empty($configdata['height'])) ? hsc($configdata['height']) : self::$default_height;

        if (isset($configdata['appsid'])) {
            $smarty = smarty_core();
            $smarty->assign('url', $apps['url']);
            switch ($type) {
                case 'iframe':
                    // Google Docs (documents, presentations, spreadsheets, forms), Google Calendar, Google Maps
                    $smarty->assign('height', $height);
                    return $smarty->fetch('blocktype:googleapps:iframe.tpl');
                case 'spanicon':
                    // Google Docs collections (folder icon)
                    $smarty->assign('img', get_config('wwwroot') . 'blocktype/googleapps/images/folder_documents.png');
                    return $smarty->fetch('blocktype:googleapps:spanicon.tpl');
                case 'image':
                    // Google Docs drawing
                    $smarty->assign('height', $height);
                    return $smarty->fetch('blocktype:googleapps:image.tpl');
            }
        }

        return '';
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');

        return array(
            'appsid' => array(
                'type'  => 'textarea',
                'title' => get_string('appscodeorurl','blocktype.googleapps'),
                'description' => get_string('appscodeorurldesc','blocktype.googleapps') . self::get_html_of_supported_googleapps(),
                'rows' => 5,
                'cols' => 80,
                'defaultvalue' => (!empty($configdata['appsid']) ? $configdata['appsid'] : null),
                'rules' => array(
                    'required' => true
                ),
                'help' => true,
            ),
            'height' => array(
                'type' => 'text',
                'title' => get_string('height','blocktype.googleapps'),
                'size' => 3,
                'rules' => array(
                    'required' => true,
                    'integer'  => true,
                ),
                'defaultvalue' => (!empty($configdata['height'])) ? $configdata['height'] : self::$default_height,
            ),
        );
    }

    private static function make_apps_url($url) {
        static $embedsources = array(
            // docs.google.com/leaf - Google collections
            // $1 - domain, e.g. /a/domainname/
            // $2 - id, key, etc. of the collection
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)leaf\?id=([a-zA-Z0-9]+).*#',
                'url'   => 'http://docs.google.com/$1leaf?id=$2',
                'type'  => 'spanicon',
            ),
            // docs.google.com/present - Google presentation incl. custom domain presentation
            // $1 - domain, e.g. /a/domainname/
            // $2 - mode, e.g. view or embed
            // $3 - id, key, etc. of the presentation
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)present/([a-z]+).*id=([a-zA-Z0-9\_\-\&\=]+).*#',
                'url'   => 'http://docs.google.com/$1present/embed?id=$3',
                'type'  => 'iframe',
            ),
            // docs.google.com/drawings - Google drawing incl. custom domain drawing
            // $1 - domain, e.g. /a/domainname/
            // $2 - id, key, etc. of the drawing
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)drawings.*id=([a-zA-Z0-9\_\-\&\=]+).*#',
                'url'   => 'http://docs.google.com/$1drawings/pub?id=$2',
                'type'  => 'image',
            ),
            // docs.google.com - Google document (before July 2010) incl. custom domain document
            // $1 - domain, e.g. /a/domainname/
            // $2 - id, key, etc. of the document
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)View.*id=([a-zA-Z0-9\_\-]+).*#',
                'url'   => 'http://docs.google.com/$1View?id=$2',
                'type'  => 'iframe',
            ),
            // docs.google.com - Google document (after July 2010) incl. custom domain document
            // $1 - domain, e.g. /a/domainname/
            // $2 - id, key, etc. of the document
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)document/pub.*id=([a-zA-Z0-9\_\-]+).*#',
                'url'   => 'http://docs.google.com/$1document/pub?id=$2',
                'type'  => 'iframe',
            ),
            // spreadsheets.google.com/viewform - Google form incl. custom domain form
            // $1 - domain, e.g. /a/domainname/
            // $2 - id, key, etc. of the form
            array(
                'match' => '#.*spreadsheets[0-9]?.google.com/([a-zA-Z0-9\_\-\.\/]*)viewform.*formkey=([a-zA-Z0-9\_\-]+).*#',
                'url'   => 'https://spreadsheets.google.com/$1embeddedform?formkey=$2',
                'type'  => 'iframe',
            ),
            // spreadsheets.google.com/embeddedform - Google form incl. custom domain form
            // $1 - domain, e.g. /a/domainname/
            // $2 - id, key, etc. of the form
            array(
                'match' => '#.*spreadsheets[0-9]?.google.com/([a-zA-Z0-9\_\-\.\/]*)embeddedform.*formkey=([a-zA-Z0-9\_\-]+).*#',
                'url'   => 'https://spreadsheets.google.com/$1embeddedform?formkey=$2',
                'type'  => 'iframe',
            ),
            // spreadsheets.google.com - Google spreadsheet incl. custom domain spreadsheet
            // $1 - domain, e.g. /a/domainname/
            // $2 - id, key, etc. of the spreadsheet
            array(
                'match' => '#.*spreadsheets[0-9]?.google.com/([a-zA-Z0-9\_\-\.\/]*)pub.*key=([a-zA-Z0-9\_\-]+).*#',
                'url'   => 'http://spreadsheets.google.com/$1pub?key=$2',
                'type'  => 'iframe',
            ),
            // www.google.com/calendar - Google calendar
            array(
                'match' => '#.*www.google.com/calendar.*src=([a-zA-Z0-9\.\_\-\&\%\=/]+).*#',
                'url'   => 'http://www.google.com/calendar/embed?src=$1',
                'type'  => 'iframe',
            ),
            // maps.google.com - Google My Maps (IMPORTANT: this is ONLY for My Maps)
            array(
                'match' => '#.*maps.google.[^/]*/maps/ms\?([a-zA-Z0-9\.\,\;\_\-\&\%\=\+/]+).*#',
                'url'   => 'http://maps.google.com/maps/ms?$1',
                'type'  => 'iframe',
            ),
            // maps.google.com - Google Maps (IMPORTANT: this is for ANY Maps EXCEPT My Maps)
            array(
                'match' => '#.*maps.google.[^/]*/(maps)?\?([a-zA-Z0-9\.\,\;\_\-\&\%\=\+/]+).*#',
                'url'   => 'http://maps.google.com/maps?$1',
                'type'  => 'iframe',
            ),
            // books.google.com - Google Books
            array(
                'match' => '#.*books.google.[^/]*/books.*id=([a-zA-Z0-9\_\-\&\%\=]+).*#',
                'url'   => 'http://books.google.com/books?id=$1',
                'type'  => 'iframe',
            ),
            // If everything else fails, match if it is a valid link to a file... and than show that file with Google Dovs Viewer
            // Google Docs Viewer supported files: PDF, TIFF, PPT, DOC, DOCX
            array(
                'match' => '#http([a-zA-Z0-9\.\,\;\_\-\&\%\=\+/\:]+)\.(pdf|tif|tiff|ppt|doc|docx)#',
                'url'   => 'http://docs.google.com/gview?url=http$1.$2&embedded=true',
                'type'  => 'iframe',
            ),
        );

        foreach ($embedsources as $source) {
            $url = htmlspecialchars_decode($url); // convert &amp; back to &, etc.
            if (preg_match($source['match'], $url)) {
                $apps_url = preg_replace($source['match'], $source['url'], $url);
                // For correctly embed Google maps...
                $apps_url = str_replace('source=embed', 'output=embed', $apps_url);
                $apps_type = $source['type'];
                return array('url' => $apps_url, 'type' => $apps_type);
            }
        }
        // TODO handle failure case
    }


    /**
     * Returns a block of HTML that the Google Apps block can use to list
     * which Google services  are supported.
     */
    private static function get_html_of_supported_googleapps() {
        $smarty = smarty_core();
        $smarty->assign('lang', substr(get_config('lang'), 0, 2));
        if (stripos(get_config('wwwroot'), 'https') === 0) {
            $smarty->assign('protocol', 'https');
        }
        else {
            $smarty->assign('protocol', 'http');
        }
        return $smarty->fetch('blocktype:googleapps:supported.tpl');
    }

    public static function default_copy_type() {
        return 'full';
    }

}
