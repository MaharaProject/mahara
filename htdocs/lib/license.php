<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2012 Catalyst IT Australia Pty Ltd http://catalyst-au.net
 */

defined('INTERNAL') || die();

/**
 * Given an artefact object, return the first of the two pieform elements.
 *
 * @param object  The artefact
 * @return array  A pieform element (license field).
 */
function license_form_el_basic($artefact, $always_allow_none=false) {
    if (!get_config('licensemetadata')) {
        return array(
            'ignore' => true,
        );
    }
    global $USER;
    $licenses = get_records_assoc('artefact_license', null, null, 'displayname');
    foreach ($licenses as $l) {
        $options[$l->name] = $l->displayname;
    }

    if ($always_allow_none) {
        $options[''] = '';
    }

    $institution = $USER->get('institutions');
    if ($institution) {
        $institution = array_shift($institution);
        if (empty($institution->licensemandatory)) {
            $options[''] = '';
        }
    }
    else {
        $options[''] = '';
    }
    if (empty($artefact)) {
        // Find the correct default license.
        $license = $USER->get_account_preference('licensedefault');
        if ($license === NULL or $license === '-') {
            if ($institution and isset($institution->licensedefault)) {
                $license = $institution->licensedefault;
            }
            else {
                $license = '';
            }
        }
        if (!isset($options[$license]) and !get_config('licenseallowcustom')) {
            // Note: this won't happen normally, but it can happen for instance
            // if the site admin removes a license which is the default for the
            // user's institution.
            $license = array_keys($licenses);
            $license = array_shift($license);
        }
    }
    else {
        $license = $artefact->get('license');
        if (empty($license)) {
            $license = '';
        }
        if (!isset($options[$license])) {
            $options[$license] = $license;
        }
    }

    if (isset($options[''])) {
        $options[''] = get_string('licensenone');
    }
    $res = array(
        'defaultvalue' => $license,
        'type'         => 'select',
        'options'      => $options,
        'title'        => get_string('license'),
        'description'  => get_string('licensedesc'),
        'help'         => true,
    );
    if (get_config('licenseallowcustom')) {
        $res['allowother'] = true;
        $res['options']['other'] = 'Other license (enter URL)';
    }
    if (!isset($options[''])) {
        $res['rules'] = array('required' => true);
    }
    return $res;
}


/**
 * Given an artefact object, return the second of the two pieform elements.
 *
 * @param object  The artefact
 * @param string  A prefix for each of the element names; default ''.
 * @return array  A pieform element (fieldset with licensor and licensorurl fields).
 */
function license_form_el_advanced($artefact, $prefix = '') {
    if (!get_config('licensemetadata')) {
        return array(
            'ignore' => true,
        );
    }
    if (!empty($artefact)) {
        $licensor = $artefact->get('licensor');
        $licensorurl = $artefact->get('licensorurl');
    }
    return array(
        'type'        => 'fieldset',
        'collapsible' => true,
        'collapsed'   => true,
        'legend'      => get_string('licensingadvanced'),
        'elements'    => array(
            $prefix . 'licensor' => array(
                'defaultvalue' => $licensor,
                'type'         => 'text',
                'title'        => get_string('licensor'),
                'description'  => get_string('licensordesc'),
                'help'         => true,
            ),
            $prefix . 'licensorurl' => array(
                'defaultvalue' => $licensorurl,
                'type'         => 'text',
                'title'        => get_string('licensorurl'),
                'description'  => get_string('licensorurldesc'),
                'help'         => true,
            ),
        )
    );

}

/**
 * Given an artefact object, render the license information.
 */
function render_license($artefact) {
    $license = $artefact->get('license');
    $licensor = $artefact->get('licensor');
    $licensorurl = $artefact->get('licensorurl');

    if ($license) {
        $details = get_record('artefact_license', 'name', $license);
    }
    if (strpos($license, '://') === FALSE) {
        $license = 'http://' . $license;
    }

    if (!empty($details)) {
        $html = '<a href="' . hsc($license) . '" class="license">';
        if ($details->icon) {
            $html .= '<img src="' . license_icon_url($details->icon) . '" class="license-icon"' .
                'alt="' . get_string('licenseiconalt') . '">';
        }
        $html .= hsc($details->displayname) . '</a>';
        if ($details->shortname != '') {
            $html .= ' (' . $details->shortname . ')';
        }
    }
    else {
        // No details configured, just format it up as a linked URL.
        $html = '<a href="' . hsc($license) . '" class="license">' .
            hsc(preg_replace('(^https?://)', '', $license)) . '</a>';
    }

    if ($licensorurl != '') {
        if ($licensor == '') {
            $licensor = preg_replace('(^https?://)', '', $license);
        }
        if (strpos($licensorurl, '://') === FALSE) {
            $licensorurl = 'http://' . $licensorurl;
        }
        $html .= '<br>' . get_string('licensor') . ': '
              . '<a href="' . hsc($licensorurl) . '" class="licensor">'
              . hsc($licensor) . '</a>';
    }
    else if ($licensor != '') {
        $html .= '<br>' . get_string('licensor') . ': '
              . '<span class="licensor">' . hsc($licensor) . '</span>';
    }
    else {
        $html .= '<br><span class="licensor"></span>';
    }


    return $html;
}

/**
 * Return the URL for a given icon.
 */
function license_icon_url($icon) {
    global $THEME;
    if (preg_match('/^license:([a-z_-]+\.png)$/', $icon, $m)) {
        $icon = $THEME->get_url('images/license/' . $m[1]);
    }
    return $icon;
}
