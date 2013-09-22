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
 * @subpackage skin
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2010-2013 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

defined('INTERNAL') || die();

$string['pluginname'] = 'Skin';
$string['myskins'] = 'Skins';
$string['siteskinmenu'] = 'Skins';

$string['deletethisskin'] = 'Delete this skin';
$string['skindeleted'] = 'Skin deleted';
$string['cantdeleteskin'] = 'You cannot delete this skin.';
$string['deletespecifiedskin'] = 'Delete skin \'%s\'';
$string['deleteskinconfirm'] = 'Do you really want to delete this skin? It cannot be undone.';
$string['importskins'] = 'Import skin(s)';
$string['importskinsnotice'] = 'Please select a valid XML file to import, which contains the definition(s) of the skin(s).';
$string['validxmlfile'] = 'Valid XML file';
$string['notvalidxmlfile'] = 'The uploaded file is not a valid XML file.';
$string['import'] = 'Import';
$string['exportthisskin'] = 'Export this skin';
$string['exportskins'] = 'Export skin(s)';
$string['createskin'] = 'Create skin';
$string['editskin'] = 'Edit skin';
$string['skinsaved'] = 'Skin saved successfully';
$string['skinimported'] = 'Skin imported successfully';
$string['clicktoedit'] = 'Click to edit skin';
$string['clickimagetoedit'] = 'Click image to edit';
$string['addtofavorites'] = 'Add to favorites';
$string['removefromfavorites'] = 'Remove from favorites';
$string['skinaddedtofavorites'] = 'Skin added to favorites';
$string['skinremovedfromfavorites'] = 'Skin removed from favorites';
$string['cantremoveskinfromfavorites'] = 'Can\'t remove skin from favorites';

$string['noskins'] = 'There are no skins';
$string['skin'] = 'skin';
$string['skins'] = 'skins';

$string['allskins'] = 'All skins';
$string['siteskins'] = 'Site skins';
$string['userskins'] = 'My skins';
$string['favoriteskins'] = 'Favorite skins';
$string['publicskins'] = 'Public skins';
$string['currentskin'] = 'Current skin';
$string['skinnotselected'] = 'Skin not selected';
$string['skindefault'] = 'Default site skin';

// Create Skin Form Fieldsets
$string['skingeneraloptions'] = 'General';
$string['skinbackgroundoptions'] = 'Skin background';
$string['viewbackgroundoptions'] = 'Page background';
$string['viewheaderoptions'] = 'Page header';
$string['viewcontentoptions'] = 'Page fonts and colours';
$string['viewtableoptions'] = 'Page tables and buttons';
$string['viewadvancedoptions'] = 'Advanced';

// Create Skin Form
$string['skintitle'] = 'Skin title';
$string['skindescription'] = 'Skin description';
$string['skinaccessibility'] = 'Skin accessibility';
$string['privateskinaccess'] = 'This is a private skin';
$string['publicskinaccess'] = 'This is a public skin';
$string['siteskinaccess'] = 'This is a site skin';
$string['Untitled'] = 'Untitled';

$string['backgroundcolor'] = 'Background colour';
$string['bodybgcolor'] = 'Skin background colour';
$string['viewbgcolor'] = 'Page background colour';
$string['textcolor'] = 'Text colour';
$string['textcolordescription'] = 'This is the colour of normal text.';
$string['headingcolor'] = 'Heading text colour';
$string['headingcolordescription'] = 'This is the colour of a page heading.';
$string['emphasizedcolor'] = 'Emphasized text colour';
$string['emphasizedcolordescription'] = 'This is the colour of page sub-headings and emphasized text.';
$string['bodybgimage'] = 'Skin background image';
$string['viewbgimage'] = 'Page background image';
$string['backgroundrepeat'] = 'Background image repeat';
$string['backgroundrepeatboth'] = 'Repeat both directions';
$string['backgroundrepeatx'] = 'Repeat only horizontally';
$string['backgroundrepeaty'] = 'Repeat only vertically';
$string['backgroundrepeatno'] = 'Don\'t repeat';
$string['backgroundattachment'] = 'Background image attachment';
$string['backgroundfixed'] = 'Fixed';
$string['backgroundscroll'] = 'Scroll';
$string['backgroundposition'] = 'Background image position';
$string['viewwidth'] = 'Page width';

$string['textfontfamily'] = 'Text font';
$string['headingfontfamily'] = 'Heading font';
$string['fontsize'] = 'Font size';
$string['fontsizesmallest'] = 'smallest';
$string['fontsizesmaller'] = 'smaller';
$string['fontsizesmall'] = 'small';
$string['fontsizemedium'] = 'medium';
$string['fontsizelarge'] = 'large';
$string['fontsizelarger'] = 'larger';
$string['fontsizelargest'] = 'largest';

