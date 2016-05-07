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

defined('INTERNAL') || die();

$string['pluginname'] = 'Skin';
$string['myskins'] = 'Skins';
$string['siteskinmenu'] = 'Skins';

$string['deletethisskin'] = 'Delete this skin';
$string['skindeleted'] = 'Skin deleted';
$string['cantdeleteskin'] = 'You cannot delete this skin.';
$string['deletespecifiedskin'] = 'Delete skin \'%s\'';
$string['deleteskinconfirm'] = 'Do you really want to delete this skin? It cannot be undone.';
$string['deleteskinusedinpages'] = array(
    0 => 'The skin you are about to delete is used in %d page.',
    1 => 'The skin you are about to delete is used in %d pages.');
$string['importskins'] = 'Import skin(s)';
$string['importskinsnotice'] = 'Please select a valid XML file to import, which contains the definition(s) of the skin(s).';
$string['validxmlfile'] = 'Valid XML file';
$string['notvalidxmlfile'] = 'The uploaded file is not a valid XML file.';
$string['import'] = 'Import';
$string['exportthisskin'] = 'Export this skin';
$string['exportspecific'] = 'Export "%s"';
$string['exportskins'] = 'Export skin(s)';
$string['createskin'] = 'Create skin';
$string['editthisskin'] = 'Edit this skin';
$string['editsiteskin?'] = 'This is a site skin. Do you want to edit it?';
$string['editskin'] = 'Edit skin';
$string['skinsaved'] = 'Skin saved successfully';
$string['skinimported'] = 'Skin imported successfully';
$string['clicktoedit'] = 'Click to edit this skin';
$string['skinpreview'] = 'Preview of "%s"';
$string['skinpreviewedit'] = 'Preview of "%s" - click to edit';
$string['addtofavorites'] = 'Add to favourites';
$string['addtofavoritesspecific'] = 'Add "%s" to favourites';
$string['removefromfavorites'] = 'Remove from favourites';
$string['removefromfavoritesspecific'] = 'Remove "%s" from favourites';
$string['skinaddedtofavorites'] = 'Skin added to favourites';
$string['skinremovedfromfavorites'] = 'Skin removed from favourites';
$string['cantremoveskinfromfavorites'] = 'Can\'t remove skin from favourites';
$string['viewmetadata'] = 'View skin information';
$string['viewmetadataspecific'] = 'View information for "%s"';
$string['closemetadata'] = 'Close skin information';
$string['metatitle'] = 'Skin information';
$string['title'] = 'Title';
$string['displayname'] = 'Owner';
$string['description'] = 'Description';
$string['creationdate'] = 'Created';
$string['modifieddate'] = 'Modified';

$string['noskins'] = 'There are no skins';
$string['skin'] = 'skin';
$string['skins'] = 'skins';

$string['allskins'] = 'All skins';
$string['siteskins'] = 'Site skins';
$string['userskins'] = 'My skins';
$string['favoriteskins'] = 'Favourite skins';
$string['publicskins'] = 'Public skins';
$string['currentskin'] = 'Current skin';
$string['skinnotselected'] = 'Skin not selected';
$string['noskin'] = 'No skin';

// Create Skin Form Fieldsets
$string['skingeneraloptions'] = 'General';
$string['skinbackgroundoptions1'] = 'Background';
$string['viewbackgroundoptions'] = 'Page background';
$string['viewheaderoptions'] = 'Page header';
$string['viewcontentoptions1'] = 'Fonts and colours';
$string['viewtableoptions'] = 'Page tables and buttons';
$string['viewadvancedoptions'] = 'Advanced';

// Create Skin Form
$string['skintitle'] = 'Skin title';
$string['skindescription'] = 'Skin description';
$string['skinaccessibility1'] = 'Skin access';
$string['privateskinaccess'] = 'This is a private skin';
$string['publicskinaccess'] = 'This is a public skin';
$string['siteskinaccess'] = 'This is a site skin';
$string['Untitled'] = 'Untitled';

