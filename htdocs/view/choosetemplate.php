<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');

$group = param_integer('group', null);
$institution = param_alphanum('institution', null);
View::set_nav($group, $institution);

if ($group && !group_user_can_edit_views($group) || $institution && !$USER->can_edit_institution($institution)) {
    throw new AccessDeniedException();
}

define('TITLE', get_string('copyaview', 'view'));

$owners = new StdClass;
$owners->query    = trim(param_variable('ownerquery', ''));
$owners->template = null;
$owners->offset   = param_integer('owneroffset', 0);
$owners->limit    = param_integer('ownerlimit', 10);
View::get_viewownersearch_data($owners);

$views = new StdClass;
$views->query     = trim(param_variable('viewquery', ''));
$views->offset    = param_integer('viewoffset', 0);
$views->limit     = param_integer('viewlimit', 10);
$views->ownedby   = null;
if ($ownertype = param_alpha('owntype', null)) {
    $views->ownedby = (object) array($ownertype => param_alphanum('ownid'));
}
$views->copyableby = (object) array('group' => $group, 'institution' => $institution, 'user' => null);
if (!($group || $institution)) {
    $views->copyableby->user = $USER->get('id');
}
View::get_templatesearch_data($views);

$js = <<<EOF
ownerlist = new SearchTable('viewownersearch');
templatelist = new SearchTable('templatesearch');
addLoadEvent(function() {
  ownerlist.rewriteOther = function () {
    forEach(getElementsByTagAndClassName('td', 'selectowner', 'viewownersearch'), function(i) {
      disconnectAll(i);
      connect(i, 'onclick', function (e) {
        e.stop();
        var children = getElementsByTagAndClassName('a', null, this);
        if (children.length == 1) {
          var href = getNodeAttribute(children[0], 'href');
          templatelist.params = parseQueryString(href.substring(href.indexOf('?')+1, href.length));
          templatelist.params.viewlimit = {$views->limit};
          templatelist.params.viewoffset = 0;
          templatelist.doSearch();
        }
      });
    });
  };
  ownerlist.rewriteOther();
});
EOF;

$smarty = smarty(array('searchtable'));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('heading', TITLE);
$smarty->assign('owners', $owners);
$smarty->assign('views', $views);
$smarty->display('view/choosetemplate.tpl');

?>