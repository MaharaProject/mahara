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

$string['cookieconsent'] = 'Cookie Consent';
$string['cookieconsentintro1'] = 'Directive 2009/136/EC of the European Parliament and of the Council, which is also known as the "Cookie Directive" or "Cookie Law", is the instrument that defines the requirements for consent for cookies across the European Union. Basically, this is an amendment of the earlier directive: Directive 2002/58/EC, and is broadly concerned with the protection of data and privacy on the web and in other forms of electronic communication.';
$string['cookieconsentintro2'] = 'The new directive came into effect on 25 May 2011. The text of the directive is about 26 pages long, but the most important paragraph about cookies can be found on page 20, which changes the Article 5(3) of the earlier Directive 2002/58/EC:';
$string['cookieconsentintro3'] = '"Member States shall ensure that the storing of information, or the gaining of access to information already stored, in the terminal equipment of a subscriber or user is only allowed on condition that the subscriber or user concerned has given his or her consent, having been provided with clear and comprehensive information, in accordance with Directive 95/46/EC, inter alia, about the purposes of the processing. This shall not prevent any technical storage or access for the sole purpose of carrying out the transmission of a communication over an electronic communications network, or as strictly necessary in order for the provider of an information society service explicitly requested by the subscriber or user to provide the service."';
$string['readfulltext1'] = 'Read the full text of the directive';
$string['directive2009136'] = 'Directive 2009/136/EC';
$string['readdirectiveBG'] = 'Read Directive 2009/136/EC in Bulgarian';
$string['readdirectiveCS'] = 'Read Directive 2009/136/EC in Czech';
$string['readdirectiveDA'] = 'Read Directive 2009/136/EC in Danish';
$string['readdirectiveDE'] = 'Read Directive 2009/136/EC in German';
$string['readdirectiveEL'] = 'Read Directive 2009/136/EC in Greek';
$string['readdirectiveEN'] = 'Read Directive 2009/136/EC in English';
$string['readdirectiveES'] = 'Read Directive 2009/136/EC in Spanish';
$string['readdirectiveET'] = 'Read Directive 2009/136/EC in Estonian';
$string['readdirectiveFI'] = 'Read Directive 2009/136/EC in Finnish';
$string['readdirectiveFR'] = 'Read Directive 2009/136/EC in French';
$string['readdirectiveHU'] = 'Read Directive 2009/136/EC in Hungarian';
$string['readdirectiveIT'] = 'Read Directive 2009/136/EC in Italian';
$string['readdirectiveLT'] = 'Read Directive 2009/136/EC in Lithuanian';
$string['readdirectiveLV'] = 'Read Directive 2009/136/EC in Latvian';
$string['readdirectiveMT'] = 'Read Directive 2009/136/EC in Maltese';
$string['readdirectiveNL'] = 'Read Directive 2009/136/EC in Dutch';
$string['readdirectivePL'] = 'Read Directive 2009/136/EC in Polish';
$string['readdirectivePT'] = 'Read Directive 2009/136/EC in Portuguese';
$string['readdirectiveRO'] = 'Read Directive 2009/136/EC in Romanian';
$string['readdirectiveSK'] = 'Read Directive 2009/136/EC in Slovak';
$string['readdirectiveSL'] = 'Read Directive 2009/136/EC in Slovene';
$string['readdirectiveSV'] = 'Read Directive 2009/136/EC in Swedish';
$string['cookieconsentintro4'] = 'In short, this means before somebody can store or retrieve any information from a computer, mobile phone or other device, the user must give informed consent to do so. The intention is to increase the privacy of the end user and prevent organisations from obtaining information about people without them knowing about it.';
$string['cookieconsentintro51'] = 'First enable the %sCookie Consent plugin by Silktide%s below, then set the desired options and save your changes. You will be redirected to another page with detailed instructions of what to modify or how to update your theme header files or the <tt>$cfg->additionalhtmlhead</tt> config setting for Cookie Control to take full effect.';
$string['cookieconsentenable'] = 'Enable Cookie Consent';
$string['cookieconsentenabled'] = 'Cookie Consent enabled and settings saved';
$string['cookieconsentdisabled'] = 'Cookie Consent disabled';

$string['cookieconsent2'] = 'Additional site modifications';
$string['additionalmodifications'] = 'If you want Cookie Control to take full effect, you will have to modify or update your theme header files or the <tt>$cfg->additionalhtmlhead</tt> config setting.';
$string['instructiontext1'] = 'Find any Javascript elements that set %s cookies. Examples might include %s.';
$string['instructiontext2'] = 'Modify the <tt>&lt;script&gt;</tt> tag so that the type attribute is "text/plain" rather than "text/javascript"';
$string['instructiontext3'] = 'Add a class of "%s" to the <tt>&lt;script&gt;</tt> tag';
$string['example1social'] = 'Facebook buttons and Twitter widgets';
$string['example1analytics'] = 'Google Analytics and StatCounter';
$string['example1advertising'] = 'Google AdSense and other targeted advertising plugins';
$string['example1necessary'] = 'Google AdSense and other targeted advertising plugins';
$string['example'] = 'Example';
$string['examplebefore'] = 'Before:';
$string['exampleafter'] = 'After (changes are in bold):';
$string['itdidntwork'] = 'It didn\'t work.';
$string['itdidntwork1'] = 'First, try substituting <b>%s</b> for <b>%s</b>. This fixes some Javascript plugins which use <tt>document.write()</tt>.';
$string['itdidntwork2'] = 'If this doesn\'t help, %scheck out Cookie Consent code examples page%s or ask for help in the %sCookie Consent LinkedIn group%s.';

