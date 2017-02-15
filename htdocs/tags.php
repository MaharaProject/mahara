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
define('MENUITEM', 'myportfolio');
require('init.php');
require_once('searchlib.php');
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
      var aid = $(a).prop('id');
      var bid = $(b).prop('id');
      return aid.toLowerCase() < bid.toLowerCase() ? -1 : (aid.toLowerCase() > bid.toLowerCase() ? 1 : 0);
  }

  function sortTagFreq(a, b) {
    var aid = $(a).prop('id');
    var bid = $(b).prop('id');
      return mytags[bid] - mytags[aid];
  }

  var sort_functions = {'alpha': sortTagAlpha, 'freq': sortTagFreq};

  function rewriteTagSortLink(id, elem) {
      $(elem).on('click', function(e) {
          e.preventDefault();
          var elems = $(mytags_container).find('a.tag');
          elems.sort(sort_functions[getUrlParameter('ts', $(this).prop('href'))]);

          // FF needs spaces in between each element for wrapping
          $(mytags_container).empty();
          elems.each(function () {
              $(mytags_container).append(this, ' ')
          });

          $('a.current-tab').each(function() {
              $(this).removeClass('current-tab');
          });
          $(this).addClass('current-tab');

          return false;
      });
  }

  function rewriteTagLink(elem, keep, replace) {
      $(elem).off();
      $(elem).on('click', function(e) {
          e.preventDefault();
          var reqparams = {};
          reqparams[replace] = getUrlParameter('replace', $(this).prop('href'));
          for (var i = 0; i < keep.length; i++) {
              if (params[keep[i]]) {
                  reqparams[keep[i]] = params[keep[i]];
              }
          }

          sendjsonrequest(config.wwwroot + 'json/tagsearch.php', reqparams, 'POST', function(data) {
              p.updateResults(data);

              if (data.data.tag != params.tag) {

                  // Update tag links in the My Tags list:
                  $(mytags_container).find('a.selected', function() {
                      $(this).removeClass('selected');
                  });

                  // Mark the selected tag in the My Tags list:
                  if (data.data.tag) {
                      $('[id="tag:' + data.data.tagurl + '""]').addClass('selected');
                  }

                  // Replace the tag in the Search Results heading
                  var heading_tag = $('#results_heading a.tag').first();
                  if (heading_tag.length) {
                      heading_tag.prop('href', href);
                      heading_tag.html(data.data.tagdisplay);
                  }
                  var edit_tag_link = $('#results_container a.edit-tag').first();
                  if (edit_tag_link.length) {
                      if (data.data.tag) {
                          edit_tag_link.prop('href', config.wwwroot + 'edittags.php?tag=' + data.data.tagurl);
                          edit_tag_link.removeClass('hidden');
                      }
                      else {
                          edit_tag_link.addClass('hidden');
                      }
                  }

                  if (data.data.tag) {
                      params.tag = data.data.tag;
                  }
              }

              // Rewrite tag links in the results list:
              $('#results a.tag').each(function () {rewriteTagLink(this, [], 'tag')});

              // Change selected Sort By links above the Search results:
              if (data.data.sort != params.sort) {
                  $('#results_sort a').each(function () {
                      if ($(this).hasClass('selected') && data.data.sort != getUrlParameter('sort', $(this).prop('href'))) {
                          $(this).removeClass('selected');
                      }
                      else if (!$(this).removeClass('selected') && data.data.sort == getUrlParameter('sort', $(this).prop('href'))) {
                          $(this).addClass('selected');
                      }
                  });
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
                      }
                  });
                  params.type = data.data.type;
              }

          });
          return false;
      });
  }

      $.each(inittags, function(i, tag) {
          mytags['tag:' + tag.tagurl] = tag.count;
      });
      $('a.tag-sort').each(rewriteTagSortLink);

      mytags_container = $('#main-column-container .mytags').first();
      p = {$data->pagination_js}
      mytags_container.find('a.tag').each(function () {rewriteTagLink(this, [], 'tag')});
      $('#sb-tags a.tag').each(function () {rewriteTagLink(this, [], 'tag')});
      $('#results a.tag').each(function () {rewriteTagLink(this, [], 'tag')});
      $('#results_sort a').each(function () {rewriteTagLink(this, ['tag', 'type'], 'tag')});
      $('#results_filter a').each(function () {rewriteTagLink(this, ['tag', 'sort'], 'tag')});
  });
EOF;

$tagsortoptions = array();
foreach (array('alpha', 'freq') as $option) {
    $tagsortoptions[$option] = $option == $tagsort;
}

if (strpos($data->baseurl, 'tags.php?') !== 0) {
    $data->queryprefix = '?';
}
else {
    $data->queryprefix = '&';
}

$smarty = smarty(array('paginator'));
$smarty->assign('tags', $tags);
$smarty->assign('tagsortoptions', $tagsortoptions);
$smarty->assign('tag', $tag);
$smarty->assign('results', $data);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('tags.tpl');
