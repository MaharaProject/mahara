<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2011 Catalyst IT Ltd and others; see:
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
 * @subpackage blocktype-openbadgedisplayer
 * @author     Discendum Oy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2012 Discedum Oy http://discendum.com
 * @copyright  (C) 2011 Catalyst IT Ltd http://catalyst.net.nz
 *
 */


defined('INTERNAL') || die();

class PluginBlocktypeOpenbadgedisplayer extends SystemBlocktype {

    private static $source = null;

    public static function single_only() {
        return false;
    }

    public static function get_title() {
        return get_string('title', 'blocktype.openbadgedisplayer');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.openbadgedisplayer');
    }

    public static function get_categories() {
        return array('external');
    }

    public static function get_viewtypes() {
        return array('portfolio', 'profile');
    }

    public static function get_backpack_source() {
        if (is_null(self::$source)) {
            $source = get_config('openbadgedisplayer_source');

            if (empty($source)) {
                // default values
                $source = array(
                    'backpack' => 'https://backpack.openbadges.org/',
                    'passport' => null
                );
            }

            self::$source = $source;
        }

        return self::$source;
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');
        if (empty($configdata) || !isset($configdata['badgegroup'])) {
            return;
        }

        // HACK: In Mahara 1.8 blocktypes cannot declare their own styles. Let's
        // make a small hack to include our own styles to page.
        $blocks_can_have_css = method_exists('View', 'get_all_blocktype_css');

        if (!$blocks_can_have_css) {
            global $CFG;
            $CFG->additionalhtmlhead .= '<link rel="stylesheet" type="text/css" href="' . $CFG->wwwroot . 'blocktype/openbadgedisplayer/theme/raw/static/style/style.css" media="all"></link>';
        }

        $host = 'backpack';
        $badgegroups = $configdata['badgegroup'];
        $html = '';

        // Support the legacy format (a string, not an array).
        if (is_string($badgegroups)) {
            $badgegroups = array($badgegroups);
        }

        if ($editing) {
            $items = array();

            foreach ($badgegroups as $badgegroup) {
                list($host, $bid, $group) = explode(':', $badgegroup);

                $backpack_url = self::get_backpack_url($host);
                $res = mahara_http_request(array(CURLOPT_URL => $backpack_url . "displayer/{$bid}/groups.json"));
                $json = json_decode($res->data);

                if (!empty($json->groups)) {
                    foreach ($json->groups as $g) {
                        if ((int) $group === (int) $g->groupId) {
                            $items[] = hsc($g->name) . ' (' . get_string('nbadges', 'blocktype.openbadgedisplayer', $g->badges) . ')';
                        }
                    }
                }
            }

            if (count($items) > 0) {
                $html .= '<ul>' . implode('', array_map(function ($item) { return "<li>{$item}</li>"; }, $items)) . '</ul>';
            }

            return $html;
        }
        else {
            $smarty = smarty_core();
            $smarty->assign('id', $instance->get('id'));
            $smarty->assign('badgehtml', self::get_badge_html($badgegroups));

            $has_pagemodal = true;

            // HACK: Mahara 15.10 uses a separate template to include the modal
            // window used in showPreview JS-function. Let's check whether that
            // template exists and use that information in our template.
            try {
                $sm = smarty_core();
                $sm->fetch('pagemodal.tpl');
            }
            catch (Dwoo_Exception $e) {
                // Pagemodal template does not exist.
                $has_pagemodal = false;
            }

            $smarty->assign('has_pagemodal', (int) $has_pagemodal);
            $html = $smarty->fetch('blocktype:openbadgedisplayer:openbadgedisplayer.tpl');
        }

        return $html;
    }

    private static function get_badge_html($groups) {
        $html = '';
        $existing = array();

        foreach ($groups as $group) {
            $parts = explode(':', $group);

            if (count($parts) < 3) {
                continue;
            }

            $backpack_url = self::get_backpack_url($parts[0]);
            $url = $backpack_url . 'displayer/' . $parts[1] . '/group/' . $parts[2] . '.json';
            $res = mahara_http_request(array(CURLOPT_URL => $url));

            if ($res->info['http_code'] != 200) {
                continue;
            }

            $json = json_decode($res->data);

            if (isset($json->badges) && is_array($json->badges)) {

                foreach ($json->badges as $badge) {
                    $b = $badge->assertion->badge;

                    // TODO: Simple check for unique badges, improve me!
                    if (array_key_exists($b->name, $existing) && strcmp($existing[$b->name], $b->description) === 0) {
                        continue;
                    }

                    if (self::assertion_has_expired($badge->assertion)) {
                        continue;
                    }

                    $html .= '<img '
                            . 'src="' . $b->image . '" '
                            . 'title="' . $b->name . '" '
                            . 'data-assertion="' . htmlentities(json_encode($badge->assertion)) . '" />';

                    $existing[$b->name] = $b->description;
                }
            }

        }

        return $html;
    }

