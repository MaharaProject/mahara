/**
 *
 * @package    mahara
 * @subpackage artefact-module
 * @author     Alexander Del Ponte
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

$(document).ready(function () {

    jQuery.fn.Piertable = function(options, callback) {
        let Me = this;
        this.$tableElement = $(this[0]);
        this.options = options || {};
        this.dataTable = this.$tableElement.DataTable(options);
        this.clickHandled = false;
        this.selectedRow = null;

        this.searchElementId = 0;

        this.getDataTable = function() {
            return Me.dataTable;
        };

        this.getDefaultEditInfo = function() {
            return {
                editMode: false,
                sendingData: false,
                $editElement: null,
                editOptions: null,
                editCell: null,
                editRow: null
            };
        };

        this.editInfo = this.getDefaultEditInfo();

        this.attachEditOptionsToColumns = function(options) {
            $.each(options.fields, function(key, fieldOptions) {
                fieldOptions.isFixable = fieldOptions.isFixable || false;
                $(Me.dataTable.column(fieldOptions.columnName + ':name').header()).data('editOptions', fieldOptions);
            });
        };

        this.getRowOfTableElement = function(tableElement) {
            let row = null;

            if ($(tableElement).closest('tr').hasClass('child')) {
                row = $(tableElement).closest('tr').prev();
            }
            else {
                row = $(tableElement).closest('tr');
            }
            return Me.dataTable.row(row);
        };

        this.createEditTextElement = function(td) {
            return $('<input type="text" value="' + $(td).html() + '" />');
        };

        this.createEditSelectElement = function(rowData, editOptions) {
            let arrayValueText = [];

            if (editOptions.htmlInputElement.options.inputOptionsDataSourceIsRow) {
                arrayValueText = rowData.editOptions[editOptions.name].arrayValueText;
            }
            else {
                arrayValueText = editOptions.htmlInputElement.options.arrayValueText;
            }

            let $selectElement = $('<select class="form-control form-control-sm">');
            $(arrayValueText).each(function() {
                $selectElement.append($('<option>').attr('value', this.value).html(this.text));
            });

            $('option', $selectElement).filter(function() {
                return this.value == rowData[editOptions.valueField];
            }).attr('selected', true);

            return $selectElement;
        };

        this.editStart = function(td) {
            let cell = Me.dataTable.cell(td);
            let column = Me.dataTable.column(cell.index().column);
            let row = Me.dataTable.row(cell.index().row);

            let editOptions = $(column.header()).data('editOptions');
            if (editOptions === undefined) {
                return;
            }

            if (row.data().isEditable || row.data().isFixable && editOptions.isFixable) {
                let $editElement = null;
                if (typeof editOptions.htmlInputElement === 'function') {
                    $editElement = editOptions.htmlInputElement(row.data());
                }
                else {
                    switch (editOptions.htmlInputElement.type) {
                        case 'text':
                            $editElement = Me.createEditTextElement(td);
                            break;
                        case 'select':
                            $editElement = Me.createEditSelectElement(row.data(), editOptions);
                            break;
                        case 'check':
                            break;
                        case 'radio':
                            break;
                        case 'number':
                            break;
                        case 'date':
                            break;
                    }
                }
                $(td).html($editElement);

                $editElement.on('change', function() {
                    Me.editOk($(this).val());
                });

                Me.editInfo = {
                    editMode: true,
                    sendingData: false,
                    $editElement: $editElement,
                    editOptions: editOptions,
                    editCell: cell,
                    editRow: row
                };

                $editElement.select();
            }
        };

        this.editEnd = function() {
            let $editElement = Me.editInfo.$editElement;
            if ($editElement) {
                $editElement.off();
                $editElement.remove();
            }
            Me.editInfo = Me.getDefaultEditInfo();
        };

        this.editCancel = function() {
            Me.editInfo.editRow.invalidate().draw(false);
            Me.editEnd();
        };

        this.editOk = function (value) {
            if (Me.editInfo.editMode) {
                // let row = Me.dataTable.row(Me.editInfo.editCell.index().row);
                let row = Me.editInfo.editRow;
                let backupRowData = JSON.stringify(row.data()); // We have to because invalidate doesn't always work

                if (Me.editInfo.editOptions.valueField === undefined) {
                    row.data()[Me.options.columns[Me.editInfo.editCell.index().column].data] = value;
                }
                else {
                    row.data()[Me.editInfo.editOptions.valueField] = value;
                }

                Me.sendData(row.data(), function(success, newRowData) {
                    if (success) {
                        // Give backend the chance to update the row edit options
                        if (newRowData.editOptions === undefined) {
                            newRowData.editOptions = JSON.parse(backupRowData).editOptions;
                        }
                        row.data(newRowData).draw(false);
                        Me.editEnd();
                    }
                    else {
                        if (newRowData === undefined) {
                            row.data(JSON.parse(backupRowData));
                        }
                        else {
                            row.data(newRowData);
                        }
                        Me.editCancel();
                    }
                });
            }
        };

        this.handleEditingEventExternally = function(e) {
            e.stopImmediatePropagation();

            if (Me.editInfo.editMode) {
                Me.editCancel();
            }
            else {
                Me.selectRowByTableElement(e.target);
            }
        };

        this.jumpToRow = function(row) {
            let rowPos = Me.dataTable.table().rows({order: 'current', search: 'applied'})[0].indexOf(row.index());
            let page = Math.floor(rowPos / Me.dataTable.page.info().length);
            Me.dataTable.table().page(page).draw(false);
        };

        this.selectRow = function(row) {
            Me.selectedRow = row;
            Me.dataTable.$('tr.selected').removeClass('selected');
            $(row.node()).addClass('selected');

            if (Me.options.stateSave) {
                Me.dataTable.state.save();
            }
        };

        this.selectRowByTableElement = function(tableElement) {
            Me.selectRow(Me.getRowOfTableElement(tableElement));
        };

        this.createEventHandlers = function() {

            $(document).on('click', function(e) {
                if (Me.clickHandled === false) {
                    if (Me.editInfo.editMode && Me.editInfo.sendingData === false) {
                        if (e.target !== Me.editInfo.$editElement.get(0)) {
                            Me.editCancel();
                        }
                    }
                }
                else {
                    Me.clickHandled = false;
                }
            });

            $(document).on('keydown', function(e) {
                if (Me.editInfo.editMode) {
                    if (e.keyCode === 27) {
                        Me.editCancel();
                    }
                }
            });

            this.$tableElement.on('click', 'tbody td', function(e) {
                if (Me.editInfo.editMode === false) {
                    Me.selectRowByTableElement(this);

                    if ($(this).hasClass('child') === false) {
                        Me.clickHandled = true;
                        Me.editStart(this);
                    }
                }
            });

            this.$tableElement.on('click', '.dtr-details li', function(e) {
                if (Me.editInfo.editMode === false) {
                    Me.selectRowByTableElement(this);

                    Me.clickHandled = true;
                    Me.editStart($('.dtr-data', this));
                }
            });

            this.$tableElement.on('contextmenu', 'tr', function(e) {
                Me.selectRowByTableElement(this);
            });

            this.dataTable.on('responsive-resize', function(e, datatable, columns) {
                if (Me.editInfo.editMode) {
                    Me.editCancel();
                }
            });

            this.$tableElement.on('init.dt', function(e, settings, json) {
                Me.dataTable.on('stateSaveParams.dt', function(e, settings, data) {
                    if (Me.options.quickFilter && $('#' + Me.$tableElement.prop('id') + '-filter').length > 0) {
                        let checkedSearchElements = [];
                        $('.search-element input[type=radio]:checked').each(function(index, inputElement) {
                            checkedSearchElements.push({
                                id: inputElement.parentNode.id,
                                inputElementSearchValue: $(inputElement).next('input, select').val()
                            });
                        });
                        data.quickFilter = {checkedSearchElements: checkedSearchElements};
                    }
                    if (Me.selectedRow) {
                        data.selectedRowId = Me.selectedRow.index();
                    }
                });

                let state = (Me.options.stateSave ? Me.dataTable.state.loaded() : null);

                if (Me.options.quickFilter) {
                    Me.createQuickSearch();

                    if (state && state.quickFilter) {
                        $.each(state.quickFilter.checkedSearchElements, function(index, searchElement) {
                            $('#' + searchElement.id).find('input:radio').prop('checked', true).next('input, select').val(searchElement.inputElementSearchValue);
                        });
                        Me.setAllOffButton();
                    }
                }

                if (state && state.selectedRowId) {
                    selectedRow = Me.dataTable.row(state.selectedRowId);
                    Me.selectRow(selectedRow);
                    if ($(selectedRow.node()).is(':visible')) {
                        Me.jumpToRow(selectedRow);
                    }
                }
            });
        };

        this.sendData = function(rowData, callback, url) {
            url = url || Me.dataTable.ajax.url();
            Me.editInfo.sendingData = true;
            $.ajax({
                url: url,
                global: false,
                context: this,
                type: 'POST',
                datatype: 'JSON',
                data: rowData,
                async: true
            })
                .done(function (jsonObj) {
                    if (jsonObj.error) {
                        if (jsonObj.message.hasOwnProperty('data')) {
                            Me.displayMessageTemp(jsonObj.message.message, 'error', true);
                            callback(false, jsonObj.message.data);
                        }
                        else {
                            Me.displayMessageTemp(jsonObj.message.message, 'error', true);
                            callback(false);
                        }
                    }
                    else {
                        if (jsonObj.message.message !== undefined) {
                            Me.displayMessageTemp(jsonObj.message.message, 'info', true);
                        }
                        callback(true, jsonObj.message.data);
                    }
                });
        };

        // Set settings to true for dynamic display length according to the amount of text to read
        this.displayMessageTemp = function(message, type, settings) {
            if (settings === true) {
                settings = {delay: 1000 + message.length * 30, fadeOut: 1000};
            }
            else {
                settings = settings || {delay: 4000, fadeOut: 1000};
            }

            displayMessage(message, type);
            $('#messages div').last().delay(settings.delay).fadeOut(settings.fadeOut, function() {
                $(this).remove();
            });
        }

        this.setAllOffButton = function() {
            let quickfilterIsActive = ($('.category-filter-off').length - $('.category-filter-off:checked').length > 0);
            $('#all-off-button').prop('disabled', !quickfilterIsActive).toggleClass('quickfilter-active', quickfilterIsActive);
        };

        this.createSearchElement = function(name, title, checked, onClickFunction, inputClasses) {
            checked = checked || false;
            onClickFunction = onClickFunction || null;
            inputClasses = (typeof inputClasses === 'undefined' ? '' : ' class="' + inputClasses + '" ');

            let radioElement = $('<div id="search-element-' + this.searchElementId.toString() + '" class="search-element">');
            this.searchElementId += 1;
            let inputElement = $('<input ' + inputClasses + 'type="radio" name="' + name + '"> ' + title + ' </input>');
            inputElement.prop('checked', checked).appendTo(radioElement);

            if (onClickFunction) {
                radioElement.on('click', onClickFunction);
            }
            return radioElement;
        };

        this.createAutoQuickSearchItem = function(column, offTitle) {
            let select = $('<select><option value="">' + offTitle + '</option></select>')
                .on( 'change', function () {
                    let val = $.fn.DataTable.util.escapeRegex(
                        $(this).val()
                    );
                    column
                        .search( val ? '^'+val+'$' : '', true, false )
                        .draw();
                } );

            column.data().unique().sort().each(function(d, j) {
                let displayText = d;
                if (d) {
                    if (d.length > 30) {
                        displayText = displayText.substr(0, 30) + '...';
                    }
                    select.append( '<option value="' + d + '" title="' + d + '">' + displayText + '</option>' )
                }
            });
            return select;
        };

        this.createQuickSearch = function() {
            let offTitle = this.options.quickFilter.offTitle;
            let filterElement = $('<div id="' + this.prop('id') + '-filter" class="filter"></div>');
            let filterTitlebarElement = $('<div class="filter-titlebar">').html('<div class="icon icon-chevron-circle-up text-muted"></div> Quick Filter ');
            let allOffButtonElement = $('<button type="button" id="all-off-button" class="btn btn-secondary btn-sm icon icon-filter"></button>').prop('disabled', true);
            let categoryContainer = $('<div class="row small">');

            // We need this wrapper for running the animation which doesn't work directly on flexbox elements
            let categoryContainerWrapper = $('<div class="container">');
            categoryContainer.appendTo(categoryContainerWrapper);

            allOffButtonElement.appendTo(filterTitlebarElement);
            filterTitlebarElement.appendTo(filterElement);

            filterTitlebarElement.on('click', function(e) {
                if (e.target !== allOffButtonElement[0]) {
                    categoryContainerWrapper.slideToggle();
                    filterTitlebarElement.find('.icon').toggleClass('icon-chevron-circle-up icon-chevron-circle-down');
                }
            });

            $.each(this.options.quickFilter.categories, function(index, category) {
                let categoryElement = $('<div class="search-category"></div>').html($('<div>').html($('<strong>').html(category.title)));
                let radioElement = $('<div class="radio-group well">');

                let radioOffElement = Me.createSearchElement(
                    category.title,
                    offTitle,
                    true,
                    function() {
                    Me.dataTable.column(category.columnName + ':name').search('').draw();
                    $(this).find('input:radio').prop('checked', true);
                    Me.setAllOffButton();
                    },
                    'category-filter-off'
                    ).appendTo(radioElement);

                allOffButtonElement.on('click', function(e) {
                    radioOffElement.trigger('click');
                });

                $.each(category.items, function(index, item) {
                    let searchElement = Me.createSearchElement(category.title, item.title);
                    let inputElement = null;

                    switch (true) {
                        case item.hasOwnProperty('doSearch'):
                            searchElement.on('click', function() {
                                item.doSearch(Me.dataTable);
                            });
                            break;
                        case item.hasOwnProperty('getHtmlElement'):
                            inputElement = item.getHtmlElement(Me.dataTable).addClass('form-control form-control-sm');
                            inputElement.appendTo(searchElement);
                            searchElement.on('click', function() {
                                inputElement.trigger('change');
                            });
                            break;
                        default:
                            inputElement = Me.createAutoQuickSearchItem(Me.dataTable.column(category.columnName + ':name'), offTitle).addClass('form-control form-control-sm');
                            inputElement.appendTo(searchElement);
                            searchElement.on('click', function() {
                                inputElement.trigger('change');
                            });
                    }
                    searchElement.appendTo(radioElement);
                    searchElement.on('click', function() {
                        $(this).find('input:radio').prop('checked', true);
                        Me.setAllOffButton();
                    });
                });
                radioElement.appendTo(categoryElement);
                categoryElement.appendTo(categoryContainer);
            });
            categoryContainerWrapper.appendTo(filterElement);
            filterElement.prependTo($('div.toolbar'));
        };

        Me.attachEditOptionsToColumns(options);
        Me.createEventHandlers();

        if (typeof callback === 'function') { // make sure the callback is a function
            callback.call(Me); // brings the scope to the callback
        }
        return Me;
    };
});
