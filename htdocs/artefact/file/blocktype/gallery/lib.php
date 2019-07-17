<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-gallery
 * @author     Catalyst IT Ltd
 * @author     Gregor Anzelj (External Galleries, e.g. Flickr, Picasa)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2011 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeGallery extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.file/gallery');
    }

    public static function get_description() {
        return get_string('description1', 'blocktype.file/gallery');
    }

    public static function get_categories() {
        return array('fileimagevideo' => 5000);
    }

    public static function get_instance_javascript(BlockInstance $instance) {
        $blockid = $instance->get('id');
        // The initjs for the fancybox will be applied to all galleries on the page
        return array(
            array(
                'file'   => get_config('wwwroot') . 'js/fancybox/jquery.fancybox.min.js',
                'initjs' => " $('[data-fancybox]').fancybox({
                      buttons : [
                               'zoom',
                               'slideShow',
                               'download',
                               'close'
                               ],
                      loop : true,
                      lang : '" . current_language() . "',
                      i18n : {
                          '" . current_language() . "' : {
                              CLOSE: \"" . get_string('CLOSE', 'blocktype.file/gallery') . "\",
                              NEXT: \"" . get_string('NEXT', 'blocktype.file/gallery') . "\",
                              PREV: \"" . get_string('PREV', 'blocktype.file/gallery') . "\",
                              ERROR: \"" . get_string('ERROR', 'blocktype.file/gallery') . "\",
                              PLAY_START: \"" . get_string('PLAY_START', 'blocktype.file/gallery') . "\",
                              PLAY_STOP: \"" . get_string('PLAY_STOP', 'blocktype.file/gallery') . "\",
                              FULL_SCREEN: \"" . get_string('FULL_SCREEN', 'blocktype.file/gallery') . "\",
                              THUMBS: \"" . get_string('THUMBS', 'blocktype.file/gallery') . "\",
                              DOWNLOAD: \"" . get_string('DOWNLOAD', 'blocktype.file/gallery') . "\",
                              SHARE: \"" . get_string('SHARE', 'blocktype.file/gallery') . "\",
                              ZOOM: \"" . get_string('ZOOM', 'blocktype.file/gallery') . "\"
                          }
                      }
                });"
            ),
            array(
                'file'   => get_config('wwwroot') . 'js/masonry/masonry.min.js',
                'initjs' => " $('.js-masonry.thumbnails').masonry({ itemSelector: '.thumb' });"
            )
        );
    }

    public static function get_instance_css(BlockInstance $instance) {
        return array(
            get_config('wwwroot') . 'js/fancybox/jquery.fancybox.min.css'
        );
    }

    public static function get_instance_config_javascript(BlockInstance $instance) {
        return array(
            'js/configform.js',
        );
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        $configdata = $instance->get('configdata'); // this will make sure to unserialize it for us
        $configdata['viewid'] = $instance->get('view');
        $style = isset($configdata['style']) ? intval($configdata['style']) : 2;
        $copyright = null; // Needed to set Panoramio copyright later...
        $width = !empty($configdata['width']) ? $configdata['width'] : 75;
        $width = floor($width);
        switch ($style) {
            case 0: // thumbnails
                $template = 'thumbnails';
                break;
            case 1: // slideshow
                $template = 'slideshow';
                $width = !empty($configdata['width']) ? $configdata['width'] : 400;
                break;
            case 2: // square thumbnails
                $template = 'squarethumbs';
                break;
        }

        $images = array();
        $fancyboxattr = get_config_plugin('blocktype', 'gallery', 'usefancybox') ? 'lightbox_' . $instance->get('id') : null;

        // if we're trying to embed external gallery (thumbnails or slideshow)
        if (isset($configdata['select']) && $configdata['select'] == 2) {
            $gallery = self::make_gallery_url($configdata['external']);
            if (empty($gallery)) {
                return get_string('externalnotsupported', 'blocktype.file/gallery');
            }
            $url  = isset($gallery['url']) ? hsc($gallery['url']) : null;
            $type = isset($gallery['type']) ? hsc($gallery['type']) : null;
            $var1 = isset($gallery['var1']) ? hsc($gallery['var1']) : null;
            $var2 = isset($gallery['var2']) ? hsc($gallery['var2']) : null;

            switch ($type) {
                case 'widget':
                /*****************************
                  Roy Tanck's FLICKR WIDGET
                  for Flickr RSS & Picasa RSS
          http://www.roytanck.com/get-my-flickr-widget/
                 *****************************/
                    $widget_sizes = array(100, 200, 300);
                    $width = self::find_nearest($widget_sizes, $width);
                    $images = urlencode(str_replace('&amp;', '&', $url));
                    $template = 'imagecloud';
                    break;
                case 'picasa':
                    // Slideshow
                    if ($style == 1) {
                        $picasa_show_sizes = array(144, 288, 400, 600, 800);
                        $width = self::find_nearest($picasa_show_sizes, $width);
                        $height = round($width * 0.75);
                        $images = array('user' => $var1, 'gallery' => $var2);
                        $template = 'picasashow';
                    }
                    // Thumbnails
                    else {
                        $picasa_thumbnails = array(32, 48, 64, 72, 104, 144, 150, 160);
                        $width = self::find_nearest($picasa_thumbnails, $width);
                        // If the Thumbnails should be Square...
                        if ($style == 2) {
                            $small = 's' . $width . '-c';
                            $URL = 'http://picasaweb.google.com/data/feed/api/user/' . $var1 . '/album/' . $var2 . '?kind=photo&thumbsize=' . $width . 'c';
                        }
                        else {
                            $small = 's' . $width;
                            $URL = 'http://picasaweb.google.com/data/feed/api/user/' . $var1 . '/album/' . $var2 . '?kind=photo&thumbsize=' . $width;
                        }
                        $big = 's' . get_config_plugin('blocktype', 'gallery', 'previewwidth');

                        libxml_before(true);
                        $xmlDoc = new DOMDocument('1.0', 'UTF-8');
                        $config = array(
                            CURLOPT_URL => $URL,
                            CURLOPT_RETURNTRANSFER => true,
                        );
                        $result = mahara_http_request($config);
                        $xmlDoc->loadXML($result->data);
                        $photos = $xmlDoc->getElementsByTagNameNS('http://search.yahoo.com/mrss/', 'group');
                        libxml_after();

                        foreach ($photos as $photo) {
                            $children = $photo->cloneNode(true);
                            $thumb = $children->getElementsByTagNameNS('http://search.yahoo.com/mrss/', 'thumbnail')->item(0)->getAttribute('url');
                            $description = null;
                            if (isset($children->getElementsByTagNameNS('http://search.yahoo.com/mrss/', 'description')->item(0)->firstChild->nodeValue)) {
                                $description = $children->getElementsByTagNameNS('http://search.yahoo.com/mrss/', 'description')->item(0)->firstChild->nodeValue;
                            }

                            $images[] = array(
                                'link' => str_replace($small, $big, $thumb),
                                'source' => $thumb,
                                'title' => $description,
                                'fancybox' => $fancyboxattr
                            );
                        }
                    }
                    break;
                case 'flickr':
                    // Slideshow
                    if ($style == 1) {
                        $flickr_show_sizes = array(400, 500, 700, 800);
                        $width = self::find_nearest($flickr_show_sizes, $width);
                        $height = round($width * 0.75);
                        $images = array('user' => $var1, 'gallery' => $var2);
                        $template = 'flickrshow';
                    }
                    // Thumbnails
                    else {
                        $width = 75; // Currently only thumbnail size, that Flickr supports

                        $api_key = get_config_plugin('blocktype', 'gallery', 'flickrapikey');
                        $URL = 'https://api.flickr.com/services/rest/?method=flickr.photosets.getPhotos&extras=url_sq,url_t&photoset_id=' . $var2 . '&api_key=' . $api_key;
                        libxml_before(true);
                        $xmlDoc = new DOMDocument('1.0', 'UTF-8');
                        $config = array(
                            CURLOPT_URL => $URL,
                            CURLOPT_RETURNTRANSFER => true,
                        );
                        $result = mahara_http_request($config);
                        $xmlDoc->loadXML($result->data);
                        $photos = $xmlDoc->getElementsByTagName('photo');
                        libxml_after();
                        foreach ($photos as $photo) {
                            // If the Thumbnails should be Square...
                            if ($style == 2) {
                                $thumb = $photo->getAttribute('url_sq');
                                $link = str_replace('_s.jpg', '_b.jpg', $thumb);
                            }
                            else {
                                $thumb = $photo->getAttribute('url_t');
                                $link = str_replace('_t.jpg', '_b.jpg', $thumb);
                            }
                            $description = $photo->getAttribute('title');

                            $images[] = array(
                                'link' => $link,
                                'source' => $thumb,
                                'title' => $description,
                                'fancybox' => $fancyboxattr
                            );
                        }
                    }
                    break;
                case 'photobucket':
                    // Slideshow
                    if ($style == 1) {
                        $height = round($width * 0.75);
                        $images = array('url' => $url, 'user' => $var1, 'album' => $var2);
                        $template = 'photobucketshow';
                    }
                    // Thumbnails
                    else {
                        $consumer_key = get_config_plugin('blocktype', 'gallery', 'pbapikey'); // PhotoBucket API key
                        $consumer_secret = get_config_plugin('blocktype', 'gallery', 'pbapiprivatekey'); //PhotoBucket API private key

                        $oauth_signature_method = 'HMAC-SHA1';
                        $oauth_version = '1.0';
                        $oauth_timestamp = time();
                        $mt = microtime();
                        $rand = mt_rand();
                        $oauth_nonce = md5($mt . $rand);

                        $method = 'GET';
                        $albumname = $var1 . '/' . $var2;
                        $api_url = 'http://api.photobucket.com/album/' . urlencode($albumname);

                        $params = null;
                        $paramstring = 'oauth_consumer_key=' . $consumer_key . '&oauth_nonce=' . $oauth_nonce . '&oauth_signature_method=' . $oauth_signature_method . '&oauth_timestamp=' . $oauth_timestamp . '&oauth_version=' . $oauth_version;
                        $base = urlencode($method) . '&' . urlencode($api_url) . '&' . urlencode($paramstring);
                        $oauth_signature = base64_encode(hash_hmac('sha1', $base, $consumer_secret.'&', true));

                        $URL = $api_url . '?' . $paramstring . '&oauth_signature=' . urlencode($oauth_signature);
                        libxml_before(true);
                        $xmlDoc = new DOMDocument('1.0', 'UTF-8');
                        $config = array(
                            CURLOPT_URL => $URL,
                            CURLOPT_HEADER => false,
                            CURLOPT_RETURNTRANSFER => true,
                        );
                        $result = mahara_http_request($config);
                        $xmlDoc->loadXML($result->data);

                        $xmlDoc2 = new DOMDocument('1.0', 'UTF-8');
                        $config2 = array(
                            CURLOPT_URL => $xmlDoc->getElementsByTagName('url')->item(0)->firstChild->nodeValue,
                            CURLOPT_HEADER => false,
                            CURLOPT_RETURNTRANSFER => true,
                        );
                        $result2 = mahara_http_request($config2);
                        $xmlDoc2->loadXML($result->data);

                        $photos = $xmlDoc2->getElementsByTagName('media');
                        libxml_after();
                        foreach ($photos as $photo) {
                            $children = $photo->cloneNode(true);
                            $link = $children->getElementsByTagName('url')->item(0)->firstChild->nodeValue;
                            $thumb = $children->getElementsByTagName('thumb')->item(0)->firstChild->nodeValue;
                            $description = null;
                            if (isset($children->getElementsByTagName('description')->item(0)->firstChild->nodeValue)) {
                                $description = $children->getElementsByTagName('description')->item(0)->firstChild->nodeValue;
                            }

                            $images[] = array(
                                'link' => $link,
                                'source' => $thumb,
                                'title' => $description,
                                'fancybox' => $fancyboxattr
                            );
                        }
                    }
                    break;
                case 'windowslive':
                    // Slideshow
                    if ($style == 1) {
                        $images = array('url' => $url, 'user' => $var1, 'album' => $var2);
                        $template = 'windowsliveshow';
                    }
                    // Thumbnails
                    else {
                        $config = array(
                            CURLOPT_URL => str_replace(' ', '%20', $url),
                            CURLOPT_HEADER => false,
                            CURLOPT_RETURNTRANSFER => true,
                        );
                        $result = mahara_http_request($config);
                        $data = $result->data;

                        // Extract data about images and thumbs from HTML source - hack!
                        preg_match_all("#previewImageUrl: '([a-zA-Z0-9\_\-\.\\\/]+)'#", $data, $photos);
                        preg_match_all("#thumbnailImageUrl: '([a-zA-Z0-9\_\-\.\\\/]+)'#", $data, $thumbs);

                        for ($i = 0; $i < sizeof($photos[1]); $i++) {
                            $images[] = array(
                                'link' => str_replace(array('\x3a','\x2f','\x25','\x3fpsid\x3d1'), array(':','/','%',''), $photos[1][$i]),
                                'source' => str_replace(array('\x3a','\x2f','\x25','\x3fpsid\x3d1'), array(':','/','%',''), $thumbs[1][$i]),
                                'title' => null,
                                'fancybox' => $fancyboxattr
                            );
                        }
                    }
                    break;
            }
        }
        else {
            safe_require('artefact', 'file');
            $artefactids = array();
            if (isset($configdata['select']) && $configdata['select'] == 1 && is_array($configdata['artefactids'])) {
                $artefactids = $configdata['artefactids'];
            }
            else if ($versioning && !empty($configdata['existing_artefacts'])) {
                $artefactids = (array) $configdata['existing_artefacts'];
            }
            else if (!empty($configdata['artefactid'])) {
                // Get descendents of this folder.
                $artefactids = artefact_get_descendants(array(intval($configdata['artefactid'])));
            }

            $artefactids = $instance->order_artefacts_by_title($artefactids);

            // This can be either an image or profileicon. They both implement
            // render_self
            foreach ($artefactids as $artefactid) {
                $image = $instance->get_artefact_instance($artefactid);

                if ($image instanceof ArtefactTypeProfileIcon) {
                    $src = get_config('wwwroot') . 'thumb.php?type=profileiconbyid&id=' . $artefactid;
                    $src .= '&view=' . $instance->get('view');
                    $description = $image->get('title');
                }
                else if ($image instanceof ArtefactTypeImage) {
                    $src = get_config('wwwroot') . 'artefact/file/download.php?file=' . $artefactid;
                    $src .= '&view=' . $instance->get('view');
                    $description = $image->get('description');
                }
                else {
                    continue;
                }

                if ($fancyboxattr) {
                    $link = $src . '&maxwidth=' . get_config_plugin('blocktype', 'gallery', 'previewwidth');
                }
                else {
                    $link = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $artefactid . '&view=' . $instance->get('view');
                }

                // If the Thumbnails are Square or not...
                if ($style == 2) {
                    // Determine the scaling for the fitting the image in the square of $width size
                    // Calculate the bigger, width vs height, to work out the ratio
                    $configwidth = $width - (!empty($configdata['photoframe']) ? 8 : 0); // $width - photo frame padding
                    $imagewidth = $image->get('width');
                    $imageheight = $image->get('height');
                    if ($imagewidth > $imageheight) {
                        $ratio = $imagewidth / $configwidth;
                    }
                    else {
                        $ratio = $imageheight / $configwidth;
                    }
                    $ratiowidth = floor($imagewidth / $ratio);
                    $ratioheight = floor($imageheight / $ratio);
                    // All image dimensions need to be bigger than 15px
                    // see function imagesize_data_to_internal_form()
                    $ratiowidth = $ratiowidth < 16 ? 16 : $ratiowidth;
                    $ratioheight = $ratioheight < 16 ? 16 : $ratioheight;

                    $topoffset = floor(($configwidth - $ratioheight) / 2);
                    $src .= '&size=' . $ratiowidth . 'x' . $ratioheight;
                    $height = $ratioheight;
                }
                else {
                    $src .= '&maxwidth=' . $width;
                    $imgwidth = $image->get('width');
                    $imgheight = $image->get('height');
                    $height = ($imgwidth > $width) ? intval(($width / $imgwidth) * $imgheight) : $imgheight;
                }

                $images[] = array(
                    'link' => $link,
                    'source' => $src,
                    'height' => $height,
                    'width' => (!empty($ratiowidth) ? $ratiowidth : null),
                    'title' => $image->get('description'),
                    'fancybox' => $fancyboxattr,
                    'squaredimensions' => $width,
                    'squaretop' => (!empty($topoffset) ? $topoffset : null),
                );
            }
        }

        $smarty = smarty_core();
        $smarty->assign('instanceid', $instance->get('id'));
        $smarty->assign('count', count($images));
        $smarty->assign('images', $images);
        $smarty->assign('showdescription', (!empty($configdata['showdescription'])) ? $configdata['showdescription'] : false);
        $smarty->assign('width', $width);
        if (isset($height)) {
            $smarty->assign('height', $height);
        }
        if (isset($needsapikey)) {
            $smarty->assign('needsapikey', $needsapikey);
        }
        $smarty->assign('frame', !empty($configdata['photoframe']));
        $smarty->assign('copyright', $copyright);
        if (!empty($configdata['artefactid'])) {
            $artefact = $instance->get_artefact_instance($configdata['artefactid']);

            require_once(get_config('docroot') . 'artefact/comment/lib.php');
            require_once(get_config('docroot') . 'lib/view.php');
            $view = new View($configdata['viewid']);
            list($commentcount, $comments) = ArtefactTypeComment::get_artefact_comments_for_view($artefact, $view, $instance->get('id'), true, $editing, $versioning);
            $smarty->assign('commentcount', $commentcount);
            $smarty->assign('comments', $comments);
        }
        return $smarty->fetch('blocktype:gallery:' . $template . '.tpl');
    }

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        $elements = array();
        $elements['gallerysettings'] = array(
            'type' => 'fieldset',
            'legend' => get_string('gallerysettings', 'blocktype.file/gallery'),
            'collapsible' => true,
            'elements' => array(
                'usefancybox' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('usefancybox', 'blocktype.file/gallery'),
                    'description'  => get_string('usefancyboxdesc', 'blocktype.file/gallery'),
                    'defaultvalue' => get_config_plugin('blocktype', 'gallery', 'usefancybox'),
                ),
                'previewwidth' => array(
                    'type'         => 'text',
                    'size'         => 4,
                    'title'        => get_string('previewwidth', 'blocktype.file/gallery'),
                    'description'  => get_string('previewwidthdesc', 'blocktype.file/gallery'),
                    'defaultvalue' => get_config_plugin('blocktype', 'gallery', 'previewwidth'),
                    'rules'        => array('integer' => true, 'minvalue' => 16, 'maxvalue' => 1600),
                )
            ),
        );
        $elements['flickrsettings'] = array(
            'type' => 'fieldset',
            'legend' => get_string('flickrsettings', 'blocktype.file/gallery'),
            'collapsible' => true,
            'collapsed' => true,
            'elements' => array(
                'flickrapikey' => array(
                    'type'         => 'text',
                    'title'        => get_string('flickrapikey', 'blocktype.file/gallery'),
                    'size'         => 40, // Flickr API key is actually 32 characters long
                    'description'  => get_string('flickrapikeydesc', 'blocktype.file/gallery'),
                    'defaultvalue' => get_config_plugin('blocktype', 'gallery', 'flickrapikey'),
                ),
            ),
        );
        $elements['photobucketsettings'] = array(
            'type' => 'fieldset',
            'legend' => get_string('pbsettings', 'blocktype.file/gallery'),
            'collapsible' => true,
            'collapsed' => true,
            'class' => 'last',
            'elements' => array(
                'pbapikey' => array(
                    'type'         => 'text',
                    'title'        => get_string('pbapikey', 'blocktype.file/gallery'),
                    'size'         => 20, // PhotoBucket API key is actually 9 characters long
                    'description'  => get_string('pbapikeydesc', 'blocktype.file/gallery'),
                    'defaultvalue' => get_config_plugin('blocktype', 'gallery', 'pbapikey'),
                ),
                'pbapiprivatekey' => array(
                    'type'         => 'text',
                    'title'        => get_string('pbapiprivatekey', 'blocktype.file/gallery'),
                    'size'         => 40, // PhotoBucket API private key is actually 32 characters long
                    'defaultvalue' => get_config_plugin('blocktype', 'gallery', 'pbapiprivatekey'),
                ),
            ),
        );
        return array(
            'elements' => $elements,
            // Don't apply "panel panel-body" style to this form.
            'class' => null,
        );

    }

    public static function save_config_options(Pieform $form, $values) {
        set_config_plugin('blocktype', 'gallery', 'usefancybox', (int)$values['usefancybox']);
        set_config_plugin('blocktype', 'gallery', 'previewwidth', (int)$values['previewwidth']);
        set_config_plugin('blocktype', 'gallery', 'flickrapikey', $values['flickrapikey']);
        set_config_plugin('blocktype', 'gallery', 'pbapikey', $values['pbapikey']);
        set_config_plugin('blocktype', 'gallery', 'pbapiprivatekey', $values['pbapiprivatekey']);
    }

    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            set_config_plugin('blocktype', 'gallery', 'usefancybox', 1); // Use Fancybox 3 by default
            set_config_plugin('blocktype', 'gallery', 'previewwidth', 1024); // Maximum photo width for fancybox preview
        }
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        safe_require('artefact', 'file');
        $instance->set('artefactplugin', 'file');
        $user = $instance->get('view_obj')->get('owner');
        $select_options = array(
            0 => get_string('selectfolder', 'blocktype.file/gallery'),
            1 => get_string('selectimages', 'blocktype.file/gallery'),
            2 => get_string('selectexternal', 'blocktype.file/gallery'),
        );
        $style_options = array(
            0 => get_string('stylethumbs', 'blocktype.file/gallery'),
            2 => get_string('stylesquares', 'blocktype.file/gallery'),
            1 => get_string('styleslideshow', 'blocktype.file/gallery'),
        );
        if (isset($configdata['select']) && $configdata['select'] == 1) {
            $imageids = isset($configdata['artefactids']) ? $configdata['artefactids'] : array();
            $imageselector = self::imageselector($instance, $imageids);
            $folderselector = self::folderselector($instance, null, 'd-none');
            $externalurl = self::externalurl($instance, null, 'd-none');
        }
        else if (isset($configdata['select']) && $configdata['select'] == 2) {
            $imageselector = self::imageselector($instance, null, 'd-none');
            $folderselector = self::folderselector($instance, null, 'd-none');
            $url = isset($configdata['external']) ? urldecode($configdata['external']) : null;
            $externalurl = self::externalurl($instance, $url);
        }
        else {
            $imageselector = self::imageselector($instance, null, 'd-none');
            $folderid = !empty($configdata['artefactid']) ? array(intval($configdata['artefactid'])) : null;
            $folderselector = self::folderselector($instance, $folderid);
            $externalurl = self::externalurl($instance, null, 'd-none');
        }
        return array(
            'user' => array(
                'type' => 'hidden',
                'value' => $user,
            ),
            'select' => array(
                'type' => 'radio',
                'title' => get_string('select', 'blocktype.file/gallery'),
                'options' => $select_options,
                'defaultvalue' => (isset($configdata['select'])) ? $configdata['select'] : 0,
            ),
            'images' => $imageselector,
            'folder' => $folderselector,
            'external' => $externalurl,
            'style' => array(
                'type' => 'radio',
                'title' => get_string('style', 'blocktype.file/gallery'),
                'options' => $style_options,
                'defaultvalue' => (isset($configdata['style'])) ? $configdata['style'] : 2, // Square thumbnails should be default...
            ),
            'showdescription' => array(
                'type'  => 'switchbox',
                'title' => get_string('showdescriptions', 'blocktype.file/gallery'),
                'description' => get_string('showdescriptionsdescription', 'blocktype.file/gallery'),
                'defaultvalue' => !empty($configdata['showdescription']) ? true : false,
            ),
            'photoframe' => array(
                'type'         => 'switchbox',
                'title'        => get_string('photoframe', 'blocktype.file/gallery'),
                'description'  => get_string('photoframedesc2', 'blocktype.file/gallery'),
                'defaultvalue' => !empty($configdata['photoframe']) ? true : false,
            ),
            'width' => array(
                'type' => 'text',
                'title' => get_string('width', 'blocktype.file/gallery'),
                'size' => 3,
                'description' => get_string('widthdescription', 'blocktype.file/gallery'),
                'rules' => array(
                    'minvalue' => 16,
                    'maxvalue' => get_config('imagemaxwidth'),
                ),
                'defaultvalue' => (isset($configdata['width'])) ? $configdata['width'] : '75',
            ),
        );
    }

    public static function instance_config_validate(Pieform $form, $values) {
        global $USER;

        if (!empty($values['images'])) {
            foreach ($values['images'] as $id) {
                $image = artefact_instance_from_id($id);
                if (!($image instanceof ArtefactTypeImage) || !$USER->can_view_artefact($image)) {
                    $result['message'] = get_string('unrecoverableerror', 'error');
                    $form->set_error(null, $result['message']);
                    $form->reply(PIEFORM_ERR, $result);
                }
            }
        }

        if (!empty($values['folder'])) {
            $folder = artefact_instance_from_id($values['folder']);
            if (!($folder instanceof ArtefactTypeFolder) || !$USER->can_view_artefact($folder)) {
                $result['message'] = get_string('unrecoverableerror', 'error');
                $form->set_error(null, $result['message']);
                $form->reply(PIEFORM_ERR, $result);
            }
        }
    }

    public static function instance_config_save($values) {
        if ($values['select'] == 0) {
            $values['artefactid'] = $values['folder'];
            unset($values['artefactids']);
            unset($values['external']);
        }
        else if ($values['select'] == 1) {
            $values['artefactids'] = $values['images'];
            unset($values['artefactid']);
            unset($values['external']);
        }
        else if ($values['select'] == 2) {
            unset($values['artefactid']);
            unset($values['artefactids']);
        }
        unset($values['folder']);
        unset($values['images']);
        switch ($values['style']) {
            case 0: // thumbnails
            case 2: // square thumbnails
                $values['width'] = !empty($values['width']) ? $values['width'] : 75;
                break;
            case 1: // slideshow
                $values['width'] = !empty($values['width']) ? $values['width'] : 400;
                break;
        }
        return $values;
    }

    public static function imageselector(&$instance, $default=array(), $class=null) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('Images', 'artefact.file');
        $element['name'] = 'images';
        $element['accept'] = 'image/*';
        if ($class) {
            $element['class'] = $class;
        }
        $element['config']['selectone'] = false;
        $element['config']['selectmodal'] = true;
        $element['filters'] = array(
            'artefacttype'    => array('image', 'profileicon'),
        );
        return $element;
    }

    public static function folderselector(&$instance, $default=array(), $class=null) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('folder', 'artefact.file');
        $element['name'] = 'folder';
        if ($class) {
            $element['class'] = $class;
        }
        $element['config']['upload'] = false;
        $element['config']['selectone'] = true;
        $element['config']['selectfolders'] = true;
        $element['config']['selectmodal'] = true;
        $element['filters'] = array(
            'artefacttype'    => array('folder'),
        );
        return $element;
    }

    public static function externalurl(&$instance, $default=null, $class=null) {
        $element['title'] = get_string('externalgalleryurl', 'blocktype.file/gallery');
        $element['name'] = 'external';
        $element['type'] = 'textarea';
        if ($class) {
            $element['class'] = $class;
        }
        $element['rows'] = 5;
        $element['cols'] = 76;
        $element['defaultvalue'] = $default;
        $element['description'] = '<tr id="externalgalleryhelp" class="'.($class ? $class : '').'"><td colspan="2" class="description">'.
                                  get_string('externalgalleryurldesc', 'blocktype.file/gallery') . self::get_supported_external_galleries() .
                                  '</td></tr>';
        $element['help'] = true;
        return $element;
    }

    private static function make_gallery_url($url) {
        static $embedsources = array(
            // PicasaWeb Album (RSS) - for Roy Tanck's widget
            array(
                'match' => '#.*picasaweb.google.([a-zA-Z]{3}).*user\/([a-zA-Z0-9\_\-\=\&\.\/\:\%]+)\/albumid\/(\d+).*#',
                'url'   => 'http://picasaweb.google.$1/data/feed/base/user/$2/albumid/$3?alt=rss&kind=photo',
                'type'  => 'widget',
                'var1' => '$2',
                'var2' => '$3',
            ),
            // PicasaWeb Album (embed code)
            array(
                'match' => '#.*picasaweb.google.([a-zA-Z]{3})\/s\/c.*picasaweb.google.([a-zA-Z]{3})\/([a-zA-Z0-9\_\-\.]+)\/([a-zA-Z0-9\_\-\=\&\.\/\:\%]+).*#',
                'url'   => 'http://picasaweb.google.$2',
                'type'  => 'picasa',
                'var1' => '$3',
                'var2' => '$4',
            ),
            // PicasaWeb Album (direct link)
            array(
                'match' => '#.*picasaweb.google.([a-zA-Z]{3})\/([a-zA-Z0-9\_\-\.]+)\/([a-zA-Z0-9\_\-\=\&\.\/\:\%]+).*#',
                'url'   => 'http://picasaweb.google.$1',
                'type'  => 'picasa',
                'var1' => '$2',
                'var2' => '$3',
            ),
            // Flickr Set (RSS) - for Roy Tanck's widget
            array(
                'match' => '#.*api.flickr.com.*set=(\d+).*nsid=([a-zA-Z0-9\@]+).*#',
                'url'   => 'https://api.flickr.com/services/feeds/photoset.gne?set=$1&nsid=$2',
                'type'  => 'widget',
                'var1' => '$2',
                'var2' => '$1',
            ),
            // Flickr Set (direct link)
            array(
                'match' => '#.*www.flickr.com/photos/([a-zA-Z0-9\_\-\.\@]+).*/sets/([0-9]+).*#',
                'url'   => 'https://www.flickr.com/photos/$1/sets/$2/',
                'type'  => 'flickr',
                'var1' => '$1',
                'var2' => '$2',
            ),
            // Photobucket User Photos (direct link)
            array(
                'match' => '#.*([a-zA-Z0-9]+).photobucket.com/albums/([a-zA-Z0-9]+)/([a-zA-Z0-9\.\,\:\;\@\-\_\+\ ]+).*#',
                'url'   => 'http://$1.photobucket.com/albums/$2/$3',
                'type'  => 'photobucket',
                'var1' => '$3',
                'var2' => null,
            ),
            // Photobucket User Album Photos (direct link)
            array(
                'match' => '#.*([a-zA-Z0-9]+).photobucket.com/albums/([a-zA-Z0-9]+)/([a-zA-Z0-9\.\,\:\;\@\-\_\+\ ]+)/([a-zA-Z0-9\.\,\:\;\@\-\_\+\ ]*).*#',
                'url'   => 'http://$1.photobucket.com/albums/$2/$3/$4',
                'type'  => 'photobucket',
                'var1' => '$3',
                'var2' => '$4',
            ),
            // Windows Live Photo Gallery (MUST be a direct link to one of the photos in the album!)
            // This is a hack - in order to show photos from the album, we require a direct link to one of the photos.
            array(
                'match' => '#.*cid-([a-zA-Z0-9]+).photos.live.com/self.aspx/([a-zA-Z0-9\.\,\:\;\@\-\_\+\%\ ]+)/([a-zA-Z0-9\,\:\;\@\-\_\+\%\ ]+).(gif|png|jpg|jpeg)*#',
                'url'   => 'http://cid-$1.photos.live.com/self.aspx/$2/$3.$4',
                'type'  => 'windowslive',
                'var1' => 'cid-$1',
                'var2' => '$2',
            ),
        );

        foreach ($embedsources as $source) {
            $url = htmlspecialchars_decode($url); // convert &amp; back to &, etc.
            if (preg_match($source['match'], $url)) {
                $images_url = preg_replace($source['match'], $source['url'], $url);
                $images_type = $source['type'];
                $images_var1 = preg_replace($source['match'], $source['var1'], $url);
                $images_var2 = preg_replace($source['match'], $source['var2'], $url);
                return array('url' => $images_url, 'type' => $images_type, 'var1' => $images_var1, 'var2' => $images_var2);
            }
        }
        return array();
    }

    /**
     * Returns a block of HTML that the Gallery block can use to list
     * which external galleries or photo services are supported.
     */
    private static function get_supported_external_galleries() {
        $smarty = smarty_core();
        $smarty->assign('wwwroot', get_config('wwwroot'));
        if (is_https() === true) {
            $smarty->assign('protocol', 'https');
        }
        else {
            $smarty->assign('protocol', 'http');
        }
        return $smarty->fetch('blocktype:gallery:supported.tpl');
    }

    // Function to find nearest value (in array of values) to given value
    // e.g.: user defined thumbnail width is 75, abvaliable picasa thumbnails are array(32, 48, 64, 72, 104, 144, 150, 160)
    //         so this function should return 72 (which is nearest form available values)
    // Function found at http://www.sitepoint.com/forums/showthread.php?t=537541
    public static function find_nearest($values, $item) {
        if (in_array($item,$values)) {
            $out = $item;
        }
        else {
            sort($values);
            $length = count($values);
            for ($i=0; $i<$length; $i++) {
                if ($values[$i] > $item) {
                    if ($i == 0) {
                        return $values[$i];
                    }
                    $out = ($item - $values[$i-1]) > ($values[$i]-$item) ? $values[$i] : $values[$i-1];
                    break;
                }
            }
        }
        if (!isset($out)) {
            $out = end($values);
        }
        return $out;
    }

    public static function artefactchooser_element($default=null) {
    }

    public static function default_copy_type() {
        return 'full';
    }

    public static function get_current_artefacts(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $artefacts = array();
        if (isset($configdata['artefactid'])) {
            safe_require('artefact', 'file');
            $artefacts = artefact_get_descendants(array(intval($configdata['artefactid'])));
        }
        return $artefacts;
    }
}
