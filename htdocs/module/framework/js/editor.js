/**
 * Javascript for the SmartEvidence editor
 * Mahara implementation of third party plugin - https://github.com/json-editor/json-editor
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Some wishlist functionality to be implemented later than 19.04:
 * 1 @TODO - Make preview button work
 *   It should show what current framework looks like as the left column of the SmartEvidence map -
 *   i.e. what you see when you look at the first page of a SE collection
 * 2 @TODO - Turn edit overall form option into an export json button and have it create a file.matrix
 *   for sharing.  (Note that in the custom code patch 5accb2c9d1259005249248d5cb4f2fa8acba97b5,
 *   there is code that re-names the button, which is there for this function.)
 *  * - do we want this:
 *   Clicking the "Save" button keeps you on the form and you have to click "Cancel" to return to the overview -> Implement the Moodle "Save" -
 * "Save and return to overview" - "Cancel"?
 * - Maybe: Add third-level nav to management screen of a framework? But then what to call the nav item? Overview wouldn't work,
 *  would be better to then call it "Management".
 */

/**
 * Functionality still to be implemented by 19.04:
 * @TODO - review:
 *  - make sub-sub elements work
 * - eid increments correctly  - make update_eid function work
 * check copy save
 * add inst default in the php. It's already there?
 */
