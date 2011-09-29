function simple_resumefield_success(form, data) {
    var displaynode = $j("#resumefieldform_" + data.update + "display_container td");
    displaynode.html(data.content);
    simple_resumefield_init();
    formSuccess(form, data);
}

function simple_resumefield_error(form, data) {
    simple_resumefield_init();
    var errornodeid = $j("#resumefieldform textarea.error.wysiwyg").attr("id");
    if (errornodeid) {
        var editbutton = $j("input#" + errornodeid + "edit");
        if (editbutton) {
            editbutton.click();
        }
    }
}

function connect_editbuttons() {
    $j("#resumefieldform input.openedit").click(function() {
        var t = this.id.substr(0, this.id.length - 4);
        $j("#" + t + "_container").removeClass("js-hidden");
        $j("#" + t + "submit_container").removeClass("js-hidden");
        $j("#" + t + "submit").removeClass("js-hidden");
        $j("#cancel_" + t + "submit").removeClass("js-hidden");
        $j("#" + t + "display_container").addClass("hidden");
        $j("#" + t + "display_container").removeClass("nojs-hidden-block");
        $j("#" + t + "edit_container").addClass("hidden");
        $j("#" + t + "edit_container").removeClass("nojs-hidden-block");
        tinyMCE.get(t).show();
    });
}

function connect_cancelbuttons() {
    $j("#resumefieldform input.submitcancel.cancel").click(function(e) {
        e.preventDefault();
        var t = this.id.substr(7, this.id.length - 7 - 6);
        $j("#" + t + "_container").addClass("js-hidden");
        $j("#" + t + "submit_container").addClass("js-hidden");
        $j("#" + t + "submit").addClass("js-hidden");
        $j("#cancel_" + t + "submit").addClass("js-hidden");
        $j("#" + t + "display_container").removeClass("hidden");
        $j("#" + t + "edit_container").removeClass("hidden");
        tinyMCE.get(t).hide();
    });
}

function simple_resumefield_init() {
    connect_editbuttons();
    connect_cancelbuttons();
}
