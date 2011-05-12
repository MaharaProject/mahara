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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 */

defined('INTERNAL') || die();

class HtmltoText {

    private $body;
    private $lines;
    private $line;
    private $prefix;
    private $newlines;
    private $indent;
    private $baseurl;
    private $links;
    private $linkcount;

    public function __construct($html, $baseurl) {
        $doc = new domDocument;
        $doc->loadHTML($html);
        $this->body = $doc->getElementsByTagName('html')->item(0)->getElementsByTagName('body')->item(0);
        $this->lines = array();
        $this->line = (object) array('text' => '', 'wrap' => true, 'prefix' => "\n");
        $this->pre = 0;
        $this->indent = array();
        $this->baseurl = $baseurl;
        $this->links = array();
        $this->linkcount = 0;
    }

    public function text() {
        $this->process_children($this->body);

        if (!empty($this->links)) {
            $this->para();
            foreach ($this->links as $link => $i) {
                $this->output("[$i] $link", false);
                $this->newline();
            }
        }

        if ($this->line) {
            $this->wrap_line();
        }

        return join("\n", $this->lines);
    }

    private function get_attributes($node) {
        $attrs = array();
        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                $attrs[$attr->name] = $attr->value;
            }
        }
        return $attrs;
    }

    private function wrap_line() {
        if ($this->line->wrap) {
            $this->lines[] = wordwrap($this->line->text, 75, $this->line->prefix);
        }
        else {
            $this->lines[] = $this->line->text;
        }
    }

    private function newline() {
        if ($this->newlines == 0) {
            $this->newlines = 1;
        }
    }

    private function para() {
        $this->newlines = 2;
    }

    private $indentfirstchar = array('bq' => '> ', 'list' => '- ', 'dd' => '   ', 'td' => '  ');
    private $indentchar      = array('bq' => '> ', 'list' => '  ', 'dd' => '   ', 'td' => '  ');

    private function output($str, $wrap=true) {
        if ($this->newlines) {
            $this->wrap_line();
            $this->line = (object) array('text' => '', 'wrap' => $wrap, 'prefix' => "\n");
            $totalindents = count($this->indent);
            if ($totalindents) {
                $this->line->text .= ' ';
                for ($i = 0; $i < $totalindents - 1; $i++) {
                    $this->line->text .= $this->indentchar[$this->indent[$i]];
                }
                $this->line->prefix .= $this->line->text . $this->indentchar[$this->indent[$i]];
                $this->line->text .= $this->indentfirstchar[$this->indent[$i]];
            }
            if ($this->newlines == 2) {
                $this->line->text = "\n" . $this->line->text;
            }
            $this->newlines = 0;
        }
        $this->line->text .= $str;
        $this->line->wrap &= $wrap;
    }

    private function process_children($node) {
        if (is_object($node) && $node->childNodes->length) {
            foreach ($node->childNodes as $child) {
                $this->process_node($child);
            }
        }
    }

    private function process_node($node) {
        if ($node->nodeType === XML_TEXT_NODE) {
            if ($this->newlines) {
                $text = ltrim($node->nodeValue);
                if (empty($text)) {
                    return;
                }
            }
            $this->output($node->nodeValue);
        }
        else if ($node->nodeType === XML_ELEMENT_NODE) {
            switch ($node->tagName) {
            case 'script':
            case 'style':
            case 'head':
                return;

            case 'hr':
                $this->para();
                $this->output('----------------------------------------------------------');
                $this->para();
                return;

            case 'br':
                $this->newline();
                return;

            case 'img':
                $attrs = $this->get_attributes($node);
                if (!empty($attrs['src'])) {
                    $href = $attrs['src'];
                    $alt = isset($attrs['alt']) ? $attrs['alt'] : '';
                    if (strpos($href, '://') == false) {
                        $href = $this->baseurl . $href;
                    }
                    if (!isset($this->links[$href])) {
                        $this->links[$href] = ++$this->linkcount;
                    }
                    $this->output('![' . $alt . '][' . $this->links[$href] . ']');
                }
                return;
            }

            if (!$node->childNodes->length) {
                return;
            }

            switch ($node->tagName) {

            case 'h1': case 'h2': case 'h3': case 'h4': case 'h5': case 'h6':
                $n = substr($node->tagName, 1, 1);
                $this->para();
                $this->output(str_repeat('#', $n) . ' ');
                $this->process_children($node);
                $this->para();
                break;
            
            case 'p': case 'div':
                $this->para();
                $this->process_children($node);
                $this->para();
                break;

            case 'blockquote':
                $this->para();
                $this->indent[] = 'bq';
                $this->process_children($node);
                array_pop($this->indent);
                $this->para();
                break;

            case 'em': case 'i': case 'u':
                $this->output('_');
                $this->process_children($node);
                $this->output('_');
                break;

            case 'strong': case 'b':
                $this->output('**');
                $this->process_children($node);
                $this->output('**');
                break;

            case 'dl':
                $this->para();
                $this->process_children($node);
                $this->para();
                break;

            case 'dt':
                $this->para();
                $this->process_children($node);
                $this->newline();
                break;

            case 'dd':
                $this->newline();
                $this->indent[] = 'dd';
                $this->process_children($node);
                $this->newline();
                array_pop($this->indent);
                break;

            case 'ol': case 'ul':
                $this->para();
                $this->indent[] = 'list';
                $this->process_children($node);
                $this->para();
                array_pop($this->indent);
                break;
                
            case 'li':
                $this->newline();
                $this->process_children($node);
                $this->newline();
                break;

            case 'table': case 'tr':
                $this->para();
                $this->process_children($node);
                $this->para();
                break;

            case 'td': case 'th':
                $this->newline();
                $this->indent[] = 'td';
                $this->process_children($node);
                $this->newline();
                array_pop($this->indent);
                break;

            case 'pre':
                $this->para();
                $this->process_children($node);
                break;

            case 'a':
                $attrs = $this->get_attributes($node);
                $href = $attrs['href'];
                if (!empty($href) && substr($href, 0, 1) != '#' && substr($href, 0, 11) != 'javascript:') {
                    if (strpos($href, '://') == false) {
                        $href = $this->baseurl . $href;
                    }
                    if (!isset($this->links[$href])) {
                        $this->links[$href] = ++$this->linkcount;
                    }
                    $this->output('[');
                }
                else {
                    $href = null;
                }
                $this->process_children($node);
                if (!empty($href)) {
                    $this->output('][' . $this->links[$href] . ']');
                }
                break;

            default:
                $this->process_children($node);
            }
        }
    }
}