$string['backgroundcolor'] = 'Background colour';
$string['bodybgcolor1'] = 'Background colour';
$string['viewbgcolor'] = 'Page background colour';
$string['textcolor'] = 'Text colour';
$string['textcolordescription'] = 'This is the colour of normal text.';
$string['headingcolor'] = 'Heading text colour';
$string['headingcolordescription'] = 'This is the colour of a page heading.';
$string['emphasizedcolor'] = 'Emphasized text colour';
$string['emphasizedcolordescription'] = 'This is the colour of page sub-headings and emphasized text.';
$string['bodybgimage1'] = 'Background image';
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
$string['topleft'] = 'Top left';
$string['top'] = 'Top';
$string['topright'] = 'Top right';
$string['left'] = 'Left';
$string['centre'] = 'Centre';
$string['right'] = 'Right';
$string['bottomleft'] = 'Bottom left';
$string['bottom'] = 'Bottom';
$string['bottomright'] = 'Bottom right';
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

$string['headerlogoimage1'] = 'Logo';
$string['headerlogoimagenormal'] = 'Default theme logo';
$string['headerlogoimagelight1'] = 'White Mahara logo and text (suitable for darker header backgrounds)';
$string['headerlogoimagedark1'] = 'Dark Mahara logo and text (suitable for lighter header backgrounds)';

$string['normallinkcolor'] = 'Normal link colour';
$string['hoverlinkcolor'] = 'Highlighted link colour';
$string['linkunderlined'] = 'Underline link';

$string['tableborder'] = 'Table border colour';
$string['tableheader'] = 'Header background colour';
$string['tableheadertext'] = 'Header text colour';
$string['tableoddrows'] = 'Background colour for odd rows';
$string['tableevenrows'] = 'Background colour for even rows';

$string['normalbuttoncolor'] = 'Normal button colour';
$string['hoverbuttoncolor'] = 'Highlighted button colour';
$string['buttontextcolor'] = 'Button text colour';

$string['skincustomcss'] = 'Custom CSS';
$string['skincustomcssdescription'] = 'Custom CSS will not be reflected in skin preview images.';

$string['chooseviewskin'] = 'Choose page skin';
$string['chooseskin'] = 'Choose skin';
$string['notsavedyet'] = 'Not saved yet.';
$string['notcompatiblewiththeme'] = 'Your Mahara site theme "%s" does not support page skins. This means the skin you select will have no effect on how you see this page, but it may affect the page\'s appearance for other users viewing the site with a different theme.';
$string['notcompatiblewithpagetheme'] = 'This page\'s theme "%s" does not support page skins. The skin you choose here will have no effect on the page\'s appearance until you select a different theme.';
$string['viewskinchanged'] = 'Page skin changed';
$string['manageskins'] = 'Manage skins';


/* SKINS - SITE FONTS */
$string['sitefontsmenu'] = 'Fonts';
$string['sitefonts'] = 'Fonts';
$string['sitefontsdescription'] = '<p>The following fonts have been installed on your site for use in skins.</p>';
$string['installfontinstructions'] = '<p>
Add fonts, which allow font embedding into web pages, via the CSS @font-face rule. Remember that not all authors / foundries allow this.
</p>
<p>
When you find an appropriate free font that you are allowed to embed into a web page, you must convert it into the following formats:
<br />TrueType Font, Embedded OpenType Font, Web Open Font Format Font and Scalable Vector Graphic Font.
</p>
<p>
You can use <a href="http://www.fontsquirrel.com/fontface/generator/">FontSquirrel Online Generator</a> for the conversion.
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
$string['viewfontspecimenfor'] = ' for \'%s\'';
$string['deletefont'] = 'Delete font';
$string['deletespecifiedfont'] = 'Delete font \'%s\'';
$string['deletefontconfirm1'] = 'Do you really want to delete this font?';
$string['deletefontconfirm2'] = 'It cannot be undone.';
$string['deletefontconfirmused'] = array(
    ' This font is used in %s skin. ',
    ' This font is used in %s skins. ',
);
$string['fontdeleted'] = 'Font deleted';
$string['cantdeletefont'] = 'You cannot delete this font.';

$string['fontname'] = 'Font name';
$string['invalidfonttitle'] = 'Invalid font title. It must contain at least one alphanumeric character.';
$string['genericfontfamily'] = 'Generic font family';

$string['fontstyle'] = 'Font style';
$string['regular'] = 'Regular';
$string['bold'] = 'Bold';
$string['italic'] = 'Italic';
$string['bolditalic'] = 'Bold italic';

$string['fonttype'] = 'Font type';
$string['headingandtext'] = 'Heading and text';
$string['headingonly'] = 'Heading only';

