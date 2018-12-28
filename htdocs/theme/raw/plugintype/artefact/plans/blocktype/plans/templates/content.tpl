{if $noplans && $editing}
    <p class="editor-description">{$noplans}</p>
{/if}
{foreach from=$plans item=plan}
<div class="card-body flush">
    {if $editing}
        <div class="float-right btn-group">
            <a class="btn btn-secondary btn-sm" href="{$WWWROOT}artefact/plans/edit/index.php?id={$plan.id}{if $plan.view}&view={$plan.view}{/if}" title="{str(tag=editspecific arg1=$plan.title)|escape:html|safe}">
                <span class="icon icon-pencil"></span>
                <span class="sr-only">{str tag='edit'}</span>
            </a>
            <a class="btn btn-secondary btn-sm" href="{$WWWROOT}artefact/plans/new.php?id={$plan.id}{if $plan.view}&view={$plan.view}{/if}" title="{str(tag=addtaskspecific section='artefact.plans' arg1=$plan.title)|escape:html|safe}">
                <span class="icon icon-plus"></span>
                <span class="sr-only">{str tag='addtask' section='artefact.plans'}</span>
            </a>
            <a class="btn btn-secondary btn-sm" href="{$WWWROOT}artefact/plans/delete/index.php?id={$plan.id}{if $plan.view}&view={$plan.view}{/if}" title="{str(tag=deletespecific arg1=$plan.title)|escape:html|safe}">
                <span class="icon icon-trash text-danger"></span>
                <span class="sr-only">{str tag='Delete'}</span>
            </a>
        </div>
    {/if}
    {if count($plans) > 1}
    <h4>{$plan.title}</h4>
    {/if}
    <p>{$plan.description}</p>

    {if $plan.tags}
    <div class="tags">
        <strong>{str tag=tags}:</strong> {list_tags owner=$plan.owner tags=$plan.tags view=$plan.view}
    </div>
    {/if}
    {if !$plan.description && !$plan.tags} &nbsp; {/if}

    {if $plan.numtasks != 0}
        {foreach from=$alltasks item=tasks}
            {if $tasks.planid == $plan.id}
                <div id="tasklist_{$blockid}_plan{$tasks.planid}" class="list-group list-unstyled">
                    {$tasks.tablerows|safe}
                </div>
                {if $tasks.pagination && !$versioning}
                    <div id="plans_page_container_{$blockid}_plan{$tasks.planid}" class="d-none">
                        {$tasks.pagination|safe}
                    </div>
                    <script>
                    jQuery(function($) {literal}{{/literal}
                        {$tasks.pagination_js|safe}
                        $('#plans_page_container_{$blockid}_plan{$tasks.planid}').removeClass('d-none');
                    {literal}}{/literal});
                    </script>
                {/if}
            {/if}
        {/foreach}
        {if !$editing && !$versioning}
        <a href="{$plan.details}" class="detail-link link-blocktype"><span class="icon icon-link" role="presentation" aria-hidden="true"></span> {str tag=detailslinkalt section=view}</a>
        {/if}
    {else}
        <div class="lead text-center content-text">{str tag='notasks' section='artefact.plans'}</div>
    {/if}
</div>
{/foreach}
