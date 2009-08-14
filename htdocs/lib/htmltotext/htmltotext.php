<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 * @copyright  (C) portions from Moodle, (C) Martin Dougiamas http://dougiamas.com
 */

defined('INTERNAL') || die();

// Bastard child of html2text.py, class.html2text.php

class HtmltoText {

    private $body;
    private $lines;
    private $line;
    private $prefix;
    private $blockquote;
    private $list;
    private $nls;

    public function __construct($html) {
        $doc = new domDocument;
        $doc->loadHTML($html);
        log_debug($doc->saveHTML());
        $this->body = $doc->getElementsByTagName('html')->item(0)->getElementsByTagName('body')->item(0);
        $this->lines = array();
        $this->line = '';
        $this->prefix = '';
        $this->blockquote = 0;
        $this->pre = 0;
    }

    public function text() {
        $this->process_children($this->body);
        if ($this->line) {
            $this->wrap_line();
        }
        return join("\n", $this->lines);
    }

    private function attributes($node_map) {
        if ($node_map->length === 0) return array();
        $array = array();
        foreach ($node_map as $attr) {
            $array[$attr->name] = $attr->value;
        }
        return $array;
    }

    private function wrap_line() {
        $this->lines[] = wordwrap($this->line, 75, $this->prefix);
    }

    private function nl() {
        if ($this->nls == 0) {
            $this->nls = 1;
        }
    }

    private function para() {
        $this->nls = 2;
    }

    private function output($str) {
        if ($this->nls) {
            $this->wrap_line();
            $this->prefix = "\n";
            $this->line = '';
            $bq = $this->blockquote ? str_repeat('>', $this->blockquote) . ' ' : '';
            $this->prefix .= $bq;
            $this->line .= str_repeat("$bq\n", $this->nls - 1) . $bq;
            if ($this->list) {
                $list = str_repeat(' ', $this->list);
                $this->prefix .= $list . '  ';
                $this->line .= $list . '- ';
            }
            $this->nls = 0;
        }
        $this->line .= $str;
    }

    private function process_children($node) {
        if ($node->childNodes->length) {
            foreach ($node->childNodes as $child) {
                $this->process_node($child);
            }
        }
    }

    private function process_node($node) {
        if ($node->nodeType === XML_TEXT_NODE) {
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
                $this->nl();
                return;

            case 'img':
                if ($node->hasAttributes()) {
                    $attr = $this->attributes($node->attributes);
                    if (!empty($attr['src'])) {
                        $this->output('[' . $attr['src'] . ']');
                    }
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
                $this->blockquote += 1;
                $this->process_children($node);
                $this->blockquote -= 1;
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
                $this->nl();
                $this->process_children($node);
                $this->nl();
                break;
                
            case 'dd':
                $this->output('    ');
                $this->process_children($node);
                break;

            case 'ol': case 'ul':
                $this->list += 1;
                $this->para();
                $this->process_children($node);
                $this->para();
                $this->list -= 1;
                break;
                
            case 'li':
                $this->nl();
                $this->output(str_repeat(' ', $this->list) . '- ');
                $this->process_children($node);
                $this->nl();
                break;

            case 'table': case 'tr':
                $this->para();
                $this->process_children($node);
                break;

            case 'td':
                $this->nl();
                $this->process_children($node);
                break;

            case 'pre':
                $this->para();
                $this->process_children($node);
                break;

            default:
                $this->process_children($node);
            }
        }
    }
}

?>