<?php
/**
 
 *
 * @package    mahara
 * @subpackage interaction
 * @author     Maxime Rigo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL

 *
 */


define('INTERNAL', 1);
define('PUBLIC', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('view.php');
safe_require('blocktype', 'wall');

$instance = param_integer('instance');
$return = param_alpha('return');




//validation
$form = pieform(array(
    'name'     => 'deletepost',
    'renderer' => 'div',
    'autofocus' => false,
    'elements' => array(
        'title' => array(
            'value' => get_string('deletepostsure', 'blocktype.wall'),
        ),
        'submit' => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto'  => get_config('wwwroot').'user/view.php'
            ),
       
    )
));


//smarty

function deletepost_submit(Pieform $form, $values) {
    global $SESSION;
    $instance = param_integer('instance');
delete_records('blocktype_wall_post', 'id', $instance);
    $SESSION->add_ok_msg(get_string('deletepostsuccess', 'blocktype.wall'));
    redirect('/user/view.php');
}


$smarty = smarty();
$smarty->assign('deleteform', $form);
$smarty->display('blocktype:wall:deletepostwall.tpl');

?>
