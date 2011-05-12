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
 * @subpackage lib
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class Country {

    /**
     * This listing obtained by:
     *  perl -MLocale::Country -le 'print join("\n", sort map { country2code($_) . " => " . country2code($_, LOCALE_CODE_ALPHA_3) . ", // " . $_ } all_country_names())'
     * Hopefully it doesn't need updating too often :)
     */
    private static $mapping = array(
        array(
            '2' => 'ad',
            '3' => 'and',
            'match' => "^Andorra\$",
        ),
        array(
            '2' => 'ae',
            '3' => 'are',
            'match' => "^United Arab Emirates\$",
        ),
        array(
            '2' => 'af',
            '3' => 'afg',
            'match' => "^Afghanistan\$",
        ),
        array(
            '2' => 'ag',
            '3' => 'atg',
            'match' => "^Antigua and Barbuda\$",
        ),
        array(
            '2' => 'ai',
            '3' => 'aia',
            'match' => "^Anguilla\$",
        ),
        array(
            '2' => 'al',
            '3' => 'alb',
            'match' => "^Albania\$",
        ),
        array(
            '2' => 'am',
            '3' => 'arm',
            'match' => "^Armenia\$",
        ),
        array(
            '2' => 'an',
            '3' => 'ant',
            'match' => "^Netherlands Antilles\$",
        ),
        array(
            '2' => 'ao',
            '3' => 'ago',
            'match' => "^Angola\$",
        ),
        array(
            '2' => 'aq',
            '3' => 'ata',
            'match' => "^Antarctica\$",
        ),
        array(
            '2' => 'ar',
            '3' => 'arg',
            'match' => "^Argentina\$",
        ),
        array(
            '2' => 'as',
            '3' => 'asm',
            'match' => "^American Samoa\$",
        ),
        array(
            '2' => 'at',
            '3' => 'aut',
            'match' => "^Austria\$",
        ),
        array(
            '2' => 'au',
            '3' => 'aus',
            'match' => "^Australia\$",
        ),
        array(
            '2' => 'aw',
            '3' => 'abw',
            'match' => "^Aruba\$",
        ),
        array(
            '2' => 'ax',
            '3' => 'ala',
            'match' => "^Aland Islands\$",
        ),
        array(
            '2' => 'az',
            '3' => 'aze',
            'match' => "^Azerbaijan\$",
        ),
        array(
            '2' => 'ba',
            '3' => 'bih',
            'match' => "^Bosnia and Herzegovina\$",
        ),
        array(
            '2' => 'bb',
            '3' => 'brb',
            'match' => "^Barbados\$",
        ),
        array(
            '2' => 'bd',
            '3' => 'bgd',
            'match' => "^Bangladesh\$",
        ),
        array(
            '2' => 'be',
            '3' => 'bel',
            'match' => "^Belgium\$",
        ),
        array(
            '2' => 'bf',
            '3' => 'bfa',
            'match' => "^Burkina Faso\$",
        ),
        array(
            '2' => 'bg',
            '3' => 'bgr',
            'match' => "^Bulgaria\$",
        ),
        array(
            '2' => 'bh',
            '3' => 'bhr',
            'match' => "^Bahrain\$",
        ),
        array(
            '2' => 'bi',
            '3' => 'bdi',
            'match' => "^Burundi\$",
        ),
        array(
            '2' => 'bj',
            '3' => 'ben',
            'match' => "^Benin\$",
        ),
        array(
            '2' => 'bm',
            '3' => 'bmu',
            'match' => "^Bermuda\$",
        ),
        array(
            '2' => 'bn',
            '3' => 'brn',
            'match' => "^Brunei Darussalam\$",
        ),
        array(
            '2' => 'bo',
            '3' => 'bol',
            'match' => "^Bolivia\$",
        ),
        array(
            '2' => 'br',
            '3' => 'bra',
            'match' => "^Brazil\$",
        ),
        array(
            '2' => 'bs',
            '3' => 'bhs',
            'match' => "^Bahamas\$",
        ),
        array(
            '2' => 'bt',
            '3' => 'btn',
            'match' => "^Bhutan\$",
        ),
        array(
            '2' => 'bv',
            '3' => 'bvt',
            'match' => "^Bouvet Island\$",
        ),
        array(
            '2' => 'bw',
            '3' => 'bwa',
            'match' => "^Botswana\$",
        ),
        array(
            '2' => 'by',
            '3' => 'blr',
            'match' => "^Belarus\$",
        ),
        array(
            '2' => 'bz',
            '3' => 'blz',
            'match' => "^Belize\$",
        ),
        array(
            '2' => 'ca',
            '3' => 'can',
            'match' => "^Canada\$",
        ),
        array(
            '2' => 'cc',
            '3' => 'cck',
            'match' => "^Cocos (Keeling) Islands\$",
        ),
        array(
            '2' => 'cd',
            '3' => 'cod',
            'match' => "^Congo, The Democratic Republic of the\$",
        ),
        array(
            '2' => 'cf',
            '3' => 'caf',
            'match' => "^Central African Republic\$",
        ),
        array(
            '2' => 'cg',
            '3' => 'cog',
            'match' => "^Congo\$",
        ),
        array(
            '2' => 'ch',
            '3' => 'che',
            'match' => "^Switzerland\$",
        ),
        array(
            '2' => 'ci',
            '3' => 'civ',
            'match' => "^Cote D'Ivoire\$",
        ),
        array(
            '2' => 'ck',
            '3' => 'cok',
            'match' => "^Cook Islands\$",
        ),
        array(
            '2' => 'cl',
            '3' => 'chl',
            'match' => "^Chile\$",
        ),
        array(
            '2' => 'cm',
            '3' => 'cmr',
            'match' => "^Cameroon\$",
        ),
        array(
            '2' => 'cn',
            '3' => 'chn',
            'match' => "^China\$",
        ),
        array(
            '2' => 'co',
            '3' => 'col',
            'match' => "^Colombia\$",
        ),
        array(
            '2' => 'cr',
            '3' => 'cri',
            'match' => "^Costa Rica\$",
        ),
        array(
            '2' => 'cs',
            '3' => 'scg',
            'match' => "^Serbia and Montenegro\$",
        ),
        array(
            '2' => 'cu',
            '3' => 'cub',
            'match' => "^Cuba\$",
        ),
        array(
            '2' => 'cv',
            '3' => 'cpv',
            'match' => "^Cape Verde\$",
        ),
        array(
            '2' => 'cx',
            '3' => 'cxr',
            'match' => "^Christmas Island\$",
        ),
        array(
            '2' => 'cy',
            '3' => 'cyp',
            'match' => "^Cyprus\$",
        ),
        array(
            '2' => 'cz',
            '3' => 'cze',
            'match' => "^Czech Republic\$",
        ),
        array(
            '2' => 'de',
            '3' => 'deu',
            'match' => "^Germany\$",
        ),
        array(
            '2' => 'dj',
            '3' => 'dji',
            'match' => "^Djibouti\$",
        ),
        array(
            '2' => 'dk',
            '3' => 'dnk',
            'match' => "^Denmark\$",
        ),
        array(
            '2' => 'dm',
            '3' => 'dma',
            'match' => "^Dominica\$",
        ),
        array(
            '2' => 'do',
            '3' => 'dom',
            'match' => "^Dominican Republic\$",
        ),
        array(
            '2' => 'dz',
            '3' => 'dza',
            'match' => "^Algeria\$",
        ),
        array(
            '2' => 'ec',
            '3' => 'ecu',
            'match' => "^Ecuador\$",
        ),
        array(
            '2' => 'ee',
            '3' => 'est',
            'match' => "^Estonia\$",
        ),
        array(
            '2' => 'eg',
            '3' => 'egy',
            'match' => "^Egypt\$",
        ),
        array(
            '2' => 'eh',
            '3' => 'esh',
            'match' => "^Western Sahara\$",
        ),
        array(
            '2' => 'er',
            '3' => 'eri',
            'match' => "^Eritrea\$",
        ),
        array(
            '2' => 'es',
            '3' => 'esp',
            'match' => "^Spain\$",
        ),
        array(
            '2' => 'et',
            '3' => 'eth',
            'match' => "^Ethiopia\$",
        ),
        array(
            '2' => 'fi',
            '3' => 'fin',
            'match' => "^Finland\$",
        ),
        array(
            '2' => 'fj',
            '3' => 'fji',
            'match' => "^Fiji\$",
        ),
        array(
            '2' => 'fk',
            '3' => 'flk',
            'match' => "^Falkland Islands (Malvinas)\$",
        ),
        array(
            '2' => 'fm',
            '3' => 'fsm',
            'match' => "^Micronesia, Federated States of\$",
        ),
        array(
            '2' => 'fo',
            '3' => 'fro',
            'match' => "^Faroe Islands\$",
        ),
        array(
            '2' => 'fr',
            '3' => 'fra',
            'match' => "^France\$",
        ),
        array(
            '2' => 'fx',
            '3' => 'fxx',
            'match' => "^France, Metropolitan\$",
        ),
        array(
            '2' => 'ga',
            '3' => 'gab',
            'match' => "^Gabon\$",
        ),
        array(
            '2' => 'gb',
            '3' => 'gbr',
            'match' => "^United Kingdom\$",
        ),
        array(
            '2' => 'gd',
            '3' => 'grd',
            'match' => "^Grenada\$",
        ),
        array(
            '2' => 'ge',
            '3' => 'geo',
            'match' => "^Georgia\$",
        ),
        array(
            '2' => 'gf',
            '3' => 'guf',
            'match' => "^French Guiana\$",
        ),
        array(
            '2' => 'gh',
            '3' => 'gha',
            'match' => "^Ghana\$",
        ),
        array(
            '2' => 'gi',
            '3' => 'gib',
            'match' => "^Gibraltar\$",
        ),
        array(
            '2' => 'gl',
            '3' => 'grl',
            'match' => "^Greenland\$",
        ),
        array(
            '2' => 'gm',
            '3' => 'gmb',
            'match' => "^Gambia\$",
        ),
        array(
            '2' => 'gn',
            '3' => 'gin',
            'match' => "^Guinea\$",
        ),
        array(
            '2' => 'gp',
            '3' => 'glp',
            'match' => "^Guadeloupe\$",
        ),
        array(
            '2' => 'gq',
            '3' => 'gnq',
            'match' => "^Equatorial Guinea\$",
        ),
        array(
            '2' => 'gr',
            '3' => 'grc',
            'match' => "^Greece\$",
        ),
        array(
            '2' => 'gs',
            '3' => 'sgs',
            'match' => "^South Georgia and the South Sandwich Islands\$",
        ),
        array(
            '2' => 'gt',
            '3' => 'gtm',
            'match' => "^Guatemala\$",
        ),
        array(
            '2' => 'gu',
            '3' => 'gum',
            'match' => "^Guam\$",
        ),
        array(
            '2' => 'gw',
            '3' => 'gnb',
            'match' => "^Guinea-Bissau\$",
        ),
        array(
            '2' => 'gy',
            '3' => 'guy',
            'match' => "^Guyana\$",
        ),
        array(
            '2' => 'hk',
            '3' => 'hkg',
            'match' => "^Hong Kong\$",
        ),
        array(
            '2' => 'hm',
            '3' => 'hmd',
            'match' => "^Heard Island and McDonald Islands\$",
        ),
        array(
            '2' => 'hn',
            '3' => 'hnd',
            'match' => "^Honduras\$",
        ),
        array(
            '2' => 'hr',
            '3' => 'hrv',
            'match' => "^Croatia\$",
        ),
        array(
            '2' => 'ht',
            '3' => 'hti',
            'match' => "^Haiti\$",
        ),
        array(
            '2' => 'hu',
            '3' => 'hun',
            'match' => "^Hungary\$",
        ),
        array(
            '2' => 'id',
            '3' => 'idn',
            'match' => "^Indonesia\$",
        ),
        array(
            '2' => 'ie',
            '3' => 'irl',
            'match' => "^Ireland\$",
        ),
        array(
            '2' => 'il',
            '3' => 'isr',
            'match' => "^Israel\$",
        ),
        array(
            '2' => 'in',
            '3' => 'ind',
            'match' => "^India\$",
        ),
        array(
            '2' => 'io',
            '3' => 'iot',
            'match' => "^British Indian Ocean Territory\$",
        ),
        array(
            '2' => 'iq',
            '3' => 'irq',
            'match' => "^Iraq\$",
        ),
        array(
            '2' => 'ir',
            '3' => 'irn',
            'match' => "^Iran, Islamic Republic of\$",
        ),
        array(
            '2' => 'is',
            '3' => 'isl',
            'match' => "^Iceland\$",
        ),
        array(
            '2' => 'it',
            '3' => 'ita',
            'match' => "^Italy\$",
        ),
        array(
            '2' => 'jm',
            '3' => 'jam',
            'match' => "^Jamaica\$",
        ),
        array(
            '2' => 'jo',
            '3' => 'jor',
            'match' => "^Jordan\$",
        ),
        array(
            '2' => 'jp',
            '3' => 'jpn',
            'match' => "^Japan\$",
        ),
        array(
            '2' => 'ke',
            '3' => 'ken',
            'match' => "^Kenya\$",
        ),
        array(
            '2' => 'kg',
            '3' => 'kgz',
            'match' => "^Kyrgyzstan\$",
        ),
        array(
            '2' => 'kh',
            '3' => 'khm',
            'match' => "^Cambodia\$",
        ),
        array(
            '2' => 'ki',
            '3' => 'kir',
            'match' => "^Kiribati\$",
        ),
        array(
            '2' => 'km',
            '3' => 'com',
            'match' => "^Comoros\$",
        ),
        array(
            '2' => 'kn',
            '3' => 'kna',
            'match' => "^Saint Kitts and Nevis\$",
        ),
        array(
            '2' => 'kp',
            '3' => 'prk',
            'match' => "^Korea, Democratic People's Republic of\$",
        ),
        array(
            '2' => 'kr',
            '3' => 'kor',
            'match' => "^Korea, Republic of\$",
        ),
        array(
            '2' => 'kw',
            '3' => 'kwt',
            'match' => "^Kuwait\$",
        ),
        array(
            '2' => 'ky',
            '3' => 'cym',
            'match' => "^Cayman Islands\$",
        ),
        array(
            '2' => 'kz',
            '3' => 'kaz',
            'match' => "^Kazakhstan\$",
        ),
        array(
            '2' => 'la',
            '3' => 'lao',
            'match' => "^Lao People's Democratic Republic\$",
        ),
        array(
            '2' => 'lb',
            '3' => 'lbn',
            'match' => "^Lebanon\$",
        ),
        array(
            '2' => 'lc',
            '3' => 'lca',
            'match' => "^Saint Lucia\$",
        ),
        array(
            '2' => 'li',
            '3' => 'lie',
            'match' => "^Liechtenstein\$",
        ),
        array(
            '2' => 'lk',
            '3' => 'lka',
            'match' => "^Sri Lanka\$",
        ),
        array(
            '2' => 'lr',
            '3' => 'lbr',
            'match' => "^Liberia\$",
        ),
        array(
            '2' => 'ls',
            '3' => 'lso',
            'match' => "^Lesotho\$",
        ),
        array(
            '2' => 'lt',
            '3' => 'ltu',
            'match' => "^Lithuania\$",
        ),
        array(
            '2' => 'lu',
            '3' => 'lux',
            'match' => "^Luxembourg\$",
        ),
        array(
            '2' => 'lv',
            '3' => 'lva',
            'match' => "^Latvia\$",
        ),
        array(
            '2' => 'ly',
            '3' => 'lby',
            'match' => "^Libyan Arab Jamahiriya\$",
        ),
        array(
            '2' => 'ma',
            '3' => 'mar',
            'match' => "^Morocco\$",
        ),
        array(
            '2' => 'mc',
            '3' => 'mco',
            'match' => "^Monaco\$",
        ),
        array(
            '2' => 'md',
            '3' => 'mda',
            'match' => "^Moldova, Republic of\$",
        ),
        array(
            '2' => 'mg',
            '3' => 'mdg',
            'match' => "^Madagascar\$",
        ),
        array(
            '2' => 'mh',
            '3' => 'mhl',
            'match' => "^Marshall Islands\$",
        ),
        array(
            '2' => 'mk',
            '3' => 'mkd',
            'match' => "^Macedonia, the Former Yugoslav Republic of\$",
        ),
        array(
            '2' => 'ml',
            '3' => 'mli',
            'match' => "^Mali\$",
        ),
        array(
            '2' => 'mm',
            '3' => 'mmr',
            'match' => "^Myanmar\$",
        ),
        array(
            '2' => 'mn',
            '3' => 'mng',
            'match' => "^Mongolia\$",
        ),
        array(
            '2' => 'mo',
            '3' => 'mac',
            'match' => "^Macao\$",
        ),
        array(
            '2' => 'mp',
            '3' => 'mnp',
            'match' => "^Northern Mariana Islands\$",
        ),
        array(
            '2' => 'mq',
            '3' => 'mtq',
            'match' => "^Martinique\$",
        ),
        array(
            '2' => 'mr',
            '3' => 'mrt',
            'match' => "^Mauritania\$",
        ),
        array(
            '2' => 'ms',
            '3' => 'msr',
            'match' => "^Montserrat\$",
        ),
        array(
            '2' => 'mt',
            '3' => 'mlt',
            'match' => "^Malta\$",
        ),
        array(
            '2' => 'mu',
            '3' => 'mus',
            'match' => "^Mauritius\$",
        ),
        array(
            '2' => 'mv',
            '3' => 'mdv',
            'match' => "^Maldives\$",
        ),
        array(
            '2' => 'mw',
            '3' => 'mwi',
            'match' => "^Malawi\$",
        ),
        array(
            '2' => 'mx',
            '3' => 'mex',
            'match' => "^Mexico\$",
        ),
        array(
            '2' => 'my',
            '3' => 'mys',
            'match' => "^Malaysia\$",
        ),
        array(
            '2' => 'mz',
            '3' => 'moz',
            'match' => "^Mozambique\$",
        ),
        array(
            '2' => 'na',
            '3' => 'nam',
            'match' => "^Namibia\$",
        ),
        array(
            '2' => 'nc',
            '3' => 'ncl',
            'match' => "^New Caledonia\$",
        ),
        array(
            '2' => 'ne',
            '3' => 'ner',
            'match' => "^Niger\$",
        ),
        array(
            '2' => 'nf',
            '3' => 'nfk',
            'match' => "^Norfolk Island\$",
        ),
        array(
            '2' => 'ng',
            '3' => 'nga',
            'match' => "^Nigeria\$",
        ),
        array(
            '2' => 'ni',
            '3' => 'nic',
            'match' => "^Nicaragua\$",
        ),
        array(
            '2' => 'nl',
            '3' => 'nld',
            'match' => "^Netherlands\$",
        ),
        array(
            '2' => 'no',
            '3' => 'nor',
            'match' => "^Norway\$",
        ),
        array(
            '2' => 'np',
            '3' => 'npl',
            'match' => "^Nepal\$",
        ),
        array(
            '2' => 'nr',
            '3' => 'nru',
            'match' => "^Nauru\$",
        ),
        array(
            '2' => 'nu',
            '3' => 'niu',
            'match' => "^Niue\$",
        ),
        array(
            '2' => 'nz',
            '3' => 'nzl',
            'match' => "^New Zealand\$",
        ),
        array(
            '2' => 'om',
            '3' => 'omn',
            'match' => "^Oman\$",
        ),
        array(
            '2' => 'pa',
            '3' => 'pan',
            'match' => "^Panama\$",
        ),
        array(
            '2' => 'pe',
            '3' => 'per',
            'match' => "^Peru\$",
        ),
        array(
            '2' => 'pf',
            '3' => 'pyf',
            'match' => "^French Polynesia\$",
        ),
        array(
            '2' => 'pg',
            '3' => 'png',
            'match' => "^Papua New Guinea\$",
        ),
        array(
            '2' => 'ph',
            '3' => 'phl',
            'match' => "^Philippines\$",
        ),
        array(
            '2' => 'pk',
            '3' => 'pak',
            'match' => "^Pakistan\$",
        ),
        array(
            '2' => 'pl',
            '3' => 'pol',
            'match' => "^Poland\$",
        ),
        array(
            '2' => 'pm',
            '3' => 'spm',
            'match' => "^Saint Pierre and Miquelon\$",
        ),
        array(
            '2' => 'pn',
            '3' => 'pcn',
            'match' => "^Pitcairn\$",
        ),
        array(
            '2' => 'pr',
            '3' => 'pri',
            'match' => "^Puerto Rico\$",
        ),
        array(
            '2' => 'ps',
            '3' => 'pse',
            'match' => "^Palestinian Territory, Occupied\$",
        ),
        array(
            '2' => 'pt',
            '3' => 'prt',
            'match' => "^Portugal\$",
        ),
        array(
            '2' => 'pw',
            '3' => 'plw',
            'match' => "^Palau\$",
        ),
        array(
            '2' => 'py',
            '3' => 'pry',
            'match' => "^Paraguay\$",
        ),
        array(
            '2' => 'qa',
            '3' => 'qat',
            'match' => "^Qatar\$",
        ),
        array(
            '2' => 're',
            '3' => 'reu',
            'match' => "^Reunion\$",
        ),
        array(
            '2' => 'ro',
            '3' => 'rou',
            'match' => "^Romania\$",
        ),
        array(
            '2' => 'ru',
            '3' => 'rus',
            'match' => "^Russian Federation\$",
        ),
        array(
            '2' => 'rw',
            '3' => 'rwa',
            'match' => "^Rwanda\$",
        ),
        array(
            '2' => 'sa',
            '3' => 'sau',
            'match' => "^Saudi Arabia\$",
        ),
        array(
            '2' => 'sb',
            '3' => 'slb',
            'match' => "^Solomon Islands\$",
        ),
        array(
            '2' => 'sc',
            '3' => 'syc',
            'match' => "^Seychelles\$",
        ),
        array(
            '2' => 'sd',
            '3' => 'sdn',
            'match' => "^Sudan\$",
        ),
        array(
            '2' => 'se',
            '3' => 'swe',
            'match' => "^Sweden\$",
        ),
        array(
            '2' => 'sg',
            '3' => 'sgp',
            'match' => "^Singapore\$",
        ),
        array(
            '2' => 'sh',
            '3' => 'shn',
            'match' => "^Saint Helena\$",
        ),
        array(
            '2' => 'si',
            '3' => 'svn',
            'match' => "^Slovenia\$",
        ),
        array(
            '2' => 'sj',
            '3' => 'sjm',
            'match' => "^Svalbard and Jan Mayen\$",
        ),
        array(
            '2' => 'sk',
            '3' => 'svk',
            'match' => "^Slovakia\$",
        ),
        array(
            '2' => 'sl',
            '3' => 'sle',
            'match' => "^Sierra Leone\$",
        ),
        array(
            '2' => 'sm',
            '3' => 'smr',
            'match' => "^San Marino\$",
        ),
        array(
            '2' => 'sn',
            '3' => 'sen',
            'match' => "^Senegal\$",
        ),
        array(
            '2' => 'so',
            '3' => 'som',
            'match' => "^Somalia\$",
        ),
        array(
            '2' => 'sr',
            '3' => 'sur',
            'match' => "^Suriname\$",
        ),
        array(
            '2' => 'st',
            '3' => 'stp',
            'match' => "^Sao Tome and Principe\$",
        ),
        array(
            '2' => 'sv',
            '3' => 'slv',
            'match' => "^El Salvador\$",
        ),
        array(
            '2' => 'sy',
            '3' => 'syr',
            'match' => "^Syrian Arab Republic\$",
        ),
        array(
            '2' => 'sz',
            '3' => 'swz',
            'match' => "^Swaziland\$",
        ),
        array(
            '2' => 'tc',
            '3' => 'tca',
            'match' => "^Turks and Caicos Islands\$",
        ),
        array(
            '2' => 'td',
            '3' => 'tcd',
            'match' => "^Chad\$",
        ),
        array(
            '2' => 'tf',
            '3' => 'atf',
            'match' => "^French Southern Territories\$",
        ),
        array(
            '2' => 'tg',
            '3' => 'tgo',
            'match' => "^Togo\$",
        ),
        array(
            '2' => 'th',
            '3' => 'tha',
            'match' => "^Thailand\$",
        ),
        array(
            '2' => 'tj',
            '3' => 'tjk',
            'match' => "^Tajikistan\$",
        ),
        array(
            '2' => 'tk',
            '3' => 'tkl',
            'match' => "^Tokelau\$",
        ),
        array(
            '2' => 'tl',
            '3' => 'tls',
            'match' => "^Timor-Leste\$",
        ),
        array(
            '2' => 'tm',
            '3' => 'tkm',
            'match' => "^Turkmenistan\$",
        ),
        array(
            '2' => 'tn',
            '3' => 'tun',
            'match' => "^Tunisia\$",
        ),
        array(
            '2' => 'to',
            '3' => 'ton',
            'match' => "^Tonga\$",
        ),
        array(
            '2' => 'tr',
            '3' => 'tur',
            'match' => "^Turkey\$",
        ),
        array(
            '2' => 'tt',
            '3' => 'tto',
            'match' => "^Trinidad and Tobago\$",
        ),
        array(
            '2' => 'tv',
            '3' => 'tuv',
            'match' => "^Tuvalu\$",
        ),
        array(
            '2' => 'tw',
            '3' => 'twn',
            'match' => "^Taiwan, Province of China\$",
        ),
        array(
            '2' => 'tz',
            '3' => 'tza',
            'match' => "^Tanzania, United Republic of\$",
        ),
        array(
            '2' => 'ua',
            '3' => 'ukr',
            'match' => "^Ukraine\$",
        ),
        array(
            '2' => 'ug',
            '3' => 'uga',
            'match' => "^Uganda\$",
        ),
        array(
            '2' => 'um',
            '3' => 'umi',
            'match' => "^United States Minor Outlying Islands\$",
        ),
        array(
            '2' => 'us',
            '3' => 'usa',
            'match' => "^United States\$",
        ),
        array(
            '2' => 'uy',
            '3' => 'ury',
            'match' => "^Uruguay\$",
        ),
        array(
            '2' => 'uz',
            '3' => 'uzb',
            'match' => "^Uzbekistan\$",
        ),
        array(
            '2' => 'va',
            '3' => 'vat',
            'match' => "^Holy See (Vatican City State)\$",
        ),
        array(
            '2' => 'vc',
            '3' => 'vct',
            'match' => "^Saint Vincent and the Grenadines\$",
        ),
        array(
            '2' => 've',
            '3' => 'ven',
            'match' => "^Venezuela\$",
        ),
        array(
            '2' => 'vg',
            '3' => 'vgb',
            'match' => "^Virgin Islands, British\$",
        ),
        array(
            '2' => 'vi',
            '3' => 'vir',
            'match' => "^Virgin Islands, U.S.\$",
        ),
        array(
            '2' => 'vn',
            '3' => 'vnm',
            'match' => "^Vietnam\$",
        ),
        array(
            '2' => 'vu',
            '3' => 'vut',
            'match' => "^Vanuatu\$",
        ),
        array(
            '2' => 'wf',
            '3' => 'wlf',
            'match' => "^Wallis and Futuna\$",
        ),
        array(
            '2' => 'ws',
            '3' => 'wsm',
            'match' => "^Samoa\$",
        ),
        array(
            '2' => 'ye',
            '3' => 'yem',
            'match' => "^Yemen\$",
        ),
        array(
            '2' => 'yt',
            '3' => 'myt',
            'match' => "^Mayotte\$",
        ),
        array(
            '2' => 'za',
            '3' => 'zaf',
            'match' => "^South Africa\$",
        ),
        array(
            '2' => 'zm',
            '3' => 'zmb',
            'match' => "^Zambia\$",
        ),
        array(
            '2' => 'zw',
            '3' => 'zwe',
            'match' => "^Zimbabwe\$",
        ),
    );

    public static function iso3166_alpha3_to_iso3166_alpha2($countrycode) {
        $countrycode = strtolower($countrycode);
        foreach (self::$mapping as $countrydata) {
            if ($countrydata['3'] == $countrycode) {
                return $countrydata['2'];
            }
        }
        return null;
    }

    /**
     * This is really simplistic - the matches need lots of tweaking
     */
    public static function countryname_to_iso3166_alpha2($countryname) {
        foreach (self::$mapping as $countrydata) {
            if (preg_match('#' . $countrydata['match'] . '#i', $countryname)) {
                return $countrydata['2'];
            }
        }
        return null;
    }

    public static function iso3166_1alpha2_to_iso3166_1alpha3($countrycode) {
        $countrycode = strtolower($countrycode);
        foreach (self::$mapping as $countrydata) {
            if ($countrydata['2'] == $countrycode) {
                return $countrydata['3'];
            }
        }
        return null;
    }

}
