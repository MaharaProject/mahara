{if $noplans && $editing}
    <p class="editor-description">{$noplans}</p>
{/if}
{foreach from=$plans item=plan}
    <div class="listing{if $plan.title} card-body flush{/if}">
        {if $editing}
            <div class="float-right btn-group">
                <a class="btn btn-secondary btn-sm" href="{$WWWROOT}artefact/plans/plan/edit.php?id={$plan.id}{if $plan.view}&view={$plan.view}{/if}" title="{str(tag=editspecific arg1=$plan.title)|escape:html|safe}">
                    <span class="icon icon-pencil-alt"></span>
                    <span class="sr-only">{str tag='edit'}</span>
                </a>
                <a class="btn btn-secondary btn-sm" href="{$WWWROOT}artefact/plans/task/new.php?id={$plan.id}{if $plan.view}&view={$plan.view}{/if}" title="{str(tag=addtaskspecific section='artefact.plans' arg1=$plan.title)|escape:html|safe}">
                    <span class="icon icon-plus"></span>
                    <span class="sr-only">{str tag='addtask' section='artefact.plans'}</span>
                </a>
                <a class="btn btn-secondary btn-sm" href="{$WWWROOT}artefact/plans/plan/delete.php?id={$plan.id}{if $plan.view}&view={$plan.view}{/if}" title="{str(tag=deletespecific arg1=$plan.title)|escape:html|safe}">
                    <span class="icon icon-trash-alt text-danger"></span>
                    <span class="sr-only">{str tag='Delete'}</span>
                </a>
            </div>
        {/if}
        {if count($plans) > 1}
            <h4 class="title">{$plan.title}</h4>
        {/if}
        {if $plan.description}
            <p>{$plan.description}</p>
        {else}
            <div class="clearfix"></div>
        {/if}
        {if $plan.tags}
            <div class="tags text-small">
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
                        <div id="plans_page_container_{$blockid}_plan{$tasks.planid}" class="d-none">
                            {$tasks.pagination|safe}
                        </div>
                        <script type="application/javascript">
                            jQuery(function($) {literal}{{/literal}
                                {$tasks.pagination_js|safe}
                                $('#plans_page_container_{$blockid}_plan{$tasks.planid}').removeClass('d-none');
                                {literal}}{/literal});
                        </script>
                    {/if}
                {/if}
            {/foreach}
        {else}
            <div class="lead text-center content-text">{str tag='notasks' section='artefact.plans'}</div>
        {/if}
    </div>
{/foreach}
