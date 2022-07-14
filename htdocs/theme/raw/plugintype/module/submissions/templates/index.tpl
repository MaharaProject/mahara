{include file="header.tpl"}

<table id="submissions" class="table table-striped responsive nowrap">
    <thead>
    <tr>
        <th class="noExport">SubmissionId</th>
        <th class="noExport">EvaluationId</th>
        {if empty($options.groupid)}
            <th>{str tag='Group' section='module.submissions'}</th>
            <th>{str tag='Role' section='module.submissions'}</th>
        {/if}
        <th>{str tag='Name' section='module.submissions'}</th>
        <th>{str tag='PreferredName' section='module.submissions'}</th>
        <th>{str tag='Portfolio' section='module.submissions'}</th>
        <th>{str tag='Date' section='module.submissions'}</th>
        <th>{str tag='Task' section='module.submissions'}</th>
        <th>{str tag='Evaluator' section='module.submissions'}</th>
        <th class="noPdfExport">{str tag='Feedback' section='module.submissions'}</th>
        <th>{str tag='Rating' section='module.submissions'}</th>
        <th>{str tag='Result' section='module.submissions'}</th>
        <th>{str tag='State' section='module.submissions'}</th>
    </tr>
    </thead>
</table>

{include file="pagemodal.tpl"}
{include file="footer.tpl"}

