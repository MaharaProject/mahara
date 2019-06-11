<input type="hidden" name="accesslist" value="">
<div class="card card-secondary view-container" id="editaccesswrap"
    data-viewtype="{{$viewtype}}"
    data-user-roles='{{$userroles}}'
    data-group-roles='{{$grouproles}}' >
    {{if $viewtype == "profile" }}
        <h2 class="card-header">{{str tag=Profile section=view}}</h2>
    {{/if}}

    <table id="accesslisttable" class="fullwidth accesslists table">
        <thead>
            <tr class="accesslist-head th-has-shared">
                <th></th>
                <th>{{str tag=sharedwith section=view}}</th>
                <th class="text-center">{{str tag=From}}</th>
                <th class="text-center">{{str tag=To}}</th>
                {{if  $viewtype !== "profile" }}
                <th class="text-center commentcolumn"><div class="th-shared-wrap"><span class="th-shared-heading">{{str tag=comments section=view}}</span> <span class="th-shared-title">{{str tag=allow section=view}}</span></div></th>
                <th class="text-center commentcolumn"><span class="sr-only">{{str tag=Comments}}</span> <span class="th-shared-title">{{str tag=moderate section=view}}</span></th>
                {{/if}}
            </tr>
        </thead>
        <tbody id ="accesslistitems" data-id="accesslistitems">
        </tbody>
    </table>
</div>

<script type="text/x-tmpl" id="selectoption-template">
{% if (o.id) { %}<option value="{%=o.id%}" selected></option>{% } %}
</script>

<script type="text/x-tmpl" id="roles-template">
    <option value="" selected>{%=o.defaultText%}</option>
    {% for (var i=0; i<o.roles.length; i++) { %}
         <option value="{%=o.roles[i].name%}">{%=o.roles[i].display%}</option>
    {% } %}
</script>

