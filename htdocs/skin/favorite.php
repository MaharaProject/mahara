<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2010 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @subpackage skin
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2010-2013 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

define('INTERNAL', true);
require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once('skin.php');

if (!get_config('skins')) {
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
