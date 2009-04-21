<?php

error_reporting(E_ALL);
define('INTERNAL', 1);
require_once(dirname(dirname(__FILE__)) . '/init.php');

$replacearray = array(
    '<br>' => '<br />',
    '&nbsp;' => '&#160;',
    '&#160' => '&#160;',
);

db_replace($replacearray);

echo 'done';
