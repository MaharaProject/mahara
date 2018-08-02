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

$views = new stdClass();
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

jQuery(function($) {

  templatelist.rewriteOther = function () {
    $('#templatesearch a.grouplink').each(function() {
      $(this).on('click', function (e) {
        e.preventDefault();
        var href = $(this).prop('href');
        var params = {
          'id': getUrlParameter('id', href)
        }
        sendjsonrequest(config.wwwroot + 'group/groupinfo.json.php', params, 'POST', showPreview.bind(null, 'small'));
      });
  });
    $('#templatesearch a.userlink').each(function() {
      jQuery(this).on('click', function (e) {
        e.preventDefault();
        var href = jQuery(this).prop('href');
        var params = {
          'id': getUrlParameter('id', href)
        }
        sendjsonrequest(config.wwwroot + 'user/userdetail.json.php', params, 'POST', showPreview.bind(null, 'small'));
      });
    });
    $('#templatesearch a.viewlink').each(function() {
      $(this).off();
      $(this).prop('title', {$strpreview});
      $(this).on('click', function (e) {
        e.preventDefault();
        var href = $(this).prop('href');
        var params = {
          'id': getUrlParameter('id', href)
        }
        sendjsonrequest('viewcontent.json.php', params, 'POST', showPreview.bind(null, 'big'));
      });
    });
    $('#templatesearch a.collectionlink').each(function() {
      $(this).off();
      $(this).prop('title', {$strpreview});
      $(this).on('click', function (e) {
        e.preventDefault();
        var href = $(this).prop('href');
        var params = {
          'id': getUrlParameter('id', href)
        }
        sendjsonrequest('../collection/viewcontent.json.php', params, 'POST', showPreview.bind(null, 'big'));
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
