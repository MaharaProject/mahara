<?php
/**
 * spellcheck.php
 *
 * Copyright, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://www.tinymce.com/license
 * Contributing: http://www.tinymce.com/contributing
 */

define('INTERNAL', 1);
require_once('../../../../init.php');

require('./includes/Engine.php');
require('./includes/EnchantEngine.php');
require('./includes/PSpellEngine.php');

$engine = get_config('tinymcespellcheckerengine');

$tinymceSpellCheckerConfig = array(
	"engine" => $engine, // enchant, pspell

	// Enchant options
	"enchant_dicts_path" => "./dicts",

	// PSpell options
	"pspell.mode" => "fast",
	"pspell.spelling" => "",
	"pspell.jargon" => "",
	"pspell.encoding" => ""
);

TinyMCE_Spellchecker_Engine::processRequest($tinymceSpellCheckerConfig);
