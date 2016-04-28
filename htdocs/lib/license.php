<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2012 Catalyst IT Australia Pty Ltd http://catalyst-au.net
 */

defined('INTERNAL') || die();

define('LICENSE_INSTITUTION_DEFAULT', '(institution default)');

/**
 * Given an artefact object, return the first of the two pieform elements.
 *
 * @param object $artefact The artefact
 * @param boolean $always_allow_none True to indicate that it should definitely include "allow none"; otherwise the function decides
 * @return array  A pieform element (license field).
 */
function license_form_el_basic($artefact, $always_allow_none=false) {
    if (!get_config('licensemetadata')) {
        return array(
            'type' => 'hidden',
            'ignore' => true,
        );
    }
    global $USER;
    $licenses = get_records_assoc('artefact_license', null, null, 'displayname');
    if ($licenses) {
        foreach ($licenses as $l) {
            $options[$l->name] = $l->displayname;
        }
    }

    // Determine whether to include the "none selected" option in the list of licenses
    $include_noneselected = false;
    // If it was passed in as a param, then we will include "none selected"
    if ($always_allow_none) {
        $include_noneselected = true;
    }

    $institution = $USER->get('institutions');
    if ($institution) {
        $institution = array_shift($institution);
        // If the user's institution is not set to "license mandatory", then we will include "none selected"
        if (empty($institution->licensemandatory)) {
            $include_noneselected = true;
        }
    }
    else {
        // If the user has no institution, then we will include "none selected"
        $include_noneselected = true;
    }
    if ($include_noneselected) {
        $options[''] = get_string('licensenone1');
    }

    if (empty($artefact)) {
        // Find the correct default license.
        $license = $USER->get_account_preference('licensedefault');
        // If the user is set to "institution default"
        if ($license == LICENSE_INSTITUTION_DEFAULT) {
            if ($institution and isset($institution->licensedefault)) {
                $license = $institution->licensedefault;
            }
            else {
                $license = '';
            }
        }

        if (!isset($options[$license]) && !get_config('licenseallowcustom')) {
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
        $res['options']['other'] = get_string('licenseother');
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
            'type' => 'hidden',
            'ignore' => true,
        );
    }
    if (!empty($artefact)) {
        $licensor = $artefact->get('licensor');
        $licensorurl = $artefact->get('licensorurl');
    }
    else {
        $licensor = '';
        $licensorurl = '';
    }
    return array(
        'type'        => 'fieldset',
        'collapsible' => true,
        'collapsed'   => true,
        'class'       => 'last with-formgroup',
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
 * Given the old license value and the values from the form license and
 * license_other elements, calculate the correct new license value. This is
 * mostly used by the file artefact, because it doesn't use pieforms;
 * everything else uses the similar logic in pieform_element_select_get_value.
 *
 * @param string   Old license value.
 * @param string   Value of the license form element.
 * @param string   Value of the license_other form element.
 * @param string   Optional out argument giving the reason for returning the old license.
 * @return string  New license value.
 */
function license_coalesce($old_license, $new_license, $new_license_other, &$error = '') {

    global $USER;
    if ($new_license === 'other') {
        $new_license = trim($new_license_other);
    }

    $institution = $USER->get('institutions');
    if ($institution) {
        $institution = array_shift($institution);
        if (!empty($institution->licensemandatory) and trim($new_license)==='') {
            $error = get_string('licensemandatoryerror');
            return $old_license;
        }
    }

    if (get_config('licenseallowcustom') or $new_license === $old_license) {
        return $new_license;
    }

    if (record_exists('artefact_license', 'name', $new_license)) {
        return $new_license;
    }
    else {
        $error = get_string('licensenocustomerror');
        return $old_license;
    }
}

/**
 * Given an artefact object, return the form already rendered.
 *
 * @param object   The artefact
 * @return string  HTML containing <tr> tags at the top level.
 */
function license_form_files($prefix, $prefix2=null) {
    if (!get_config('licensemetadata')) {
        return '';
    }
    if ($prefix2 !== null) {
        $prefix .= '_' . $prefix2;
    }
    $form = array(
        'name' => $prefix,
        'plugintype' => 'artefact',
        'pluginname' => 'file',
        'elements' => array(
            $prefix . '_license' => license_form_el_basic(null),
            'license_advanced' => license_form_el_advanced(null, $prefix . '_'),
        ),
    );
    $pie = pieform_instance($form);
    $pie->build();
    $rendered = $pie->get_property('elements');
    if (empty($form['elements'][$prefix . '_license']['rules']['required'])) {
        $rowattr = '';
    }
    else {
        $rowattr = 'required';
    }
    $html = '';
    foreach (array(
        $rendered[$prefix . '_license'],
        $rendered['license_advanced']['elements'][$prefix . '_licensor'],
        $rendered['license_advanced']['elements'][$prefix . '_licensorurl'],
    ) as $e) {
        $helphtml = preg_replace('/files_filebrowser_(edit_)?licens/', 'licens', $e['helphtml']);
        $html .= '<div class="form-group'.' '.$rowattr.'">' . $e['labelhtml'] . '' .
                 '' . $e['html'] . $helphtml . '</div>';
        $rowattr = '';
    }
    $html = str_replace(
        'id="' . $prefix . '_' . $prefix . '_',
        'id="' . $prefix . '_',
        $html);
    $html = str_replace(
        'for="' . $prefix . '_' . $prefix . '_',
        'for="' . $prefix . '_',
        $html);
    return $html;
}

/**
 * Given an artefact object, render the license information.
 */
function render_license($artefact) {
    global $USER;

    if (!$artefact || !($artefact instanceof ArtefactType)) {
        throw new MaharaException('The object ' . $artefact . 'is NOT an artefact');
    }
    $license = $artefact->get('license');
    $licensor = $artefact->get('licensor');
    $licensorurl = $artefact->get('licensorurl');
    // TODO: Should probably rewrite this URL cleanup code
    if ($licensorurl) {
        if ($licensor == '') {
            $licensor = preg_replace('(^https?://)', '', $license);
        }
        if (strpos($licensorurl, '://') === FALSE) {
            $licensorurl = 'http://' . $licensorurl;
        }
    }

    if ($license) {
        $details = get_record('artefact_license', 'name', $license);
    }
    else {
        // No license selected, so it's the default "all rights reserved" to the copyright holder
        if ($licensor) {
            $copyrightholder = $licensor;
        }
        else {
            $copyrightholder = $artefact->display_owner();
        }

        if ($licensorurl) {
            $copyrightholder = '<a href="' . hsc($licensorurl) . '" class="licensor">' . hsc($copyrightholder) . "</a>";
        }
        return get_string('licensenonedetailed1', 'mahara', $copyrightholder);
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

    if ($licensorurl) {
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
    if (preg_match('/^license:([a-z_-]+)\.png$/', $icon, $m)) {
        $icon = $THEME->get_image_url('license/' . $m[1]);
    }
    return $icon;
}

/**
 * Install the initial set of licenses (if the license table is empty).
 */
function install_licenses_default() {
    $exist = record_exists('artefact_license');
    if ($exist) {
        return;
    }

    $license = new stdClass();
    $license->name = 'http://creativecommons.org/licenses/by-sa/4.0/';
    $license->displayname = get_string('licensedisplaynamebysa', 'install');
    $license->shortname = get_string('licenseshortnamebysa', 'install');
    $license->icon = 'license:by-sa.png';
    insert_record('artefact_license', $license);

    $license = new stdClass();
    $license->name = 'http://creativecommons.org/licenses/by/4.0/';
    $license->displayname = get_string('licensedisplaynameby', 'install');
    $license->shortname = get_string('licenseshortnameby', 'install');
    $license->icon = 'license:by.png';
    insert_record('artefact_license', $license);

    $license = new stdClass();
    $license->name = 'http://creativecommons.org/licenses/by-nd/4.0/';
    $license->displayname = get_string('licensedisplaynamebynd', 'install');
    $license->shortname = get_string('licenseshortnamebynd', 'install');
    $license->icon = 'license:by-nd.png';
    insert_record('artefact_license', $license);

    $license = new stdClass();
    $license->name = 'http://creativecommons.org/licenses/by-nc-sa/4.0/';
    $license->displayname = get_string('licensedisplaynamebyncsa', 'install');
    $license->shortname = get_string('licenseshortnamebyncsa', 'install');
    $license->icon = 'license:by-nc-sa.png';
    insert_record('artefact_license', $license);

    $license = new stdClass();
    $license->name = 'http://creativecommons.org/licenses/by-nc/4.0/';
    $license->displayname = get_string('licensedisplaynamebync', 'install');
    $license->shortname = get_string('licenseshortnamebync', 'install');
    $license->icon = 'license:by-nc.png';
    insert_record('artefact_license', $license);

    $license = new stdClass();
    $license->name = 'http://creativecommons.org/licenses/by-nc-nd/4.0/';
    $license->displayname = get_string('licensedisplaynamebyncnd', 'install');
    $license->shortname = get_string('licenseshortnamebyncnd', 'install');
    $license->icon = 'license:by-nc-nd.png';
    insert_record('artefact_license', $license);

    $license = new stdClass();
    $license->name = 'http://www.gnu.org/copyleft/fdl.html';
    $license->displayname = get_string('licensedisplaynamegfdl', 'install');
    $license->shortname = get_string('licenseshortnamegfdl', 'install');
    $license->icon = 'license:gfdl.png';
    insert_record('artefact_license', $license);

}
