<div class="flexbox">
    {if $form}
    <div class="col-md-6">
        {$form|safe}
        {if $tableheadings}
            <div class="collapsible reportconfig float-right">
                <div class="title card-header js-heading">
                    <a data-toggle="collapse" href="#reportconfig" aria-expanded="false" class="outer-link collapsed"></a>
                    {str tag="Columns" section="admin"}
                    <span class="icon icon-chevron-up collapse-indicator float-right inner-link" role="presentation" aria-hidden="true"></span>
                </div>
                <div class="block collapse options" id="reportconfig">
                {foreach from=$tableheadings item=heading}
                    <div class="with-label-widthauto">
                        <label class="reportcol">
                            <input name="{$heading.id}" id="report-column-{$heading.id}" type="checkbox" {if $heading.selected}checked{/if} {if $heading.required}disabled{/if}>
                            {$heading.name}
                        </label>
                    </div>
                {/foreach}
                </div>
            </div>
        {/if}
    </div>
    {/if}
    <div class="{if $form}col-md-6{/if}">
        <h4>{str tag="reportdesctitle" section="statistics"}</h4>
        {$reportinformation|safe}
    </div>
</div>
<script>
    function update_report_config() {
        var institution = $('#reportconfigform_institution').val();
        var typesubtype = $('#reportconfigform_typesubtype').val().split("_");
        opts.type = typesubtype[0];
        opts.subtype = typesubtype[1];
        opts.institution = institution;
        show_stats_config();
    }
    var previnst;
    $('#reportconfigform_institution').on('focus', function() {
        previnst = this.value;
    }).on('change', function() {
        if ((previnst == 'all' && this.value != 'all') ||
            (previnst != 'all' && this.value == 'all')) {
            previnst = this.value;
            update_report_config();
        }
    });
    $('#reportconfigform_institution').on('change', function() {
        update_report_config();
    });
    $('#reportconfigform_typesubtype').on('change', function() {
        update_report_config();
    });

</script>
