<?php
define('INTERNAL', 1);
define('JSON', 1);

require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once(dirname(dirname(__FILE__)). '/group/outcomes.php');
require_once(get_config('docroot') . 'lib/collection.php');

$collectionid =  param_integer('collection', null);
$formscount =  param_integer('formscount', null);
$group =  param_integer('group', null);

$collection = new Collection($collectionid);
// check if user admin
if (!($collection->get('group') && group_user_access($collection->get('group')) === 'admin')) {
  throw new AccessDeniedException();
}

$name = 'outcome' . $formscount;
$title = get_string('outcometitle', 'collection', $formscount + 1);
$form = create_outcome_form($name, $title, $collection, true);

$deletestring = get_string('deletenewoutcome', 'collection', $title);

$deleteform = '
<div class="delete-button-container">
  <span class="btn-group btn-group-top">
    <span class="delete-outcome deletebutton btn btn-secondary btn-sm">
      <a href="#" title="'. $deletestring . '">
        <span role="presentation" class="icon icon-trash-alt text-danger"></span>
      </a>
    </span>
  </span>
</div>';

json_headers();
json_reply(false, array( 'html' => $deleteform . $form));
