<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Richard Mansfield
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configextensions/iframesites');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'iframesites');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
require_once('upgrade.php');
define('TITLE', get_string('allowediframesites', 'admin'));

$iframesources = get_records_menu('iframe_source', '', '', 'name,prefix');
$iframedomains = get_records_menu('iframe_source_icon');

$newform = pieform(array(
    'name'     => 'newurl',
    'elements' => array(
        'url' => array(
            'type'        => 'text',
            'title'       => get_string('Site'),
            'description' => get_string('iframeurldescription', 'admin'),
            'rules'       => array(
                'minlength' => 4,
                'maxlength' => 255,
                'regex'     => '/^[a-zA-Z0-9\/\._-]+$/',
            ),
        ),
        'name' => array(
            'type'        => 'text',
            'title'       => get_string('displayname'),
            'description' => get_string('iframedisplaynamedescription', 'admin'),
            'rules'       => array('minlength' => 1, 'maxlength' => 100),
        ),
        'submit' => array(
            'type'        => 'submit',
            'value'       => get_string('add'),
        ),
    ),
));

$editurls = array();
$i = 0;
foreach ($iframesources as $url => $name) {
    $elements = array(
        'url' => array(
            'type' => 'hidden',
            'value' => $url,
        ),
        'name' => array(
            'type' => 'text',
            'title' => get_string('displayname'),
            'description' => get_string('iframedisplaynamedescription', 'admin'),
            'defaultvalue' => $name,
        ),
        'icon' => array(
            'type' => 'text',
            'title' => get_string('iframeiconhost', 'admin'),
            'description' => get_string('iframeiconhostdescription', 'admin'),
            'defaultvalue' => $iframedomains[$name],
            'rules' => array(
                'minlength' => 4,
                'maxlength' => 253,
                'regex'     => '/^[a-zA-Z0-9-]{1,63}(\.[a-zA-Z0-9-]{1,63})+$/', // approximately
            ),
        ),
        'submit' => array(
            'type'  => 'submit',
            'value' => get_string('save'),
        ),
    );
    $editurls[$i] = array(
        'id'         => $i,
        'url'        => $url,
        'name'       => $name,
        'icon'       => favicon_display_url($iframedomains[$name]),
        'editform'   => pieform(array(
            'name'             => 'editurl_' . $i,
            'successcallback'  => 'editurl_submit',
            'elements'         => $elements,
        )),
        'deleteform' => pieform(array(
            'name'             => 'deleteurl_' . $i,
            'successcallback'  => 'deleteurl_submit',
            'renderer'         => 'oneline',
            'class'            => 'oneline inline',
            'elements'         => array(
                'url'  => array(
                    'type'         => 'hidden',
                    'value'        => $url,
                ),
                'submit' => array(
                    'type'         => 'image',
                    'src'          => $THEME->get_image_url('btn_deleteremove'),
                    'alt'          => get_string('deletespecific', 'mahara', $name),
                    'elementtitle' => get_string('delete'),
                    'confirm'      => get_string('confirmdeletemenuitem', 'admin'),
                ),
            ),
        )),
    );
    $i++;
}

function editurl_submit(Pieform $form, $values) {
    global $iframesources, $iframedomains, $SESSION;

    if (isset($iframesources[$values['url']])) {
        $oldname = $iframesources[$values['url']];
        $newname = strip_tags($values['name']);
        $iframesources[$values['url']] = $newname;

        db_begin();

        // Update the icon list if necessary
        if (!isset($iframedomains[$newname])) {
            insert_record('iframe_source_icon', (object) array('name' => $newname, 'domain' => $values['icon']));
        }
        else if ($iframedomains[$newname] != $values['icon']) {
            set_field('iframe_source_icon', 'domain', $values['icon'], 'name', $newname);
        }

        set_field('iframe_source', 'name', $newname, 'prefix', $values['url']);
        if (!in_array($oldname, $iframesources)) {
            delete_records('iframe_source_icon', 'name', $oldname);
        }

        update_safe_iframe_regex();
        db_commit();

        $SESSION->add_ok_msg(get_string('itemupdated'));
    }
    else {
        $SESSION->add_error_msg(get_string('updatefailed'));
    }

    redirect('/admin/extensions/iframesites.php');
}

function deleteurl_submit(Pieform $form, $values) {
    global $iframesources, $iframedomains, $SESSION;

    if (isset($iframesources[$values['url']])) {
        $oldname = $iframesources[$values['url']];

        db_begin();
        delete_records('iframe_source', 'prefix', $values['url']);

        // If this was the last site using this name, remove it from
        // the domain list too.
        unset($iframesources[$values['url']]);
        if (!in_array($oldname, $iframesources)) {
            delete_records('iframe_source_icon', 'name', $oldname);
        }

        update_safe_iframe_regex();
        db_commit();

        $SESSION->add_ok_msg(get_string('itemdeleted'));
    }
    else {
        $SESSION->add_error_msg(get_string('deletefailed', 'admin'));
    }

    redirect('/admin/extensions/iframesites.php');
}

function newurl_validate(Pieform $form, $values) {
    global $iframesources;

    if (!$urldata = process_allowed_iframe_url($values['url'])) {
        $form->set_error('url', get_string('iframeinvalidsite', 'admin'));
    }
    if (isset($iframesources[$urldata['key']])) {
        $form->set_error('url', get_string('urlalreadyexists', 'admin'));
    }
}

function newurl_submit(Pieform $form, $values) {
    global $iframesources, $iframedomains;

    $urldata = process_allowed_iframe_url($values['url']);

    db_begin();
    if (!isset($iframedomains[$values['name']])) {
        insert_record('iframe_source_icon', (object) array('name' => $values['name'], 'domain' => strtolower($urldata['domain'])));
    }
    insert_record('iframe_source', (object) array('prefix' => $urldata['key'], 'name' => strip_tags($values['name'])));
    update_safe_iframe_regex();
    db_commit();
    redirect('/admin/extensions/iframesites.php');
}

/**
 * To generate the htmlpurifier URI.SafeIframeRegexp for allowed iframe
 * sites, we'll only use the host and path parts of the given url, and
 * just strip out the rest.
 *
 * @param string $url A URL prefix to be used in the htmlpurifier regex
 *
 * @return array with the following elements
 *               key: key for the iframesources array and to use in the regex,
 *               domain: domain to use when fetching a favicon for the site.
 */
function process_allowed_iframe_url($url) {
    $parts = parse_url($url);

    if (isset($parts['scheme'])) {
        return false;
    }

    // Add 'http://' here just to run it through the url validator.
    $tovalidate = 'http://' . $url;

    if (!filter_var($tovalidate, FILTER_VALIDATE_URL)) {
        return false;
    }

    $parts = parse_url($tovalidate);

    if (isset($parts['host'])) {
        $domain = $parts['host'];
        $key = $parts['host'] . (isset($parts['path']) ? $parts['path'] : '');
    }
    else {
        $domain = $key = $parts['path'];
    }

    // Add a trailing slash if there's no path part in the given site,
    // to stop the user entering something obviously silly.
    if (strpos($key, '/') === false) {
        $key .= '/';
    }

    return array('domain' => $domain, 'key' => $key);
}

$js = <<<EOF
\$j(function() {
    \$j('.url-open-editform').click(function(e) {
        e.preventDefault();
        \$j('#' + this.id + '-form').toggleClass('js-hidden');
    });
});
EOF;

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('editurls', $editurls);
$smarty->assign('newform', $newform);
$smarty->display('admin/extensions/iframesites.tpl');
