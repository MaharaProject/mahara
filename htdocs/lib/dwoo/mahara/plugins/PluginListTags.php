<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {list_tags} function plugin
 *
 * Type:     function<br>
 * Name:     str<br>
 * Date:     June 22, 2006<br>
 * Purpose:  Render a list of tags
 * @author   Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @author   Penny Leach <penny@mjollnir.org>
 * @version  1.0
 * @param array
 * @param Smarty
 * @return Internationalized string
 */
use Dwoo\Core;

function PluginListTags(Core $core, $tags, $owner, $view = null, $showtags = null) {
    global $USER;
    if (!is_array($tags)) {
        return '';
    }
    if ($view) {
        $viewobj= new View($view);
    }
    foreach ($tags as &$t) {
        if ($view && !$viewobj->get('owner')) {
            $t = (is_array($t) ? hsc(str_shorten_text($t['tag'], 50)) : hsc(str_shorten_text($t, 50)));
        }
        else if ($owner != $USER->get('id')) {
            if (is_array($t)) {
                $t = '<a class="tag" href="' . get_config('wwwroot') . 'relatedtags.php?tag=' . urlencode($t['tag']) . '&view=' . $t['view'] . '">' . hsc(str_shorten_text($t['tag'], 50)) . '</a>';
            }
            else if ($view) {
                $t = '<a class="tag" href="' . get_config('wwwroot') . 'relatedtags.php?tag=' . urlencode($t) . '&view=' . $view . '">' . hsc(str_shorten_text($t, 50)) . '</a>';
            }
            else {
                $t = hsc(str_shorten_text($t, 50));
            }
        }
        else {
            if (is_array($t)) {
                $t = '<a class="tag" href="' . get_config('wwwroot') . 'tags.php?tag=' . urlencode($t['tag']) . '&view=' . $t['view'] . '">' . hsc(str_shorten_text($t['tag'], 50)) . '</a>';
            }
            else {
                $t = '<a class="tag" href="' . get_config('wwwroot') . 'tags.php?tag=' . urlencode($t) . '">' . hsc(str_shorten_text($t, 50)) . '</a>';
            }
        }
    }

    return join(', ', $tags);
}

?>
