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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');

$owner = param_integer('owner', 0);;
$groupid = param_integer('group', null);
$institution = param_alphanum('institution', null);

View::set_nav($groupid, $institution);

if ($usetemplate = param_integer('usetemplate', null)) {
    // If a form has been submitted, build it now and pieforms will
    // call the submit function straight away
    pieform(create_view_form($groupid, $institution, $usetemplate, param_integer('copycollection', null)));
}

if ($groupid && (!group_user_can_edit_views($groupid) || !group_within_edit_window($groupid)) || $institution && !$USER->can_edit_institution($institution)) {
    throw new AccessDeniedException();
}

if (!empty($groupid)) {
    $group = group_current_group();
    define('TITLE', $group->name);
}
else {
    $owner = $USER->get('id');
    define('TITLE', get_string('copyvieworcollection', 'view'));
}

define('SUBTITLE', get_string('copyvieworcollection', 'view'));

$views = new StdClass;
$views->query      = trim(param_variable('viewquery', ''));
$views->ownerquery = trim(param_variable('ownerquery', ''));
$views->offset     = param_integer('viewoffset', 0);
$views->limit      = param_integer('viewlimit', 10);
$views->copyableby = (object) array('group' => $groupid, 'institution' => $institution, 'owner' => null);
if ($groupid) {
    $views->group = $groupid;
    $helptext = get_string('choosetemplategrouppageandcollectiondescription', 'view');
}
else if ($institution) {
    $views->institution = $institution;
    if ($institution == 'mahara') {
        $helptext = get_string('choosetemplatesitepageandcollectiondescription1', 'view');
    }
    else {
        $helptext = get_string('choosetemplateinstitutionpageandcollectiondescription', 'view');
    }
}
else {
    $views->copyableby->owner = $USER->get('id');
    $helptext = get_string('choosetemplatepageandcollectiondescription', 'view');
}
View::get_templatesearch_data($views);

$strpreview = json_encode(get_string('Preview','view'));
$strclose = json_encode(get_string('Close'));
$js = <<<EOF

templatelist = new SearchTable('templatesearch');

addLoadEvent(function() {

  templatelist.rewriteOther = function () {
    forEach(getElementsByTagAndClassName('a', 'grouplink', 'templatesearch'), function(i) {
      connect(i, 'onclick', function (e) {
        e.stop();
        var href = getNodeAttribute(this, 'href');
        var params = parseQueryString(href.substring(href.indexOf('?')+1, href.length));
        sendjsonrequest(config.wwwroot + 'group/groupinfo.json.php', params, 'POST', partial(showPreview, 'small'));
      });
    });
    forEach(getElementsByTagAndClassName('a', 'userlink', 'templatesearch'), function(i) {
      connect(i, 'onclick', function (e) {
        e.stop();
        var href = getNodeAttribute(this, 'href');
        var params = parseQueryString(href.substring(href.indexOf('?')+1, href.length));
        sendjsonrequest(config.wwwroot + 'user/userdetail.json.php', params, 'POST', partial(showPreview, 'small'));
      });
    });
    forEach(getElementsByTagAndClassName('a', 'viewlink', 'templatesearch'), function(i) {
      disconnectAll(i);
      setNodeAttribute(i, 'title', {$strpreview});
      connect(i, 'onclick', function (e) {
        e.stop();
        var href = getNodeAttribute(this, 'href');
        var params = parseQueryString(href.substring(href.indexOf('?')+1, href.length));
        sendjsonrequest('viewcontent.json.php', params, 'POST', partial(showPreview, 'big'));
      });
    });
    forEach(getElementsByTagAndClassName('a', 'collectionlink', 'templatesearch'), function(i) {
      disconnectAll(i);
      setNodeAttribute(i, 'title', {$strpreview});
      connect(i, 'onclick', function (e) {
        e.stop();
        var href = getNodeAttribute(this, 'href');
        var params = parseQueryString(href.substring(href.indexOf('?')+1, href.length));
        sendjsonrequest('../collection/viewcontent.json.php', params, 'POST', partial(showPreview, 'big'));
      });
    });
  };

  templatelist.rewriteOther();

});
EOF;

$smarty = smarty(
    array('js/preview.js', 'searchtable'),
    array('<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'theme/views.css">'),
    array(),
    array('stylesheets' => array('style/views.css'))
);
$smarty->assign('INLINEJAVASCRIPT', $js);
if (!empty($groupid)) {
    $smarty->assign('PAGESUBHEADING', SUBTITLE);
    $smarty->assign('PAGEHELPNAME', '0');
    $smarty->assign('SUBPAGEHELPNAME', '1');
}
else {
    $smarty->assign('PAGEHEADING', SUBTITLE);
}
$smarty->assign('helptext', $helptext);
$smarty->assign('views', $views);
$smarty->display('view/choosetemplate.tpl');