<script type="text/x-tmpl" id="row-template">
<tr id="row-{%=o.id%}" data-id="{%=o.id%}">
    <td class="text-center with-icon tiny">
        <a class="{% if (o.presets.locked || o.presets.empty) { %}icon-placeholder{% } %} text-block" data-bind="remove-share" href="#" id="remove-share{%=o.id%}">
            <span class="text-danger icon icon-lg icon-trash" role="presentation" aria-hidden="true"></span>
            <span class="sr-only">{%={{jstr tag=remove section=view}}%}</span>
        </a>
    </td>
    <td>
        <div class="dropdown-group dropdown-single-option">
            <span class="picker input-short">
                <input data-settype="true" type="hidden" id="typehidden-{%=o.id%}" value="{%=o.presets.type%}" name="accesslist[{%=o.id%}][type]" />
                <select id="type-{%=o.id%}" name="accesslist[{%=o.id%}][searchtype]" class="js-share-type form-control input-small select" {% if (o.presets.locked) { %}disabled{% } %}>
                    <option data-type="" {% if (!o.presets.type) { %}selected{% } %} value="">{%={{jstr tag=sharewith section=view}}%}</option>

                    <optgroup label="{%={{jstr tag=searchfor section=view}}%}">
                        <option data-search-option="true" id="friend" value="friend"{% if (o.presets.type == "friend") { %} selected{% } %}>{{str tag=friend section=view}}</option>
                        <option data-search-option="true" id="group" value="group"{% if (o.presets.type == "group") { %} selected{% } %}>{{str tag=group section=view}}</option>
                        <option data-search-option="true" id="user" value="user"{% if (o.presets.type == "user") { %} selected{% } %}>{{str tag=user section=view}}</option>
                    </optgroup>

                    <optgroup label="{%={{jstr tag=general section=view}}%}" id="potentialpresetitemssharewith">
                        {% for (var i=0; i<o.shareoptions.general.length; i++) { %}
                            <option value="{%=o.shareoptions.general[i].id%}"{% if (o.presets.type == o.shareoptions.general[i].id) { %} selected{% } %} {% if (o.shareoptions.general[i].locked) { %} disabled{% } %}>{%=o.shareoptions.general[i].name%}</option>
                        {% } %}
                    </optgroup>

                    <optgroup label="{%={{jstr tag=institutions section=view}}%}" id="potentialpresetitemsinstitutions">
                        {% for (var i=0; i<o.shareoptions.institutions.length; i++) { %}
                            <option data-type="institution" value="{%=o.shareoptions.institutions[i].id%}"{% if (o.presets.id == o.shareoptions.institutions[i].id && o.presets.type == 'institution') { %} selected{% } %}>{%=o.shareoptions.institutions[i].name%}</option>
                        {% } %}
                    </optgroup>
                    <optgroup label="{%={{jstr tag=groups section=view}}%}" id="potentialpresetitemsgroups">
                        {% for (var i=0; i<o.shareoptions.myGroups.length; i++) { %}
                            <option data-type="group" value="{%=o.shareoptions.myGroups[i].id%}"{% if (o.presets.id == o.shareoptions.myGroups[i].id && o.presets.type == 'group') { %} selected{% } %}>
                            {%=o.shareoptions.myGroups[i].name%}
                            </option>
                        {% } %}
                    </optgroup>
                </select>
            </span>
            {% if(o.presets.empty) { %}<p class="table-help-text">{%={{jstr tag=whosharewith section=view}}%}</p>{% } %}
            <div class="d-none picker input-short" data-select-wrapper="true">
                <select id="hidden-user-search-[{%=o.id%}]" name="accesslist[{%=o.id%}][id]" class=" select js-select2-search">
                    {% if (o.presets.id) { %}<option value="{%=o.presets.id%}">{%=o.presets.name%}</option>{% } %}
                </select>
            </div>
            <span class="picker input-short{% if (!(o.presets.type == 'group' || o.presets.type == 'user')) { %} d-none {% } %}">
                <select data-roles="grouproles" name="accesslist[{%=o.id%}][role]" class="form-control input-small select">
                    {% if (o.presets.type == 'group' || o.presets.type == 'user') { %}
                        <option value="" >{%=o.defaultText%}</option>
                        {% for (var i=0; i<o.roles.length; i++) { %}
                             <option value="{%=o.roles[i].name%}" {% if (o.presets.role == o.roles[i].name) { %} selected {% } %}>{%=o.roles[i].display%}</option>
                        {% } %}
                    {% } %}
                </select>
            </span>
        </div>
    </td>
    <td class="text-center js-date short" data-name='from'>
        <div class="date-picker js-date-picker js-hide-empty {% if (o.presets.empty) { %}d-none{% } %}">
            <div class="hasDatepickerwrapperacl">
                <input type="text" id="accesslist{%=o.id%}_startdate" name="accesslist[{%=o.id%}][startdate]" class="form-control float-left datetimepicker-input" data-setmin="true" setdatatarget="to" value="{%=o.presets.startdate%}" aria-label="{{str tag=element.calendar.datefrom section=pieforms}} ({{str tag='element.calendar.format.arialabel' section='pieforms'}})" {% if (o.presets.locked) { %}disabled{% } %} data-toggle="datetimepicker" data-target="#accesslist{%=o.id%}_startdate" autocomplete="off">
            </div>
        </div>
    </td>
    <td class="text-center js-date short" data-name='to'>
        <div class="date-picker js-date-picker js-hide-empty {% if (o.presets.empty) { %}d-none{% } %}">
            <div class="hasDatepickerwrapperacl">
                <input type="text" id="accesslist{%=o.id%}_stopdate" name="accesslist[{%=o.id%}][stopdate]" class="form-control float-left datetimepicker-input" data-setmax="true" setdatatarget="from" value="{%=o.presets.stopdate%}" aria-label="{{str tag=element.calendar.dateto section=pieforms}} ({{str tag='element.calendar.format.arialabel' section='pieforms'}})" value="{%=o.presets.stopdate%}" {% if (o.presets.locked) { %}disabled{% } %} data-toggle="datetimepicker" data-target="#accesslist{%=o.id%}_stopdate" autocomplete="off">
            </div>
        </div>
    </td>
    {% if (o.viewtype !== "profile") { %}
        <td class="text-center tiny commentcolumn">
            <input value="1" name="accesslist[{%=o.id%}][allowcomments]" class="allow-comments-checkbox form-check js-hide-empty {% if (o.presets.empty) { %}d-none{% } %}" type="checkbox" {% if (o.presets.allowcomments == "1") { %}checked{% } else { %}{% } %} {% if (o.presets.locked) { %}disabled{% } %}>
        </td>
        <td class="text-center tiny commentcolumn">
            <input value="1" name="accesslist[{%=o.id%}][approvecomments]" class="moderate-comments-checkbox form-check js-hide-empty {% if (o.presets.empty) { %}d-none{% } %}" type="checkbox" {% if (o.presets.approvecomments == "1" && o.presets.allowcomments == "1") { %}checked{% } else { %}{% } %}  {% if (o.presets.locked) { %}disabled{% } %}>
        </td>
    {% } %}

