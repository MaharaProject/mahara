<?php
/**
 *
 * @package    mahara
 * @subpackge  admin
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2013 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configsite/cookieconsent');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'cookieconsent2');

require(dirname(dirname(dirname(__FILE__))).'/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('cookieconsent2', 'cookieconsent'));
define('DEFAULTPAGE', 'home');


$examplesocialbefore = <<<CODE
<pre>
  &lt;div id="fb-root"&gt;&lt;/div&gt;
  &lt;script type="text/javascript"&gt;(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1&appId=000000000000000";
      fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));&lt;/script&gt;
</pre>
CODE;

$examplesocialafter = <<<CODE
<pre>
  &lt;div id="fb-root"&gt;&lt;/div&gt;
  &lt;script type="<b>text/plain</b>" class="<b>cc-onconsent-social</b>"&gt;(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1&appId=000000000000000";
      fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));&lt;/script&gt;
</pre>
CODE;

$exampleanalyticsbefore = <<<CODE
<pre>
  &lt;script type="text/javascript"&gt;
      // &lt;![CDATA[
      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-00000000-1']);
      _gaq.push(['_setAllowLinker', true]);
      _gaq.push(['_trackPageview']);
      (function() {
          var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
          ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
          var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();
      // ]]&gt;
  &lt;/script&gt;
</pre>
CODE;

$exampleanalyticsafter = <<<CODE
<pre>
  &lt;script type="<b>text/plain</b>" class="<b>cc-onconsent-analytics</b>"&gt;
      // &lt;![CDATA[
      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-00000000-1']);
      _gaq.push(['_setAllowLinker', true]);
      _gaq.push(['_trackPageview']);
      (function() {
          var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
          ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
          var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();
      // ]]&gt;
  &lt;/script&gt;
</pre>
CODE;

$exampleadvertisingbefore = <<<CODE
<pre>
  &lt;script type="text/javascript"&gt;
      &lt;!--
      google_ad_client = "ca-pub-0000000000000000";
      /* test */
      google_ad_slot = "0000000000";
      google_ad_width = 728;
      google_ad_height = 90;
      //-->
  &lt;/script&gt;
  &lt;script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"&gt;
  &lt;/script&gt;
</pre>
CODE;

$exampleadvertisingafter = <<<CODE
<pre>
  &lt;script type="<b>text/plain</b>" class="<b>cc-onconsent-advertising</b>"&gt;
      &lt;!--
      google_ad_client = "ca-pub-0000000000000000";
      /* test */
      google_ad_slot = "0000000000";
      google_ad_width = 728;
      google_ad_height = 90;
      //-->
  &lt;/script&gt;
  &lt;script type="<b>text/plain</b>" class="<b>cc-onconsent-advertising</b>" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"&gt;
  &lt;/script&gt;
</pre>
CODE;

$examplenecessarybefore = <<<CODE
<pre>
  &lt;script type="text/javascript"&gt;
      &lt;!--
      google_ad_client = "ca-pub-0000000000000000";
      /* test */
      google_ad_slot = "0000000000";
      google_ad_width = 728;
      google_ad_height = 90;
      //-->
  &lt;/script&gt;
  &lt;script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"&gt;
  &lt;/script&gt;
</pre>
CODE;

$examplenecessaryafter = <<<CODE
<pre>
  &lt;script type="<b>text/plain</b>" class="<b>cc-onconsent-necessary</b>"&gt;
      &lt;!--
      google_ad_client = "ca-pub-0000000000000000";
      /* test */
      google_ad_slot = "0000000000";
      google_ad_width = 728;
      google_ad_height = 90;
      //-->
  &lt;/script&gt;
  &lt;script type="<b>text/plain</b>" class="<b>cc-onconsent-necessary</b>" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"&gt;
  &lt;/script&gt;
</pre>
CODE;

