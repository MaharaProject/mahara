/**
 * plugin.js
 *
 * Copyright, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://www.tinymce.com/license
 * Contributing: http://www.tinymce.com/contributing
 */

/*global tinymce:true */

tinymce.PluginManager.add('imagebrowser', function(editor) {

    function imageBrowserDialogue() {
        return function () {
            // open our own dialogue instead of using editor.windowManager.open
            // this enables us to use existing Mahara infrastructure for config windows more easily
            // including firing of Pieform js
            loadImageBrowser();
        }
    }

    function loadImageBrowser() {
        // Check to see if we need to add an overlay. In block edit
        // page we don't as the configure form already has overlay but
        // elsewhere we do.
        var formname = '#imgbrowserconf',
            win,
            data = {},
            dom = editor.dom,
            imgElm = editor.selection.getNode();
        jQuery('body').addClass('modal-open');
        var selected = null;

        if (imgElm.nodeName == 'FIGCAPTION') {
            // we need to find the image associated with it
            imgElm = $(imgElm).parent().find('img')[0];
        }
        if (imgElm.nodeName == 'FIGURE') {
            // we need to find the image inside it
            imgElm = $(imgElm).find('img')[0];
        }

        if (imgElm.nodeName == 'IMG' && !imgElm.getAttribute('data-mce-object') && !imgElm.getAttribute('data-mce-placeholder')) {
            // existing values
            var urlquerystr = dom.getAttrib(imgElm, 'src').match(/\?.+/);
            if (urlquerystr) {
                urlquerystr = urlquerystr[0];
                var urlparts = urlquerystr.split('&');
                for (var x in urlparts) {
                    if (urlparts[x].match('file=')) {
                        selected = urlparts[x].split('=')[1];
                    }
                }
            }
            data = {
                src: dom.getAttrib(imgElm, 'src'),
                alt: dom.getAttrib(imgElm, 'alt'),
                width: dom.getAttrib(imgElm, 'width'),
                height: dom.getAttrib(imgElm, 'height'),
            };
        } else {
            imgElm = null;
        }

        // Parse styles from img
        if (imgElm) {
            data.hspace = removePixelSuffix(imgElm.style.marginLeft || imgElm.style.marginRight);
            data.vspace = removePixelSuffix(imgElm.style.marginTop || imgElm.style.marginBottom);
            data.border = removePixelSuffix(imgElm.style.borderWidth);
            var vertalign = imgElm.style.verticalAlign;
            var floatalign = imgElm.style.float;
            data.align = (floatalign.length) ? floatalign : vertalign;
            data.align = (data.align.length) ? data.align : 'none';
            data.style = editor.dom.serializeStyle(editor.dom.parseStyle(editor.dom.getAttrib(imgElm, 'style')));
            data.showcaption = hasCaption(imgElm);
        } else {
            data.width = data.height = data.hspace = data.vspace = data.border = data.align = data.style = data.showcaption = '';
        }

        // are we in a view, somewhere in a group, etc? (forum topic, forum or group page, blog, blog post, etc.?)
        // this determines selected tab for file browser
        // TODO find a better way than scraping the page like this
        var viewid = 0;
        if (jQuery('#viewid').length) {
            viewid = jQuery('#viewid').val()
        }
        var blogpostid = 0;
        if (jQuery('#editpost_blogpost').length) {
            blogpostid = jQuery('#editpost_blogpost').val()
        }
        var blogid = 0;
        if (jQuery('#editpost_blog').length) {
            blogid = jQuery('#editpost_blog').val()
        }
        var postid = 0;
        if (jQuery('#edittopic_post').length) {
            postid = jQuery('#edittopic_post').val()
        }
        var group = 0;
        if (jQuery('#newblog_group').length) {
            group = jQuery('#newblog_group').val();
        }
        else if (jQuery('#editblog_group').length) {
            group = jQuery('#editblog_group').val();
        }
        else if (jQuery('#edit_interaction_group').length) {
            group = jQuery('#edit_interaction_group').val();
        }
        else if (jQuery('input[name="group"]').length) {
            group = jQuery('input[name="group"]').val();
        }
        var institution = 0;
        if (jQuery('#newblog_institution').length) {
            institution = jQuery('#newblog_institution').val();
        }
        if (jQuery('#editblog_institution').length) {
            institution = jQuery('#editblog_institution').val();
        }
        var pd = {'view': viewid,
                  'post': postid,
                  'blogid': blogid,
                  'blogpostid': blogpostid,
                  'group': group,
                  'institution': institution,
                  'selected': selected,
                  'change': 1};

        sendjsonrequest(config['wwwroot'] + 'json/imagebrowser.json.php', pd, 'POST', function(ibdata) {
            addImageBrowser(ibdata);
            // fill url field and the selected image's title in the heading of the 'Image' expander
            jQuery(formname + '_width').val(data.width);
            jQuery(formname + '_url').val(data.src);
            jQuery(formname + '_style').val(data.style);
            jQuery(formname + '_align').val(data.align);
            jQuery(formname + '_hspace').val(data.hspace);
            jQuery(formname + '_vspace').val(data.vspace);
            jQuery(formname + '_border').val(data.border);
            jQuery(formname + '_showcaption').prop('checked', data.showcaption);
            if (selected) {
                jQuery(formname + '_artefactfieldset_container').find('.collapse-indicator').before('<span class="text-small text-midtone file-name"> - ' + getSelectedObject().title + '</span>');
            }
        });

        function addImageBrowser(configblock) {
            var browser = jQuery(configblock.data.html);


            jQuery('body').append(browser);
            win = jQuery('#imagebrowser');

            jQuery(formname).on('submit', function( event ) {
                event.preventDefault();
                onSubmitForm();
            });

            jQuery('#filebrowserupdatetarget').on("click", function() {
                // update dimensions on change of selection
                srcChange(getSelectedImageUrl());
            });

            // Formatting options toggle
            jQuery('#imgbrowserconf_toggleformatting_container').on('click', function(event) {
                jQuery('#imgbrowserconf_formattingoptions_container').toggleClass('js-hidden');
                jQuery('#formattingoptionstoggle').toggleClass('retracted');
            });

            jQuery(formname + '_align, ' + formname + '_hspace, ' + formname + '_vspace, ' + formname + '_border').on('change', function() {
                updateStyle();
            });

            var cancelbutton = jQuery(browser).find('#cancel_imgbrowserconf_action_submitimage');
            if (cancelbutton.length) {
                cancelbutton.off('click');
                cancelbutton.on('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();

                    removeImageBrowser();
                });
            }

            var deletebutton = jQuery(browser).find('.deletebutton.btn-close');
            if (deletebutton.length) {
                deletebutton.off('click');
                deletebutton.on('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();

                    removeImageBrowser();
                });
            }

            jQuery(browser).removeClass('d-none');

            // execute additional js for the config block
            eval(configblock.data.javascript);

            if (deletebutton.length) {
                deletebutton.trigger('focus');
            }

            // As we have several submit buttons in the form
            // Add the attribute clicked=true to the clicked button
            // This will help identify which submit button was clicked
            jQuery('form' + formname + ' input[type=submit], button[type=submit]').on('click', function() {
                jQuery("input[type=submit], button[type=submit]", jQuery(this).parents('form' + formname)).removeAttr("clicked");
                // Add the submit button name/value as a hidden field to get this to work in FF
                if (jQuery('#edit_file').length) {
                    jQuery('#edit_file').prop('name', jQuery(this)[0].name).prop('value', jQuery(this)[0].value);
                }
                else {
                    jQuery('<input>').attr({
                        type: 'hidden',
                        id: 'edit_file',
                        name: jQuery(this)[0].name,
                        value: jQuery(this)[0].value
                    }).appendTo(jQuery(this).parents('form' + formname));
                }
                jQuery(this).attr("clicked", "true");
            });

        } // end of addImageBrowser()

        function getSelectedImageUrl() {
            var selected = window.imgbrowserconf_artefactid.selecteddata;
            var url;
            for (var a in selected) {
                if (selected[a].artefacttype == 'image' || selected[a].artefacttype == 'profileicon') {
                    url = config.wwwroot + 'artefact/file/download.php?file=' + selected[a].id + "&embedded=1";
                }
            }
            return url;
        }

        function getSelectedObject() {
            // As we can only select one image at a time we can accept the first in the array as selected item
            var keys = Object.keys(window.imgbrowserconf_artefactid.selecteddata);
            var selected = window.imgbrowserconf_artefactid.selecteddata[keys[0]];
            if (selected) {
                return selected;
            }
            return null;
        }

        function onSubmitForm(e) {
            // Find which submit button was clicked
            var clickedButton = jQuery('form' + formname + " input[type=submit][clicked=true]");
            if ((clickedButton.length > 0)
                && ('#' + clickedButton[0].id == formname + '_artefactid_edit_artefact')) {
                var fileBrowserForm = window["imgbrowserconf_artefactid"];
                if (fileBrowserForm) {
                    fileBrowserForm.submitform();
                }
            }
            else {
                function waitLoad(imgElm) {
                    function selectImage() {
                        imgElm.onload = imgElm.onerror = null;
                        editor.selection.select(imgElm);
                        editor.nodeChanged();
                    }

                    imgElm.onload = function() {
                        if (!data.width && !data.height) {
                            dom.setAttribs(imgElm, {
                                width: imgElm.clientWidth,
                                height: imgElm.clientHeight
                            });
                        }
                        selectImage();
                    };

                    imgElm.onerror = selectImage;
                }

                updateStyle();

                var data = getFormVals();

                if (data.width === '') {
                    data.width = null;
                }

                if (data.height === '') {
                    data.height = null;
                }

                if (data.style === '') {
                    data.style = null;
                }

                var description = null;

                var selected = getSelectedObject();
                if (selected) {
                    if (!selected.isdecorative) {
                        description = selected.description;
                        if (selected.alttext) {
                            data.alt = selected.alttext;
                        }
                        else if (selected.description) {
                            data.alt = selected.description;
                        }
                    }
                    else {
                        data.alt = '';
                    }
                }

                data = {
                    src: data.src,
                    alt: data.alt,
                    width: data.width,
                    height: data.height,
                    style: data.style,
                    showcaption: data.showcaption && !selected.isdecorative
                };

                editor.undoManager.transact(function() {
                    if (!data.src) {
                        if (imgElm) {
                            dom.remove(imgElm);
                            editor.nodeChanged();
                        }
                        return;
                    }

                    if (!imgElm) {
                        data.id = '__mcenew';
                        editor.focus();
                        editor.selection.setContent(dom.createHTML('img', data));
                        imgElm = dom.get('__mcenew');
                        dom.setAttrib(imgElm, 'id', null);
                        if (data.showcaption) {
                            wrapInFigure(imgElm, description);
                        }
                    } else {
                        dom.setAttribs(imgElm, data);
                        if (data.showcaption) {
                            if (hasCaption(imgElm)) {
                                removeFigure(imgElm);
                            }
                            wrapInFigure(imgElm, description);
                        }
                        else {
                            if (hasCaption(imgElm)) {
                                removeFigure(imgElm);
                            }
                        }
                    }

                    waitLoad(imgElm);
                });
                if (jQuery('#configureblock').length) {
                    jQuery('#configureblock').removeClass('d-none');
                }
                removeImageBrowser();
            }
        } // end onSubmitForm

        function hasCaption(image) {
            return image.parentNode !== null && image.parentNode.nodeName === 'FIGURE';
        }

        function wrapInFigure(image, description) {
            var caption = description;
            if (caption) {
                var figureElm = dom.create('figure', { class: 'figure image' });
                dom.insertAfter(figureElm, image);
                figureElm.appendChild(image);
                figureElm.appendChild(dom.create('figcaption', { contentEditable: 'true', class: 'figure-caption' }, caption));
                figureElm.contentEditable = 'false';
            }
        }

        function removeFigure(image) {
            var figureElm = image.parentNode;
            dom.insertAfter(image, figureElm);
            dom.remove(figureElm);
        }

        function removeImageBrowser() {
            setTimeout(function() {
                jQuery('body').removeClass('modal-open');
                jQuery('#imagebrowser div.configure').each( function() {
                    jQuery(this).addClass('d-none');
                });
                jQuery('#imagebrowser').remove();
            }, 1);
            processingStop();
        }

        function getFormVals() {
            data = {
                    src: jQuery(formname + '_url').val(),
                    width: jQuery(formname + '_width').val(),
                    style: jQuery(formname + '_style').val(),
                    hspace: jQuery(formname + '_hspace').val(),
                    vspace: jQuery(formname + '_vspace').val(),
                    border: jQuery(formname + '_border').val(),
                    align: jQuery(formname + '_align').val(),
                    showcaption: jQuery(formname + '_showcaption').prop("checked"),
                };
            return data;
        }

        function removePixelSuffix(value) {
            if (value) {
                value = value.replace(/px$/, '');
            }
            return value;
        }

        function srcChange(imgurl) {
            getImageSize(imgurl, function(data) {
                if (data.width && data.height) {
                    width = data.width;
                    height = data.height;

                    win.find(formname + '_width').val(width);
                    win.find(formname + '_height').val(height);
                }
            });
            // fill url field
            win.find(formname + '_url').val(imgurl);
        }

        function getImageSize(url, callback) {
            // create dom element in order to get dimensions
            // remove it when done
            var img = document.createElement('img');

            function done(width, height) {
                if (img.parentNode) {
                    img.parentNode.removeChild(img);
                }

                callback({width: width, height: height});
            }

            img.onload = function() {
                done(img.clientWidth, img.clientHeight);
            };

            img.onerror = function() {
                done();
            };

            var style = img.style;
            style.visibility = 'hidden';
            style.position = 'fixed';
            style.bottom = style.left = 0;
            style.width = style.height = 'auto';

            document.body.appendChild(img);
            img.src = url;
        }

        function updateStyle() {
            function addPixelSuffix(value) {
                if (value.length > 0 && /^[0-9]+$/.test(value)) {
                    value += 'px';
                }
                return value;
            }

            var data = getFormVals();
            var css = dom.parseStyle(data.style);

            delete css.margin;
            css['margin-top'] = css['margin-bottom'] = addPixelSuffix(data.vspace);
            css['margin-left'] = css['margin-right'] = addPixelSuffix(data.hspace);
            css['border-width'] = addPixelSuffix(data.border);
            switch(data.align)
                            {
                            case 'left':
                                css['float'] = 'left';
                                css['vertical-align'] = '';
                               break;
                            case 'right':
                                css['float'] = 'right';
                                css['vertical-align'] = '';
                              break;
                            case 'top':
                                css['vertical-align'] = 'top';
                                css['float'] = '';
                              break;
                            case 'bottom':
                                css['vertical-align'] = 'bottom';
                                css['float'] = '';
                              break;
                            case 'middle':
                                css['vertical-align'] = 'middle';
                                css['float'] = '';
                              break;
                            default:
                                css['vertical-align'] = '';
                                css['float'] = '';
                            }

            win.find(formname +'_style').val(dom.serializeStyle(dom.parseStyle(dom.serializeStyle(css))));
        }
    } // end of loadImageBrowser

    var hasImageClass = function (node) {
        var className = node.attr('class');
        return className && /\bimage\b/.test(className);
    };

    var toggleContentEditableState = function (state) {
        return function (nodes) {
            var i = nodes.length;
            var toggleContentEditable = function (node) {
                node.attr('contenteditable', state ? 'true' : null);
            };
            while (i--) {
                var node = nodes[i];
                if (hasImageClass(node)) {
                    node.attr('contenteditable', state ? 'false' : null);
                    tinymce.util.Tools.resolve('tinymce.util.Tools').each(node.getAll('figcaption'), toggleContentEditable);
                }
            }
        };
    };

    editor.on('PreInit', function() {
        editor.parser.addNodeFilter('figure', toggleContentEditableState(true));
        editor.serializer.addNodeFilter('figure', toggleContentEditableState(false));
    });

    editor.ui.registry.addButton('imagebrowser', {
        icon: 'image',
        tooltip: 'Insert/edit image',
        onAction: imageBrowserDialogue(),
        stateSelector: 'img:not([data-mce-object],[data-mce-placeholder])'
    });

    editor.ui.registry.addMenuItem('imagebrowser', {
        icon: 'image',
        text: 'Insert image',
        onclick: imageBrowserDialogue(),
        context: 'insert',
        prependToContext: true
    });
});