$string['generaloptions'] = 'General options';
$string['cookietypes'] = 'Types of cookies';
$string['cookietypesdesc'] = 'Select which types of cookies are in use on your site.';
$string['cookietypessocial'] = 'Social media';
$string['cookietypesanalytics'] = 'Analytics';
$string['cookietypesadvertising'] = 'Advertising';
$string['cookietypesnecessary'] = 'Strictly necessary';
$string['consentmode'] = 'Consent mode';
$string['consentmodedesc1'] = 'Cookie Consent will always use explicit mode when a browser\'s "do not track" setting is enabled (unless overridden in "Feature options" below).';
$string['consentmodedesc2'] = 'The "do not track" setting is enabled by default in the latest versions of Internet Explorer.';
$string['consentmodeexplicit'] = 'Explicit - no cookies will be set until a visitor consents';
$string['consentmodeimplicit'] = 'Implied - set cookies and allow visitors to opt out';

$string['stylingoptions'] = 'Styling options';
$string['pluginstyle'] = 'Style';
$string['pluginstyledesc'] = 'Change the look of Cookie Consent.';
$string['pluginstyledark'] = 'Dark';
$string['pluginstylelight'] = 'Light';
$string['bannerposition'] = 'Banner position';
$string['bannerpositiondesc'] = 'Choose whether the consent banner appears at the top or bottom of the screen.';
$string['bannerpositiontop'] = 'Top';
$string['bannerpositionpush'] = 'Push from top (experimental)';
$string['bannerpositionbottom'] = 'Bottom';
$string['tabposition'] = 'Tab position';
$string['tabpositiondesc'] = 'Choose where the privacy settings tab appears.';
$string['tabpositionbottomright'] = 'Bottom right';
$string['tabpositionbottomleft'] = 'Bottom left';
$string['tabpositionverticalleft'] = 'Left side';
$string['tabpositionverticalright'] = 'Right side';
$string['hideprivacytab'] = 'Hide privacy settings tab';
$string['hideprivacytabdesc'] = 'If you plan to use your own privacy settings link in-line (e.g. in your theme\'s template), you may wish to disable the standard privacy settings tab.';

$string['featureoptions'] = 'Feature options';
$string['pagerefresh'] = 'Page refresh';
$string['pagerefreshdesc'] = 'If you have a server-side application that needs to be aware of the consent to cookies, selecting this option will cause the page to be reloaded after consent has been given.';
$string['ignoredonottrack'] = 'Ignore "do not track"';
$string['ignoredonottrackdesc'] = 'Enabling this setting will mean Cookie Consent ignores any "do not track" headers from the visitor\'s browser.';
$string['usessl'] = 'Use SSL';
$string['usessldesc'] = 'If any of the pages Cookie Consent appears on use SSL, you will need to select this option.';

/* ===== COOKIE CONSENT strings ===== */
/* ----- General ----- */
$string['advertisingDefaultTitle'] = 'Advertising';
$string['socialDefaultTitle'] = 'Social media';
$string['socialDefaultDescription'] = 'Facebook, Twitter and other social websites need to know who you are to work properly.';
$string['analyticsDefaultTitle'] = 'Analytics';
$string['analyticsDefaultDescription'] = 'We anonymously measure your use of this website to improve your experience.';
$string['advertisingDefaultDescription'] = 'Adverts will be chosen for you automatically based on your past behaviour and interests.';
$string['necessaryDefaultTitle'] = 'Strictly necessary';
$string['necessaryDefaultDescription'] = 'Some cookies on this website are strictly necessary and cannot be disabled.';
$string['defaultTitle'] = 'Default cookie title';
$string['defaultDescription'] = 'Default cookie description.';
$string['learnMore'] = 'Learn more';
$string['closeWindow'] = 'Close window';
/* ----- Slide down notification ----- */
$string['notificationTitle'] = 'Your experience on this site will be improved by allowing cookies';
$string['notificationTitleImplicit'] = 'We use cookies to ensure you get the best experience on our website';
$string['customCookie'] = 'This website uses a custom type of cookie which needs specific approval';
$string['seeDetails'] = 'see details';
$string['seeDetailsImplicit'] = 'change your settings';
$string['hideDetails'] = 'hide details';
$string['allowCookies'] = 'Allow cookies';
$string['allowCookiesImplicit'] = 'Close';
$string['allowForAllSites'] = 'Allow for all sites';
$string['savePreference'] = 'Save preference';
$string['saveForAllSites'] = 'Save for all sites';
/* ----- Privacy settings tab ----- */
$string['privacySettings'] = 'Privacy settings';
/* ----- Privacy settings dialog ----- */
$string['privacySettingsDialogTitleA'] = 'Privacy settings';
$string['privacySettingsDialogTitleB'] = 'for this website';
$string['privacySettingsDialogSubtitle'] = 'Some features of this website need your consent to remember who you are.';
$string['changeForAllSitesLink'] = 'Change settings for all websites';
$string['preferenceUseGlobal'] = 'Use global setting';
$string['preferenceConsent'] = 'I consent';
$string['preferenceDecline'] = 'I decline';
$string['notUsingCookies'] = 'This website does not use any cookies.';
/* ----- Global settings dialog ----- */
$string['allSitesSettingsDialogTitleA'] = 'Privacy settings';
$string['allSitesSettingsDialogTitleB'] = 'for all websites';
$string['allSitesSettingsDialogSubtitle'] = 'You may consent to these cookies for all websites that use this plugin.';
$string['backToSiteSettings'] = 'Back to website settings';
$string['preferenceAsk'] = 'Ask me each time';
$string['preferenceAlways'] = 'Always allow';
$string['preferenceNever'] = 'Never allow';