$string['fontuploadinstructions'] = '<br />To upload the needed font files, you can either upload the ZIP file generated by the <a href="http://www.fontsquirrel.com/fontface/generator/">FontSquirrel Online Generator</a> directly
<br />or upload the EOT, SVG, TTF, WOFF, and license files individually.';
$string['fontfiles'] = 'Font files';
$string['fontfilemissing'] = 'ZIP file does not contain a \'%s\' font file.';
$string['zipfontfiles'] = 'Font files in ZIP archive';
$string['fontfilezip'] = 'ZIP archive';
$string['zipdescription'] = 'ZIP file containing the EOT, SVG, TTF, WOFF, and license files for a font';
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
$string['fontfilelicence'] = 'License file';
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
$string['samplefonttitle'] = 'Font name';
$string['sampletitle11'] = 'Latin alphabet (ASCII only)';
$string['sampletext11'] = 'Aa Bb Cc Dd Ee Ff Gg Hh Ii Jj Kk Ll Mm Nn Oo Pp Qq Rr Ss Tt Uu Vv Ww Xx Yy Zz';
$string['sampletitle12'] = 'Latin alphabet (ISO/IEC 8859-1)';
$string['sampletext12'] = 'Àà Áá Ââ Ãã Ää Åå Ææ Çç Èè Éé Êê Ëë Ìì Íí Îî Ïï Ðð Ññ Òò Óó Ôô Õõ Öö Øø Ùù Úú Ûû Üü Ýý Þþ ß';
$string['sampletitle13'] = 'Latin alphabet (ISO/IEC 8859-2)';
$string['sampletext13'] = 'Āā Ăă Ąą Ćć Čč Ďď Đđ Ēē Ėė Ęę Ěě Ğğ Ģģ Īī Ĭĭ Įį İı Ķķ Ĺĺ Ļļ Ľľ Łł Ńń Ņņ Ňň Ōō Őő Œœ Ŕŕ Ŗŗ Řř Śś Şş Šš Ţţ Ťť Ūū Ŭŭ Ůů Űű Ųų Źź Żż Žž ſ';
$string['sampletitle14'] = 'Cyrillic alphabet (ISO/IEC 8859-5)';
$string['sampletext14'] = 'Аа Бб Вв Гг Дд Ее Ёё Жж Зз Ии Йй Кк Лл Мм Нн Оо Пп Рр Сс Тт Уу Фф Хх Цц Чч Шш Щщ Ъъ Ыы Ьь Ээ Юю Яя';
$string['sampletitle15'] = 'Greek alphabet (ISO/IEC 8859-7)';
$string['sampletext15'] = 'Αα Ββ Γγ Δδ Εε Ζζ Ηη Θθ Ιι Κκ Λλ Μμ Νν Ξξ Οο Ππ Ρρ Σσς Ττ Υυ Φφ Χχ Ψψ Ωω';
$string['sampletitle18'] = 'Numbers and fractions';
$string['sampletext18'] = '1 2 3 4 5 6 7 8 9 0 ¼ ½ ¾ ⅓ ⅔ ⅛ ⅜ ⅝ ⅞ ¹ ² ³';
$string['sampletitle19'] = 'Punctuation';
$string['sampletext19'] = '& ! ? » « @ $ € § * # %% / () \ {} []';
$string['sampletitle20'] = 'Lorem ipsum...';
$string['sampletext20'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
$string['sampletitle21'] = 'Grumpy wizards make...';
$string['sampletext21'] = 'Grumpy wizards make toxic brew for the evil Queen and Jack.';
$string['sampletitle22'] = 'The quick brown fox...';
$string['sampletext22'] = 'The quick brown fox jumps over the lazy dog.';

/* SKINS - GOOGLE WEB FONTS */
$string['installgwfont'] = 'Install Google font(s)';
$string['archivereadingerror'] = 'Error reading ZIP archive.';
$string['gwfontadded'] = 'Google font(s) installed successfully';
$string['gwfontsnotavailable'] = 'Google fonts are currently not available.';

$string['gwfinstructions'] = '<ol>
<li>Visit <a href="http://www.google.com/fonts/">Google fonts</a></li>
<li>Select fonts and add them to your collection</li>
<li>Download fonts in a collection as a ZIP file</li>
<li>Upload that ZIP file in this form</li>
<li>Install Google font(s)</li>
</ol>';
$string['gwfzipfile'] = 'Valid ZIP file';
$string['gwfzipdescription'] = 'A valid ZIP file that contains all selected Google fonts to be installed.';
$string['notvalidzipfile'] = 'This is not a valid ZIP file.';

$string['fontlicence'] = 'Font license';
$string['fontlicencenotfound'] = 'Font license not found';

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
