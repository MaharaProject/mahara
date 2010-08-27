/**
 * TinyMCE plugin to provide a popup for inserting an image that has
 * been uploaded or attached to a blog post
 * @source: http://gitorious.org/mahara/mahara
 *
 * @licstart
 * Copyright (C) 2006-2010  Catalyst IT Ltd
 *
 * The JavaScript code in this page is free software: you can
 * redistribute it and/or modify it under the terms of the GNU
 * General Public License (GNU GPL) as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option)
 * any later version.  The code is distributed WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU GPL for more details.
 *
 * As additional permission under GNU GPL version 3 section 7, you
 * may distribute non-source (e.g., minimized or compacted) forms of
 * that code without the copy of the GNU GPL normally required by
 * section 4, provided you include this license notice and a URL
 * through which recipients can access the Corresponding Source.
 * @licend
 */

var ImageDialog = {
    preInit : function() {
        var url;
        tinyMCEPopup.requireLangPack();
    },

    init : function() {
        var f = document.forms[0], ed = tinyMCEPopup.editor;

        e = ed.selection.getNode();

        if (e.nodeName == 'IMG') {
            f.src.value = ed.dom.getAttrib(e, 'src');
            f.alt.value = ed.dom.getAttrib(e, 'alt');
            f.border.value = this.getAttrib(e, 'border');
            f.vspace.value = this.getAttrib(e, 'vspace');
            f.hspace.value = this.getAttrib(e, 'hspace');
            f.width.value = ed.dom.getAttrib(e, 'width');
            f.height.value = ed.dom.getAttrib(e, 'height');
            f.insert.value = ed.getLang('update');
            this.styleVal = ed.dom.getAttrib(e, 'style');
            //selectByValue(f, 'image_list', f.src.value);
            selectByValue(f, 'align', this.getAttrib(e, 'align'));
            this.updateStyle();
        }

        // Get image list from calling window
        document.getElementById('image_list_container').innerHTML = this.imageSelectorHTML(f.src.value);

        // Check if the image attached
        if (e.nodeName == 'IMG' && f.image_list.selectedIndex == 0) {
            f.img_src.value = f.src.value;
        }
        // Refresh
        this.getImageData(f.image_list.options[f.image_list.selectedIndex].value);
    },

    imageSelectorHTML : function(src) {
        var imageid = tinyMCEPopup.getWin().imageIdFromSrc(src);
        var imagefiles = tinyMCEPopup.getWin().imageList;
        var disabled = '';

        if (imagefiles.length == 0) {
            disabled = 'disabled';
        }

        var sel = '<select class="select" name="image_list" id="image_list" ' + disabled + ' onchange="this.form.src.value=this.options[this.selectedIndex].value;ImageDialog.resetImageData();ImageDialog.getImageData(this.form.src.value);">';
        sel += '<option value="">--</option>';
        for (var i = 0; i < imagefiles.length; i++) {
            sel += '<option value="' + imagefiles[i].id + '" title="' + imagefiles[i].description + '"';
            if (imageid == imagefiles[i].id) {
                sel += ' selected';
            }
            sel += '>' + imagefiles[i].name + '</option>';
        }
        return sel;

    },

    update : function() {
        var f = document.forms[0], nl = f.elements, ed = tinyMCEPopup.editor, args = {}, el;

        tinyMCEPopup.restoreSelection();

        if (f.src.value === '') {
            if (ed.selection.getNode().nodeName == 'IMG') {
                ed.dom.remove(ed.selection.getNode());
                ed.execCommand('mceRepaint');
            }

            tinyMCEPopup.close();
            return;
        }

        if (!ed.settings.inline_styles) {
            args = tinymce.extend(args, {
                vspace : nl.vspace.value,
                hspace : nl.hspace.value,
                border : nl.border.value,
                align : getSelectValue(f, 'align')
            });
        } else {
            this.updateStyle();
            args.style = this.styleVal;
        }

        tinymce.extend(args, {
            src : f.src.value,
            alt : f.alt.value,
            width : f.width.value,
            height : f.height.value
        });

        el = ed.selection.getNode();

        if (el && el.nodeName == 'IMG') {
            ed.dom.setAttribs(el, args);
        } else {
            ed.execCommand('mceInsertContent', false, '<img id="__mce_tmp" />', {skip_undo : 1});
            ed.dom.setAttribs('__mce_tmp', args);
            ed.dom.setAttrib('__mce_tmp', 'id', '');
            ed.undoManager.add();
        }

        tinyMCEPopup.close();
    },

    updateStyle : function() {
        var dom = tinyMCEPopup.dom, st, v, f = document.forms[0];

        if (tinyMCEPopup.editor.settings.inline_styles) {
            st = tinyMCEPopup.dom.parseStyle(this.styleVal);

            // Handle align
            v = getSelectValue(f, 'align');
            if (v) {
                if (v == 'left' || v == 'right') {
                    st['float'] = v;
                    delete st['vertical-align'];
                } else {
                    st['vertical-align'] = v;
                    delete st['float'];
                }
            } else {
                delete st['float'];
                delete st['vertical-align'];
            }

            // Handle border
            v = f.border.value;
            if (v || v == '0') {
                if (v == '0')
                    st['border'] = '0';
                else
                    st['border'] = v + 'px solid black';
            } else
                delete st['border'];

            // Handle hspace
            v = f.hspace.value;
            if (v) {
                delete st['margin'];
                st['margin-left'] = v + 'px';
                st['margin-right'] = v + 'px';
            } else {
                delete st['margin-left'];
                delete st['margin-right'];
            }

            // Handle vspace
            v = f.vspace.value;
            if (v) {
                delete st['margin'];
                st['margin-top'] = v + 'px';
                st['margin-bottom'] = v + 'px';
            } else {
                delete st['margin-top'];
                delete st['margin-bottom'];
            }

            // Merge
            st = tinyMCEPopup.dom.parseStyle(dom.serializeStyle(st));
            this.styleVal = dom.serializeStyle(st);
        }
    },

    getAttrib : function(e, at) {
        var ed = tinyMCEPopup.editor, dom = ed.dom, v, v2;

        if (ed.settings.inline_styles) {
            switch (at) {
                case 'align':
                    if (v = dom.getStyle(e, 'float'))
                        return v;

                    if (v = dom.getStyle(e, 'vertical-align'))
                        return v;

                    break;

                case 'hspace':
                    v = dom.getStyle(e, 'margin-left')
                    v2 = dom.getStyle(e, 'margin-right');
                    if (v && v == v2)
                        return parseInt(v.replace(/[^0-9]/g, ''));

                    break;

                case 'vspace':
                    v = dom.getStyle(e, 'margin-top')
                    v2 = dom.getStyle(e, 'margin-bottom');
                    if (v && v == v2)
                        return parseInt(v.replace(/[^0-9]/g, ''));

                    break;

                case 'border':
                    v = 0;

                    tinymce.each(['top', 'right', 'bottom', 'left'], function(sv) {
                        sv = dom.getStyle(e, 'border-' + sv + '-width');

                        // False or not the same as prev
                        if (!sv || (sv != v && v !== 0)) {
                            v = 0;
                            return false;
                        }

                        if (sv)
                            v = sv;
                    });

                    if (v)
                        return parseInt(v.replace(/[^0-9]/g, ''));

                    break;
            }
        }

        if (v = dom.getAttrib(e, at))
            return v;

        return '';
    },

    resetImageData : function() {
        var f = document.forms[0];

        f.width.value = f.height.value = f.alt.value = "";
    },

    updateImageData : function() {
        var f = document.forms[0], t = ImageDialog;

        if (f.width.value == "")
            f.width.value = t.preloadImg.width;

        if (f.height.value == "")
            f.height.value = t.preloadImg.height;
    },

    getImageData : function(imageid) {
        var f = document.forms[0];
        var imgsrc = '';

        this.preloadImg = new Image();
        this.preloadImg.onload = this.updateImageData;
        this.preloadImg.onerror = this.resetImageData;

        if (imageid) {
            // Image list
            imgsrc = tinyMCEPopup.getWin().imageSrcFromId(imageid);
            f.src.value = imgsrc;
            // Use discription of attached image if possible, but preserve if it was changed.
            if (f.image_list.options[f.image_list.selectedIndex].title && !f.alt.value.length) {
                f.alt.value = f.image_list.options[f.image_list.selectedIndex].title;
            }
            else if (f.image_list.selectedIndex && !f.alt.value.length) {
                f.alt.value = f.image_list.options[f.image_list.selectedIndex].childNodes[0].nodeValue;
            }
            // Disable img_src inputbox
            f.img_src.disabled = true;
        }
        else {
            // Image URL
            f.img_src.disabled = false;
            f.image_list.disabled = (f.img_src.value.length || f.image_list.options.length == 1) ? true : false;
            imgsrc = f.src.value;
        }

        this.preloadImg.src = imgsrc;
    }

};

ImageDialog.preInit();
tinyMCEPopup.onInit.add(ImageDialog.init, ImageDialog);





