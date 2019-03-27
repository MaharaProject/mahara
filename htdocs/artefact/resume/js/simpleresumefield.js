/*jslint browser: true, nomen: true,  white: true */
/* global jQuery, $ */
var simpleresume = {};
jQuery(function($) {
    "use strict";

    simpleresume.connect_editbuttons = function() {
        $("#resumefieldform input.openedit").on("click", function() {
            //takes id and removes the word 'edit' from the end...
            var t = this.id.substr(0, this.id.length - 4),
                container = $("#" + t + "_container"),
                submitcontainer =  $("#" + t + "submit_container"),
                submit = $("#" + t + "submit"),
                cancel = $("#cancel_" + t + "submit"),
                displaycontainer = $("#" + t + "display_container"),
                editcontainer = $("#" + t + "edit_container");

            container.removeClass("js-hidden");
            submitcontainer.removeClass("js-hidden");
            submit.removeClass("js-hidden");
            cancel.removeClass("js-hidden");

            displaycontainer.addClass("d-none").removeClass("nojs-hidden-block");
            editcontainer.addClass("d-none").removeClass("nojs-hidden-block");



            if (typeof tinyMCE != 'undefined') {
                var editor = tinyMCE.get(t),
                formTop =  container.closest('#main-column-container').attr('id');
                $('.mce-toolbar.mce-first').siblings().toggleClass('d-none');
                editor.show();
                editor.focus();
                document.location.href = "#" + formTop;
            }
            else {
                $("#" + t).removeClass("js-hidden").trigger("focus");
            }
        });
    };

    simpleresume.connect_cancelbuttons = function() {
        $("#resumefieldform input.submitcancel.cancel").on("click", function(e) {
            e.preventDefault();
             //takes id and removes the word 'cancel' from the end...
            var t = this.id.substr(7, this.id.length - 7 - 6),
                container = $("#" + t + "_container"),
                submitcontainer =  $("#" + t + "submit_container"),
                submit = $("#" + t + "submit"),
                cancel = $("#cancel_" + t + "submit"),
                displaycontainer = $("#" + t + "display_container"),
                editcontainer = $("#" + t + "edit_container");


            container.addClass("js-hidden");
            submitcontainer.addClass("js-hidden");
            submit.addClass("js-hidden");
            cancel.addClass("js-hidden");
            displaycontainer.removeClass("d-none");
            editcontainer.removeClass("d-none");
            if (typeof tinyMCE != 'undefined') {
                // Clear any cancelled content back to original
                var ed = tinyMCE.get(t);
                ed.setContent($('#' + t).text());
                ed.hide();
            }
            else {
                $("#" + t).addClass("js-hidden");
            }
            $("#" + t + "edit_container").find('input.openedit').trigger("focus");
        });
    };

    simpleresume.simple_resumefield_init = function() {
        this.connect_editbuttons();
        this.connect_cancelbuttons();

        var ids = [];
        $("#resumefieldform input.submitcancel.cancel").each(function() {
            var prefix = 'cancel_';
            var suffix = 'submit';
            ids.push(this.id.substr(prefix.length, this.id.length - prefix.length - suffix.length));
        });
        if (typeof(tinyMCE) != 'undefined') {
            tinyMCE.EditorManager.on('SetupEditor', function(editor) {
                if (ids.indexOf(editor.id) >= 0) {
                    editor.on('init', function() {
                        editor.hide();
                    });
                }
            });
        }
    };

     simpleresume.simple_resumefield_init();
});


function simple_resumefield_success(form, data) {
    var displaynode = jQuery("#resumefieldform_" + data.update + "display_container");
    displaynode.html(data.content);
    simpleresume.simple_resumefield_init();
    formSuccess(form, data);
}

function simple_resumefield_error(form, data) {
   simpleresume.simple_resumefield_init();
    var errornodeid = jQuery("#resumefieldform textarea.error.wysiwyg").attr("id");
    if (errornodeid) {
        var editbutton = jQuery("input#" + errornodeid + "edit");
        if (editbutton) {
            editbutton.trigger("click");
        }
    }
}
