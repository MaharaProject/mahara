<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');

$owner = param_integer('owner', 0);;
$groupid = param_integer('group', null);
if (!empty($groupid)) {
    define('SUBSECTIONHEADING', get_string('Views', 'view'));
}
$institution = param_alphanum('institution', null);
$searchcollection = param_integer('searchcollection', false);
View::set_nav($groupid, $institution, false, $searchcollection);

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

$subtitle = get_string('copyvieworcollection', 'view');

$views = new StdClass;
$views->query      = trim(param_variable('viewquery', ''));
$views->ownerquery = trim(param_variable('ownerquery', ''));
$views->offset     = param_integer('viewoffset', 0);
$views->limit      = param_integer('limit', 10);
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
$sort[] = array('column' => 'title',
                'desc' => 0,
                );
if ($searchcollection) {
    array_unshift($sort, array('column' => 'name',
                               'desc' => 0,
                               'tablealias' => 'c'
                               ));
    $views->collection = $searchcollection;
}
$views->sort = (object) $sort;
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
    array('js/preview.js', 'searchtable', 'paginator'),
    array(),
    array('stylesheets' => array('style/views.css'))
);
$smarty->assign('INLINEJAVASCRIPT', $js);
if (!empty($groupid)) {
    $smarty->assign('PAGESUBHEADING', $subtitle);
    $smarty->assign('PAGEHELPNAME', '0');
    $smarty->assign('SUBPAGEHELPNAME', '1');

    $smarty->assign('headingclass', 'page-header');
}
else {
    $smarty->assign('PAGEHEADING', $subtitle);
}
$smarty->assign('helptext', $helptext);
$smarty->assign('views', $views);
$smarty->display('view/choosetemplate.tpl');
