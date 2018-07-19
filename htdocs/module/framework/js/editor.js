jQuery(function($) {
    //use bootstrap
    //@TODO - change to bootstrap4
    JSONEditor.defaults.options.theme = 'bootstrap4';
    //@TODO this is supposed to enable fontawesome icons, but currently it breaks everything
    //JSONEditor.defaults.options.iconlib = "fontawesome4";

    //Remove edit json buttons. The functionality creates a form based on the submitted json, without
    //calling the custom code on this page.
    //@TODO remove override code for Edit json button text, since we're not using it.
    //@TODO turn edit overall form option into an export json button and have it create a file.matrix with
    //it for sharing.
    //re-name the button and override the functionality, then put it back in some form
    JSONEditor.defaults.options.disable_edit_json = 'true';

    //override default editor strings to allow translation by us
    //original editor defaults in htdocs/js/jsoneditor/src/defaults.js
    //Not all editor strings are included here, just the ones we are using.
    JSONEditor.defaults.languages.en.button_collapse = get_string('collapse');
    JSONEditor.defaults.languages.en.button_expand = get_string('expand');
    JSONEditor.defaults.languages.en.button_add_row_title = get_string('add');
    JSONEditor.defaults.languages.en.button_delete_last_title = get_string('deletelast') + " {{0}}";
    JSONEditor.defaults.languages.en.button_move_down_title = get_string('moveright');//Move right
    JSONEditor.defaults.languages.en.button_move_up_title = get_string('moveleft');
    JSONEditor.defaults.languages.en.button_delete_all_title = get_string('deleteall');

    //allow select dropdown
    JSONEditor.plugins.select2.enable = true;


    var editor;
    refresh_editor();

    // Initialize the editor
function refresh_editor() {
    editor = new JSONEditor(document.getElementById('editor_holder'),
    {
    //json-editor properties
    ajax: true,
    disable_properties : true,
    show_errors: "always",
    // The schema for the editor, info on https://github.com/json-editor/json-editor
    schema: {
        "title": get_string('Framework'),
        "type": "object",
        "properties": {
            "institution": {
                "type" : "string",
                "title" : get_string('institution'),
                "description" : get_string('instdescription'),
                "id" : "inst_desc",
                "enum" : inst_names.split(','),
                "default" : get_string('all')
            },
            "name": {
                "type" : "string",
                "title" : get_string('name'),
                "description": get_string('titledesc'),
                "default" : get_string('frameworktitle'),
            },
            "description" : {
                "type" : "string",
                "title" : get_string('description'),
                "format" : "textarea",
                "default" : get_string('defaultdescription'),
                "description" : get_string('descriptioninfo')
            },
            "selfassess" : {
                "type" : "boolean",
                "title" : get_string('selfassessed'),
                "description" : get_string('selfassesseddescription'),
                "default" : false,
                "options" : {
                    "enum_titles" : [get_string('yes'), get_string('no')]
                }
            },
            "evidencestatuses":{
            "title": get_string('evidencestatuses'),
            "id" : "evidencestatuses",
            "type" : "object",
            "options" : {
                "disable_array_reorder" : true,
                "disable_edit_json" : true,
                "disable_collapse" : true
            },
            "description": get_string('evidencedesc'),
            "properties": {
                "begun": {
                    "title" : get_string('Begun'),
                    "type" : "string",
                    "default" : get_string('begun'),
                    "propertyOrder" : 1
                },
                "incomplete": {
                    "title" : get_string('Incomplete'),
                    "type" : "string",
                    "default" : get_string('incomplete'),
                    "propertyOrder" : 2
                },
                "partialcomplete": {
                    "title" : get_string('Partialcomplete'),
                    "type" : "string",
                    "default" : get_string('partialcomplete'),
                    "propertyOrder" : 3
                },
                "completed": {
                    "title" : get_string('Completed'),
                    "type" : "string",
                    "default" : get_string('completed'),
                    "propertyOrder" : 4
                }
            }
            },
            "standards" : {
                "title" : get_string('standards'),
                "type" : "array",
                "id" : "standards",
                "format" : "tabs-top",
                "minItems":1,
                "description" : get_string('standardsdescription'),
                "items" : {
                    "title" : get_string('standard'),
                    "headerTemplate" : "{{i1}} - {{self.shortname}}",
                    "type" : "object",
                    "id" : "standard",
                    "options" : {
                        "disable_collapse" : true
                    },
                    "properties" : {
                        "shortname" : {
                            "type" : "string",
                            "title" : get_string('Shortname'),
                            "description" : get_string('shortnamestandard'),
                            "default" : get_string('Shortname'),
                            "maxLength" : 100
                        },
                        "name" : {
                            "type" : "string",
                            "title" : get_string('name'),
                            "description" : get_string('titlestandard'),
                            "format" : "textarea",
                            "maxLength" : 255
                        },
                        "description" : {
                            "type" : "string",
                            "title" : get_string('description'),
                            "format" : "textarea",
                            "default" : get_string('descstandarddefault'),
                            "description" : get_string('descstandard')
                        },
                        "standardid" : {
                            "type" : "number",
                            "title" : get_string('standardid'),
                            "default" : "1",
                            "description" : get_string('standardiddesc')
                        },
                        "uid" : {
                            "type" : "number",
                            "default" : null,
                            "options" : {
                                "hidden" : true
                            }
                        }
                    }
                }
            },
            "standardelements" : {
                "title" : get_string('standardelements'),
                "id" : "standardelements",
                "type" : "array",
                "uniqueItems" : true,
                "minItems":1,
                "format" : "tabs-top",
                "description" : get_string('standardelementsdescription', 'module.framework'),
                "items" : {
                    "title" : get_string('standardelement'),
                    "headerTemplate" : "{{self.elementid}}",
                    "type" : "object",
                    "options" : {
                        "disable_collapse" : true
                    },
                    "properties" : {
                        "shortname" : {
                            "type" : "string",
                            "title" : get_string('Shortname'),
                            "description" : get_string('shortnamestandard'),
                            "maxLength" : 100
                        },
                        "name" : {
                            "type" : "string",
                            "title" : get_string('name'),
                            "description" : get_string('titlestandard'),
                            "format" : "textarea",
                            "maxLength" : 255
                        },
                        "description" : {
                            "type" : "string",
                            "title" : get_string('description'),
                            "format" : "textarea",
                            "default" : get_string('standardelementdefault'),
                            "description" : get_string('standardelementdesc')
                        },
                        "elementid" : {
                            "type" : "string",
                            "title" : get_string('elementid'),
                            "default" : '1.1',
                            "description" : get_string('elementiddesc')
                        },
                        "parentelementid" : {
                            "title" : get_string('parentelementid'),
                            "type" : "string",
                            "default" : null,
                            "description" : get_string('parentelementdesc')
                        },
                        "standardid" : {
                            "title" : get_string('standardid'),
                            "type" : "number",
                            "default" : 1,
                            "description" : get_string('standardiddesc1')
                        },
                        "uid" : {
                            "type" : "number",
                            "default" : null,
                            "options" : {
                                "hidden" : true
                            }
                        }
                    }
                }
            }
        }
    },
    });
    //add ids to things so we can call them more easily later.
    $(".json-editor-btn-add").eq(2).attr("id", "add_standard");
    $(".json-editor-btn-add").eq(4).attr("id", "add_standardelement");
    //creating ids for adding wysiwyg - not currently active: @TODO
    $("div.form-group textarea.form-control").eq(0).attr("id", "title_textarea");
    $("div.form-group textarea.form-control").eq(1).attr("id", "std_textarea");
    $("div.form-group textarea.form-control").eq(2).attr("id", "std_element_textarea");
    //add class for correct styling of help block text
    $("[data-schemaid=\"standards\"] >p").addClass("help-block");
    $("[data-schemaid=\"evidencestatuses\"] >p").addClass("help-block");

    $("#add_standard").click(function() {
        standard_count += 1;
        std_index = standard_count -1;
        var sid_field = editor.getEditor("root.standards." + std_index + ".standardid");
        sid_field.setValue(standard_count);
        //set standard element fields to increment too
        //add new standard element if current one modified
        if (se_index > 0 && editor.getEditor("root.standardelements." + se_index + ".shortname")) {
            var se_field = editor.getEditor("root.standardelements");
            se_field.addRow();
            se_index ++;
        }
        var se_sid_field = editor.getEditor("root.standardelements." + se_index + ".standardid");
        se_sid_field.setValue(standard_count);
        //reset standard element count
        se_count = 1;
        var se_eid_field = editor.getEditor("root.standardelements." + se_index + ".elementid");
        se_eid_field.setValue(se_count);
        $('div.form-group textarea.form-control').on('click', function () {
            textarea_autoexpand(this);
        });
        });

    $("#add_standardelement").click(function() {
        se_count ++;
        se_index ++;
        var sid_field = editor.getEditor("root.standards." + std_index + ".standardid");
        var sid = sid_field.getValue();
        var se_sid_field = editor.getEditor("root.standardelements." + se_index + ".standardid");
        se_sid_field.setValue(sid);
        var eid_field = editor.getEditor("root.standardelements." + se_index + ".elementid");
        //@TODO: change this display to reflect the data
        eid_field.setValue(se_count);
        $('div.form-group textarea.form-control').on('click', function () {
            textarea_autoexpand(this);
        });
    });
}

    //counts to increment standard and standardelement ids
    var std_index = 0;
    var standard_count = 1;
    var se_count = 1;
    var se_index = 0;
    var fw_id = null; //framework id if editing an existing framework
    var edit = false; //flag for edit vs. copy

    var evidence_type = ['begun' ,'incomplete', 'partialcomplete', 'completed'];

    //make textarea expand with text
    $("div.form-group textarea.form-control").on('click input', function () {
        textarea_autoexpand(this);
    });

    function textarea_autoexpand(element) {
        element.setAttribute('style', 'height:' + (element.scrollHeight) + 'px;overflow-y:hidden;');
        element.style.height = 'auto';
        element.style.minHeight = '64px';
        element.style.height = (element.scrollHeight) + 'px';
    }

    //choose from edit dropdown
    $('#edit').on('change',function() {
        //rebuild the form so that data doesn't get added to existing
        //@TODO reset copy select box
        editor.destroy();
        refresh_editor();
        edit = true;
        var fw = $('#edit_framework')[0];
        var select_index = fw.children.edit.options.selectedIndex;
        var fwe = JSON.parse(fw_edit);
        //get the db index
        index = Object.keys(fwe[select_index]);
        index = index[0];
        populate_editor(index, edit);
    });

    //choose from copy dropdown.
    $("#copy").on('change', function() {
        //rebuild the form so that data doesn't get added to existing
        //@TODO reset edit select box
        editor.destroy();
        refresh_editor();
        edit = false;
        var fw = $('#copy_framework')[0];
        var select_index = fw.children.copy.options.selectedIndex;
        var fwc = JSON.parse(fws);
        index = Object.keys(fwc[select_index]);
        index = index[0];
        populate_editor(index);
        });

    // Manage button - goes to fw screen
    $(".cancel").click(function() {
        //@TODO - warn about not saving form?
        window.location.href = config['wwwroot'] + 'module/framework/frameworks.php';
    });

    //@TODO, make preview button work - should show what current framework looks like as the left
    //column of the SmartEvidence map - i.e. what you see when you look at the first page of a SE collection
    // $('#preview').click(function() {
    //I have some code saved locally that produces a lot of errors
    //so, currently I just want to hide the button:
    $('#preview').hide();

    // Hook up the submit button to log to the console
    $(".submit").click(function() {
        // Get all the form's values from the editor
        var json_form = editor.getValue();
        url = config['wwwroot'] + 'module/framework/framework.json.php';
        //if framework id is set, we are editing an existing framework
        if (fw_id) {
            json_form.fw_id = fw_id;
        }
        //save completed form data
        sendjsonrequest(url, json_form, 'POST');
        //@TODO, redirect to success message at the top of page
    });

    function populate_editor(framework_id, edit) {
        url = config['wwwroot'] + 'module/framework/getframework.json.php';
        //get data from existing framework
        sendjsonrequest(url, {'framework_id': framework_id} , 'POST', function(data) {
            if (edit) {
                fw_id = data.data.title.id;
            }
            //set the values for the first 'title' section
            $.each(data.data.title, function (k, value) {
                if (k === 'selfassess') {
                    if (value == 1) {
                        value = true;
                    }
                    else {
                        value = false;
                    }
                var ed = editor.getEditor("root." + k);
                ed.setValue(value);
                }
                var ed = editor.getEditor("root." + k);
                if (ed) {
                    if (k === 'description') {
                        $('div.form-group textarea.form-control').on('click', function () {
                            textarea_autoexpand(this);
                        });
                        ed.setValue(value)
                        //@TODO wysiwyg editing:
                        //tinyMCE will display this field correctly, but then I can't save.
                        //tinyMCE.execCommand('mceAddEditor', false, title_textarea);
                    }
                    else {
                        ed.setValue(value);
                    }
                }
            });
            //set the values for the evidence statuses
            $.each(data.data.evidencestatuses, function (k, value) {
                var type = evidence_type[value.type];
                var es = editor.getEditor("root.evidencestatuses." + type);
                es.setValue(value.name);
            });
            var std_nums = new Array();
            //set the values for the standards
            $.each(data.data.standards, function (k, value) {
                //k is standard index.
                var stg_index = k;
                //if the standard doesn't already exist, we need to add it to the editor.
                if (stg_index > 0 && !editor.getEditor("root.standards." + stg_index)) {
                    var std_ed = editor.getEditor("root.standards");
                    std_ed.addRow();
                    standard_count += 1;
                    $('div.form-group textarea.form-control').on('click', function () {
                        textarea_autoexpand(this);
                    });
                }
                //this makes an array with the 0 index empty and the db std ids matched with the index
                //of their standard number.
                var s_count = parseInt(stg_index) + 1;
                 std_nums[s_count] = value.id;

                $.each(value, function(k, val) {
                    //this works where the data field name is the same as the DOM's id
                    var field = editor.getEditor("root.standards." + stg_index + "." + k );
                    if (field) {
                        field.setValue(val);
                    }
                    //the standardid is called priority in the db
                    if (k === "priority") {
                        //priority count for standards starts from 0
                        val = parseInt(val) + 1;
                        field = editor.getEditor("root.standards." + stg_index + "." + "standardid");
                        if (field) {
                            field.setValue(val);
                        }
                    }
                    //this is the db id, which we need to track if this is an edit
                    if (k === "id") {
                        field = editor.getEditor("root.standards." + stg_index + "." + "uid");
                        if (field) {
                            field.setValue(val);
                        }
                    }
                });
            });
            //keep count of standard elements
            var count = 0;
            var eid = 0;
            //get standard elements for each standard.
            //first 'each' is all the standard elements associated with a standard
            $.each(data.data.standards.element, function (k, value) {
                if (typeof value != 'undefined' && value.length > 0 ) {
                    var se_array = value;
                    //convert the absolute standard id from the db to the local standard id
                    //for this framework
                    //@TODO reconcile vars
                    var std_id = value[0].standard;
                    var se_val = 0;
                    var subel_val = 0
                    var std_val = std_nums.indexOf(std_id); //the sid in the editor
                    var priority;//eid
                    var pid_val = 0;
                    var sid;
                    var eid_field;
                    var pid_field;
                    var sid_field;
                    var eid_val;
                    //each standard element
                    $.each(se_array, function (k, value){
                        //add a row for each new standard element
                        var se = editor.getEditor("root.standardelements");
                        if (count > 0) {
                            se.addRow();
                            $('div.form-group textarea.form-control').on('click', function () {
                                textarea_autoexpand(this);
                            });
                        }
                        //each value from a standard element
                        $.each(value, function (k,value ) {
                            //set if exists - works for shortname, name and description
                            var se = editor.getEditor("root.standardelements." + count + "." + k);
                            if (se) {
                                se.setValue(value);
                            }
                            //standard is standardid in the editor
                            if (k === "standard") {
                                sid_field = editor.getEditor("root.standardelements." + count + "." + "standardid");
                                if (sid_field && std_val > 0) {
                                   sid_field.setValue(std_val);
                                    sid = std_val;
                                }
                            }
                            //priority is elementid in the editor
                            //if there is no parentid, we just set the element id with the priority
                            if (k === "priority") {
                                if (eid_field) {
                                eid_val = value;
                                eid++;
                                }
                            }
                            if (k === "parent" ) {
                                if (value == null) {
                                    //anything after this will have a new parent, so increment parent value
                                    se_val++;
                                    //this is also the element id if there is no parent
                                    eid_val = se_val;
                                    //reset the count of element ids for sub elements of this standard element
                                    subel_val = 0;
                                }
                                //there is a parent element, we need to handle it
                                else {
                                    subel_val++;
                                    eid_val = subel_val;
                                    pid_val = se_val;
                                }
                            }
                             //this is the db id, which we need to track if this is an edit or if parentids are used
                            if (k === "id") {
                                field = editor.getEditor("root.standardelements." + count + "." + "uid");
                                if (field) {
                                    field.setValue(value);
                                }
                            }
                        });
                        //since pid_val and eid_val depend on each other, we need to set them outside the loop.
                        pid_field = editor.getEditor("root.standardelements." + count + ".parentelementid");
                        eid_field = editor.getEditor("root.standardelements." + count + "." + "elementid");
                        if (pid_val) {
                            eid_field.setValue(sid + "." + pid_val + "." + eid_val);
                            pid_field.setValue(sid + "." + pid_val);
                        }
                        else {
                            eid_field.setValue(sid + "." + eid_val);
                        }
                        pid_val = null;
                        count ++;//increment se count
                    });
                    eid = 1;
                }
            });

        });
    }
    // validation indicator
    editor.on('change',function() {
        // Get an array of errors from the validator
        var errors = editor.validate();
        // Not valid
        if (errors.length) {
            $('#messages').empty().append($('<div>', {'class':'alert alert-danger', 'text':get_string('invalidjson', 'module.framework')}));
        }
        // Valid
        else {
             $('#messages').empty().append($('<div>', {'class':'alert alert-success', 'text':get_string('validjson')}));
        }
    });

});
