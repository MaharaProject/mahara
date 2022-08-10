<?php
/**
 *
 * @package    mahara
 * @subpackage skin
 * @author     Gregor Anzelj
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2010-2013 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

define('INTERNAL', true);
require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once('skin.php');
safe_require('artefact', 'file');

$exportid = param_integer('id', 0); // id(s) of skin(s) to be exported...
$exportsiteskins = param_boolean('site', false);

if (!can_use_skins(null, $exportsiteskins)) {
    throw new FeatureNotEnabledException();
}

if ($exportid == 0) {
    if ($exportsiteskins) {
        // We are exporting site skins...
        $exportskins = get_records_array('skin', 'type', 'site');
        $xmlfilename = 'siteskins';
    }
    else {
        // We are exporting user skins...
        $exportskins = get_records_array('skin', 'owner', $USER->get('id'));
        $xmlfilename = 'myskins';
    }
}
else {
    $exportskins = get_record('skin', 'id', $exportid, 'owner', $USER->get('id'));
    // Convert to array with one object - for one view skin, with specified id...
    $exportskins = array($exportskins);
    $xmlfilename = 'skin' . $exportid;
}

// ===============================
//  Dynamically create export XML
// ===============================

$xmldoc = new DOMDocument('1.0', 'UTF-8');
$comment = $xmldoc->appendChild(new DOMComment('Skin definitions to be used with Mahara pages. More info about Mahara at https://mahara.org'));

$topelement = $xmldoc->createElement('skins');
$topnode = $xmldoc->appendChild($topelement);

if (!empty($exportskins)) {
    foreach ($exportskins as $exportskin) {
        $skinobj = new Skin($exportskin->id);
        // Only allow a user to export skins they have edit permissions for
        if (!$skinobj->can_edit()) {
            continue;
        }

        // Get default viewksin settings and merge with skin to export in case this skin hasn't been updated
        $defaultskin = Skin::$defaultviewskin;
        $viewskin = unserialize($exportskin->viewskin);
        $viewskin = array_merge($defaultskin, $viewskin);

        $rootelement = $xmldoc->createElement('skin');
        $rootnode = $topelement->appendChild($rootelement);
        $rootnode->setAttribute('title', $exportskin->title);
        $rootnode->setAttribute('description', $exportskin->description);

        // Body element...
        $childelement = $xmldoc->createElement('body');
        $itemnode = $rootelement->appendChild($childelement);
        $itemnode->setAttribute('background-color', $viewskin['body_background_color']);
        $itemnode->setAttribute('background-repeat', Skin::background_repeat_number_to_value($viewskin['body_background_repeat']));
        $itemnode->setAttribute('background-attachment', $viewskin['body_background_attachment']);
        $itemnode->setAttribute('background-position', Skin::background_position_number_to_value($viewskin['body_background_position']));

        // Header element...
        $childelement = $xmldoc->createElement('header');
        $itemnode = $rootelement->appendChild($childelement);
        $itemnode->setAttribute('background-color', $viewskin['header_background_color']);

        // Text element...
        $childelement = $xmldoc->createElement('text');
        $itemnode = $rootelement->appendChild($childelement);
        $itemnode->setAttribute('text-font', $viewskin['view_text_font_family']);
        $itemnode->setAttribute('heading-font', $viewskin['view_heading_font_family']);
        $itemnode->setAttribute('font-size', $viewskin['view_text_font_size']);
        $itemnode->setAttribute('font-color', $viewskin['view_text_font_color']);
        $itemnode->setAttribute('heading-color', $viewskin['view_text_heading_color']);
        $itemnode->setAttribute('block-heading-font', $viewskin['view_block_header_font']);
        $itemnode->setAttribute('block-heading-color', $viewskin['view_block_header_font_color']);

        // Link element...
        $childelement = $xmldoc->createElement('link');
        $itemnode = $rootelement->appendChild($childelement);
        $itemnode->setAttribute('normal-color', $viewskin['view_link_normal_color']);
        if (intval($viewskin['view_link_normal_underline']) == 0) {
            $itemnode->setAttribute('normal-decoration', 'none');
        }
        else {
            $itemnode->setAttribute('normal-decoration', 'underline');
        }
        $itemnode->setAttribute('hover-color', $viewskin['view_link_hover_color']);
        if (intval($viewskin['view_link_hover_underline']) == 0) {
            $itemnode->setAttribute('hover-decoration', 'none');
        }
        else {
            $itemnode->setAttribute('hover-decoration', 'underline');
        }

        // Skin background image element...
        $bodybg = $viewskin['body_background_image'];
        if (!empty($bodybg) && $bodybg > 0) {
            // Get existing skin background image data...
            $artefactobj = new ArtefactTypeImage($bodybg);
            if ($USER->can_view_artefact($artefactobj)) {
                $artefact = get_record('artefact', 'id', $bodybg, null, null, null, null, 'artefacttype,title,description,note');
                $artefactfilefiles = get_record('artefact_file_files', 'artefact', $bodybg);
                $artefactfileimage = get_record('artefact_file_image', 'artefact', $bodybg);

                // Open and read the contents of each file...
                $bodybgimage = $artefactobj->get_path();
                $fp = fopen($bodybgimage, 'rb');
                $filesize = filesize($bodybgimage);
                $contents = fread($fp, $filesize);
                fclose($fp);
                // Export each file...
                $childelement = $xmldoc->createElement('image');
                $itemnode = $rootelement->appendChild($childelement);
                $itemnode->setAttribute('type', 'body-background-image');
                $itemnode->setAttribute('artefact', serialize($artefact));
                $itemnode->setAttribute('artefact_file_files', serialize($artefactfilefiles));
                $itemnode->setAttribute('artefact_file_image', serialize($artefactfileimage));
                $itemnode->setAttribute('contents', base64_encode($contents));
            }
        }

        // Skin header background image element...
        $headerbg = $viewskin['header_background_image'];
        if (!empty($headerbg) && $headerbg > 0) {
            // Get existing skin background image data...
            $artefactobj = new ArtefactTypeImage($headerbg);
            if ($USER->can_view_artefact($artefactobj)) {
                $artefact = get_record('artefact', 'id', $headerbg, null, null, null, null, 'artefacttype,title,description,note');
                $artefactfilefiles = get_record('artefact_file_files', 'artefact', $headerbg);
                $artefactfileimage = get_record('artefact_file_image', 'artefact', $headerbg);

                // Open and read the contents of each file...
                $headerbgimage = $artefactobj->get_path();
                $fp = fopen($headerbgimage, 'rb');
                $filesize = filesize($headerbgimage);
                $contents = fread($fp, $filesize);
                fclose($fp);
                // Export each file...
                $childelement = $xmldoc->createElement('image');
                $itemnode = $rootelement->appendChild($childelement);
                $itemnode->setAttribute('type', 'header-background-image');
                $itemnode->setAttribute('artefact', serialize($artefact));
                $itemnode->setAttribute('artefact_file_files', serialize($artefactfilefiles));
                $itemnode->setAttribute('artefact_file_image', serialize($artefactfileimage));
                $itemnode->setAttribute('contents', base64_encode($contents));
            }
        }

        // Fonts element...
        // for exporting site fonts, which get embedded via @font-face CSS rule...
        $textfonttype = get_field('skin_fonts', 'fonttype', 'name', $viewskin['view_text_font_family']);
        $headingfonttype = get_field('skin_fonts', 'fonttype', 'name', $viewskin['view_heading_font_family']);
        if ($textfonttype == 'site' or $headingfonttype == 'site') {
            $fontsElement = $xmldoc->createElement('fonts');
            $itemnode = $rootelement->appendChild($fontsElement);

            // Export text font if it is a site font...
            if ($textfonttype == 'site') {
                $fontdata = get_record('skin_fonts', 'name', $viewskin['view_text_font_family']);
                $fontelement = $xmldoc->createElement('font');
                $fontnode = $fontsElement->appendChild($fontelement);
                $fontnode->setAttribute('name', $fontdata->name);
                $fontnode->setAttribute('title', $fontdata->title);
                $fontnode->setAttribute('font-licence', $fontdata->licence);
                $fontnode->setAttribute('font-preview', $fontdata->previewfont);
                $fontnode->setAttribute('font-variants', base64_encode($fontdata->variants));
                $fontnode->setAttribute('font-type', $fontdata->fonttype);
                $fontnode->setAttribute('heading-font-only', $fontdata->onlyheading);
                $fontnode->setAttribute('font-stack', $fontdata->fontstack);
                $fontnode->setAttribute('generic-font', $fontdata->genericfont);

                // Also export all the files in the appropriate folder...
                $fontpath = get_config('dataroot') . 'skins/fonts/' . $fontdata->name . '/';
                if ($handle = opendir($fontpath)) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                            // Open and read the contents of each file...
                            $fp = fopen($fontpath . $file, 'rb');
                            $filesize = filesize($fontpath . $file);
                            $contents = fread($fp, $filesize);
                            fclose($fp);
                            // Export each file...
                            $fileelement = $xmldoc->createElement('file');
                            $filenode = $fontelement->appendChild($fileelement);
                            $filenode->setAttribute('name', $file);
                            $filenode->setAttribute('contents', base64_encode($contents));
                        }
                    }
                    closedir($handle);
                }
            }

            // Export heading font if it is a site font...
            if ($headingfonttype == 'site') {
                $fontdata = get_record('skin_fonts', 'name', $viewskin['view_heading_font_family']);
                $fontelement = $xmldoc->createElement('font');
                $fontnode = $fontsElement->appendChild($fontelement);
                $fontnode->setAttribute('name', $fontdata->name);
                $fontnode->setAttribute('title', $fontdata->title);
                $fontnode->setAttribute('font-licence', $fontdata->licence);
                $fontnode->setAttribute('font-preview', $fontdata->previewfont);
                $fontnode->setAttribute('font-variants', base64_encode($fontdata->variants));
                $fontnode->setAttribute('font-type', $fontdata->fonttype);
                $fontnode->setAttribute('heading-font-only', $fontdata->onlyheading);
                $fontnode->setAttribute('font-stack', $fontdata->fontstack);
                $fontnode->setAttribute('generic-font', $fontdata->genericfont);

                // Also export all the files in the appropriate folder...
                $fontpath = get_config('dataroot') . 'skins/fonts/' . $fontdata->name . '/';
                if ($handle = opendir($fontpath)) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                            // Open and read the contents of each file...
                            $fp = fopen($fontpath . $file, 'rb');
                            $filesize = filesize($fontpath . $file);
                            $contents = fread($fp, $filesize);
                            fclose($fp);
                            // Export each file...
                            $fileelement = $xmldoc->createElement('file');
                            $filenode = $fontelement->appendChild($fileelement);
                            $filenode->setAttribute('name', $file);
                            $filenode->setAttribute('contents', base64_encode($contents));
                        }
                    }
                    closedir($handle);
                }
            }
        }

        // custom CSS element...
        $childelement = $xmldoc->createElement('customcss');
        $itemnode = $rootelement->appendChild($childelement);
        $itemnode->setAttribute('contents', serialize($viewskin['view_custom_css']));
    }
}
$content = $xmldoc->saveXML();

header('Content-Type: text/xml; charset=utf-8');
header('Content-Disposition: attachment; filename=' . str_replace('"', '\"', $xmlfilename) . '.xml');
echo($content);
exit;