    private static function assertion_has_expired($assertion) {
        if (!isset($assertion->expires)) {
            return false;
        }

        // Unix timestamp
        if (preg_match('/^[0-9]+$/', $assertion->expires)) {
            return ($assertion->expires * 1000) < time();
        }

        // Formatted date
        return strtotime($assertion->expires) < time();
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        global $USER;

        $sources = self::get_backpack_source();
        $configdata = $instance->get('configdata');
        $addresses = get_column('artefact_internal_profile_email', 'email', 'owner', $USER->id, 'verified', 1);
        $current_values = array();

        if (isset($configdata['badgegroup'])) {
            $current_values = $configdata['badgegroup'];

            // Support the legacy format (a string, not an array).
            if (is_string($current_values)) {
                $current_values = array($current_values);
            }

            foreach ($current_values as &$current_value) {
                if (substr_count($current_value, ':') == 1) {
                    // Legacy value, prepend host
                    $current_value = 'backpack:' . $current_value;
                }
            }
        }

        $sourcelinks = array();

        foreach ($sources as $source => $url) {
            if (!empty($url)) {
                $title = get_string('title_' . $source, 'blocktype.openbadgedisplayer');
                $sourcelinks[] = '<a href="' . $url . '" target="_blank">' . $title . '</a>';
            }
        }

        $fields = array(
            'message' => array(
                'type' => 'html',
                'class' => '',
                'value' => '<p class="message">'. get_string('confighelp', 'blocktype.openbadgedisplayer', implode(', ', $sourcelinks)) .'</p>'
            ),
            'badgegroups' => array(
                'type' => 'container',
                'class' => '',
                'elements' => array()
            )
        );

        $groupcount = 0;

        foreach (array_keys($sources) as $source) {
            $groups = self::get_form_fields($source, $addresses);
            $fields['badgegroups']['elements'][$source] = array(
                'title' => get_string('title_' . $source, 'blocktype.openbadgedisplayer'),
                'type' => 'checkboxes',
                'class' => '',
                'labelwidth' => false,
                'elements' => $groups
            );

            // Set checked states for elements.
            if (is_array($groups)) {
                $groupcount += count($groups);

                foreach ($fields['badgegroups']['elements'][$source]['elements'] as &$element) {
                    $element['defaultvalue'] = in_array($element['value'], $current_values);
                }
            }
        }

        if ($groupcount === 0) {
            return array(
                'colorcode' => array('type' => 'hidden', 'value' => ''),
                'title' => array('type' => 'hidden', 'value' => ''),
                'message' => array(
                    'type' => 'html',
                    'value' => '<p class="message">'. get_string('nogroups', 'blocktype.openbadgedisplayer', $sources['backpack']) .'</p>'
                )
            );
        }

        return $fields;
    }

    public static function get_instance_config_javascript(\BlockInstance $instance) {
        // pieform_element_checkboxes_get_headdata() includes the javascript
        // needed by the "Select all/none" -links. That function isn't called
        // when the config form is rendered, so let's just copy the code here
        // and add it to window scope.
        return <<<JS
            if (typeof pieform_element_checkboxes_update === 'undefined') {
                window.pieform_element_checkboxes_update = function (p, v) {
                    forEach(getElementsByTagAndClassName('input', 'checkboxes', p), function(e) {
                        if (!e.disabled) {
                            e.checked = v;
                        }
                    });
                    if (typeof formchangemanager !== 'undefined') {
                        var form = jQuery('div#' + p).closest('form')[0];
                        formchangemanager.setFormState(form, FORM_CHANGED);
                    }
                };
            }
JS;
    }

    private static function get_form_fields($host, $addresses) {
        if ( ! $host) {
            return array();
        }

        $backpackid = array();
        foreach ($addresses AS $email) {
            $backpackid[] = self::get_backpack_id($host, $email);
        }
        $backpackid = array_filter($backpackid);

        $opt = array();
        foreach ($backpackid AS $uid) {
            $opt += self::get_group_opt($host, $uid);
        }

        return $opt;

    }


    private static function get_backpack_id($host, $email) {
        $backpack_url = self::get_backpack_url($host);

        if ($backpack_url !== false) {
            $res = mahara_http_request(
                array(
                    CURLOPT_URL        => $backpack_url . 'displayer/convert/email',
                    CURLOPT_POST       => 1,
                    CURLOPT_POSTFIELDS => 'email=' . urlencode($email)
                )
            );
            $res = json_decode($res->data);
            if (isset($res->userId)) {
                return $res->userId;
            }
        }
        return null;
    }


    private static function get_group_opt($host, $uid) {
        $opt = array();
        $backpack_url = self::get_backpack_url($host);
        $res = mahara_http_request(array(CURLOPT_URL => $backpack_url . "displayer/{$uid}/groups.json"));
        $res = json_decode($res->data);

        if (!empty($res->groups)) {
            foreach ($res->groups AS $g) {
                if ($g->name == 'Public badges' && $g->groupId == 0) {
                    $name = get_string('obppublicbadges', 'blocktype.openbadgedisplayer');
                }
                else {
                    $name = hsc($g->name);
                }

                $name .= ' (' . get_string('nbadges', 'blocktype.openbadgedisplayer', $g->badges) . ')';
                $cb_id = $host . ':' . $uid . ':' . $g->groupId;
                $cb_name = self::_sanitize_name($cb_id);
                $opt[$cb_name] = array(
                    'type' => 'checkbox',
                    'title' => $name,
                    'value' => $cb_id
                );
            }
        }
        return $opt;
    }

    public static function get_backpack_url($host) {
        $sources = self::get_backpack_source();

        return isset($sources[$host]) ? $sources[$host] : false;
    }

    private static function _sanitize_name($name) {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
    }

    public static function instance_config_save($values) {
        unset($values['message']);
        // Support old save format.
        $sources = array_keys(self::get_backpack_source());
        $values['badgegroup'] = array();

        foreach ($sources as $source) {
            if (isset($values[$source])) {
                $values['badgegroup'] = array_merge($values['badgegroup'], $values[$source]);
                unset($values[$source]);
            }
        }
        return $values;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

    public static function get_instance_javascript() {
        return array(get_config('wwwroot') . 'js/preview.js');
    }

}
