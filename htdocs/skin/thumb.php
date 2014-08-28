<?php
/**
 *
 * @package    mahara
 * @subpackage skin
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2010-2013 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('NOCHECKPASSWORDCHANGE', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('docroot') . '/lib/skin.php');

if (!get_config('skins')) {
    throw new FeatureNotEnabledException();
}

$id = param_integer('id', 0);
$skinobj = new Skin($id);
if (!$skinobj->can_use()) {
    throw new AccessDeniedException();
}

//Set no caching for thumbnails...
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


$thumbfile = get_config('dataroot') . 'skins/' . $id . '.png';
if ($id <> 0 and file_exists($thumbfile)) {
    header('Content-type: image/png');
    readfile($thumbfile);
    exit;
}
else {
    header('Content-type: image/png');
    readfile(get_config('docroot') . 'skin/no-thumb.png');
    exit;
}