$string['headerlogoimage'] = 'Mahara logo image';
$string['headerlogoimagenormal'] = 'Normal text (suitable for lighter header backgrounds)';
$string['headerlogoimagewhite'] = 'White text (suitable for darker header backgrounds)';

$string['normallinkcolor'] = 'Normal link colour';
$string['hoverlinkcolor'] = 'Highlighted link colour';
$string['linkunderlined'] = 'Underline link';

$string['tableborder'] = 'Table border colour';
$string['tableheader'] = 'Header background colour';
$string['tableheadertext'] = 'Header text colour';
$string['tableoddlines'] = 'Background colour for odd lines';
$string['tableevenlines'] = 'Background colour for even lines';

$string['normalbuttoncolor'] = 'Normal button colour';
$string['hoverbuttoncolor'] = 'Highlighted button colour';
$string['buttontextcolor'] = 'Button text colour';

$string['skincustomcss'] = 'Custom CSS';
$string['skincustomcssdescription'] = 'Custom CSS will not be reflected in skin preview images.';

$string['chooseviewskin'] = 'Choose page skin';
$string['chooseskin'] = 'Choose skin';
$string['notsavedyet'] = 'Not saved yet.';
$string['viewskinchanged'] = 'Page skin changed';
$string['manageskins'] = 'Manage skins';


/* SKINS - SITE FONTS */
$string['sitefontsmenu'] = 'Fonts';
$string['sitefonts'] = 'Fonts';
$string['sitefontsdescription'] = '<p>The following fonts have been installed in your site, for use in skins.</p>';
$string['installfontinstructions'] = '<p>
Add fonts, which allow font embedding into web pages via the CSS @font-face rule. Remember that not all authors / foundries allow this.
</p>
<p>
When you find an appropriate free font that you are allowed to embed into a web page, you must convert it into the formats:
<br />TrueType Font, Embedded OpenType Font, Web Open Font Format Font and Scalable Vector Graphic Font.
</p>
<p>
You can use <a href="http://www.fontsquirrel.com/fontface/generator/" target="_blank">FontSquirrel Online Generator</a> for the conversion.
</p>';
$string['nofonts'] = 'There are no fonts.';
$string['font'] = 'font';
$string['fonts'] = 'fonts';

$string['installfont'] = 'Install font';
$string['fontinstalled'] = 'Font installed successfully';
$string['addfontvariant'] = 'Add font style';
$string['fontvariantadded'] = 'Font style added successfully';
$string['editfont'] = 'Edit font';
$string['fontedited'] = 'Font edited successfully';
$string['editproperties'] = 'Edit font properties';
$string['viewfontspecimen'] = 'View font specimen';
$string['deletefont'] = 'Delete font';
$string['deletespecifiedfont'] = 'Delete font \'%s\'';
$string['deletefontconfirm'] = 'Do you really want to delete this font? It cannot be undone.';
$string['fontdeleted'] = 'Font deleted';
$string['cantdeletefont'] = 'You cannot delete this font.';

$string['fontname'] = 'Font name';
$string['invalidfonttitle'] = 'Invalid font title. (Must contain at least one alphanumeric character.)';
$string['genericfontfamily'] = 'Generic font family';

$string['fontstyle'] = 'Font style';
$string['regular'] = 'Regular';
$string['bold'] = 'Bold';
$string['italic'] = 'Italic';
$string['bolditalic'] = 'Bold Italic';

$string['fonttype'] = 'Font type';
$string['headingandtext'] = 'Heading and text';
$string['headingonly'] = 'Heading only';

$string['fontfiles'] = 'Font files';
$string['fontfileeot'] = 'EOT font file';
$string['eotdescription'] = 'Embedded OpenType font (for Internet Explorer 4+)';
$string['notvalidfontfile'] = 'This is not a valid %s font file.';
$string['nosuchfont'] = 'There is no font with the supplied name.';
$string['fontfilesvg'] = 'SVG font file';
$string['svgdescription'] = 'Scalable Vector Graphic font (for iPad and iPhone)';
$string['fontfilettf'] = 'TTF font file';
$string['ttfdescription'] = 'TrueType font (for Firefox 3.5+, Opera 10+, Safari 3.1+, Chrome 4.0.249.4+)';
$string['fontfilewoff'] = 'WOFF font file';
$string['woffdescription'] = 'Web Open Font Format font (for Firefox 3.6+, Internet Explorer 9+, Chrome 5+)';
$string['fontfilelicence'] = 'Licence file';
$string['fontnotice'] = 'Font notice';
$string['fontnoticedescription'] = 'One line added to the CSS file describing the font and the author.';
$string['filepathnotwritable'] = 'Cannot write the files to \'%s\'';

