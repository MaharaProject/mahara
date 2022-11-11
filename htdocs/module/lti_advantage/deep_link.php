<?php
/**
 *
 * @package    mahara
 * @subpackage module.lti_advantage
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
use \IMSGlobal\LTI;

define('INTERNAL', 1);
define('PUBLIC_ACCESS', 1);

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'collection.php');
require_once(get_config('libroot') . 'web.php');
require_once('database.php');
require_once('webservice/functions/module_lti_advantage_launch.php');

global $USER, $SESSION;

// We need session cookies to work in an iframe.
$SESSION->allow_cookie_in_iframe();

// The content security policy URL to allow the form to load in an iframe.
$cspurl = $SESSION->get('csp-ancestor-exemption');
update_csp_headers($cspurl);

$launch_id = param_alphanumext('launch_id');

$lti_db = new LTI_Advantage_Database();
$lti_cache = new LTI\Cache();
$lti_cache->set_cache_dir($CFG->dataroot . '/temp');

$launch = LTI\LTI_Message_Launch::from_cache($launch_id, $lti_db, $lti_cache);

if (!$launch->is_deep_link_launch()) {
  throw new Exception("Must be a deep link.");
}

/**
 * Here we:
 * look up the portfolio
 * verify it belongs to the current user
 * "lock" it.
 */
$portfolio_id = param_integer('portfolio_id');
$collection_id = param_integer('collection_id', null);

$data = null;

if (!empty($collection_id)) {
  // We have a Collection.
  $portfolio = new Collection($collection_id);
  $link_text_field = 'name';
}
else {
  // By default, we have a View (Page).
  $portfolio = new View($portfolio_id);
  $link_text_field = 'title';
}

// Submitting the View or Collection locks it. The content security policy URL
// can be used as our Submitted Host for locking the View or Collection.
$copy = $portfolio->submit(null, $cspurl, null, false);

if ($copy == false) {
  // We have no copy. Present all the session messages for the submitter to
  // see.  Looking at $launch->get_deep_link()->output_response_form() below
  // that does not return any html/title/body tags. Just the HTML for the form.
  // We shouldn't need to wrap this in anything.
  echo $SESSION->rendermessages();
}
else {
  // We have a copy. Prepare and return the deep link response.
  $portfolio_url = $copy->get_url();
  $portfolio_title = $copy->get($link_text_field);

  // Return the deep link resource.
  $resource = LTI\LTI_Deep_Link_Resource::new()
    ->set_url(get_config('wwwroot') . 'module/lti_advantage/login.php')
    ->set_custom_params([
      'PublicUrl' => $portfolio_url,
    ])
    ->set_title($portfolio_title);

  $launch->get_deep_link()
    ->output_response_form([$resource]);
}