</tr>
</script>

<script>
var count = 0;

jQuery(function($) {
"use strict";

    $(function() {

        // For some reasons, form validation for required select element generated by select2js
        // does not work properly on Microsoft Edge
        // This is a workaround
        // It will check if the required select is not empty and remove the attribute 'required'
        $j('#{{$formname}}_submit').on("click", function(e) {
            $j('#{{$formname}} select:required').each(function() {
                if ($j(this).val()) {
                    $j(this).prop("required", false);
                    $j(this).parent().parent().find('div.errmsg').remove();
                }
                else {
                    var b = $j(this).attr("data-type");
                    $j('#messages').html('<div class="alert alert-danger"><div>' + get_string('errorprocessingform', 'mahara') + '</div></div>');
                    if ($j(this).parent().parent().find('div.errmsg').length === 0) {
                        $j(this).parent().parent().append('<div class="errmsg"><span class="input-short-error"></span><span id="' + $j(this).prop('id') + '_error">' + get_string('rule.required.required', 'pieforms') + '</span></div>');
                    }
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });
        // Remove 'required' on cancel
        $j('#cancel_{{$formname}}_submit').on("click", function(e) {
            $j('#{{$formname}} select:required').each(function() {
                $j(this).prop("required", false);
            });
        });

        function setDatePicker(target) {
            var loc = '{{strstr(current_language(), '.', true)}}'; // Get current langauge to use for locale
            target.each(function() {
                // ugly fix for open issue in tempusdominus bootstrap lib not getting the value from html tag
                // https://github.com/tempusdominus/bootstrap-4/issues/126
                var value = $(this).attr('value');
                value = value == '' ? null : value;
                $(this).datetimepicker({
                    format: "{{str(tag='pieform_calendar_dateformat' section='langconfig')|pieform_element_calendar_convert_dateformat}} {{str(tag='pieform_calendar_timeformat' section='langconfig')|pieform_element_calendar_convert_timeformat}}",
                    date: value,
                    useCurrent: false,
                    locale: loc,
                    buttons: {
                        showClear: true,
                        showToday: true,
                    },
                    tooltips: {{$datepickertooltips|safe}},
                    icons: {
                        time: "icon icon-clock-o",
                        date: "icon icon-calendar",
                        up: "icon icon-arrow-up",
                        down: "icon icon-arrow-down",
                        previous: "icon icon-chevron-left",
                        next: "icon icon-chevron-right",
                        close: "icon icon-times",
                        clear: "icon icon-trash",
                        today: "icon icon-crosshairs",
                    },
                });
                $(this).val(value);
                $(this).on('hide.datetimepicker', function(selectedDate) {
                    if (selectedDate !== "") {
                        formchangemanager.setFormStateById('{{$formname}}', FORM_CHANGED);
                    }
                });
            });
        }

        function formatSelect2Results (data) {
            if (data.loading) {
                return data.text;
            }

            var markup;

            // Need to know which row
            if (data.grouptype !== undefined) {
                markup = data.name;
            }
            else {
                markup =
                '<img class="select2-user-icon" src="' + config.wwwroot + 'thumb.php?type=profileicon&maxwidth=40&maxheight=40&id=' + data.id + '" />' +
                '<span>' + data.name + '</span>';
            }
            return markup;
        }

        function formatSelect2Selected (data) {
            if (data._resultId !== undefined) {
                formchangemanager.setFormStateById('{{$formname}}', FORM_CHANGED);
            }
            if (data.grouptype !== undefined) {
                return '<span data-grouptype="'+ data.grouptype + '">'+ data.name + '</span>';
            }
            else {
                return data.name || data.text;
            }
        }

        function attachSelect2Search(object) {
            var self = object;

            $(self).select2({
                placeholder: {{jstr tag=search section=view}},
                ajax: {
                    url: "access.json.php",
                    dataType: 'json',
                    delay: 250,
                    type: 'POST',
                    data: function (params) {
                        return {
                            'type' : $(self).attr('data-type'),
                            'query': params.term, // search term
                            'offset': 0,
                            'limit': 10,
                            'page': params.page || 0,
                            'sesskey': config.sesskey
                        };
                    },
                    processResults: function (data, page) {
                        // parse the results into the format expected by Select2.
                        // since we are using custom formatting functions we do not need to
                        // alter the remote JSON data
                        if (data.message.roles) {
                            $(self).attr('data-roles', JSON.stringify(data.message.roles));
                        }

                        return {
                            results: (data.message.count > 0) ? data.message.data : [],
                            pagination: {
                                more: data.message.more
                            }
                        };
                    },
                    cache: true
                  },
                escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
                templateResult: formatSelect2Results,
                templateSelection: formatSelect2Selected,
                maximumSelectionLength: 20,
                language: {
                    errorLoading: function () {
                        return {{jstr tag=errorLoading section=mahara}};
                    },
                    inputTooShort: function () {
                        return {{jstr tag=inputTooShort section=mahara}};
                    },
                    inputTooLong: function () {
                        return {{jstr tag=inputTooLong section=mahara}};
                    },
                    loadingMore: function () {
                        return {{jstr tag=loadingMore section=mahara}};
                    },
                    maximumSelected: function () {
                        return {{jstr tag=maximumSelected section=mahara}};
                    },
                    noResults: function () {
                        return {{jstr tag=noResults section=mahara}};
                    },
                    searching: function () {
                        return {{jstr tag=searching section=mahara}};
                    }
                }
            });

            $(self).on("select2:select", function (e) {
                if($(self).attr('data-roles').length > 0) {
                    showRoleSelect(e, self);
                }
                formchangemanager.setFormStateById('{{$formname}}', FORM_CHANGED);
            });

            /* Select2 lib does not hide the element with "style=display:none"
             * anymore. see https://github.com/select2/select2/issues/3065
             * We need the line below to let Chome know the text is not visible
             * and behat tests won't break
             */
            $('.select2-hidden-accessible').hide();
        }

        /*
         * Render all existing rules into our table
         */
        function renderAccessList(shareoptions) {
            var accesslist = {{$accesslist|safe}},
                i;

            accesslist = accesslist.sort(function(a, b) {
                if (a.locked === true) {
                    return 0;
                }
                return 1;
            });

            if (accesslist.length > 0) {
                for (i = 0; i < accesslist.length; i = i + 1) {
                    addNewRow(shareoptions, accesslist[i]);
                }
            }

            // render empty row
            addNewRow(shareoptions, {empty: true});
        }

        function addNewRow(shareoptions, presets) {
            if (presets === undefined) {
                presets = {};
            }

            var data,
                lastrow,
                id,
                viewtype = $('[data-viewtype]').attr('data-viewtype'),
                roles,
                defaultText,
                grouproles;

            if($('#accesslistitems tr').length > 0){
                lastrow = $('#accesslistitems tr:last-child');
                id = parseInt(lastrow.attr('data-id'), 10) + 1;
            }
            else {
                id = 0;
            }

            if (!presets.empty) {
                if (presets.type == 'user') {
                    roles = JSON.parse($('[data-user-roles]').attr('data-user-roles'));
                    defaultText = {{jstr tag=nospecialrole section=view}};
                }
                else {
                    // group
                    grouproles = JSON.parse($('[data-group-roles]').attr('data-group-roles'));
                    defaultText = {{jstr tag=everyoneingroup section=view}};
                    if (presets.grouptype == 'course') {
                        roles = grouproles.course;
                    }
                    else {
                        roles = grouproles.standard;
                    }
                }
            }
            data = {
                id: id,
                shareoptions: shareoptions,
                presets: presets,
                viewtype: viewtype,
                roles: roles,
                defaultText: defaultText,
            };

            $('#accesslistitems').append(tmpl("row-template", data));

            attachEventListeners(id);
        }

        function attachEventListeners(id) {
            var newrow = $('#accesslistitems').find('[data-id="' + id + '"]');
            attachShareTypeEvent(newrow);
            setDatePicker($(newrow).find('.js-date-picker > div > input'));
            attachSelect2Search($(newrow).find('.js-select2-search'));
            attachCommentEvents($(newrow));
            onChange($(newrow));

            // Remove a table row when the remove button is clicked
            $('[data-bind="remove-share"]').on('click', function(e) {
                e.preventDefault();
                if (!$(this).hasClass('icon-placeholder')) {
                    clearRow(this);
                    formchangemanager.setFormStateById('{{$formname}}', FORM_CHANGED);
                }
            });
        }

        function onChange(row) {
            row.find('.js-share-type').on('change', function(e) {
                var remove = row.find('[data-bind="remove-share"]'),
                    helpText = row.find('.table-help-text');

                if (remove.hasClass('icon-placeholder')) {
                    if (helpText.length > 0) {
                        helpText.remove();
                    }
                    remove.removeClass('icon-placeholder js-empty');
                    row.find('.js-hide-empty').removeClass('d-none');
                    addNewRow(shareoptions, {empty: true});
                }
                formchangemanager.setFormStateById('{{$formname}}', FORM_CHANGED);
            });
        }

        function clearRow(self) {
            $(self).closest('tr').remove();

            if ($('#accesslistitems tr').length < 1) {
                addNewRow(shareoptions, {empty: true});
            }
        }

        /*
         * Construct data for share with dropdown
         *
         * @return Array | array of objects
         */
        function shareWithOptions() {
            var general = {{$potentialpresets|safe}},
                institutions = {{$myinstitutions|safe}},
                allGroups = {{$allgroups|safe}},
                myGroups = {{$mygroups|safe}},
                i,
                results = {
                    general: [],
                    institutions: [],
                    allGroups: [],
                    myGroups: []
                },
                item;

            for (i = 0; i < general.length; i = i + 1 ) {
                item = general[i];

                results.general[i] = {
                    'id':  item.id,
                    'name': item.name,
                    'selected': item.preset,
                    'locked': item.locked
                };
            }

            for (i = 0; i < institutions.length; i = i + 1 ) {
                item = institutions[i];

                results.institutions[i] = {
                    'id':  item.id,
                    'name': item.name,
                    'selected': item.preset
                };
            }

            item = allGroups;
            results.allGroups = {
                'id':  item.id,
                'name': item.name,
                'selected': item.preset
            };

            for (i = 0; i < myGroups.length; i = i + 1 ) {
                item = myGroups[i];

                results.myGroups[i] = {
                    'id':  item.id,
                    'name': item.name,
                    'selected': item.preset
                };
            }

            return results;
        }


        /*
         * Show the role select when a group is selected
         * @param e | event, self | select Object
         */
        function showRoleSelect(e, self) {

            var roles = JSON.parse($(self).attr('data-roles')),
                data,
                id = $(self).closest('tr').attr('data-id'),
                select = $(self).closest('.dropdown-group').find('[data-roles="grouproles"]');
            if ($(self).attr('data-type') == 'group') {
                var grouptype = $(self).parent().find('[data-grouptype]').attr('data-grouptype'),
                    defaultText = {{jstr tag=everyoneingroup section=view}};
                data = {
                    id: id,
                    defaultText: defaultText,
                    roles: roles[grouptype]
                };
            }
            else {
                var defaultText = {{jstr tag=nospecialrole section=view}};
                data = {
                    id: id,
                    defaultText: defaultText,
                    roles: roles
                };
            }
            select.html(tmpl("roles-template", data));
            select.prop('disabled', false).parent().removeClass('d-none');
        }

        function hideRoleSelect(self) {
            var roleSelect = $(self).closest('.dropdown-group').find('[data-roles="grouproles"]');
            roleSelect.prop('disabled', true).empty().parent().addClass('d-none');
        }

        /*
         * When a 'search option' is picked in the share with dropdown, show ID picker (search field)
         * When a 'id' option is picked, hide ID picker and set id on ID picker field
         *
         * @params Self | DOM object, isPreset | boolean (clear the search select if false)
         */
        function setIDField(self, isPreset) {

            var selected = $(self).find("option:selected"),
                val = selected.val(),
                searchoption = selected.attr("data-search-option"),
                idFieldWrapper = $(self).closest('td').find('[data-select-wrapper="true"]'),
                idField = idFieldWrapper.find('.js-select2-search');

            if (!isPreset) {
                resetIdField(idField);
            }

            // set data-type as a param on select related fields
            idFieldWrapper.find('> *').attr('data-type', val);

            if (searchoption) {
                idFieldWrapper.removeClass('d-none');
                idField.prop('required', true);
            }
            else {
                idFieldWrapper.addClass('d-none');
                idField.html(tmpl("selectoption-template", {id: val}));
                idField.prop('required', false);
            }
        }

        function resetIdField(idField) {
            idField.select2('val', '');
            idField.attr('data-roles','');

            // Reset and remove selected option if set manually
            if (idField.find('option:selected') !== undefined) {
                idField.find('option:selected').prop('selected', false);
                idField.html('');
            }
        }

        function setTypeField(self) {
            var value = $(self).val(),
                selectedOption =  $(self).find('option:selected');

            // Friend is a type of user, so we need a special case so we can still search for friends
            // but the backend will recieve 'user' as the type attribute
            if (value === 'friend') {
                value = 'user';
            }

            if (selectedOption.attr('data-type') !== undefined) {
                value =  selectedOption.attr('data-type');
            }

            $(self).siblings('[data-settype]').val(value);
        }


        function attachShareTypeEvent(scope) {

            setIDField(scope.find(".js-share-type"), true);

            // Make search box visible only when friends, groups, or users is selected
            scope.find(".js-share-type").on('change', function() {
                setIDField(this, false);
                setTypeField(this);
                hideRoleSelect(this);
            });
        }

        function attachCommentEvents(newrow) {
            if ($('#{{$formname}}_allowcomments').prop('checked') === true) {
                // Hide the per row comment options
                newrow.find('.commentcolumn').addClass('d-none');
            }

            var allowcommentsbox = newrow.find('.allow-comments-checkbox');
            var moderatecommentsbox = newrow.find('.moderate-comments-checkbox');
            // TODO: could probably use a function to eliminate the duplicate code between
            // here and the change event handler
            if (allowcommentsbox.prop('checked') == false) {
                moderatecommentsbox.prop("disabled", true).prop("checked", false);
            }
            else {
                moderatecommentsbox.prop('disabled', false);
            }
            allowcommentsbox.on('change', function() {
                if ($(this).prop('checked') == false) {
                    moderatecommentsbox.prop("disabled", true).prop("checked", false);
                }
                else {
                    moderatecommentsbox.prop('disabled', false);
                }
            });
        }

        var rows = $('#accesslistitems > tr'),
            i,
            shareoptions = shareWithOptions(rows[i]);

        renderAccessList(shareoptions);
        setDatePicker($( ".js-date-picker > div > input" ));
    });
});

</script>
