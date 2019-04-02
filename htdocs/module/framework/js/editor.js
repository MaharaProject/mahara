jQuery(function($) {
    //use bootstrap
    JSONEditor.defaults.options.theme = 'bootstrap4';
    //Hide edit json buttons. The functionality creates a form based on the submitted json, without
    //calling the custom code on this page.
    JSONEditor.defaults.options.disable_edit_json = 'true';
    //@TODO turn edit overall form option into an export json button and have it create a file.matrix with
    //it for sharing.
    //re-name the button and override the functionality, then put it back in some form, may need the text override,
    //(custom patch, not currently used), so keeping for now.

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
    var standards_array = [];// @TODO, remove with dropdown
    var parent_array = [''];
    //counts to increment standard and standardelement ids
    var std_index = 0;
    var standard_count = 1;
    var eid = 1;//count of standard elements per standard
    var se_count = 1; //count of total standard elements
    var se_index = 0; //index of total standard elements

    var fw_id = null; //framework id if editing an existing framework
    var edit = false; //flag for edit vs. copy
    //@TODO - needs to be translatable?? or is this a varname? check!
    var evidence_type = ['begun' ,'incomplete', 'partialcomplete', 'completed'];

    formchangemanager.add('editor_holder');

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
                    "id" : "standardelement",
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
                            "id" : "parentid",
                            "type" : "string",
                            "description" : get_string('parentelementdesc'),
                            "enumSource" : "source",
                            "watch" : {
                                "source" : "pid_array"
                            },
                        },
                        "pid_array" : {
                            "id" : "hidden_pid_array",
                            "type" : "array",
                            "items" : {
                                "enum" : parent_array,
                            },
                            "options" : {
                                "hidden" : true,
                            },
                        },
                        //@TODO, to be removed?
                        // "standardid" : {
                        //     "title" : get_string('standardid'),
                        //    // "type" : "array",
                        //    "type" : "string",
                        //     "format" : "select",
                        //     "default" : 1,
                        //     "description" : get_string('standardiddesc1'),
                        //     "enum" : []
                        // },
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
    $("div.form-group textarea.form-control").eq(0).attr("id", "title_desc_textarea");
    $("div.form-group textarea.form-control").eq(2).attr("id", "std_desc_textarea");
    $("div.form-group textarea.form-control").eq(4).attr("id", "std_element_desc_textarea");
    //make text same as rest of site
    $("div.form-group p.form-text").addClass("description");
    $("div.form-group form-control-label").addClass("label");
    //add class for correct styling of help block text
    $("[data-schemaid=\"standards\"] >p").addClass("help-block");
    $("[data-schemaid=\"evidencestatuses\"] >p").addClass("help-block");
    //set min row height for desc fields to 6
    $("textarea[id$='_desc_textarea']").attr('rows', '6');
    textarea_init();


    $("#add_standard").click(function() {
        standard_count += 1;
        std_index = standard_count -1;
        var sid_field = editor.getEditor("root.standards." + std_index + ".standardid");
        sid_field.setValue(standard_count);

        var se_sid_field = editor.getEditor("root.standardelements." + se_index + ".standardid");
        if (se_sid_field) {
            se_sid_field.setValue(standard_count);
        }
        //reset standard element count
        eid = 0;
        update_parent_array();
        set_parent_array();

        textarea_init();
        set_editor_dirty();
    });

    $("#add_standardelement").click(function() {
        se_count++;
        se_index = se_count -1;
        console.log(se_index);//correct
        console.log(eid);
        console.log(se_count);
        console.log(standard_count);
        // var sid_field = editor.getEditor("root.standards." + std_index + ".standardid");
        // var sid = sid_field.getValue();
        // var se_sid_field = editor.getEditor("root.standardelements." + se_index + ".standardid");
        // se_sid_field.setValue(sid);
        var eid_field = editor.getEditor("root.standardelements." + se_index + ".elementid");
        var eid_val;
        //var pid_field = editor.getEditor("root.standardelements." + se_index + ".parentelementid");
       // var eid;
    //    if (!eid) {
    //        eid = 1;
    //    }
       if (!standard_count) {
        console.log("else running");
        eid_val = "1." + eid;
        }
        else {
            // if (standard_count == 1 && eid == 1) {
            //     eid ++;
            // }
            // else {
                eid ++;
                eid_val = standard_count + "." + eid;
            }

        console.log(eid_field);
        eid_field.setValue(eid_val);

        //set_standards_array();
        update_parent_array();
        set_parent_array();
        //@TODO: change this display to reflect the data
        //eid_field.setValue(se_count);
        textarea_init();
        set_editor_dirty();
    });

    // add checks to monitor if fields are changed
    editor.on('ready', function () {
        set_editor_clean();
        $('#editor_holder textarea').each(function(el){
          $(this).on('change', function() {
              set_editor_dirty();
          });
        });
        $('#editor_holder input').each(function(el){
          $(this).on('change', function() {
              set_editor_dirty()
          });
        });
        $('#editor_holder select').each(function(el){
          $(this).on('change', function() {
              set_editor_dirty()
          });
        });
    });

    $("[data-schemaid=\"parentid\"]").on('change', function () {
        console.log("new pid");
    });
    // function parent_set() {
    //     console.log(eid);
    //     console.log(editor.getValue(eid.parentelementid));
    // }


        // validation indicator
        editor.off('change');
        editor.on('change',function() {

            // Get an array of errors from the validator
            var errors = editor.validate();
            // Not valid
            //@TODO, look at original json-editor code to get the stuff that makes something red to work,
            //otherwise error message to look down the page for the error doesn't make sense.
            if (errors.length) {
                $('#messages').empty().append($('<div>', {'class':'alert alert-danger', 'text':get_string('invalidjson', 'module.framework')}));
            }
            // Valid
            else {
                    $('#messages').empty().append($('<div>', {'class':'alert alert-success', 'text':get_string('validjson')}));
            }

        });

        // add checks to monitor if fields are changed
        editor.on('ready', function () {
            set_editor_clean();
            $('#editor_holder textarea').each(function(el){
              $(this).on('change', function() {
                  set_editor_dirty();
              });
            });
            $('#editor_holder input').each(function(el){
              $(this).on('change', function() {
                  set_editor_dirty()
              });
            });
            var editorElement = jQuery('#editor_holder');
            editorElement.on('change', function() {
                set_editor_dirty();
            });
        });

   }

    //make textarea expand with text
    function textarea_init() {
        $('div.form-group textarea[name$="description\]"]').each(function() {
            $(this).off('click input');
            $(this).on('click input', function() {
                textarea_autoexpand(this);
            })
            textarea_autoexpand(this);
        });
    }
    function textarea_autoexpand(element) {
        element.setAttribute('style', 'height:' + (element.scrollHeight) + 'px;overflow-y:hidden;');
        element.style.height = 'auto';
        element.style.minHeight = '148px';
        element.style.maxHeight = '800px';
        element.style.height = (element.scrollHeight) + 'px';
    }

    //choose from edit dropdown
    $('#edit').on('change',function() {
        var confirm = null;
        if (typeof formchangemanager !== 'undefined') {
            confirm = formchangemanager.confirmLeavingForm();
        }
        if (confirm === null || confirm === true) {

        //rebuild the form so that data doesn't get added to existing
        editor.destroy();
        refresh_editor(); //calls editorchecker.init
        $("#copy option:eq(0)").prop('selected', true);//reset copy
        edit = true;
        var index = $('#edit').val();
        populate_editor(index, edit);

        upload = false;
        textarea_init();

        set_editor_clean();

        }

    });

    //choose from copy dropdown.
    $("#copy").on('change', function() {
        var confirm = null;
        if (typeof formchangemanager !== 'undefined') {
            confirm = formchangemanager.confirmLeavingForm();
        }
        if (confirm === null || confirm === true) {

        //rebuild the form so that data doesn't get added to existing
        if (formchangemanager.checkDirtyChanges()) {
            formchangemanager.confirmLeavingForm();
        }
        editor.destroy();
        refresh_editor();
        $("#edit option:eq(0)").prop('selected', true); //reset edit
        edit = false;
        var index = $('#copy').val();
        populate_editor(index);
        textarea_init();
        set_editor_clean();
        }
    });

    // Manage button - goes to fw screen
    $(".cancel").click(function() {
        formchangemanager.setFormStateById('editor_holder', FORM_CANCELLED);
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
        formchangemanager.setFormStateById('editor_holder', FORM_SUBMITTED);
        // Get all the form's values from the editor
        var json_form = editor.getValue();
        url = config['wwwroot'] + 'module/framework/framework.json.php';
        //if framework id is set, we are editing an existing framework
        if (fw_id) {
            json_form.fw_id = fw_id;
        }
        //save completed form data
        sendjsonrequest(url, json_form, 'POST');
        window.scrollTo(0,0);
    });

    // Manage button - goes to fw screen
    $(".cancel").click(function() {
        window.location.href = config['wwwroot'] + 'module/framework/frameworks.php';
    });

    //make textarea expand with text
    function textarea_init() {
        $('div.form-group textarea[name$="description\]"]').each(function() {
            $(this).off('click input');
            $(this).on('click input', function() {
                textarea_autoexpand(this);
            })
            textarea_autoexpand(this);
        });
    }
    function textarea_autoexpand(element) {
        element.setAttribute('style', 'height:' + (element.scrollHeight) + 'px;overflow-y:hidden;');
        element.style.height = 'auto';
        element.style.minHeight = '148px';
        element.style.maxHeight = '800px';
        element.style.height = (element.scrollHeight) + 'px';
    }

        //-------------------------
    //TODO - this section is WIP related to drop-downs

    // function get_standards_array() {
    //     return standards_array;
    // }

    function get_parent_array() {
        return parent_array;
    }

    // function update_standards_array() {
    //     //console.log(standards_array);
    //     $("[data-schemaid=\"standard\"]").each(function() {
    //        // console.log($(this));
    //         var num = parseInt($(this).data("schemapath").replace(/root\.standards\./, ''));
    //         num+=1;
    //       //  console.log(num);
    //         if ($.inArray(num, standards_array)== -1) {
    //        //     console.log(num);
    //             standards_array.push(num);
    //         }
    //     });
    // }

    // function set_standards_array() {
    //     var field;
    //     $("[data-schemaid=\"standardelement\"]").each(function() {
    //         field = ($(this).data("schemapath") + ".standardid");
    //         //console.log(field);
    //         field = field.replace(/\./g, '\]\[');
    //         field = field.replace(/^root\](.*)$/, 'root$1\]');
    //         //console.log(field);
    //         //[name="root[standardelements][0][standardid]"]
    //         $("[name=\"" + field + "\"]").empty();
    //         $.each(standards_array, function (k, value) {
    //             console.log(field);
    //             $("[name=\"" + field + "\"]").append($('<option>', {
    //             value: value,
    //             text: value
    //             }));
    //         });
    //    });
    // }

    function update_parent_array() {
    //    console.log(parent_array);
        $("[data-schemaid=\"standardelement\"]").each(function() {
            //number of std elements
            var num = parseInt($(this).data("schemapath").replace(/root\.standardelements\./, ''));
            var field = editor.getEditor("root.standardelements." + num + ".elementid");
            var el = field.getValue();
            if ($.inArray(el, parent_array)== -1) {
                parent_array.push(el);
            }
        });
    }

    function set_parent_array() {
        var field;

        $("[data-schemaid=\"standardelement\"]").each(function() {
            field = ($(this).data("schemapath") + ".parentelementid");
            field = field.replace(/\./g, '\]\[');
            field = field.replace(/^root\](.*)$/, 'root$1\]');
            $("[name=\"" + field + "\"]").empty();
            $.each(parent_array, function (k, value) {
                $("[name=\"" + field + "\"]").append($('<option>', {
                    value: value,
                    text: value
                }));
            });
        });
    }
    //---------------------------------------------END of WIP

    function textarea_autoexpand(element) {
        element.setAttribute('style', 'height:' + (element.scrollHeight) + 'px;overflow-y:hidden;');
        element.style.height = 'auto';
        element.style.minHeight = '64px';
        element.style.height = (element.scrollHeight) + 'px';
    }

    function populate_editor(framework_id, edit) {
        url = config['wwwroot'] + 'module/framework/getframework.json.php';
        upload = true;
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
                        textarea_init();
                        ed.setValue(value)
                        //@TODO wysiwyg editing of description fields
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
                //k is standard index or 'element'
                if (k != 'element') {
                    std_index = parseInt(k);
                }
                console.log(std_index);
                //if the standard doesn't already exist, we need to add it to the editor.
                if (std_index > 0 && !editor.getEditor("root.standards." + std_index)) {
                    var std_ed = editor.getEditor("root.standards");
                    std_ed.addRow();
                    standard_count += 1;
                    textarea_init();
                }
                //this makes an array with the 0 index empty and the db std ids matched with the index
                //of their standard number.
                standard_count = std_index + 1;
                if (value.id) {
                    std_nums[standard_count] = value.id;
                }
                 console.log(std_nums);

                $.each(value, function(k, val) {
                    //this works where the data field name is the same as the DOM's id
                    var field = editor.getEditor("root.standards." + std_index + "." + k );
                    if (field) {
                        field.setValue(val);
                    }
                    //the standardid is called priority in the db
                    if (k === "priority") {
                        //priority count for standards starts from 0
                        val = parseInt(val) + 1;
                        field = editor.getEditor("root.standards." + std_index + "." + "standardid");
                        if (field) {
                            field.setValue(val);
                        }
                    }
                    //this is the db id, which we need to track if this is an edit
                    if (k === "id") {
                        field = editor.getEditor("root.standards." + std_index + "." + "uid");
                        if (field) {
                            field.setValue(val);
                        }
                    }
                });
            });
            //first 'each' is all the standard elements associated with a standard
            $.each(data.data.standards.element, function (k, value) {
                var se_array = value;
                //convert the absolute standard id from the db to the local standard id
                //for this framework
                //@TODO reconcile vars
                var std_id = value[0].standard;
                console.log(std_id);
                console.log(std_nums);
                var se_val = 0;
                var subel_val = 0
                standard_count = std_nums.indexOf(std_id); //the sid in the editor
                console.log(standard_count);
                //end of broken code -------
                //var priority;//eid
                var pid_val = 0;
                //var sid;
                var eid_field;
                var pid_field;
                //var sid_field;
                var eid_val;
                //each standard element
                $.each(se_array, function (k, value){
                    //add a row for each new standard element
                    var se = editor.getEditor("root.standardelements");
                    if (se_index > 0) {
                        se.addRow();
                        se_count ++;
                        textarea_init();
                    }
                    //each value from a standard element
                    $.each(value, function (k,value ) {
                        //set if exists - works for shortname, name and description
                        var se = editor.getEditor("root.standardelements." + se_index + "." + k);
                        if (se) {
                            se.setValue(value);
                        }
                            //standard is standardid in the editor
                            if (k === "standard") {
                              //  sid_field = editor.getEditor("root.standardelements." + count + "." + "standardid");
                               //std_valstd_val if (std_val > 0) {
                                 //  sid_field.setValue(std_val);
                                 //   sid = std_val;
                             //   }
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
                                field = editor.getEditor("root.standardelements." + se_index + "." + "uid");
                                if (field) {
                                    field.setValue(value);
                                }
                            }
                        });
                        //since pid_val and eid_val depend on each other, we need to set them outside the loop.
                        pid_field = editor.getEditor("root.standardelements." + se_index + ".parentelementid");
                        eid_field = editor.getEditor("root.standardelements." + se_index + "." + "elementid");
                        if (pid_val) {
                            eid_field.setValue(standard_count + "." + pid_val + "." + eid_val);
                            pid_field.setValue(standard_count + "." + pid_val);
                        }
                        else {
                            eid_field.setValue(standard_count + "." + eid_val);
                        }
                        pid_val = null;
                        se_index ++;
                        console.log(eid_val);
                        console.log(eid);
                        eid = eid_val;// ??
                    });
                    //since pid_val and eid_val depend on each other, we need to set them outside the loop.
                    pid_field = editor.getEditor("root.standardelements." + se_index + ".parentelementid");
                    eid_field = editor.getEditor("root.standardelements." + se_index + "." + "elementid");
                    if (pid_val && eid_field) {
                        eid_field.setValue(standard_count + "." + pid_val + "." + eid_val);
                        pid_field.setValue(standard_count + "." + pid_val);
                    }
                    else if (eid_field) {
                        eid_field.setValue(standard_count + "." + eid_val);
                    }
                    pid_val = null;
                    se_index ++;
                    eid = eid_val;
                });
            });
        });
    }

});

// form change checker functions

function set_editor_dirty() {
    if (typeof formchangemanager !== 'undefined') {
        formchangemanager.setFormStateById("editor_holder", FORM_CHANGED);
    }
}

function set_editor_clean() {
    if (typeof formchangemanager !== 'undefined') {
        formchangemanager.setFormStateById('editor_holder', FORM_INIT);
    }
}