<script type="application/javascript">

    $(document).ready(function () {

        var submissionsTable = $("#submissions").Piertable({
            ajax: {
                baseUrlParams: [{if $options.groupid}'group={$options.groupid}', {/if}'sesskey={$SESSKEY}'].join('&'),
                url: config.wwwroot + 'module/submissions/index.json.php?' + [{if $options.groupid}'group={$options.groupid}', {/if}'sesskey={$SESSKEY}'].join('&'),
                error: function(xhr, status, error) {
                    displayMessage(status + ': ' + error, 'error');
                },
                dataSrc: function(json) {
                    if (json.error) {
                        if (json.hasOwnProperty('message')) {
                            if (json.message.hasOwnProperty('message')) {
                                displayMessage(json.message.message, 'error');
                                return json.message.data;
                            } else {
                                displayMessage(json.message, 'error');
                            }
                        }
                        else {
                            displayMessage(json.error_message, 'error');
                        }
                        return false;
                    }
                    else if (json.message.hasOwnProperty('message')) {
                        displayMessage(json.message.message, 'info');
                    }
                    return json.message.data;
                }
            },
            {literal}
            responsive: true,
            fixedHeader: true,      // To avoid colvis column assignment issues
            colReorder: true,      // Set to false due to Quickfilter issues on referring to wrong columns after reordering
            dom: '<"dbut btn-top-right btn-group btn-group-top"B><"dt-buttons row col-auto"f><"toolbar">t<"pagein"ipl>',
            buttons: [
                {
                    extend: 'colvis',
                    text: '{/literal}<span class="icon icon-cog" role="presentation" aria-hidden="true"></span> {str tag='colvislabel' section='module.submissions'}{literal}',
                },
                {
                    extend: 'pdfHtml5',
                    text: '<span class="icon icon-file-pdf icon-regular" role="presentation" aria-hidden="true"></span> PDF',
                    exportOptions: {
                        columns: 'thead th:not(.noExport, .noPdfExport)',
                        orthogonal: 'export'
                    },
                    orientation: 'landscape',
                    // pageSize: 'LEGAL'
                },
                {
                    extend: 'csv',
                    text: '<span class="icon icon-file-csv" role="presentation" aria-hidden="true"></span> CSV',
                    exportOptions: {
                        columns: 'thead th:not(.noExport)',
                        orthogonal: 'export'
                    }
                }
            ],
            processing: true,
            stateSave: true,
            language: {
                decimal:        "{/literal}{str tag="decimal" section="module.submissions"}{literal}",
                emptyTable:     "{/literal}{str tag="emptytable" section="module.submissions"}{literal}",
                info:           "{/literal}{str tag="info" section="module.submissions"}{literal}",
                infoEmpty:      "{/literal}{str tag="infoempty" section="module.submissions"}{literal}",
                infoFiltered:   "{/literal}{str tag="infofiltered" section="module.submissions"}{literal}",
                infoPostFix:    "{/literal}{str tag="infopostfix" section="module.submissions"}{literal}",
                thousands:      "{/literal}{str tag="thousands" section="module.submissions"}{literal}",
                lengthMenu:     "{/literal}{str tag="lengthmenu" section="module.submissions"}{literal}",
                loadingRecords: "<span class=\"icon icon-spinner icon-pulse\"></span> {/literal}{str tag=loading section=module.submissions}{literal}",
                processing:     "<span class=\"icon icon-spinner icon-pulse\"></span> {/literal}{str tag=processing section=module.submissions}{literal}",
                search:         "{/literal}{str tag="search" section="module.submissions"}{literal}",
                searchPlaceholder: "{/literal}{str tag="searchplaceholder" section="module.submissions"}{literal}",
                zeroRecords:    "{/literal}{str tag="zerorecords" section="module.submissions"}{literal}",
                paginate: {
                    'first':      '{/literal}{str tag="first" section="module.submissions"}{literal}',
                    'last':       '{/literal}{str tag="last" section="module.submissions"}{literal}',
                    'next':       '{/literal}<span class="visually-hidden">{str tag="next" section="module.submissions"}</span>{literal}',
                    'previous':   '{/literal}<span class="visually-hidden">{str tag="previous" section="module.submissions"}</span>{literal}'
                },
                aria: {
                    'sortAscending':  '{/literal}{str tag="previous" section="module.submissions"}{literal}',
                    'sortDescending': '{/literal}{str tag="previous" section="module.submissions"}{literal}'
                }
            },
            order: [],
            initComplete: function(settings, json) {
                let flashback = UrlFlashback.createInstanceFromEntryAndRemoveEntry();
                let activeSubmissionId = null;
                let stateSelectedRowId = null;

                try {
                    activeSubmissionId = parseInt(flashback.data.selectedSubmission.submissionId);
                }
                catch (e) {
                    return;
                }

                try {
                    stateSelectedRowId = parseInt(submissionsTable.dataTable.state.loaded().selectedRowId);
                }
                catch (e) {
                    stateSelectedRowId = null;
                }

                // Should not be necessary, only to be sure, that the selected submission row matches the last visited portfolio
                if (activeSubmissionId && (stateSelectedRowId === null || activeSubmissionId !== stateSelectedRowId)) {
                    // // Possibly faster solution on larger tables
                    // let row = null;
                    // for (let rowIndex in submissionsTable.dataTable.rows({order: 'current', search: 'applied'})[0]) {
                    //     row = submissionsTable.dataTable.row(rowIndex);
                    //     if (parseInt(row.data().submissionId) === parseInt(activeSubmissionId)) {
                    //         submissionsTable.selectRow(row);
                    //         submissionsTable.jumpToRow(row);
                    //         break;
                    //     }
                    // }

                    // Possibly faster solution on smaller tables
                    submissionsTable.getDataTable().rows({order: 'current', search: 'applied'}).every(function(index) {
                        if (parseInt(this.data().submissionId) === activeSubmissionId) {
                            submissionsTable.selectRow(this);
                            submissionsTable.jumpToRow(this);
                        }
                    });
                }
            },
            columns: [
                {
                    name: 'submissionId',
                    data: 'submissionId',
                    visible: false,
                    responsivePriority: 6
                },
                {
                    name: 'evaluationId',
                    data: 'evaluationId',
                    visible: false,
                    responsivePriority: 6
                },
                {/literal}{if empty($options.groupid)}{literal}
                    {
                        name: 'groupName',
                        data: 'groupName',
                        responsivePriority: 1,
                    },
                    {
                        name: 'role',
                        data: 'liveUserIsAssessor',
                        responsivePriority: 1,
                        render: function(data, type, row, meta) {
                            if (['sort', 'filter'].includes(type)) {
                                return data;
                            }
                            if (data === '1') {
                                return '{/literal}{str tag="assessor" section="module.submissions"}{literal}';
                            }
                            return '{/literal}{str tag="submitter" section="module.submissions"}{literal}';
                        }
                    },
                {/literal}{/if}{literal}
                {
                    name: 'ownerName',
                    data: 'ownerName',
                    responsivePriority: 1,
                    render: function(data, type, row, meta) {
                        return row.userElementTitleHtml;
                    }
                },
                {
                    name: 'ownerPreferredName',
                    data: 'ownerPreferredName',
                    visible: false,
                    responsivePriority: 1
                },
                {
                    name: 'portfolioTitle',
                    data: 'portfolioElementTitle',
                    responsivePriority: 3,
                    render: function(data, type, row, meta) {
                        if (['sort', 'filter'].includes(type)) {
                            return data;
                        }
                        return row.portfolioElementTitleHtml
                    }
                },
                {
                    name: 'submissionDate',
                    data: 'submissionDate',
                    responsivePriority: 4,
                    render: function(data, type, row, meta) {
                        if (['sort', 'filter'].includes(type)) {
                            return data;
                        }
                        return row.submissionDateFormat;
                    }
                },
                {
                    name: 'taskTitle',
                    data: 'taskTitle',
                    responsivePriority: 5,
                },
                {
                    name: 'evaluatorName',
                    data: 'evaluatorName',
                    responsivePriority: 3,
                    render: function(data, type, row, meta) {
                        if (['filter','sort'].includes(type) || !(row.isEditable || row.isFixable)) {
                            return data ? row.evaluatorElementTitleHtml : data;
                        }

                        let text = (data ? row.evaluatorElementTitleHtml : '{/literal}{str tag='unassignedselectboxitem' section='module.submissions'}{literal}');
                        return '<button class="btn btn-sm btn-secondary">' + text + '</button>';
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.isEditable) {
                            $(td).css('cursor', 'pointer');
                        }
                    }
                },
                {
                    name: 'feedback',
                    data: 'feedback',
                    responsivePriority: 7,
                    className: 'none',
                    render: function(data, type, row, meta) {

                        if (['sort', 'filter'].includes(type) || data === null) {
                            return data;
                        }
                        return '<div class="pt-feedback">' + data + '</div>';
                    }
                },
                {
                    name: 'rating',
                    data: 'rating',
                    responsivePriority: 7,
                    render: function(data, type, row, meta) {

                        if (['filter', 'sort', 'export'].includes(type)) {
                            return data;
                        }

                        let $ratingContainer = $('<span class="star-comment-rating">');
                        {/literal}
                        let iconHtml = '<a class="icon icon-{$options.ratingIcon}" {if $options.ratingIconColour}style="color: {$options.ratingIconColour}"{/if}>&nbsp;</a>';
                        {literal}

                        for (var i = 0; i < data; i++) {
                            $ratingContainer.append($(iconHtml));
                        }
                        return $ratingContainer[0].outerHTML;
                    }
                },
                {
                    name: 'success',
                    data: 'success',
                    responsivePriority: 1,
                    className: "text-center",
                    render: function(data, type, row, meta) {
                        var classes = (row.isEditable || row.isFixable ? 'btn btn-small btn-secondary ': '');
                        var elementType = ' ';
                        var element = 'span';

                        // Prevent submitter from seeing the result before release of the submission
                        if (parseInt(row.liveUserIsAssessor) === 0 && parseInt(row.status) < 3) {
                            return '';
                        }

                        if (['filter','sort'].includes(type)) {
                            return (data === null ? '' : data);
                        }

                        if (type === 'export') {
                            switch (parseInt(data) || null) {
                                case null:
                                    return '';
                                case 1:
                                    return '{/literal}{str tag='Revision' section='module.submissions'}{literal}';
                                case 2:
                                    return '{/literal}{str tag='Fail' section='module.submissions'}{literal}';
                                default:
                                    return '{/literal}{str tag='Success' section='module.submissions'}{literal}';
                            }
                        }
                        var tooltip = '';
                        switch (parseInt(data) || null) {
                            case null:
                                classes += 'icon icon-pencil-alt text';
                                tooltip = '{/literal}{$tooltip.question}{literal}';
                                break;
                            case 1:
                                classes += 'icon icon-refresh text-warning';
                                tooltip = '{/literal}{$tooltip.refresh}{literal}';
                                break;
                            case 2:
                                classes += 'icon icon-remove text-danger';
                                tooltip = '{/literal}{$tooltip.remove}{literal}';
                                break;
                            default:
                                classes += 'icon icon-check text-success';
                                tooltip = '{/literal}{$tooltip.success}{literal}';
                        }
                        return '<' + element + elementType + 'class="' + classes + '" title="' + tooltip + '">' + '</' + element + '>';
                    }
                },
                {
                    name: 'status',
                    data: 'status',
                    responsivePriority: 2,
                    className: 'text-center',
                    render: function(data, type, row, meta) {
                        let buttonText;

                        if (['filter','sort'].includes(type)) {
                            return data;
                        }

                        switch (parseInt(data)) {
                            case 0:
                                if (row.isFixable && type !== 'export') {        // Non export and user role is Assessor
                                    buttonText = '{/literal}{str tag='fix' section='module.submissions'}{literal}';
                                } else {                    // Export or user role is Submitter
                                    return '{/literal}{str tag='notevaluated' section='module.submissions'}{literal}';
                                }
                                break;
                            case 1:
                                if (row.isEditable && type !== 'export') {       // Non export and user role is Assessor
                                    buttonText = '{/literal}{str tag='release' section='module.submissions'}{literal}';
                                } else {                    // Export or user role is Submitter
                                    return '{/literal}{str tag='submitted' section='module.submissions'}{literal}';
                                }
                                break;
                            case 2:
                                return '{/literal}{str tag='releasing' section='module.submissions'}{literal}';
                            case 3:
                            case 4:
                                return '{/literal}{str tag='completed' section='module.submissions'}{literal}';
                            default:
                                return null;
                        }

                        let classes = 'release-button btn btn-sm ';
                        switch (parseInt(row.success) || null) {
                            case null:
                                classes += 'btn-secondary';
                                break;
                            case 1:
                                classes += 'btn-warning';
                                break;
                            case 2:
                                classes += 'btn-danger';
                                break;
                            default:
                                classes += 'btn-success';
                        }

                        return '<button type="button" class="' + classes +'">' + buttonText + '</button>';
                    }
                }
            ],
            fields: [
                {
                    columnName: 'evaluatorName',
                    label: 'Evaluator Name:',
                    name: 'evaluatorName',
                    valueField: 'evaluatorId',
                    htmlInputElement: {
                        type: 'select',
                        options:
                            {
                                {/literal}
                                    {if $options.groupid}
                                        inputOptionsDataSourceIsRow: false,
                                        arrayValueText: [
                                            {foreach key=evaluatorid item=evaluatorname from=$options.evaluatorselection}
                                            { value: "{$evaluatorid}", text: '{$evaluatorname}' } ,
                                            {/foreach}
                                        ]
                                    {else}
                                        inputOptionsDataSourceIsRow: true
                                    {/if}
                                {literal}
                            },
                    }
                },
                {
                    columnName: 'success',
                    label: 'Result:',
                    name: 'success',
                    valueField: 'success',
                    isFixable: true,
                    // htmlInputElement: 'select',
                    // Only as an example for using htmlInputElement as a function (Replace later with code which can display icons)
                    htmlInputElement: function(rowData) {
                        let $selectElement = $('<select class="form-control form-control-sm">');

                        $([
                            {value: '', text: '{/literal}{str tag='chooseresult' section='module.submissions'}{literal}'},
                            {value: 1, text: '{/literal}{str tag='noresult' section='module.submissions'}{literal}'},
                            {value: 2, text: '{/literal}{str tag='fail' section='module.submissions'}{literal}'},
                            {value: 3, text: '{/literal}{str tag='success' section='module.submissions'}{literal}'},
                        ]).each(function() {
                            $selectElement.append($('<option>').attr('value', this.value).html(this.text));
                        });

                        // Select current option
                        $('option', $selectElement).filter(function() {
                            return this.value == rowData.success;
                        }).attr('selected', true);

                        return $selectElement;
                        }
                }
            ],
            drawCallback: function(settings) {
                setEventHandlers();
            },
            quickFilter:
                {
                    offTitle: '{/literal}{str tag='Off' section='module.submissions'}{literal}',
                    allOffTitle: '{/literal}{str tag='Reset' section='module.submissions'}{literal}',
                    categories: [
                        {/literal}{if empty($options.groupid)}{literal}
                            {
                                columnName: 'groupName',
                                title: '{/literal}{str tag='Group' section='module.submissions'}{literal}',
                                items: [
                                        {
                                            getHtmlElement: function(dataTable) {
                                                var select = $('<select><option value="">{/literal}{str tag='All' section='module.submissions'}{literal}</option></select>')
                                                    .on('change', function () {
                                                        var val = $.fn.DataTable.util.escapeRegex(
                                                            $(this).val()
                                                        );
                                                        dataTable.column('groupName' + ':name')
                                                            .search( val ? '^'+val+'$' : '', true, false )
                                                            .draw();
                                                    });
                                                dataTable.column('groupName' + ':name').data().unique().sort().each( function ( d, j ) {
                                                    select.append('<option value="'+d+'">'+d+'</option>')
                                                });
                                                return select;
                                            }
                                        }
                                    ]
                            },
                            {
                                columnName: 'role',
                                title: '{/literal}{str tag='Role' section='module.submissions'}{literal}',
                                items: [
                                    {
                                            getHtmlElement: function(dataTable) {
                                                var select = $('<select><option value="">{/literal}{str tag='All' section='module.submissions'}{literal}</option></select>')
                                                    .on('change', function () {
                                                        var val = $.fn.DataTable.util.escapeRegex(
                                                            $(this).val()
                                                        );
                                                        dataTable.column('role' + ':name')
                                                            .search( val ? '^'+val+'$' : '', true, false )
                                                            .draw();
                                                    });
                                                    select.append('<option value="1">{/literal}{str tag='assessor' section='module.submissions'}{literal}</option>');
                                                    select.append('<option value="0">{/literal}{str tag='submitter' section='module.submissions'}{literal}</option>');

                                                return select;
                                            }
                                    }
                                ]
                            },
                    {/literal}{/if}{literal}
                        {
                            columnName: 'taskTitle',
                            title: '{/literal}{str tag='Task' section='module.submissions'}{literal}',
                            items: [
                                    {
                                        getHtmlElement: function(dataTable) {
                                           var select = submissionsTable.createAutoQuickSearchItem(dataTable.column('taskTitle' + ':name'), submissionsTable.options.quickFilter.offTitle);
                                           {/literal}
                                           select.append('<option value=\'^$\'>{str tag='MissingTask' section='module.submissions'}</option');
                                           {literal}
                                           return select;
                                        }
                                    }
                                ]
                        },
                        {
                            columnName: 'evaluatorName',
                            title: '{/literal}{str tag='Evaluator' section='module.submissions'}{literal}',
                            items: [
                                    {
                                        getHtmlElement: function(dataTable) {
                                            var select = $('<select><option value="">{/literal}{str tag='All' section='module.submissions'}{literal}</option></select>')
                                                .on('change', function () {
                                                    var val = $.fn.DataTable.util.escapeRegex(
                                                        $(this).val()
                                                    );
                                                    if ($(this).val() == '\^\$') {
                                                        dataTable.column('evaluatorName' + ':name')
                                                            .search( '^$', true )
                                                            .draw();
                                                    }
                                                    else {
                                                        dataTable.column('evaluatorName' + ':name')
                                                            .search( val ? '^'+val+'$' : '', true, false )
                                                            .draw();
                                                    }
                                                });

                                            {/literal}
                                                {foreach key=evaluatorid item=evaluatorname from=$options.evaluatorfilterselection}
                                                    {if $evaluatorid != null}
                                                        select.append('<option value="{$evaluatorname}">{$evaluatorname}</option');
                                                    {/if}
                                                {/foreach}
                                            select.append('<option value=\'^$\'>{str tag='Unassigned' section='module.submissions'}</option');
                                            {literal}
                                            return select;
                                        }
                                    }
                                ]
                        },
                        {
                            columnName: 'feedback',
                            title: '{/literal}{str tag='Feedback' section='module.submissions'}{literal}',
                            items: [
                                    {
                                        getHtmlElement: function(dataTable) {
                                            var select = $('<select><option value="">{/literal}{str tag='Off' section='module.submissions'}{literal}</option></select>')
                                                .on('change', function () {
                                                    var val = $.fn.DataTable.util.escapeRegex(
                                                        $(this).val()
                                                    );
                                                    if ($(this).val() == '\^\$') {
                                                        dataTable.column('feedback' + ':name')
                                                            .search( '^$', true )
                                                            .draw();
                                                    }
                                                    else if ($(this).val() == '\!\^\$') {
                                                        dataTable.column('feedback' + ':name')
                                                            .search( '^(?!\\s*$).+', true )
                                                            .draw();
                                                    }
                                                    else {
                                                        dataTable.column('feedback' + ':name')
                                                            .search( '', true, false )
                                                            .draw();
                                                    }
                                                });

                                            {/literal}
                                            select.append('<option value=\'^$\'>{str tag='Uncommented' section='module.submissions'}</option');
                                            select.append('<option value=\'!^$\'>{str tag='Commented' section='module.submissions'}</option');
                                            {literal}
                                            return select;
                                        }
                                    }

                            ]
                        },
                        {
                            columnName: 'success',
                            title: '{/literal}{str tag='Result' section='module.submissions'}{literal}',
                            items: [
                                    {
                                        getHtmlElement: function(dataTable) {
                                            var select = $('<select><option value="">{/literal}{str tag='Off' section='module.submissions'}{literal}</option></select>')
                                                .on('change', function () {
                                                    var val = $.fn.DataTable.util.escapeRegex(
                                                        $(this).val()
                                                    );
                                                    if ($(this).val() == '\^\$') {
                                                        dataTable.column('success' + ':name')
                                                            .search( '^$', true )
                                                            .draw();
                                                    }
                                                    else if (val) {
                                                        dataTable.column('success' + ':name')
                                                            .search( val )
                                                            .draw();
                                                    }
                                                    else {
                                                        dataTable.column('success' + ':name')
                                                            .search( '', true, false )
                                                            .draw();
                                                    }
                                                });
                                            {/literal}
                                            select.append('<option value="1">{str tag='Revision' section='module.submissions'}</option');
                                            select.append('<option value="3">{str tag='Success' section='module.submissions'}</option');
                                            select.append('<option value="2">{str tag='Fail' section='module.submissions'}</option');
                                            select.append('<option value="^$">{str tag='Pending' section='module.submissions'}</option');
                                            {literal}
                                            return select;
                                        }
                                    }
                                ]
                        },
                        {
                            columnName: 'status',
                            title: '{/literal}{str tag='State' section='module.submissions'}{literal}',
                            items: [
                                    {
                                        getHtmlElement: function(dataTable) {
                                            var select = $('<select><option value="">{/literal}{str tag='Off' section='module.submissions'}{literal}</option></select>')
                                                .on('change', function () {
                                                    var val = $.fn.DataTable.util.escapeRegex(
                                                        $(this).val()
                                                    );
                                                    if (val == 'open') {
                                                        dataTable.column('status' + ':name')
                                                            .search( '0|1|2', true )
                                                            .draw();
                                                    }
                                                    else if (val == 'completed') {
                                                        dataTable.column('status' + ':name')
                                                            .search( '3|4', true )
                                                            .draw();
                                                    }
                                                    else {
                                                        dataTable.column('status' + ':name')
                                                            .search( '', true, false )
                                                            .draw();
                                                    }
                                                });

                                            {/literal}
                                            select.append('<option value="open">{str tag='Open' section='module.submissions'}</option');
                                            select.append('<option value="completed">{str tag='Completed' section='module.submissions'}</option');
                                            {literal}
                                            return select;
                                        }
                                    }
                                ]
                        },
                    ]
                }
        });

        function createFlashbackEntryByPortfolioTableElement(portfolioTableElement, e) {
            let rowData = submissionsTable.getRowOfTableElement(portfolioTableElement).data();
            let destinationUrls = [];

            $.each(rowData.editOptions.viewIds, function() {
                destinationUrls.push(config.wwwroot + 'view/view.php?id=' + this);
            });

            let flashback = UrlFlashback.createNewInstance(destinationUrls);
            flashback.data =
                {
                    selectedSubmission:
                        {
                            submissionId: rowData.submissionId,
                        }
                };
            return flashback.addEntry();
        }

        function setEventHandlers() {
            let $submissions = $('#submissions');
            $submissions.off('click', '.portfolio-element-preview');
            $submissions.off('click', '.release-button');
            $submissions.off('click', '.portfolio-element');

            $submissions.one('click', '.portfolio-element', function(e) {
                if (!createFlashbackEntryByPortfolioTableElement(this)) {
                    e.preventDefault();
                }
            });

            $submissions.on('click', '.portfolio-element-preview', function(e) {
                let Me = this;
                let portfolioType = $(this).hasClass('portfolio-type-collection') ? 'collection' : 'view';

                e.preventDefault();

                // Add a goto portfolio button to the modal preview if needed
                if ($('#page-modal .modal-footer .goto').length == 0) {
                    $('#page-modal .modal-footer').prepend('<button class="btn btn-secondary goto" type="button">' + get_string_ajax('displayportfolio', 'module.submissions') + '</button>');
                }
                else {
                    $('#page-modal .goto').show();
                }

                // Remove added click event and goto portfolio button after closing
                $('#page-modal').on('hidden.bs.modal', function() {
                    $('#page-modal .goto').off('click').hide();
                });

                var params = {
                    id: getUrlParameter('id', this.href) || '',
                    export: 1
                };

                sendjsonrequest(config.wwwroot + portfolioType + '/viewcontent.json.php', params, 'POST', showPreview.bind(null, 'big'));
                $('#page-modal .goto').on('click', function(e) {
                    if (createFlashbackEntryByPortfolioTableElement(Me)) {
                        $('#page-modal').modal("hide");
                        window.location = Me.href;
                    }
                });
            });

            $submissions.on('click', '.release-button', function(e) {
                // As we handle this event outside of the PierTables field editing handling, we have to tell this PierTables
                submissionsTable.handleEditingEventExternally(e);

                var tableRow = submissionsTable.getRowOfTableElement(this);
                var backupRowData = JSON.stringify(tableRow.data());
                var confirmMessage = "{/literal}{str tag='releasesubmission' section='module.submissions'}{literal}";

                if (parseInt(tableRow.data().status) === 0) {
                    confirmMessage = "{/literal}{str tag='fixsubmission' section='module.submissions'}{literal}";
                }
                if (confirm(confirmMessage)) {
                    submissionsTable.sendData(tableRow.data(), function(success, newRowData) {
                        if (success) {
                            tableRow.data(newRowData).draw('page');
                        } else {
                            if (newRowData === undefined) {
                                tableRow.data(JSON.parse(backupRowData));
                            } else {
                                tableRow.data(newRowData);
                            }
                        }
                    }, config.wwwroot + 'module/submissions/release.json.php?' + submissionsTable.options.ajax.baseUrlParams);
                }
            });

            // ToDo: Implement to redraw the table after column visibility changed
            // $submissions.on( 'column-visibility.dt', function ( e, settings, column, state ) {
            //     // ToDo: Too slow
            //     // submissionsTable.getDataTable().draw(false);
            //     // submissionsTable.getDataTable().columns.adjust();
            //     submissionsTable.getDataTable().columns.adjust().responsive.recalc();
            // });
        }
    });
{/literal}
</script>
