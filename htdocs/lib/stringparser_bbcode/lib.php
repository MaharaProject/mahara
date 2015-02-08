<?php

/**
 * Converts bbcodes in the given text to HTML. Also auto-links URLs.
 *
 * @param string $text The text to parse
 * @return string
 */
function parse_bbcode($text) {
    require_once('stringparser_bbcode/stringparser_bbcode.class.php');

    $bbcode = new StringParser_BBCode();
    $bbcode->setGlobalCaseSensitive(false);
    $bbcode->setRootParagraphHandling(true);

    // Convert all newlines to a common form
    $bbcode->addFilter(STRINGPARSER_FILTER_PRE, create_function('$a', 'return preg_replace("/\015\012|015\012/", "\n", $a);'));

    $bbcode->addParser(array('block', 'inline'), 'format_whitespace');
    $bbcode->addParser(array('block', 'inline'), 'autolink_text');

    // The bbcodes themselves
    $bbcode->addCode('b', 'simple_replace', null, array ('start_tag' => '<strong>', 'end_tag' => '</strong>'),
            'inline', array('listitem', 'block', 'inline', 'link'), array());
    $bbcode->addCode ('i', 'simple_replace', null, array ('start_tag' => '<em>', 'end_tag' => '</em>'),
            'inline', array('listitem', 'block', 'inline', 'link'), array());
    $bbcode->addCode ('url', 'usecontent?', 'bbcode_url', array('usecontent_param' => 'default'),
            'link', array('listitem', 'block', 'inline'), array('link'));
    $bbcode->addCode ('img', 'usecontent', 'bbcode_img', array(),
            'image', array ('listitem', 'block', 'inline', 'link'), array());

    $text = $bbcode->parse($text);
    return $text;
}

/**
 * Given some text, locates URLs in it and converts them to HTML
 *
 * @param string $text The text to locate URLs in
 * @return string
 *
 * {@internal{Note, it's perhaps unreasonably expected that the input to this
 * function is HTML escaped already. Especially because it's expected that
 * there are no <a href="...">s in there. This works for now because the bbcode
 * parser breaks things out into tokens, but this function might need reworking
 * to be more useful in other places.}}
 */
function autolink_text($text) {
    $text = preg_replace(
            '#(^|.)(https?://\S+)#me',
            "_autolink_text_helper('$2', '$1')",
            $text
    );
    return $text;
}

/**
 * Helps autolink_text by providing the HTML to link up URLs found.
 *
 * Intelligently decides what parts of the matched URL should be linked up, to
 * get around issues where URLs are surrounded by brackets or have trailing
 * punctuation on them
 *
 * @param string $potentialurl     The URL to check. It should already have been run through hsc()
 * @param string $leadingcharacter The character (if any) before the URL. Used
 *                                 to check for URLs surrounded by brackets
 */
function _autolink_text_helper($potentialurl, $leadingcharacter) {
    static $brackets = array('(' => ')', '{' => '}', '[' => ']', "'" => "'");
    $trailingcharacter = substr($potentialurl, -1);
    $startofurl = substr($potentialurl, 0, -1);

    // Attempt to intelligently handle several annoyances that happen with URL
    // auto linking. We don't want to link up brackets if the URL is enclosed
    // in them. We also don't want to link up punctuation after URLs
    if (in_array($leadingcharacter, array_keys($brackets)) &&
    in_array($trailingcharacter, $brackets)) {
        // The URL was surrounded by brackets
        return $leadingcharacter . '<a href="' . $startofurl . '">' . $startofurl . '</a>' . $trailingcharacter;
    }
    else {
        foreach($brackets as $opener => $closer) {
            if ($trailingcharacter == $closer &&
            false === strpos($startofurl, $opener)) {
                // The URL ended in a bracket and didn't contain one
                // Note that we can't just use this clause without using the clause
                // about URLs surrounded by brackets, because otherwise we won't catch
                // URLs with balanced brackets in them like http://url/?(foo)&bar=1
                return $leadingcharacter . '<a href="' . $startofurl . '">' . $startofurl . '</a>' . $trailingcharacter;
            }
        }

        // Check for trailing punctuation
        if (in_array($trailingcharacter, array('.', ',', '!', '?'))) {
            return $leadingcharacter . '<a href="' . $startofurl . '">' . $startofurl . '</a>' . $trailingcharacter;
        }
        else {
            return $leadingcharacter . '<a href="' . $potentialurl . '">' . $potentialurl . '</a>';
        }
    }

    // Execution should never get here
    return $potentialurl;
}

/**
 * Callback for StringParser_BBCode to handle [url] and [link] bbcode
 */
function bbcode_url($action, $attributes, $content, $params, $node_object) {
    if (!isset ($attributes['default'])) {
        $url = $content;
        $text = hsc($content);
    }
    else {
        $url = $attributes['default'];
        $text = $content;
    }
    if ($action == 'validate') {
        $valid_protos = array('http://', 'https://', 'ftp://');
        foreach ($valid_protos as $proto) {
            if (substr($url, 0, strlen($proto)) == $proto) {
                return true;
            }
        }
        return false;
    }
    return '<a href="' . hsc($url) . '">' . $text . '</a>';
}

/**
 * Callback for StringParser_BBCode to handle [img] bbcode
 */
function bbcode_img($action, $attributes, $content, $params, $node_object) {
    if ($action == 'validate') {
        $valid_protos = array('http://', 'https://');
        foreach ($valid_protos as $proto) {
            if (substr($content, 0, strlen($proto)) == $proto) {
                return true;
            }
        }
        return false;
    }
    return '<img src="' . hsc($content) . '" alt="">';
}