$string['showfonts'] = 'Show';
$string['fonttypes.all'] = 'All fonts';
$string['fonttype.site'] = 'Local font';
$string['fonttypes.site'] = 'Local fonts';
$string['fonttype.google'] = 'Google web font';
$string['fonttypes.google'] = 'Google web fonts';

// For examples of pangrams, see: http://en.wikipedia.org/wiki/List_of_pangrams
$string['preview'] = 'Preview';
$string['samplesize'] = 'Size';
$string['samplesort'] = 'Sorting';
$string['sampletext'] = 'Text';
$string['samplefonttitle'] = 'Font Name';
$string['sampletitle11'] = 'Latin alphabet (ASCII only)';
$string['sampletext11'] = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz';
$string['sampletitle12'] = 'Latin alphabet (ISO/IEC 8859-1)';
$string['sampletext12'] = 'ÀàÁáÂâÃãÄäÅåÆæÇçÈèÉéÊêËëÌìÍíÎîÏïÐðÑñÒòÓóÔôÕõÖöØøÙùÚúÛûÜüÝýÞþß';
$string['sampletitle13'] = 'Latin alphabet (ISO/IEC 8859-2)';
$string['sampletext13'] = 'ĀāĂăĄąĆćČčĎďĐđĒēĖėĘęĚěĞğĢģĪīĬĭĮįİıĶķĹĺĻļĽľŁłŃńŅņŇňŌōŐőŒœŔŕŖŗŘřŚśŞşŠšŢţŤťŪūŬŭŮůŰűŲųŹźŻżŽžſ';
$string['sampletitle14'] = 'Cyrillic alphabet (ISO/IEC 8859-5)';
$string['sampletext14'] = 'АаБбВвГгДдЕеЁёЖжЗзИиЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯя';
$string['sampletitle15'] = 'Greek alphabet (ISO/IEC 8859-7)';
$string['sampletext15'] = 'ΑαΒβΓγΔδΕεΖζΗηΘθΙιΚκΛλΜμΝνΞξΟοΠπΡρΣσςΤτΥυΦφΧχΨψΩω';
$string['sampletitle18'] = 'Numbers and fractions';
$string['sampletext18'] = '1234567890¼½¾⅓⅔⅛⅜⅝⅞¹²³';
$string['sampletitle19'] = 'Punctuation';
$string['sampletext19'] = '&!?»«@$€§*#%%/()\{}[]';
$string['sampletitle20'] = 'Lorem ipsum...';
$string['sampletext20'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
$string['sampletitle21'] = 'Grumpy wizards make...';
$string['sampletext21'] = 'Grumpy wizards make toxic brew for the evil Queen and Jack.';
$string['sampletitle22'] = 'The quick brown fox...';
$string['sampletext22'] = 'The quick brown fox jumps over the lazy dog.';

/* SKINS - GOOGLE WEB FONTS */
$string['installgwfont'] = 'Install Google font(s)';
$string['archivereadingerror'] = 'Error reading ZIP archive!';
$string['gwfontadded'] = 'Google font(s) installed successfully';
$string['gwfontsnotavailable'] = 'Google Fonts are currently not available.';

$string['gwfinstructions'] = '<ol>
<li>Visit <a href="http://www.google.com/fonts/" target="_blank">Google Fonts</a></li>
<li>Select fonts and add them to collection</li>
<li>Download fonts in a collection as a ZIP file</li>
<li>Upload that ZIP file in this form</li>
<li>Install Google font(s)</li>
</ol>';
$string['gwfzipfile'] = 'Valid ZIP file';
$string['gwfzipdescription'] = 'A valid ZIP file which contains all selected Google fonts which will be installed.';
$string['notvalidzipfile'] = 'This is not a valid ZIP file';

$string['fontlicence'] = 'Font licence';
$string['fontlicencenotfound'] = 'Font licence not found';

$string['fontsort.alpha'] = 'Alphabet';
$string['fontsort.date'] = 'Date added';
$string['fontsort.popularity'] = 'Popularity';
$string['fontsort.style'] = 'Number of styles';
$string['fontsort.trending'] = 'Trending';

$string['previewheading'] = 'Lorem ipsum';
$string['previewsubhead1'] = 'Scriptum';
$string['previewsubhead2'] = 'Imago';
$string['previewtextline1'] = 'Lorem ipsum dolor sit amet,';
$string['previewtextline2'] = 'consectetur adipiscing elit.';
$string['previewtextline3'] = 'Donec cursus orci turpis.';
$string['previewtextline4'] = 'Donec et bibendum augue.';
$string['previewtextline5'] = 'Vestibulum ante ipsum primis';
$string['previewtextline6'] = 'in faucibus orci luctus et';
$string['previewtextline7'] = 'ultrices posuere cubilia Curae;';
$string['previewtextline8'] = 'Cras odio enim, sodales at';
$string['previewtextline9'] = 'rutrum et, sollicitudin non nisi.';
