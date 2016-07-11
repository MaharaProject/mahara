<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-googleapps
 * @author     Gregor Anželj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2010 Gregor Anželj, gregor.anzelj@gmail.com
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeGoogleApps extends MaharaCoreBlocktype {

    private static $default_height = 500;

    public static function get_title() {
        return get_string('title', 'blocktype.googleapps');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.googleapps');
    }

    public static function get_categories() {
        return array('external' => 36000);
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');
        if (!isset($configdata['appsid'])) {
            return;
        }
        $apps = self::make_apps_url($configdata['appsid']);
        $url    = hsc($apps['url']);
        $type   = hsc($apps['type']);
        $height = (!empty($configdata['height'])) ? intval($configdata['height']) : self::$default_height;

        if (isset($configdata['appsid']) && !empty($type)) {
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

        return get_string('badurlerror', 'blocktype.googleapps', $url);
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $configdata = $instance->get('configdata');

        return array(
            'appsid' => array(
                'type'  => 'textarea',
                'title' => get_string('appscodeorurl','blocktype.googleapps'),
                'description' => get_string('appscodeorurldesc1','blocktype.googleapps', get_config('series')) . self::get_html_of_supported_googleapps(),
                'rows' => 5,
                'cols' => 76,
                'defaultvalue' => (!empty($configdata['appsid']) ? $configdata['appsid'] : null),
                'rules' => array(
                    'required' => true
                )
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
        $httpstr = is_https() ? 'https' : 'http';

        if (preg_match('#//goo\.gl/#', $url)) {
            $results = mahara_shorturl_request($url);
            $url = $results->fullurl;
        }
        $embedsources = array(
            // docs.google.com/leaf and (as of early 2012) docs.google.com/open - Google collections
            // $1 - domain, e.g. /a/domainname/
            // $2 - id, key, etc. of the collection
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)leaf\?id=([a-zA-Z0-9\_\-]+).*#',
                'url'   => $httpstr . '://drive.google.com/$1folderview?id=$2',
                'type'  => 'spanicon',
            ),
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)open\?id=([a-zA-Z0-9\_\-]+).*#',
                'url'   => $httpstr . '://drive.google.com/$1folderview?id=$2',
                'type'  => 'spanicon',
            ),
            // drive.google.com/folderview or drive.google.com/open - Google Drive folders
            // (formerly Google Docs collections)
            array(
                'match' => '#.*drive.google.com/([a-zA-Z0-9\_\-\.\/]*)open\?id=([a-zA-Z0-9\_\-]+).*#',
                'url'   => $httpstr . '://drive.google.com/$1folderview?id=$2',
                'type'  => 'spanicon',
            ),
            array(
                'match' => '#.*drive.google.com/([a-zA-Z0-9\_\-\.\/]*)folderview\?id=([a-zA-Z0-9\_\-]+).*#',
                'url'   => $httpstr . '://drive.google.com/$1folderview?id=$2',
                'type'  => 'spanicon',
            ),
            // docs.google.com/present - Google presentation incl. custom domain presentation
            // $1 - domain, e.g. /a/domainname/
            // $2 - present or presentation
            // $3 - mode, e.g. pub, view or embed
            // $4 - id, key, etc. of the presentation
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)present([a-z]*)/([a-z]+).*?id=([a-zA-Z0-9\_\-\&\=]+).*#',
                'url'   => $httpstr . '://docs.google.com/$1present$2/embed?id=$4',
                'type'  => 'iframe',
            ),
            // docs.google.com - Google presentation URL format (updated on Mar 2013)
            // $1 - domain, e.g. /a/domainname/
            // $2 - storage path, e.g. d/<hashstring>
            // $3 - mode, e.g. pub, view or embed
            // $4 - url parameters: start, loop, delayms
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)presentation/([a-zA-Z0-9\_\-\/]+)/([a-z]+)\?([a-zA-Z0-9\_\-\&\=]*).*#',
                'url'   => $httpstr . '://docs.google.com/$1presentation/$2/embed?$4',
                'type'  => 'iframe',
            ),
            // docs.google.com/drawings - Google drawing incl. custom domain drawing
            // $1 - domain, e.g. /a/domainname/
            // $2 - id, key, etc. of the drawing
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)drawings.*id=([a-zA-Z0-9\_\-\&\=]+).*#',
                'url'   => $httpstr . '://docs.google.com/$1drawings/pub?id=$2',
                'type'  => 'image',
            ),
            // docs.google.com - Google drawing document URL format (updated on Mar 2013)
            // $1 - domain, e.g. /a/domainname/
            // $2 - storage path, e.g. d/<hashstring>
            // $3 - mode, e.g. pub, view or embed
            // $4 - url parameters: w, h
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)drawings/([a-zA-Z0-9\_\-\/]+)/([a-z]+)\?([a-zA-Z0-9\_\-\&\=]*).*#',
                'url'   => $httpstr . '://docs.google.com/$1drawings/$2/$3?$4',
                'type'  => 'image',
            ),
            // docs.google.com - Google document (before July 2010) incl. custom domain document
            // $1 - domain, e.g. /a/domainname/
            // $2 - id, key, etc. of the document
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)View.*id=([a-zA-Z0-9\_\-]+).*#',
                'url'   => $httpstr . '://docs.google.com/$1View?id=$2',
                'type'  => 'iframe',
            ),
            // docs.google.com - Google document (after march 2012) incl. custom domain document
            // $1 - domain, e.g. /a/domainname/
            // $2 - id, key, etc. of the document
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)viewer.*srcid=([a-zA-Z0-9\_\-\&\=]+).*#',
                'url'   => $httpstr . '://docs.google.com/$1viewer?srcid=$2',
                'type'  => 'iframe',
            ),
            // docs.google.com - Google document URL format (updated on Mar 2013)
            // $1 - domain, e.g. /a/domainname/
            // $2 - storage path, e.g. d/<hashstring>
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)document/([a-zA-Z0-9\_\-\/]+)/pub.*#',
                'url'   => $httpstr . '://docs.google.com/$1document/$2/pub?embedded=true',
                'type'  => 'iframe',
            ),
            // docs.google.com - Google document viewer URL format (updated on Mar 2013)
            // $1 - domain, e.g. /a/domainname/
            // $2 - storage path, e.g. d/<hashstring>
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)file/([a-zA-Z0-9\_\-\/]+)/([a-z]+).*#',
                'url'   => $httpstr . '://docs.google.com/$1file/$2/preview',
                'type'  => 'iframe',
            ),
            // docs.google.com/viewer created urls
            // $1 - domain, e.g. /a/domainname/
            // $2 - http or https
            // $3 - domain of document, non-google or googleapps
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)viewer.*url=http([a-zA-Z0-9\.\,\;\_\-\&\%\=\+/\:]+)\.(pdf|tif|tiff|ppt|doc|docx).*#',
                'url'   => $httpstr . '://docs.google.com/$1viewer?url=http$2.$3&embedded=true',
                'type'  => 'iframe',
            ),
            // docs.google.com - Google document (after July 2010) incl. custom domain document
            // $1 - domain, e.g. /a/domainname/
            // $2 - id, key, etc. of the document
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)document/pub.*id=([a-zA-Z0-9\_\-]+).*#',
                'url'   => $httpstr . '://docs.google.com/$1document/pub?id=$2',
                'type'  => 'iframe',
            ),
            // docs.google.com - Google document (updated on Mar 2014)
            // $1 - domain, e.g. /a/domainname/
            // $2 - id, key, etc. of the document
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)document/d/([a-zA-Z0-9\_\-]+).*#',
                'url'   => $httpstr . '://docs.google.com/$1document/d/$2/pub?embedded=true',
                'type'  => 'iframe',
            ),
            // docs.google.com/spreadsheets - Google document (updated on Mar 2015)
            // $1 - domain, e.g. /a/domainname/
            // $2 - id, key, etc. of the spreadsheet
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)spreadsheets/d/([a-zA-Z0-9\_\-]+).*#',
                'url'   => $httpstr . '://docs.google.com/$1spreadsheets/d/$2/pub?embedded=true',
                'type'  => 'iframe',
            ),
            // docs.google.com - Google spreadsheet document (updated on Mar 2013)
            // $1 - domain, e.g. /a/domainname/ (optional)
            // $2 - key of the document
            // $3 - other parameters: single, gid, output
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)spreadsheet/.*key=([a-zA-Z0-9\_\-]+)([a-zA-Z0-9\_\-\&\=]*).*#',
                'url'   => $httpstr . '://docs.google.com/$1spreadsheet/pub?key=$2$3&widget=true',
                'type'  => 'iframe',
            ),
            // docs.google.com - Google form (updated on Mar 2013)
            // $1 - domain, e.g. /a/domainname/
            array(
                'match' => '#.*docs.google.com/([a-zA-Z0-9\_\-\.\/]*)forms/([a-zA-Z0-9\_\-\.\/]*)/viewform\?embedded=true.*#',
                'url'   => $httpstr . '://docs.google.com/$1forms/$2/viewform?embedded=true',
                'type'  => 'iframe',
            ),
            // spreadsheets.google.com/viewform - Google form incl. custom domain form
            // $1 - domain, e.g. /a/domainname/
            // $2 - id, key, etc. of the form
            array(
                'match' => '#.*spreadsheets[0-9]?.google.com/([a-zA-Z0-9\_\-\.\/]*)viewform.*formkey=([a-zA-Z0-9\_\-]+).*#',
                'url'   => $httpstr . '://spreadsheets.google.com/$1embeddedform?formkey=$2',
                'type'  => 'iframe',
            ),
            // spreadsheets.google.com/embeddedform - Google form incl. custom domain form
            // $1 - domain, e.g. /a/domainname/
            // $2 - id, key, etc. of the form
            array(
                'match' => '#.*spreadsheets[0-9]?.google.com/([a-zA-Z0-9\_\-\.\/]*)embeddedform.*formkey=([a-zA-Z0-9\_\-]+).*#',
                'url'   => $httpstr . '://spreadsheets.google.com/$1embeddedform?formkey=$2',
                'type'  => 'iframe',
            ),
            // spreadsheets.google.com - Google spreadsheet incl. custom domain spreadsheet
            // $1 - domain, e.g. /a/domainname/
            // $2 - id, key, etc. of the spreadsheet
            array(
                'match' => '#.*spreadsheets[0-9]?.google.com/([a-zA-Z0-9\_\-\.\/]*)pub.*key=([a-zA-Z0-9\_\-]+).*#',
                'url'   => $httpstr . '://spreadsheets.google.com/$1pub?key=$2',
                'type'  => 'iframe',
            ),
            // drive.google.com - Google uploaded file (updated on Mar 2014)
            // $1 - id, key, etc. of the file
            array(
                'match' => '#.*drive.google.com/.*file/d/([a-zA-Z0-9\_\-]+).*#',
                'url'   => $httpstr . '://docs.google.com/file/d/$1/preview',
                'type'  => 'iframe',
            ),
            // www.google.com/calendar - Google calendar
            array(
                'match' => '#.*www.google.com/calendar.*src=([a-zA-Z0-9\.\_\-\&\%\=/]+).*#',
                'url'   => $httpstr . '://calendar.google.com/calendar/embed?src=$1',
                'type'  => 'iframe',
            ),
            // calendar.google.com - Google Calendar (mid-2016)
            array(
                'match' => '#.*calendar.google.com/calendar.*src=([a-zA-Z0-9\.\_\-\&\%\=/]+).*#',
                'url'   => $httpstr . '://calendar.google.com/calendar/embed?src=$1',
                'type'  => 'iframe',
            ),
            // (maps|www).google.com - Google My Maps (IMPORTANT: this is ONLY for My Maps)
            array(
                'match' => '#.*google.[^/]*/maps/ms\?([a-zA-Z0-9\.\,\;\_\-\&\%\=\+/]+).*#',
                'url'   => $httpstr . '://maps.google.com/maps/ms?$1&output=embed',
                'type'  => 'iframe',
            ),
            // maps.google.com - Other maps.google.map URLs.
            array(
                'match' => '#.*maps.google.[^/]*/(maps)?\?([a-zA-Z0-9\.\,\;\_\-\&\%\=\+/]+).*#',
                'url'   => $httpstr . '://maps.google.com/maps?$2&output=embed',
                'type'  => 'iframe',
            ),
            // (maps|www).google.com/maps/place - Google Map for a place URLs.
            array(
                'match' => '#.*google.[^/]*/maps/place/([^/]+)/@([0-9\-\.]+),([0-9\-\.]+),([0-9]+)z/data=.+!1s([0-9xa-f]+):([0-9xa-f]+).*#',
                'url'   =>  function ($m) {
                                $zoomlevel = min(max($m[4], 3), 21);
                                $height = 188 * pow(2, (21 - $zoomlevel));
                                return "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d${height}!2d${m[3]}!3d${m[2]}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s${m[5]}%3A${m[6]}!2s${m[1]}!5e0";
                            },
                'type'  => 'iframe',
            ),
            // mapsengine.google.com/map/embed URLs.
            array(
                'match' => '#.*mapsengine.google.com[^/]*/map/[^?]*\?([a-zA-Z0-9\.\,\;\_\-\&\%\=\+\?/]+).*#',
                'url'   => $httpstr . '://mapsengine.google.com/map/embed?$1',
                'type'  => 'iframe',
            ),
            // www.google.com/maps? URLs.
            array(
                'match' => '#.*www.google.com[^/]*/maps\?([a-zA-Z0-9\.\,\;\_\-\&\%\=\+\!/]+).*#',
                'url'   => $httpstr . '://www.google.com/maps?$1&output=embed',
                'type'  => 'iframe',
            ),
            // www.google.com/maps/embed URLs - these must be https.
            array(
                'match' => '#.*www.google.com[^/]*/maps/embed\?([a-zA-Z0-9\.\,\;\_\-\&\%\=\+\!/]+).*#',
                'url'   => 'https://www.google.com/maps/embed?$1',
                'type'  => 'iframe',
            ),
            // www.google.com/maps/@ URLs.
            array(
                'match' => '#.*www.google.com.*?/\@([a-zA-Z0-9\.\-]+)\,([a-zA-Z0-9\.\-]+)\,([0-9]+).*#',
                'url'   => 'https://maps.google.com/maps?ll=$1,$2&z=$3&output=embed',
                'type'  => 'iframe',
            ),
            // books.google.com - Google Books
            array(
                'match' => '#.*books.google.[^/]*/books.*id=([a-zA-Z0-9\_\-\&\%\=]+).*#',
                'url'   => 'http://books.google.com/books?id=$1',
                'type'  => 'iframe',
            ),
            // If everything else fails, match if it is a valid link to a file... and than show that file with Google Docs Viewer
            // Google Docs Viewer supported files: PDF, TIFF, PPT, DOC, DOCX
            array(
                'match' => '#http([a-zA-Z0-9\.\,\;\_\-\&\%\=\+/\:]+)\.(pdf|tif|tiff|ppt|doc|docx)#',
                'url'   => $httpstr . '://docs.google.com/gview?url=http$1.$2&embedded=true',
                'type'  => 'iframe',
            ),
        );

        foreach ($embedsources as $source) {
            $url = htmlspecialchars_decode($url); // convert &amp; back to &, etc.
            if (preg_match($source['match'], $url)) {
                if (is_string($source['url'])) {
                    $apps_url = preg_replace($source['match'], $source['url'], $url);
                }
                else if (is_callable($source['url'])) {
                    $apps_url = preg_replace_callback($source['match'], $source['url'], $url);
                }
                // For correctly embed Google maps...
                $apps_url = str_replace('source=embed', 'output=embed', $apps_url);
                $apps_type = $source['type'];
                return array('url' => $apps_url, 'type' => $apps_type);
            }
        }
        // if we reach here then mahara does not understand the url
        return array('url' => $url, 'type' => false);
    }


    /**
     * Returns a block of HTML that the Google Apps block can use to list
     * which Google services  are supported.
     */
    private static function get_html_of_supported_googleapps() {
        $smarty = smarty_core();
        $smarty->assign('lang', substr(get_config('lang'), 0, 2));
        if (is_https() === true) {
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

    /**
     * Shouldn't be linked to any artefacts via the view_artefacts table.
     *
     * @param BlockInstance $instance
     * @return multitype:
     */
    public static function get_artefacts(BlockInstance $instance) {
        return array();
    }
}
