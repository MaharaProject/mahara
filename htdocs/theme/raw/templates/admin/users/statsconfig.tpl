<div>
    {if $form}
    <div class="col-md-6">
        {$form|safe}
    </div>
    {/if}
    <div class="{if $form}col-md-6{/if}">
        <h4>{str tag="reportdesctitle" section="statistics"}</h4>
        {$reportinformation|safe}
    </div>
</div>
<script type="application/javascript">
    $('#reportconfigform_subtype').on('change', function() {
        opts.subtype = $('#reportconfigform_subtype').val();
        show_stats_config();
    });
</script>
