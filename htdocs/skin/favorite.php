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

define('INTERNAL', true);
require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once('skin.php');

if (!can_use_skins()) {
    throw new FeatureNotEnabledException();
}

global $USER;
$add = param_integer('add', 0); // id of skin to add to favorites...
$del = param_integer('del', 0); // id of skin to remove from favorites...

// Add to Favorites
if ($add > 0) {
    $favorites = get_field('skin_favorites', 'favorites', 'user', $USER->get('id'));
    if (!$favorites) {
        // if user haven't added any skin to favorites yet, create empty array...
        $favorites = array($add);
        insert_record('skin_favorites', (object) array(
            'user'        => $USER->get('id'),
            'favorites'    => serialize($favorites),
        ));
        $SESSION->add_ok_msg(get_string('skinaddedtofavorites', 'skin'));
    }
    else {
        $favorites = unserialize($favorites);
        if (is_array($favorites)) {
            // add skin id to favorites...
            $favorites = array_merge($favorites, array($add));
        }
        else {
            $favorites = array($add);
        }
        set_field('skin_favorites', 'favorites', serialize($favorites), 'user', $USER->get('id'));
        $SESSION->add_ok_msg(get_string('skinaddedtofavorites', 'skin'));
    }
}

// Remove from Favorites
if ($del > 0) {
    $favorites = get_field('skin_favorites', 'favorites', 'user', $USER->get('id'));
    if ($favorites) {
        $favorites = unserialize($favorites);
        // check if user added any skin to favorites yet...
        if (is_array($favorites)) {
            // remove skin id from favorites...
            $favorites = array_diff($favorites, array($del));
            set_field('skin_favorites', 'favorites', serialize($favorites), 'user', $USER->get('id'));
            $SESSION->add_ok_msg(get_string('skinremovedfromfavorites', 'skin'));
        }
        else {
            $SESSION->add_error_msg(get_string('cantremoveskinfromfavorites', 'skin'));
        }
    }
}

redirect('/skin/index.php');
