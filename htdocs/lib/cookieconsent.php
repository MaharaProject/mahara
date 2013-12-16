<?php
/**
 *
 * @package    mahara
 * @subpackge  admin
 * @author     Gregor Anzelj
 * @author     Silktide Ltd.
 * @author URI http://sitebeam.net/cookieconsent/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @license    http://sitebeam.net/cookieconsent/documentation/licence/
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2013 Gregor Anzelj <gregor.anzelj@gmail.com>
 * @copyright  (C) 2013 Silktide Ltd.
 *
 *
 * This file incorporates work covered by the following copyright and
 * permission notice:
 *
 *    License for Cookie Consent plugin
 *    Copyright (C) 2013 Silktide Ltd.
 *
 *    This program is free software: you can redistribute it and/or modify it under the terms
 *    of the GNU General Public License as published by the Free Software Foundation, either
 *    version 3 of the License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *    without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU General Public License for more details.
 *
 */

defined('INTERNAL') || die();


function get_cookieconsent_code() {
    global $THEME;
    if ($links = $THEME->get_url('style/cookieconsent.css', true)) {
        $links = array_reverse($links);
        $stylesheets = '';
        foreach ($links as $link) {
            $stylesheets .= '<link rel="stylesheet" type="text/css" href="' . $link . '">' . "\n";
        }
    }
    $values = unserialize(get_config('cookieconsent_settings'));
    $initialisation = json_encode(array(
        'cookies' => get_cookieconsent_cookies($values['cookietypes']),
        'settings' => get_cookieconsent_settings($values),
        'strings' => get_cookieconsent_strings(),
    ), JSON_FORCE_OBJECT);
    $wwwroot = get_config('wwwroot');
    return <<<CODE
<!-- Begin Cookie Consent plugin by Silktide - http://silktide.com/cookieconsent -->
<script type="text/javascript" src="{$wwwroot}js/cookieconsent/plugin.js"></script>
{$stylesheets}
<script type="text/javascript">
// <![CDATA[
cc.initialise({$initialisation});
// ]]>
</script>
<!-- End Cookie Consent plugin -->
CODE;
}

/*
 *  Returns array of cookies, used by Cookie Consent plugin
 */
function get_cookieconsent_cookies($cookies) {
    $result = array();
    foreach ($cookies as $cookie) {
        $result[$cookie] = array();
    }
    return $result;
}

/*
 *  Returns array of settings, used by Cookie Consent plugin
 */
function get_cookieconsent_settings($values) {
    return array(
        'consenttype'            => $values['consentmode'],
        'style'                  => $values['pluginstyle'],
        'bannerPosition'         => $values['bannerposition'],
        'tagPosition'            => $values['tabposition'],
        'hideprivacysettingstab' => $values['hideprivacytab'],
        'refreshOnConsent'       => $values['refreshonconsent'],
        'ignoreDoNotTrack'       => $values['ignoredonottrack'],
        'useSSL'                 => $values['usessl'],
    );
}

/*
 *  Returns array of strings, used by Cookie Consent plugin
 */
function get_cookieconsent_strings() {
    return array(
        'advertisingDefaultTitle'        => get_string('advertisingDefaultTitle','cookieconsent'),
        'socialDefaultTitle'             => get_string('socialDefaultTitle','cookieconsent'),
        'socialDefaultDescription'       => get_string('socialDefaultDescription','cookieconsent'),
        'analyticsDefaultTitle'          => get_string('analyticsDefaultTitle','cookieconsent'),
        'analyticsDefaultDescription'    => get_string('analyticsDefaultDescription','cookieconsent'),
        'advertisingDefaultDescription'  => get_string('advertisingDefaultDescription','cookieconsent'),
        'necessaryDefaultTitle'          => get_string('necessaryDefaultTitle','cookieconsent'),
        'necessaryDefaultDescription'    => get_string('necessaryDefaultDescription','cookieconsent'),
        'defaultTitle'                   => get_string('defaultTitle','cookieconsent'),
        'defaultDescription'             => get_string('defaultDescription','cookieconsent'),
        'learnMore'                      => get_string('learnMore','cookieconsent'),
        'closeWindow'                    => get_string('closeWindow','cookieconsent'),
        'notificationTitle'              => get_string('notificationTitle','cookieconsent'),
        'notificationTitleImplicit'      => get_string('notificationTitleImplicit','cookieconsent'),
        'customCookie'                   => get_string('customCookie','cookieconsent'),
        'seeDetails'                     => get_string('seeDetails','cookieconsent'),
        'seeDetailsImplicit'             => get_string('seeDetailsImplicit','cookieconsent'),
        'hideDetails'                    => get_string('hideDetails','cookieconsent'),
        'allowCookies'                   => get_string('allowCookies','cookieconsent'),
        'allowCookiesImplicit'           => get_string('allowCookiesImplicit','cookieconsent'),
        'allowForAllSites'               => get_string('allowForAllSites','cookieconsent'),
        'savePreference'                 => get_string('savePreference','cookieconsent'),
        'saveForAllSites'                => get_string('saveForAllSites','cookieconsent'),
        'privacySettings'                => get_string('privacySettings','cookieconsent'),
        'privacySettingsDialogTitleA'    => get_string('privacySettingsDialogTitleA','cookieconsent'),
        'privacySettingsDialogTitleB'    => get_string('privacySettingsDialogTitleB','cookieconsent'),
        'privacySettingsDialogSubtitle'  => get_string('privacySettingsDialogSubtitle','cookieconsent'),
        'changeForAllSitesLink'          => get_string('changeForAllSitesLink','cookieconsent'),
        'preferenceUseGlobal'            => get_string('preferenceUseGlobal','cookieconsent'),
        'preferenceConsent'              => get_string('preferenceConsent','cookieconsent'),
        'preferenceDecline'              => get_string('preferenceDecline','cookieconsent'),
        'notUsingCookies'                => get_string('notUsingCookies','cookieconsent'),
        'allSitesSettingsDialogTitleA'   => get_string('allSitesSettingsDialogTitleA','cookieconsent'),
        'allSitesSettingsDialogTitleB'   => get_string('allSitesSettingsDialogTitleB','cookieconsent'),
        'allSitesSettingsDialogSubtitle' => get_string('allSitesSettingsDialogSubtitle','cookieconsent'),
        'backToSiteSettings'             => get_string('backToSiteSettings','cookieconsent'),
        'preferenceAsk'                  => get_string('preferenceAsk','cookieconsent'),
        'preferenceAlways'               => get_string('preferenceAlways','cookieconsent'),
        'preferenceNever'                => get_string('preferenceNever','cookieconsent'),
    );
}