var editor;
jQuery(function($) {
    // Use bootstrap
    JSONEditor.defaults.options.theme = 'bootstrap4';
    JSONEditor.defaults.options.iconlib = 'fontawesome5';
    // Hide edit json buttons. @TODO - main one will be needed for #2 wishlist item above
    JSONEditor.defaults.options.disable_edit_json = true;

    // disable the delete_last buttons from Standards and Standard Elements
    JSONEditor.defaults.options.disable_array_delete_last_row = true;

    // Override default editor strings to allow translation by us
    // - fyi, not all editor strings are overridden, just the ones currently used.
    // - The original editor defaults in htdocs/js/jsoneditor/src/jsoneditor.js
    JSONEditor.defaults.languages.en.button_collapse = get_string('collapse', 'module.framework'); // string used for button text
    JSONEditor.defaults.languages.en.button_collapse_title = get_string('collapseform', 'module.framework', '{{0}}'); // string used for title and aria-label
    JSONEditor.defaults.languages.en.button_expand = get_string('expand', 'module.framework'); // string used for button text
    JSONEditor.defaults.languages.en.button_expand_title = get_string('expandform', 'module.framework', '{{0}}'); // string used for title and aria-label
    JSONEditor.defaults.languages.en.button_add_row_text = get_string('add');
    JSONEditor.defaults.languages.en.button_add_row_title = get_string('addspecific', 'module.framework');
    JSONEditor.defaults.languages.en.button_move_down = get_string('moveright', 'module.framework');
    JSONEditor.defaults.languages.en.button_move_down_title = get_string('moverightspecific', 'module.framework', '{{0}}');
    JSONEditor.defaults.languages.en.button_move_up = get_string('moveleft', 'module.framework');
    JSONEditor.defaults.languages.en.button_move_up_title = get_string('moveleftspecific', 'module.framework', '{{0}}');
    JSONEditor.defaults.languages.en.button_delete_all = get_string('deleteall');
    JSONEditor.defaults.languages.en.button_delete_all_title = get_string('deleteallspecific', 'module.framework', '{{0}}');

    JSONEditor.defaults.languages.en.remove_element_message = get_string('removestandardorelementconfirm');
    JSONEditor.defaults.languages.en.button_delete_row_title_short = get_string('delete','module.framework');
    JSONEditor.defaults.languages.en.button_delete_row_title = get_string('deletespecific','mahara', '{{0}}');

    var parent_array = [''];

    // standard ids from the Standards form
    var standard_array = [];
    // standard names from the Standards form
    var standard_names = [];

    // Counts to increment standard and standardelement ids
    var std_index = 0;

    var eid = 1;      // Count of standard elements per standard
    var se_index = 0; // Index of total standard elements

    var fw_id = null; // Framework id if editing an existing framework
    var edit = false; // Flag for edit vs. copy
    // Constant identifiers for json schema
    var evidence_type = ['begun' ,'incomplete', 'partialcomplete', 'completed'];

    formchangemanager.add('editor_holder');

    /**
     * Jquery functionality outside the json-editor form:
     * includes dropdowns for edit, copy and the cancel, save and preview buttons
     * templated by theme/raw/plugintype/module/framework/templates/jsoneditor.tpl
     */

    // Edit dropdown
    $('#edit').on('change',function() {
        var confirm = null;
        if (typeof formchangemanager !== 'undefined') {
            confirm = formchangemanager.confirmLeavingForm();
        }
        if (confirm === null || confirm === true) {

            // Rebuild the form so that data doesn't get added to existing
            $("#copy option").eq(0).prop('selected', true); // Reset copy
            editor.destroy();
            refresh_editor();
            var index = $('#edit').val();
            if (index != "0") {
                 //we are selecting a framework from the dropdown
                 edit = true;
                 populate_editor(index, edit);
            }
            else {
                // We are clearing the dropdown
                fw_id= null;
                edit = false;
            }

            textarea_init();
            set_editor_clean();
        }
    });

    // Copy dropdown.
    $("#copy").on('change', function() {
        var confirm = null;
        if (typeof formchangemanager !== 'undefined') {
            confirm = formchangemanager.confirmLeavingForm();
        }
        if (confirm === null || confirm === true) {

            // Rebuild the form so that data doesn't get added to existing
            if (formchangemanager.checkDirtyChanges()) {
                formchangemanager.confirmLeavingForm();
            }
            $("#edit option").eq(0).prop('selected', true); // Reset edit
            editor.destroy();
            refresh_editor();
            edit = false;
            fw_id= null;
            var index = $('#copy').val();
            if (index != "0") {
                populate_editor(index);
            }
            textarea_init();
            set_editor_clean();
        }
    });

    // Cancel button - goes to overview screen
    $(".cancel").click(function() {
        formchangemanager.setFormStateById('editor_holder', FORM_CANCELLED);
        window.location.href = config['wwwroot'] + 'module/framework/frameworks.php';
    });

    // Hide currently inactive preview button - @TODO - needed for #1 wishlist item above
    $('#preview').hide();

    // Hook up the submit button to log to the console
    $(".submit").click(function() {
        formchangemanager.setFormStateById('editor_holder', FORM_SUBMITTED);
        // Get all the form's values from the editor
        var json_form = editor.getValue();
        url = config['wwwroot'] + 'module/framework/framework.json.php';
        // If framework id is set, we are editing an existing framework
        if (fw_id) {
            json_form.fw_id = fw_id;
        }
        // Save completed form data
        sendjsonrequest(url, json_form, 'POST', function(data) {
            // Get framework id for next save
            fw_id = data.data.id;

            // Place the name of the framework in the "Edit" dropdown
            var addoption = true;
            $.each($('select#edit')[0].options, function() {
                if (this.value == data.data.id) {
                    addoption = false;
                    return false;
                }
            });
            if (addoption) {
                $('select#edit').append($('<option>', {value:data.data.id, text:data.data.name}));
            }
            $('select#edit option[value="' + data.data.id + '"]').prop('selected', true);
            edit = true;

            // Reset the "Copy" dropdown
            $("#copy option").eq(0).prop('selected', true);
            editor.destroy();
            refresh_editor();
            populate_editor(data.data.id);
            set_editor_clean();
        });
        window.scrollTo(0,0);
    });
    // End of functionality implemented outside the editor

    refresh_editor();

    /**
     * Initialise the editor
     *  - set the json-schema for the form
     *  - add events to form elements
     *  - call initialising functions
     */
    function refresh_editor() {
        // The json-editor properties
        editor = new JSONEditor(document.getElementById('editor_holder'), {
            ajax: true,
            disable_properties: true,
            show_errors: "always",
            // The schema for the editor, info on https://github.com/json-editor/json-editor
            schema: {
                "title": get_string('Framework'),
                "type": "object",
                "properties": {
                    "institution": {
                        "type": "string",
                        "title": get_string('institution'),
                        "description": get_string('instdescription'),
                        "id": "inst_desc",
                        "enum": inst_names.split(','),
                        "default": get_string('all'),
                        "required": true
                    },
                    "name": {
                        "type": "string",
                        "title": get_string('name'),
                        "description": get_string('titledesc'),
                        "default": get_string('frameworktitle'),
                    },
                    "description": {
                        "type": "string",
                        "title": get_string('description'),
                        "format": "textarea",
                        "default": get_string('defaultdescription'),
                        "description": get_string('descriptioninfo')
                    },
                    "selfassess": {
                        "type": "boolean",
                        "title": get_string('selfassessed'),
                        "description": get_string('selfassesseddescription'),
                        "default": false,
                        "options": {
                            "enum_titles": [get_string('yes'), get_string('no')]
                        },
                        "required": true
                    },
                    "evidencestatuses": {
                        "title": get_string('evidencestatuses'),
                        "id": "evidencestatuses",
                        "type": "object",
                        "options": {
                            "disable_array_reorder": true,
                            "disable_edit_json": true,
                            "disable_collapse": true
                        },
                        "description": get_string('evidencedesc'),
                        "properties": {
                            "begun": {
                                "title": get_string('Begun'),
                                "type": "string",
                                "default": get_string('begun'),
                                "propertyOrder": 1
                            },
                            "incomplete": {
                                "title": get_string('Incomplete'),
                                "type": "string",
                                "default": get_string('incomplete'),
                                "propertyOrder": 2
                            },
                            "partialcomplete": {
                                "title": get_string('Partialcomplete'),
                                "type": "string",
                                "default": get_string('partialcomplete'),
                                "propertyOrder": 3
                            },
                            "completed": {
                                "title": get_string('Completed'),
                                "type": "string",
                                "default": get_string('completed'),
                                "propertyOrder": 4
                            }
                        }
                    },
                    "standards": {
                        "title": get_string('standards'),
                        "type": "array",
                        "id": "standards",
                        "format": "tabs-top",
                        "minItems": 1,
                        "description": get_string('standardsdescription'),
                        "options" : {
                            "disable_array_delete_all_rows": true
                        },
                        "items": {
                            "title": get_string('standard'),
                            "headerTemplate": "{{self.shortname}}",
                            "type": "object",
                            "id": "standard",
                            "options": {
                                "disable_collapse": true
                            },
                            "properties": {
                                "shortname": {
                                    "type": "string",
                                    "title": get_string('Shortname'),
                                    "description": get_string('shortnamestandard'),
                                    "default": get_string('Shortname'),
                                    "maxLength": 100
                                },
                                "name": {
                                    "type": "string",
                                    "title": get_string('name'),
                                    "description": get_string('titlestandard'),
                                    "format": "textarea",
                                    "maxLength": 255
                                },
                                "description": {
                                    "type": "string",
                                    "title": get_string('description'),
                                    "format": "textarea",
                                    "default": get_string('descstandarddefault'),
                                    "description": get_string('descstandard')
                                },
                                "standardid": {
                                    "type": "number",
                                    "default": "1",
                                    "options" : {
                                        "hidden" : true,
                                    }
                                },
                                "uid": {
                                    "type": "number",
                                    "default": "0",
                                    "options": {
                                        "hidden": true
                                    }
                                }
                            }
                        }
                    },
                    "standardelements": {
                        "title": get_string('standardelements'),
                        "id": "standardelements",
                        "type": "array",
                        "uniqueItems": true,
                        "minItems": 1,
                        "format": "tabs-top",
                        "description": get_string('standardelementsdescription', 'module.framework'),
                        "items": {
                            "title": get_string('standardelement'),
                            "headerTemplate": "Standard element {{self.elementid}}",
                            "type": "object",
                            "id": "standardelement",
                            "options": {
                                "disable_collapse" : true
                            },
                            "properties": {
                                "shortname": {
                                    "type": "string",
                                    "title": get_string('Shortname'),
                                    "description": get_string('shortnamestandard'),
                                    "maxLength": 100
                                },
                                "name": {
                                    "type": "string",
                                    "title": get_string('name'),
                                    "description": get_string('titlestandard'),
                                    "format": "textarea",
                                    "maxLength": 255
                                },
                                "description": {
                                    "type": "string",
                                    "title": get_string('description'),
                                    "format": "textarea",
                                    "default": get_string('standardelementdefault'),
                                    "description": get_string('standardelementdesc')
                                },
                                "elementid": {
                                    "type": "string",
                                    "title": get_string('elementid'),
                                    "default": '1.1',
                                    "description": get_string('elementiddesc'),
                                    "options": {
                                        "hidden": true,
                                    },
                                },
                                "standardoptions": {
                                    "title": get_string('standardid'),
                                    "id": "standardoptions",
                                    "type": "string",
                                    "description": get_string('standardiddesc1'),
                                    "enumSource": "source",
                                    "watch": {
                                        "source": "sid_array"
                                    },
                                },
                                "parentid": {
                                    "title": get_string('parentelementid'),
                                    "id": "parentid",
                                    "type": "string",
                                    "description": get_string('parentelementdesc'),
                                    "enumSource": "source",
                                    "watch": {
                                        "source": "pid_array"
                                    },
                                },
                                "parentelementid": {
                                    "id": "parentelementid",
                                    "type": "string",
                                    "options": {
                                        "hidden": true,
                                    },
                                },
                                "standardid": {
                                    "id": "standardid",
                                    "type": "number",
                                    "default" : 1,
                                    "options": {
                                        "hidden": true,
                                    },
                                },
                                "sid_array": {
                                    "id": "hidden_sid_array",
                                    "type": "array",
                                    "items": {
                                        "enum": standard_array,
                                    },
                                    "options": {
                                        "hidden": true,
                                    },
                                },
                                "pid_array": {
                                    "id": "hidden_pid_array",
                                    "type": "array",
                                    "items": {
                                        "enum": parent_array,
                                    },
                                    "options": {
                                        "hidden": true,
                                    },
                                },
                                "uid": {
                                    "type": "number",
                                    "default": "0",
                                    "options": {
                                        "hidden": true
                                    }
                                }
                            }
                        }
                    }
                }
            },
        });
        // Add ids to things so we can call them more easily later.
        $('div[data-schemaid="standards"] > span > button.json-editor-btn-add').attr("id", "add_standard");
        $('div[data-schemaid="standardelements"] > span > button.json-editor-btn-add').attr("id", "add_standardelement");
        $('div[data-schemaid="standardelements"] > span > button.json-editor-btntype-deleteall').attr("id", "deleteall_standardelements");
        // Add ids to all <input>, <select> and <textarea> tags and associate with their <label> for accessibility
        $('#editor_holder :input').each(function() {
            let name = $(this).attr('name');
            if (name !== undefined) {
                name = name.replace(/[\[\]']+/g, '');
                name = name.replace('root', '');
                $(this).prop('id', name);
                $(this).prev('label').prop('for', name);
              }
        });
        // Add aria-labels to add, delete and move buttons
        $('#add_standard').attr('aria-label', get_string('addstandard', 'module.framework'))
        $('#add_standardelement').attr('aria-label', get_string('addstandardelement', 'module.framework'));
        $('#deleteall_standardelements').attr('aria-label', get_string('deleteallstandardelements', 'module.framework'));

        // Make text same as rest of site
        $("div.form-group p.form-text").addClass("description");
        $("div.form-group form-control-label").addClass("label");
        // Add class for correct styling of help block text
        $('[data-schemaid="standards"] > p').addClass("help-block");
        $('[data-schemaid="evidencestatuses"] > p').addClass("help-block");
        parent_array = [''];
        standard_array = [];
        standard_names = [];
        std_index = 0;
        eid = 1;
        se_index = 0;

        textarea_init();

        update_parent_array();
        update_standard_array();

        set_standard_array();
        add_parent_event();

        update_delete_button_handler();
        update_delete_standard_button_handlers();
        update_delete_element_button_handlers();

        $("#add_standard").click(function() {
            update_delete_standard_button_handlers();
            std_index = standard_array.length;
            var sid_field = editor.getEditor("root.standards." + std_index + ".standardid");
            var max_standard_id = Math.max.apply(Math, standard_array);
            if (max_standard_id > 0 ) {
                sid_field.setValue(max_standard_id + 1);
            }
            else {
                // in case of an empty standard_array, Math.max will return -Infinity
                sid_field.setValue(1);
            }
            // Reset standard element count
            eid = 0;
            update_standard_shortname_handler();
            update_standard_array();
            update_parent_array();

            //set_parent_array();
            set_standard_array();

            textarea_init();
            set_editor_dirty();
        });
        $("#add_standardelement").click(function() {
            // Update delete button handlers
            update_delete_element_button_handlers();
            se_index = parent_array.length - 1;

            var eid_field = editor.getEditor("root.standardelements." + se_index + ".elementid");
            var sid_field = editor.getEditor("root.standardelements." + se_index + ".standardoptions");
            var eid_val;
            if (standard_array.length == 0) {
                eid_val = "1." + eid;
            }
            else {
                eid ++;
                eid_val = standard_array[standard_array.length - 1] + "." + eid;
            }
            eid_field.setValue(eid_val);
            set_sid(eid_val, sid_field);

            update_standard_in_standard_element(se_index, standard_array[standard_array.length - 1]);

            update_parent_array();
            update_standard_array();
            set_standard_array();

            set_parent_array();
            add_parent_event();
            textarea_init();
            set_editor_dirty();
        });

        // Add checks to monitor if fields are changed
        editor.on('ready', function () {
            set_editor_clean();
            $('#editor_holder textarea').each(function(el) {
                $(this).on('change', function() {
                    set_editor_dirty();
                });
            });
            $('#editor_holder input').each(function(el) {
                $(this).on('change', function() {
                    set_editor_dirty()
                });
            });
            $('#editor_holder select').each(function(el) {
                $(this).on('change', function() {
                    set_editor_dirty()
                });
            });
        });

        // Validation indicator
        editor.off('change');
        editor.on('change',function() {
            // @TODO, check functionality
            // Get an array of errors from the validator
            var errors = editor.validate();
            let isCopy = $('#copy option:selected').val();
            let isEdit = $('#edit option:selected').val();
            if (!(isCopy ^ isEdit)) {
                return;
            }
            // Not valid
            if (errors.length) {
                $('#messages').empty().append($('<div>', {'class':'alert alert-danger', 'text':get_string('invalidjsonineditor', 'module.framework')}));
            }
            // Valid
            else {
                $('#messages').empty().append($('<div>', {'class':'alert alert-success', 'text':get_string('validjson')}));
            }
        });

    }
    // End of refresh function

    /**
     * Populate the editor from database
     *  @param framework_id The db id for the framework
     *  @param edit boolean, true if editing an existing framework
     */

    function populate_editor(framework_id, edit) {
        url = config['wwwroot'] + 'module/framework/getframework.json.php';

        // Get data from existing framework
        sendjsonrequest(url, {'framework_id': framework_id} , 'POST', function(data) {
            if (edit) {
                fw_id = data.data.info.id;
            }
            // Set the values for the first 'info' section
            $.each(data.data.info, function (k, value) {
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
                        // @TODO wysiwyg editing of description fields
                    }
                    else {
                        ed.setValue(value);
                    }
                }
            });
            // Set the values for the evidence statuses
            $.each(data.data.evidencestatuses, function (k, value) {
                var type = evidence_type[value.type];
                var es = editor.getEditor("root.evidencestatuses." + type);
                es.setValue(value.name);
            });
            var std_nums = new Array();
            // Set the values for the standards
            $.each(data.data.standards, function (k, value) {
                // 'k' is standard index or 'element'
                // 'element' contains the standard elements, managed by next $.each
                if (k != 'element') {
                    std_index = parseInt(k);

                    // If the standard doesn't already exist, we need to add it to the editor.
                    if (std_index > 0 && !editor.getEditor("root.standards." + std_index)) {
                        var std_ed = editor.getEditor("root.standards");
                        std_ed.addRow();
                        update_standard_array();
                        textarea_init();
                    }
                    // This makes an array with the 0 index empty and the db std ids matched with the index
                    // of their standard number.
                    update_standard_array();
                    if (value.id) {
                        std_nums[standard_array.length] = value.id;
                    }

                    $.each(value, function(k, val) {
                        // This works where the data field name is the same as the DOM's id
                        var field = editor.getEditor("root.standards." + std_index + "." + k );
                        if (field) {
                            field.setValue(val);
                        }
                        // The standardid is called priority in the db
                        if (k === "priority") {
                            // Priority count for standards starts from 0
                            val = parseInt(val) + 1;
                            field = editor.getEditor("root.standards." + std_index + "." + "standardid");
                            if (field) {
                                field.setValue(val);
                            }
                        }
                        // This is the db id, which we need to track if this is an edit
                        if (k === "id") {
                            field = editor.getEditor("root.standards." + std_index + "." + "uid");
                            if (field) {
                                field.setValue(val);
                            }
                        }
                    });
                }
            });
            update_standard_array();
            update_standard_shortname_handler();
            var parent_nums = new Array();
            // First 'each' is all the standard elements associated with a standard
            $.each(data.data.standards.element, function (k, value) {
                var se_array = value;
                // Convert the absolute standard id from the db to the local standard id
                // for this framework
                var std_id = value[0].standard;
                var se_val = 0;
                var subel_val = 0
                var pid_val = 0;
                var eid_field;
                var pid_field;
                var eid_val;
                update_parent_array();
                // Have the actual standard element count
                se_index = Object.keys(parent_nums).length;
                // Each standard element
                $.each(se_array, function (k, value) {
                    // Add a row for each new standard element
                    var se = editor.getEditor("root.standardelements");
                    if (se_index > 0) {
                        se.addRow();
                        textarea_init();
                        add_parent_event();
                    }
                    // Each value from a standard element
                    $.each(value, function (k, value) {
                        // Set if exists - works for shortname, name and description
                        var se = editor.getEditor("root.standardelements." + se_index + "." + k);
                        if (se) {
                            se.setValue(value);
                        }
                        // Standard is standardid in the editor
                        if (k === "standard") {
                            var standardid = std_nums.indexOf(value);
                            update_standard_in_standard_element(se_index, standardid);
                        }
                        // Priority is elementid in the editor - if there is no parentid, we just
                        // set the element id with the priority
                        if (k === "priority") {
                            if (eid_field) {
                                eid_val = value;
                                eid++;
                            }
                        }
                        if (k === "parent" ) {
                            if (value == null) {
                                // Anything after this will have a new parent, so increment parent value
                                se_val++;
                                // This is also the element id if there is no parent
                                eid_val = se_val;
                                // Reset the count of element ids for sub elements of this standard element
                                subel_val = 0;
                            }
                            else {
                                // There is a parent element, we need to handle it
                                subel_val++;
                                eid_val = subel_val;
                                pid_val = se_val;
                            }
                        }
                        // This is the db id, which we need to track if this is an edit or if parentids are used
                        if (k === "id") {
                            field = editor.getEditor("root.standardelements." + se_index + "." + "uid");
                            if (field) {
                                field.setValue(value);
                            }
                        }
                    });
                    // Since pid_val and eid_val depend on each other, we need to set them outside the loop.
                    pid_field = editor.getEditor("root.standardelements." + se_index + ".parentelementid");
                    eid_field = editor.getEditor("root.standardelements." + se_index + ".elementid");
                    var suffix;
                    if (pid_val && eid_field) {
                        suffix = get_element_suffix(parent_nums, parent_nums[value.parent]);
                        pid_field.setValue(parent_nums[value.parent]);
                        eid_field.setValue(parent_nums[value.parent] + "." + suffix);
                        parent_nums[value.id] = parent_nums[value.parent] + "." + suffix;
                    }
                    else if (eid_field) {
                        suffix = get_element_suffix(parent_nums, std_nums.indexOf(value.standard));
                        eid_field.setValue(std_nums.indexOf(value.standard) + "." + suffix);
                        parent_nums[value.id] = std_nums.indexOf(value.standard) + "." + suffix;
                    }
                    update_parent_array();
                    pid_val = null;
                    se_index ++;
                    eid = eid_val;
                });
            });
            update_parent_array();
            update_standard_array();
            set_parent_array();

            update_delete_element_button_handlers();
            update_delete_standard_button_handlers();
        },
        function() {
             displayMessage(get_string_ajax('noframeworkfoundondb', 'module.framework'), 'error');
        });
    }
    // End of populate_editor()
    function get_element_suffix(taken, parent) {
        var i = 1;
        while ($.inArray(parent + "." + i, taken) > -1) {
            i++;
        }
        return (i);
    }
    // Add textarea expand event to description fields
    function textarea_init() {
        // Creating ids for adding wysiwyg - not currently active: @TODO
        $('div[data-schemaid="standards"] textarea[data-schemaformat="textarea"]').each(function() {
            if (!$(this).attr('id')) {
                var schemapath = $(this).closest('div[data-schemapath]').attr('data-schemapath').split('.');
                var standardid = schemapath[2];
                $(this).attr("id", "std_" +standardid + "_" + schemapath[3] + "_textarea");
            }
        });
        $('div[data-schemaid="standardelements"] textarea[data-schemaformat="textarea"]').each(function() {
            if (!$(this).attr('id')) {
                var schemapath = $(this).closest('div[data-schemapath]').attr('data-schemapath').split('.');
                var standardelementid = schemapath[2];
                $(this).attr("id", "std_element_" + standardelementid + "_" + schemapath[3] + "_textarea");
            }
        });
        // Set min row height for description fields to 6
        $("textarea[id$='description']").attr('rows', '6');

        $('div.form-group textarea[id$="description"]').each(function() {
            $(this).off('click input');
            $(this).on('click input', function() {
                textarea_autoexpand(this);
                // ScrollHeight is 0 for elements that are not visible
                this.style.height = (this.scrollHeight) + 'px';
            });
            textarea_autoexpand(this);
        });
    }

    // Expand textareas
    function textarea_autoexpand(element) {
        element.setAttribute('style', 'overflow-y:hidden;');
        element.style.height = 'auto';
        element.style.minHeight = '64px';
    }

    function set_sid(eid_val, sid_field) {
        var sid = parseInt(eid_val.replace(/(\d.?)\..*/, "$1"));
        if (sid_field) {
            sid_field.setValue(sid);
        }
    }

    // Get a list of existing standard elements
    function update_parent_array() {
        parent_array = [''];
        $("[data-schemaid=\"standardelement\"]").each(function() {
            // Number of std elements
            var num = parseInt($(this).data("schemapath").replace(/root\.standardelements\./, ''));
            var field = editor.getEditor("root.standardelements." + num + ".elementid");
            var el = field.getValue();

            parent_array.push(el);
        });
    }

    function get_standard_array() {
        return standard_array >= 1 ? standard_array : 1;
    }

    function update_standard_array() {
        standard_array = [];
        standard_names = [];
        $('[data-schemaid="standard"]').each(function() {
            // Number of std elements
            var num = parseInt($(this).data("schemapath").replace(/root\.standards\./, ''));
            var field = editor.getEditor("root.standards." + num + ".standardid");
            var id = field.getValue();

            // Get standard name
            field = editor.getEditor("root.standards." + num + ".shortname");
            var name = field.getValue();

            standard_array.push(id);
            standard_names.push(name);
        });
    }

    /**
    * Updates standard dropdown values for all standard elements
    */
    function set_standard_array() {
        var field;
        $('[data-schemaid="standardelement"]').each(function() {
            // Populate the standard id options
            var selectfield = $(this).find('[data-schemaid="standardoptions"]');

            set_standard_array_field(selectfield);

            // If standardid hidden field is set, then select the standardid in the dropdown
            var standardid = $(this).find('[name$="[elementid]"]')[0].value.split('.')[0];
            selectfield.find('select option[VALUE="' + standardid + '"]').prop('selected', true);
        });
    }

    /**
     * Updates standard dopdown values in select field given
     * @param selectfield html select element
     */
    function set_standard_array_field(selectfield) {
        var field;
        field = selectfield.data("schemapath");
        field = field.replace(/\./g, '\]\[');
        field = field.replace(/^root\](.*)$/, 'root$1\]');
        $("[name=\"" + field + "\"]").empty();
        $("[name=\"" + field + "\"]").addClass("select");
        $.each(standard_array, function (k, value) {
            $("[name=\"" + field + "\"]").append($('<option>', {
                value: value,
                text: value + ' - ' + standard_names[k]
            }));
        });
    }

    /**
      * Add the list of possible parent ids to the dropdown
      */
    function set_parent_array() {
        var field;
        $('[data-schemaid="standardelement"]').each(function() {
            //get index of standard element
            var index = this.id.split('.');
            index = index[index.length-1];

            var sid_field = editor.getEditor("root.standardelements." + index + "." + "standardid");
            var standardid = sid_field.input.value;
            update_standard_in_standard_element(index, standardid);
            field = $(this).find('[data-schemaid="standardoptions"] .form-control');
            filter_parent_options(field[0], standardid);
        });
    }

    // Add an event to update the element id when the parent id is changed
    function add_parent_event() {
        $('[data-schemaid="parentid"] .form-control').each(function () {
            $(this).off('change');
            $(this).on('change', function() {
                update_eid(this);
            });
            update_eid(this);
        });

        $('[data-schemaid="standardoptions"] .form-control').each(function () {
            $(this).off('change');
            $(this).on('change', function (el) {
                // get selected value
                var standardid = el.target.selectedOptions[0].value;
                update_sid(this);
                filter_parent_options(this, standardid);
            });
        });
    }

    // Update the element id for the passed in standard element
    function update_eid(element) {
        if (element.value) {
            var index = element.name.replace(/.*\[(\d*)\].*/, '$1');
            var eid_field = editor.getEditor("root.standardelements." + index + ".elementid");
            if (eid_field) {
                eid_field.setValue(element.value + "." + create_eid_number(element.value, 'parentid'));
            }
            // And set parentelementid in the editor
            var peid_field = editor.getEditor("root.standardelements." + index + ".parentelementid");
            if (peid_field) {
                peid_field.setValue(element.value);
            }
            update_parent_array();
            set_parent_array();
        }
    }

    /**
     * Update the standardid hidden field based on the value selected in the dropdown
     * @param element: html select from standard element section
     */
    function update_sid(element) {
        if (element.value) {
            var index = element.name.replace(/.*\[(\d*)\].*/, '$1');
            // update standard id hidden field
            var pid_field = editor.getEditor("root.standardelements." + index + ".standardid");
            if (pid_field) {
                pid_field.setValue(element.value);
            }
            // update element id hidden field
            var eid_field = editor.getEditor("root.standardelements." + index + ".elementid");
            if (eid_field) {
                eid_field.setValue(element.value + "." + create_eid_number(element.value, 'standardid'));
            }
        }
    }

    /**
    * Set the parent id dropdown of the element to have
    * elements ids belonging to the standard in the standardid field
    * @param element in the select html element that contains the standard id
    * @param standardid standard id
    */
    function filter_parent_options(element, standardid) {
        if (element) {
            var index = element.name.replace(/.*\[(\d*)\].*/, '$1');
            // get element id
            var eid_field = editor.getEditor("root.standardelements." + index + ".elementid");
            var elementid = eid_field.input.value;

            // get parent element id dropdown field
            var pid_field = editor.getEditor("root.standardelements." + index + ".parentid");
            pid_field = $('[name="' + pid_field.formname + '"]');

            // get the hidden parent id to select it from the dropdown
            var parentid_field = editor.getEditor("root.standardelements." + index + ".parentelementid");

            // Clear old element ids from the dropdown
            pid_field.empty();
            pid_field.addClass("select");
            pid_field.attr("id", "parent_select_" + index);

            pid_field.append($('<option>', {
                value: '',
                text: ''
            }));

            $.each(parent_array, function (k, value) {
                if (value.startsWith(standardid) && elementid != value) {
                    if (parseInt(parentid_field.input.value) && parentid_field.input.value == value) {
                        pid_field.append($('<option>', {
                          value: value,
                          text: value,
                          selected: true
                        }));
                    }
                    else {
                        pid_field.append($('<option>', {
                          value: value,
                          text: value,
                        }));
                    }
                }
            });
        }
    }

    /**
     *  Calculates the element id suffix after the parent id/standard id has been changed
     *  @param parent_id The parent id selected from the dropdown
     *  @param source is 'standardid' or 'parentid' depending which dropdown was changed
     *  @return number of elements that have the same parentid
     */
    function create_eid_number(parent_id, sourcefield) {
        var pel_array = [];
        $('[data-schemaid="standardelement"] .form-control[name$="' + sourcefield + ']"').each(function () {
            if (this.value) {
                pel_array.push(this.value);
            }
        });
        count_subel = 0;
        $(pel_array).each(function(k, val) {
            if (val == parent_id) {
                count_subel++
            }
        });
        return count_subel;
    }

    /**
    * Sets the stadardid hidden field and select the standard id from the dropdown
    * @param index of the standard element section
    * @param standardid id to set
    */
    function update_standard_in_standard_element(index, standardid) {
        // in case standardid is a string
        standardid = +standardid;
        // Set the standardid hidden field
        var sid_field = editor.getEditor("root.standardelements." + index + "." + "standardid");
        if (sid_field && standard_array.length > 0) {
            sid_field.setValue(standardid);
        }
        // Select corresponding element in the dropdown
        var sidoptions_field = editor.getEditor("root.standardelements." + index + "." + "standardoptions");
        set_standard_array_field($(sidoptions_field.container));
        sidoptions_field.input.selectedIndex = $.inArray(+standardid, standard_array);
    }

    function update_standard_shortname_handler() {
        $('[data-schemaid="standard"] .form-control[name$="shortname]"').each( function () {
            $(this).off('change');
            $(this).on('change', function() {
                // get all the shortnames and update the dropdowns
                update_standard_array();
                set_standard_array();
                set_editor_dirty();
            });
        });
    }

    /**
     * Manually add the handlers for the standard delete buttons
     * needs to add it also after deleting one standards because
     * the container is refreshed and the buttons recreated
     */
    function update_delete_standard_button_handlers() {
        $('[data-schemaid="standard"] > span > button.json-editor-btn-delete').off('click');
        $('[data-schemaid="standard"] > span > button.json-editor-btn-delete').on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();

            var standardid = 0,
                last_standardid = 0,
                standard_array_temp = standard_array;

            // get the last standard id from the list of standards,
            // to check if it's the one being deleted
            if (standard_array.length) {
                last_standardid = standard_array[standard_array.length - 1]
            }

            update_standard_shortname_handler();
            update_standard_array();
            update_delete_standard_button_handlers();
            textarea_init();
            set_editor_dirty();
            // get the standard id that was deleted
            standardid = $(standard_array_temp).not(standard_array).get()[0];
            // delete all the standard elements under the standard that was removed
            delete_child_standard_elements(standardid, '');

            // if the standard is the last one (or the one that has focus), change the standard element count
            if ((last_standardid == standardid)) {
                update_eid_number();
            }
            // if the standard has the last element as child
            // we need to add an empty standard element to the form (there should be at least one)
            if (editor.getEditor("root.standardelements").rows.length < 1) {
                $("#add_standardelement")[0].click();
            }

        });
    }

    function update_eid_number() {
        // no standard or no standard elements, then reset the count
        if (!standard_array.length || parent_array.length < 2) {
            eid = 0;
        }
        else {
            // last standard element name
            var spl = parent_array[parent_array.length -1].split('.');
            // last standard element parent matches last added standard
            if (spl[0] == standard_array[standard_array.length - 1]) {
                eid = spl[spl.length-1];
            }
            else {
                // last added standard doesn't have standard elements
                eid = 0;
            }
        }
    }

    function delete_child_standard_elements(std_id, el_id) {
        var change_tab = false,
            new_rows = [],
            new_i = 0,
            el_ids_to_check = [];
        $.each(editor.getEditor("root.standardelements").rows, function(i,row) {
            var
            is_parent_standard = (std_id != '' && $(this)[0].value.standardid == std_id),
            is_parent_element = (el_id != '' && $(this)[0].value.parentelementid == el_id);

            if (is_parent_standard || is_parent_element) {
                el_ids_to_check.push($(this)[0].value.elementid);
                if (row.tab == editor.getEditor("root.standardelements").active_tab) change_tab = true;
                if (row.tab && row.tab.parentNode) row.tab.parentNode.removeChild(row.tab);
                editor.getEditor("root.standardelements").destroyRow(row,true);
                editor.getEditor("root.standardelements").row_container;
                editor.getEditor("root.standardelements").row_cache[i] = null;
                editor.getEditor("root.standardelements").rows[i] = null;
                se_index--;
                set_editor_dirty();
            }
            else {
                new_rows[new_i] = row;
                new_i++;
            }
        });

        update_parent_array();
        set_parent_array();

        editor.getEditor("root.standardelements").row_cache = new_rows;
        editor.getEditor("root.standardelements").rows = new_rows;

        if (change_tab && editor.getEditor("root.standardelements").rows.length) {
            editor.getEditor("root.standardelements").active_tab = editor.getEditor("root.standardelements").rows[0];
            editor.getEditor("root.standardelements").theme.markTabActive(editor.getEditor("root.standardelements").rows[0]);
        }
        editor.getEditor("root.standardelements").refreshValue();
        editor.getEditor("root").refreshValue();

        // delete children of the deleted elements as well
        $.each(el_ids_to_check, function(index, el_id) {
          delete_child_standard_elements('', el_id);
        });
    }
    /**
     * Manually add the handlers for the standard elements delete buttons
     * needs to add it also after deleting one standard element because
     * the container is refreshed and the buttons recreated
     */
    function update_delete_element_button_handlers() {
        $('[data-schemaid="standardelement"] > div > button.json-editor-btn-delete').off('click');
        $('[data-schemaid="standardelement"] > div > button.json-editor-btn-delete').on('click', function() {
            var el_id = 0,
            parent_array_temp = parent_array;

            update_parent_array();
            se_index--;
            // If it's the last element
            if (parseInt(this.attributes['data-i'].value) == parent_array.length) {
                eid--;
            }

            // get the standard id that was deleted
            el_id = $(parent_array_temp).not(parent_array).get()[0];

            delete_child_standard_elements('', el_id);
            update_delete_element_button_handlers();
            set_parent_array();
            textarea_init();
            set_editor_dirty();
        });
    }

    /**
     * Manually add the handlers for the standard elements top delete button
     * 'Delete all'
     */
    function update_delete_button_handler() {
        // Standard element section
        // 'Delete all' button
        $('div[data-schemaid="standardelements"] > span > button.json-editor-btntype-deleteall').on('click', function () {
            update_parent_array();
            eid = 1;
            se_index = 0;
            update_delete_element_button_handlers();
            set_editor_dirty();
        });
    }
});
// End of jQuery wrapper

// Form change checker functions
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
