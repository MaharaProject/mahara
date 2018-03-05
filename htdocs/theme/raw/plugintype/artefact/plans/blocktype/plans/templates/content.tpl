{foreach from=$plans item=plan}
<div class="panel-body flush">
    <strong>{$plan.title}</strong>
    <p>{$plan.description}</p>

    {if $plan.tags}
    <div class="tags">
        <strong>{str tag=tags}:</strong> {list_tags owner=$plan.owner tags=$plan.tags view=$plan.view}
    </div>
    {/if}

    {if $plan.numtasks != 0}
        {foreach from=$alltasks item=tasks}
            {if $tasks.planid == $plan.id}
                <div id="tasklist_{$blockid}_plan{$tasks.planid}" class="list-group list-unstyled">
                    {$tasks.tablerows|safe}
                </div>
                {if $tasks.pagination}
                    <div id="plans_page_container_{$blockid}_plan{$tasks.planid}" class="hidden">
                        {$tasks.pagination|safe}
                    </div>
                    <script>
                    jQuery(function($) {literal}{{/literal}
                        {$tasks.pagination_js|safe}
                        $('#plans_page_container_{$blockid}_plan{$tasks.planid}').removeClass('hidden');
                    {literal}}{/literal});
                    </script>
                {/if}
            {/if}
        {/foreach}
        <a href="{$plan.details}" class="detail-link link-blocktype"><span class="icon icon-link" role="presentation" aria-hidden="true"></span> {str tag=detailslinkalt section=view}</a>
    {else}
        <div class="lead text-center content-text">{str tag='notasks' section='artefact.plans'}</div>
    {/if}
</div>
{/foreach}