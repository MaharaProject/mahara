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
define('PUBLIC', 1);
define('MENUITEM', 'create/tags');
require('init.php');
require_once('searchlib.php');
require_once('view.php');
require_once('collection.php');

// We need both tag and view id to proceed
$tag  = param_variable('tag');
$viewid = param_integer('view');

// Check view id to see if we are allowed access the view and the view is owned by a user
if ($viewid) {
    $view = new View($viewid);
    $owner = $view->get('owner');
    if (!can_view_view($view) || !$owner) {
        $errorstr = get_string('accessdenied', 'error');
        throw new AccessDeniedException($errorstr);
    }
    if ($owner == $USER->get('id')) {
        // we are looking at our own stuff so send them to my tags page
        redirect('/tags.php?tag=' . $tag);
    }
}
// Now we have a valid view lets get the user displayname
$user = new User();
$user->find_by_id($owner);
$displayname = display_name($user);

$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);
$sort   = param_alpha('sort', 'title');
$type   = param_alpha('type', null);
$userobj  = (object) array('type' => 'user', 'id' => $owner, 'owner' => $owner);

if ($USER->is_logged_in()) {
    // Find all views owned by owner shared to current user
    $rawdata = View::view_search(null, null, $userobj);
    define('TITLE', get_string('relatedtags', 'mahara', $displayname));
}
else {
    $rawdata = new stdClass();
    // when logged out we restrict the tags to the page/collection being viewed
    // Check to see if it is part of a collection
    if ($view->get('collection')) {
        $rawdata->ids = array();
        $viewlist = $view->get('collection')->get('views');
        foreach ($viewlist['views'] as $v) {
            $rawdata->ids[] = $v->view;
        }
        $relatedtitle = $view->get('collection')->get('name');
    }
    else {
        // Otherwise just look at the current view
        $rawdata->ids = array($view->get('id'));
        $relatedtitle = $view->get('title');
    }
    define('TITLE', get_string('relatedtagsinview', 'mahara', $displayname, $relatedtitle));
}
// Now get the subset where either the view / collection has the tag or the artefact(s) on the view have the tag
$data = get_portfolio_items_by_tag($tag, $userobj, $limit, $offset, $sort, $type, true, $rawdata->ids);
$data->isrelated = true;
$data->viewid = $view->get('id');
build_portfolio_search_html($data);

$str = array();
foreach (array('tag', 'viewid', 'sort', 'type') as $v) {
    $str[$v] = json_encode($$v);
}

$js = <<<EOF
jQuery(function($) {
  var p = null;

  var params = {
      'tag': {$str['tag']},
      'view': {$str['viewid']},
      'sort': {$str['sort']},
      'type': {$str['type']}
  };

  function rewriteTagLink(elem, keep, replace) {
      $(elem).off();
      $(elem).on('click', function(e) {
          e.preventDefault();
          var reqparams = {};
          var currenthref = $(this).prop('href');
          for (var i = 0; i < replace.length; i++) {
              if (getUrlParameter(replace[i], currenthref)) {
                  reqparams[replace[i]] = getUrlParameter(replace[i], currenthref);
              }
          }
          for (var i = 0; i < keep.length; i++) {
              if (params[keep[i]]) {
                  if (getUrlParameter(keep[i], currenthref)) {
                      reqparams[keep[i]] = getUrlParameter(keep[i], currenthref);
                  }
                  else {
                      reqparams[keep[i]] = params[keep[i]];
                  }
              }
          }
          sendjsonrequest(config.wwwroot + 'json/relatedtagsearch.php', reqparams, 'POST', function(data) {
              p.updateResults(data);

              if (data.data.tag != params.tag) {

                  // Replace the tag in the Search Results heading
                  var heading_tag = $('#results_heading a.tag').first();
                  if (heading_tag.length) {
                      heading_tag.prop('href', currenthref);
                      heading_tag.html(data.data.tagdisplay);
                  }

                  if (data.data.tag) {
                      params.tag = data.data.tag;
                  }
              }
              // Rewrite tag links in the filter list:
              $('#results_filter a').each(function () {
                  var newurl = $(this).prop('href');
                  $(this).prop('href', updateUrlParameter(newurl, 'tag', data.data.tag));
              });
              // Rewrite tag links in the sort list:
              $('#results_sort a').each(function () {
                  var newurl = $(this).prop('href');
                  $(this).prop('href', updateUrlParameter(newurl, 'tag', data.data.tag));
              });
              // Change selected Sort By links above the Search results:
              if (data.data.sort != params.sort) {
                  $('#results_sort a').each(function () {
                      if ($(this).hasClass('selected') && data.data.sort != getUrlParameter('sort', $(this).prop('href'))) {
                          $(this).removeClass('selected');
                      }
                      else if (!$(this).hasClass('selected') && data.data.sort == getUrlParameter('sort', $(this).prop('href'))) {
                          $(this).addClass('selected');
                      }
                  });
                  params.sort = data.data.sort;
              }

              // Change selected Filter By links above the Search results:
              $('#results_filter a').each(function () {
                  if ($(this).hasClass('selected') && data.data.type != getUrlParameter('type', $(this).prop('href'))) {
                      $(this).removeClass('selected');
                  }
                  else if (!$(this).hasClass('selected') && data.data.type == getUrlParameter('type', $(this).prop('href'))) {
                      $(this).addClass('selected');
                      $('#currentfilter').text($(this).text());
                      $('#results_filter').parent().removeClass('open');
                  }
              });
          });
          return false;
      });
  }

  p = {$data->pagination_js}

  $('#results a.tag').each(function () {rewriteTagLink(this, ['tag', 'view'], ['tag'])});
  $('#results_sort a').each(function () {rewriteTagLink(this, ['tag', 'view', 'sort', 'type'], ['tag', 'sort'])});
  $('#results_filter a').each(function () {rewriteTagLink(this, ['tag', 'view', 'sort', 'type'], ['tag', 'type'])});
});
EOF;

$data->queryprefix = (strpos($data->baseurl, '?') === false ? '?' : '&');

$noresultsmessage = false;
if ($data->count <= 0) {
    if ($type) {
        $noresultsmessage = get_string('norelatedtaggeditemstoviewfiltered', 'mahara', $type, hsc($tag), $displayname);
    }
    else {
        $noresultsmessage = get_string('norelatedtaggeditemstoview', 'mahara', hsc($tag), $displayname);
    }
}

$smarty = smarty(array('paginator'));
$smarty->assign('tag', $tag);
$smarty->assign('viewid', $view->get('id'));
$smarty->assign('results', $data);
$smarty->assign('noresultsmessage', $noresultsmessage);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('relatedtags.tpl');
