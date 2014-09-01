<?php

/**
 * Validates Color as defined by CSS.
 */
class HTMLPurifier_AttrDef_CSS_Color extends HTMLPurifier_AttrDef
{

    /**
     * @param string $color
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool|string
     */
    public function validate($color, $config, $context)
    {
        static $colors = null;
        if ($colors === null) {
            $colors = $config->get('Core.ColorKeywords');
        }

        $color = trim($color);
        if ($color === '') {
            return false;
        }

        $lower = strtolower($color);
        if (isset($colors[$lower])) {
            return $colors[$lower];
        }

        if (strpos($color, 'rgb(') !== false) {
            // rgb literal handling
            $length = strlen($color);
            if (strpos($color, ')') !== $length - 1) {
                return false;
            }
            $triad = substr($color, 4, $length - 4 - 1);
            $parts = explode(',', $triad);
            if (count($parts) !== 3) {
                return false;
            }
            $type = false; // to ensure that they're all the same type
            $new_parts = array();
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') {
                    return false;
                }
                $length = strlen($part);
                if ($part[$length - 1] === '%') {
                    // handle percents
                    if (!$type) {
                        $type = 'percentage';
                    } elseif ($type !== 'percentage') {
                        return false;
                    }
                    $num = (float)substr($part, 0, $length - 1);
                    if ($num < 0) {
                        $num = 0;
                    }
                    if ($num > 100) {
                        $num = 100;
                    }
                    $new_parts[] = "$num%";
                } else {
                    // handle integers
                    if (!$type) {
                        $type = 'integer';
                    } elseif ($type !== 'integer') {
                        return false;
                    }
                    $num = (int)$part;
                    if ($num < 0) {
                        $num = 0;
                    }
                    if ($num > 255) {
                        $num = 255;
                    }
                    $new_parts[] = (string)$num;
                }
            }
            $new_triad = implode(',', $new_parts);
            $color = "rgb($new_triad)";
        } else if (strpos($color, 'rgba(') !== false) {
            // rgba literal handling
            $length = strlen($color);
            if (strpos($color, ')') !== $length - 1) {
                return false;
            }
            $quartet = substr($color, 5, $length - 5 - 1);
            $parts = explode(',', $quartet);
            if (count($parts) !== 4) {
                return false;
            }
            $opacity = trim(array_pop($parts));
            if (!is_numeric($opacity)) {
                return false;
            }
            $opacity = (float) $opacity;
            if ($opacity < 0.0 || $opacity > 1.0) {
                return false;
            }
            $type = false; // to ensure that the first three parts are integers
            $new_parts = array();
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') {
                    return false;
                }
                $length = strlen($part);
                if ($part[$length - 1] === '%') {
                    // handle percents
                    if (!$type) {
                        $type = 'percentage';
                    } elseif ($type !== 'percentage') {
                        return false;
                    }
                    $num = (float)substr($part, 0, $length - 1);
                    if ($num < 0) {
                        $num = 0;
                    }
                    if ($num > 100) {
                        $num = 100;
                    }
                    $new_parts[] = "$num%";
                } else {
                    // handle integers
                    if (!$type) {
                        $type = 'integer';
                    } elseif ($type !== 'integer') {
                        return false;
                    }
                    $num = (int)$part;
                    if ($num < 0) {
                        $num = 0;
                    }
                    if ($num > 255) {
                        $num = 255;
                    }
                    $new_parts[] = (string)$num;
                }
            }
            $new_triad = implode(',', $new_parts);
            $color = "rgba($new_triad,$opacity)";
        } else if (strpos($color, 'hsl(') !== false) {
            // hsl literal handling hsl(hue(0-360), saturation%, lightness%)
            $length = strlen($color);
            if (strpos($color, ')') !== $length - 1) {
                return false;
            }
            $triad = substr($color, 4, $length - 4 - 1);
            $parts = explode(',', $triad);
            if (count($parts) !== 3) {
                return false;
            }
            $hue = trim(array_shift($parts));
            if (!is_numeric($hue)) {
                return false;
            }
            $hue = (float) $hue;
            if ($hue < 0 || $hue > 360) {
                return false;
            }
            // to ensure that saturation and lightness are valid percentage values
            $new_parts = array();
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') {
                    return false;
                }
                $length = strlen($part);
                if ($part[$length - 1] !== '%') {
                    return false;
                }
                // handle percents
                $num = (float)substr($part, 0, $length - 1);
                if ($num < 0) {
                    $num = 0;
                }
                if ($num > 100) {
                    $num = 100;
                }
                $new_parts[] = "$num%";
            }
            $new_triad = implode(',', $new_parts);
            $color = "hsl($hue,$new_triad)";
        } else if (strpos($color, 'hsla(') !== false) {
            // hsla literal handling hsla(hue(0-360), saturation%, lightness%, opacity)
            $length = strlen($color);
            if (strpos($color, ')') !== $length - 1) {
                return false;
            }
            $quartet = substr($color, 5, $length - 5 - 1);
            $parts = explode(',', $quartet);
            if (count($parts) !== 4) {
                return false;
            }
            $hue = trim(array_shift($parts));
            if (!is_numeric($hue)) {
                return false;
            }
            $hue = (float) $hue;
            if ($hue < 0 || $hue > 360) {
                return false;
            }
            $opacity = trim(array_pop($parts));
            if (!is_numeric($opacity)) {
                return false;
            }
            $opacity = (float) $opacity;
            if ($opacity < 0.0 || $opacity > 1.0) {
                return false;
            }
            $new_parts = array();
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') {
                    return false;
                }
                $length = strlen($part);
                if ($part[$length - 1] !== '%') {
                    return false;
                }
                // handle percents
                $num = (float)substr($part, 0, $length - 1);
                if ($num < 0) {
                    $num = 0;
                }
                if ($num > 100) {
                    $num = 100;
                }
                $new_parts[] = "$num%";
            }
            $new_triad = implode(',', $new_parts);
            $color = "hsla($hue,$new_triad,$opacity)";
        } else {
            // hexadecimal handling
            if ($color[0] === '#') {
                $hex = substr($color, 1);
            } else {
                $hex = $color;
                $color = '#' . $color;
            }
            $length = strlen($hex);
            if ($length !== 3 && $length !== 6) {
                return false;
            }
            if (!ctype_xdigit($hex)) {
                return false;
            }
        }
        return $color;
    }
}

// vim: et sw=4 sts=4
