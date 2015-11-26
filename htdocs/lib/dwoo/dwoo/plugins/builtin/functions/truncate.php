<?php

/**
 * Truncates a string at the given length
 * <pre>
 *  * value : text to truncate
 *  * length : the maximum length for the string
 *  * etc : the characters that are added to show that the string was cut off
 *  * break : if true, the string will be cut off at the exact length, instead of cutting at the nearest space
 *  * middle : if true, the string will contain the beginning and the end, and the extra characters will be removed from the middle
 *  * This version also supports multibyte strings.
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @copyright  Copyright (c) 2008, Jordi Boggiano
 * @author     Guy Rutenberg <guyrutenberg@gmail.com>
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 * @link       http://dwoo.org/
 * @version    1.1.0
 * @date       2009-07-18
 * @package    Dwoo
 */
function Dwoo_Plugin_truncate(Dwoo_Core $dwoo, $value, $length=80, $etc='...', $break=false, $middle=false, $charset='UTF-8')
{
	if ($length == 0) {
		return '';
	}

    $value = (string) $value;
    $etc = (string) $etc;
    $length = (int) $length;

    if (mb_strlen($value) > $length) {
        $length -= min($length, mb_strlen($etc));
        if ($break === false && $middle === false) {
            $value = preg_replace('#\s+?(\S+)?$#u', '', mb_substr($value, 0, $length+1, $charset));
        }
        if ($middle === false) {
            return mb_substr($value, 0, $length, $charset) . $etc;
        }
        else {
            return mb_substr($value, 0, ceil($length/2), $charset) . $etc . mb_substr($value, -floor($length/2), (mb_strlen($value) - floor($length/2)), $charset);
        }
    }
    else {
        return $value;
    }
}