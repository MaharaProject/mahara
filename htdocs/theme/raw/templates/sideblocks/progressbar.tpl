{if $sbdata.data || $sbdata.preview || $sbdata.count > 1}
<div class="card">
    <h3 class="card-header">
        {if $sbdata.preview}{str tag="profilecompletenesspreview"}{else}{str tag="profilecompleteness"}{/if}
    </h3>
    <div class="card-body">
        {if $sbdata.count > 1}
        <form class="pieform" name="progresssidebarselect" method="post" action="" id="progresssidebarselect">
            <div id="progresssidebarselect_institution_container" class="select dropdown with-label-widthauto">
                <label class="sr-only" for="progresssidebarselect_institution">{str tag=profilecompletionforwhichinstitution}</label>
                <select class="form-control select" id="progresssidebarselect_institution" name="institution" tabindex="1" style="">
                {foreach from=$sbdata.institutions key=inst item=displayname}
                    <option value="{$inst}"{if $inst == $sbdata.institution} selected="selected"{/if}>{$displayname|str_shorten_html:25:true}</option>
                {/foreach}
                </select>
            </div>
        </form>
        {/if}

        <div id="progressbarwrap" class="progress-container">
            {if $sbdata.percent < 100}
                <div class="progress">
                    <div id="progress_bar_fill" class="progress-bar {if $sbdata.percent < 11}small-progress{/if}" role="progressbar" aria-valuemax="100" aria-valuemin="0" style="width: {$sbdata.percent}%;">
                        <span id="progress_bar_percentage">{$sbdata.percent}%</span>
                    </div>
                </div>
                <div id="profile_completeness_tips" class="list-group">
                    <span class="d-none" id="progress_counting_total">{$sbdata.totalcounting}</span>
                    <span class="d-none" id="progress_completed_total">{$sbdata.totalcompleted}</span>
                    <div class="list-group-item-heading">{str tag=profilecompletenesstips}</div>
                    <ul class="list-nested list-group-item-text list-unstyled list-group-item-link">
                        {foreach from=$sbdata.data item=item}
                        <li{if $item.display <= 0} class="d-none"{/if}>
                            <span id="progress_counting_{$item.artefact}" class="d-none">{$item.counting}</span>
                            <span id="progress_completed_{$item.artefact}" class="d-none">{$item.completed}</span>
                            {if $item.link}<a href="{$WWWROOT}{$item.link}">{/if}<span id="progress_item_{$item.artefact}">{$item.label}</span>{if $item.link}</a>{/if}
                        </li>
                        {/foreach}
                    </ul>
                </div>
            {else}
                <div id="progress_bar_100" class="progress">
                    <div id="progress_bar_fill" class="progress-bar" role="progressbar" aria-valuemax="100" aria-valuemin="0" style="width: {$sbdata.percent}%;">
                        {$sbdata.percent}%
                    </div>
                </div>
            {/if}
        </div>
    </div>
</div>
{if $sbdata.count > 1}
<!-- @todo move this to somewhere better - it shouldn't be rendered in the middle of a page -->
<script>
    jQuery(function($) {
        function reloadSideBar() {
            window.location.href = window.location.href.split('?')[0]+'?i='+$('#progresssidebarselect_institution').val();
        }
        $('#progresssidebarselect_institution').on('change', reloadSideBar);
    });
</script>
{/if}
{/if}