$data = array(
    'social' => array(
        'title' => get_string('cookietypessocial', 'cookieconsent'),
        'item1' => get_string('instructiontext1', 'cookieconsent', get_string('cookietypessocial','cookieconsent'), get_string('example1social','cookieconsent')),
        'item2' => get_string('instructiontext2', 'cookieconsent'),
        'item3' => get_string('instructiontext3', 'cookieconsent', 'cc-onconsent-social'),
        'code1' => $examplesocialbefore,
        'code2' => $examplesocialafter,
        'help1' => get_string('itdidntwork1', 'cookieconsent', 'cc-onconsent-social', 'cc-onconsent-inline-social'),
        'help2' => get_string('itdidntwork2', 'cookieconsent', '<a href="http://sitebeam.net/cookieconsent/documentation/code-examples#examples-social" target="_blank">', '</a>', '<a href="http://www.linkedin.com/groups/Cookie-Consent-developers-4980594?trk=groups_management_submission_queue-h-dsc" target="_blank">', '</a>')
    ),
    'analytics' => array(
        'title' => get_string('cookietypesanalytics', 'cookieconsent'),
        'item1' => get_string('instructiontext1', 'cookieconsent', get_string('cookietypesanalytics','cookieconsent'), get_string('example1analytics','cookieconsent')),
        'item2' => get_string('instructiontext2', 'cookieconsent'),
        'item3' => get_string('instructiontext3', 'cookieconsent', 'cc-onconsent-analytics'),
        'code1' => $exampleanalyticsbefore,
        'code2' => $exampleanalyticsafter,
        'help1' => get_string('itdidntwork1', 'cookieconsent', 'cc-onconsent-analytics', 'cc-onconsent-inline-analytics'),
        'help2' => get_string('itdidntwork2', 'cookieconsent', '<a href="http://sitebeam.net/cookieconsent/documentation/code-examples#examples-analytics" target="_blank">', '</a>', '<a href="http://www.linkedin.com/groups/Cookie-Consent-developers-4980594?trk=groups_management_submission_queue-h-dsc" target="_blank">', '</a>')
    ),
    'advertising' => array(
        'title' => get_string('cookietypesadvertising', 'cookieconsent'),
        'item1' => get_string('instructiontext1', 'cookieconsent', get_string('cookietypesadvertising','cookieconsent'), get_string('example1advertising','cookieconsent')),
        'item2' => get_string('instructiontext2', 'cookieconsent'),
        'item3' => get_string('instructiontext3', 'cookieconsent', 'cc-onconsent-advertising'),
        'code1' => $exampleadvertisingbefore,
        'code2' => $exampleadvertisingafter,
        'help1' => get_string('itdidntwork1', 'cookieconsent', 'cc-onconsent-advertising', 'cc-onconsent-inline-advertising'),
        'help2' => get_string('itdidntwork2', 'cookieconsent', '<a href="http://sitebeam.net/cookieconsent/documentation/code-examples#examples-advertising" target="_blank">', '</a>', '<a href="http://www.linkedin.com/groups/Cookie-Consent-developers-4980594?trk=groups_management_submission_queue-h-dsc" target="_blank">', '</a>')
    ),
    'necessary' => array(
        'title' => get_string('cookietypesnecessary', 'cookieconsent'),
        'item1' => get_string('instructiontext1', 'cookieconsent', get_string('cookietypesnecessary','cookieconsent'), get_string('example1necessary','cookieconsent')),
        'item2' => get_string('instructiontext2', 'cookieconsent'),
        'item3' => get_string('instructiontext3', 'cookieconsent', 'cc-onconsent-necessary'),
        'code1' => $examplenecessarybefore,
        'code2' => $examplenecessaryafter,
        'help1' => get_string('itdidntwork1', 'cookieconsent', 'cc-onconsent-necessary', 'cc-onconsent-inline-necessary'),
        'help2' => get_string('itdidntwork2', 'cookieconsent', '<a href="http://sitebeam.net/cookieconsent/documentation/code-examples#examples-necessary" target="_blank">', '</a>', '<a href="http://www.linkedin.com/groups/Cookie-Consent-developers-4980594?trk=groups_management_submission_queue-h-dsc" target="_blank">', '</a>')
    )
);

$smarty = smarty(array('expandable'));
$smarty->assign('modifications', get_string('additionalmodifications','cookieconsent'));
$smarty->assign('data', $data);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('admin/site/cookieconsent2.tpl');
