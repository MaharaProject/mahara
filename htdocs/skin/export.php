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
//  Dinamically create export XML
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
        $viewskin = unserialize($exportskin->viewskin);

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

        // Header element...  // TODO remove this
        $childelement = $xmldoc->createElement('header');
        $itemnode = $rootelement->appendChild($childelement);
        $itemnode->setAttribute('background-color', $viewskin['header_background_color']);
        $itemnode->setAttribute('font-color', $viewskin['header_text_font_color']);
        $itemnode->setAttribute('normal-color', $viewskin['header_link_normal_color']);
        if (intval($viewskin['header_link_normal_underline']) == 0) {
            $itemnode->setAttribute('normal-decoration', 'none');
        }
        else {
            $itemnode->setAttribute('normal-decoration', 'underline');
        }
        $itemnode->setAttribute('hover-color', $viewskin['header_link_hover_color']);
        if (intval($viewskin['header_link_hover_underline']) == 0) {
            $itemnode->setAttribute('hover-decoration', 'none');
        }
        else {
            $itemnode->setAttribute('hover-decoration', 'underline');
        }
        $itemnode->setAttribute('logo-image', $viewskin['header_logo_image']);

        // View (page) element...  // TODO remove this
        $childelement = $xmldoc->createElement('view');
        $itemnode = $rootelement->appendChild($childelement);
        $itemnode->setAttribute('background-color', $viewskin['view_background_color']);
        $itemnode->setAttribute('background-repeat', Skin::background_repeat_number_to_value($viewskin['view_background_repeat']));
        $itemnode->setAttribute('background-attachment', $viewskin['view_background_attachment']);
        $itemnode->setAttribute('background-position', Skin::background_position_number_to_value($viewskin['view_background_position']));
        $itemnode->setAttribute('width', $viewskin['view_background_width'].'%');

        // Text element...
        $childelement = $xmldoc->createElement('text');
        $itemnode = $rootelement->appendChild($childelement);
        $itemnode->setAttribute('text-font', $viewskin['view_text_font_family']);
        $itemnode->setAttribute('heading-font', $viewskin['view_heading_font_family']);
        $itemnode->setAttribute('font-size', $viewskin['view_text_font_size']);
        $itemnode->setAttribute('font-color', $viewskin['view_text_font_color']);
        $itemnode->setAttribute('heading-color', $viewskin['view_text_heading_color']);
        $itemnode->setAttribute('emphasized-color', $viewskin['view_text_emphasized_color']);

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

        // Table element...  // TODO remove this
        $childelement = $xmldoc->createElement('table');
        $itemnode = $rootelement->appendChild($childelement);
        $itemnode->setAttribute('border-color', $viewskin['view_table_border_color']);
        $itemnode->setAttribute('odd-row-color', $viewskin['view_table_odd_row_color']);
        $itemnode->setAttribute('even-row-color', $viewskin['view_table_even_row_color']);

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

        // Page background image element...  // TODO remove this
        $viewbg = $viewskin['view_background_image'];
        if (!empty($viewbg) && $viewbg > 0) {
            // Get existing page background image data...
            // Get existing skin background image data...
            $artefactobj = new ArtefactTypeImage($viewbg);
            if ($USER->can_view_artefact($artefactobj)) {
                $artefact = get_record('artefact', 'id', $viewbg, null, null, null, null, 'artefacttype,title,description,note');
                $artefactfilefiles = get_record('artefact_file_files', 'artefact', $viewbg);
                $artefactfileimage = get_record('artefact_file_image', 'artefact', $viewbg);
                // Open and read the contents of each file...
                $viewbgimage = $artefactobj->get_path();
                $fp = fopen($viewbgimage, 'rb');
                $filesize = filesize($viewbgimage);
                $contents = fread($fp, $filesize);
                fclose($fp);
                // Export each file...
                $childelement = $xmldoc->createElement('image');
                $itemnode = $rootelement->appendChild($childelement);
                $itemnode->setAttribute('type', 'view-background-image');
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
