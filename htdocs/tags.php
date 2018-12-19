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
define('MENUITEM', 'create/tags');
require('init.php');
require_once('searchlib.php');
require_once('view.php');
require_once('collection.php');

define('TITLE', get_string('mytags'));

$tagsort = param_alpha('ts', null) != 'freq' ? 'alpha' : 'freq';
$tags = get_my_tags(null, false, $tagsort);

$tag    = param_variable('tag', null);

$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);
$sort   = param_alpha('sort', 'name');
$type   = param_alpha('type', null);
$owner  = (object) array('type' => 'user', 'id' => $USER->get('id'));

$data = get_portfolio_items_by_tag($tag, $owner, $limit, $offset, $sort, $type);
build_portfolio_search_html($data);

$str = array();
foreach (array('tags', 'tag', 'sort', 'type') as $v) {
    $str[$v] = json_encode($$v);
}

$js = <<<EOF
jQuery(function($) {
  var p = null;
  var mytags_container = null;
  var inittags = {$str['tags']};
  var mytags = {};

  var params = {
      'tag': {$str['tag']},
      'sort': {$str['sort']},
      'type': {$str['type']}
  };

  function sortTagAlpha(a, b) {
      var aid = $(a).children().prop('id');
      var bid = $(b).children().prop('id');
      return aid.toLowerCase() < bid.toLowerCase() ? -1 : (aid.toLowerCase() > bid.toLowerCase() ? 1 : 0);
  }

  function sortTagFreq(a, b) {
      var aid = $(a).children().prop('id');
      var bid = $(b).children().prop('id');
      return mytags[bid] - mytags[aid];
  }

  var sort_functions = {'alpha': sortTagAlpha, 'freq': sortTagFreq};

  function rewriteTagSortLink(id, elem) {
      $(elem).on('click', function(e) {
          e.preventDefault();
          var ul = $(mytags_container).children();
          var li = ul.children("li");
          li.detach().sort(sort_functions[getUrlParameter('ts', $(this).prop('href'))]);
          ul.append(li);

          // set all tabs to inactive
          $('ul.nav-tabs li').each(function() {
              $(this).removeClass('active');
              $(this).find('a').removeClass('active');
              $(this).find('.sr-only').html('(' + get_string_ajax('tab', 'mahara') + ')');
          });
          // set current one to active
          $(this).closest('li').addClass('active');
          $(this).addClass('active');
          $(this).find('.sr-only').html('(' + get_string_ajax('tab', 'mahara') + ' ' + get_string_ajax('selected', 'mahara') + ')');

          return false;
      });
  }

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

          sendjsonrequest(config.wwwroot + 'json/tagsearch.php', reqparams, 'POST', function(data) {
              p.updateResults(data);

              if (data.data.tag != params.tag) {

                  // Update tag links in the My Tags list:
                  $(mytags_container).find('a.selected').removeClass('selected');

                  // Mark the selected tag in the My Tags list:
                  if (data.data.tag) {
                      $('[id="tag:' + data.data.tagurl + '"]').addClass('selected');
                  }

                  // Replace the tag in the Search Results heading
                  var heading_tag = $('#results_heading a.tag').first();
                  if (heading_tag.length) {
                      heading_tag.prop('href', currenthref);
                      heading_tag.html(data.data.tagdisplay);
                  }
                  var edit_tag_link = $('#results_container a.edit-tag').first();
                  if (edit_tag_link.length) {
                      if (data.data.tag && !data.data.is_institution_tag) {
                          edit_tag_link.prop('href', config.wwwroot + 'edittags.php?tag=' + data.data.tagurl);
                          edit_tag_link.removeClass('d-none');
                      }
                      else {
                          edit_tag_link.addClass('d-none');
                      }
                  }

                  if (data.data.tag) {
                      params.tag = data.data.tag;
                  }
              }

              // Rewrite tag links in the results list:
              $('#results a.tag').each(function () {rewriteTagLink(this, [], ['tag'])});
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
                  $('#results_filter a').each(function () {rewriteTagLink(this, ['tag', 'sort'], ['type'])});
                  params.sort = data.data.sort;
              }

              // Change selected Filter By links above the Search results:
              if (data.data.type != params.type) {
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
                  $('#results_sort a').each(function () {rewriteTagLink(this, ['tag', 'type'], [ 'sort'])});
                  params.type = data.data.type;
              }

          });
          return false;
      });
  }

  $.each(inittags, function(i, tag) {
      mytags['tag:' + tag.tagurl] = tag.count;
  });
  $('ul.nav-tabs a').each(rewriteTagSortLink);

  mytags_container = $('#main-column-container .mytags').first();
  p = {$data->pagination_js}

  mytags_container.find('a.tag').each(function () {rewriteTagLink(this, [], ['tag'])});
  $('#sb-tags a.tag').each(function () {rewriteTagLink(this, [], ['tag'])});
  $('#results a.tag').each(function () {rewriteTagLink(this, [], ['tag'])});
  $('#results_sort a').each(function () {rewriteTagLink(this, ['tag', 'type', 'sort'], ['tag', 'sort'])});
  $('#results_filter a').each(function () {rewriteTagLink(this, ['tag', 'type', 'sort'], ['tag', 'type'])});
});
EOF;

$tagsortoptions = array();
foreach (array('alpha', 'freq') as $option) {
    $tagsortoptions[$option] = $option == $tagsort;
}

$data->queryprefix = (strpos($data->baseurl, '?') === false ? '?' : '&');

$notinstitutiontag = true;
if ($tag) {
    $tagname = strpos($tag, ':') ? explode(': ', $tag)[1] : $tag;
    if (get_field('tag', 'ownertype', 'tag', $tagname) == 'institution') {
        $notinstitutiontag = false;
    }
}
$smarty = smarty(array('paginator'));
setpageicon($smarty, 'icon-tags');
$smarty->assign('tags', $tags);
$smarty->assign('tagsortoptions', $tagsortoptions);
$smarty->assign('tag', $tag);
$smarty->assign('not_institution_tag', $notinstitutiontag);
$smarty->assign('results', $data);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('tags.tpl');
