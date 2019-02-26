<div class="list-group list-group-lite">
{foreach from=$plans.data item=plan}
    <div class="list-group-item">
        <div class="clearfix">
            <h3 class="list-group-item-heading">
                <a href="{$WWWROOT}artefact/plans/plan.php?id={$plan->id}">{$plan->title}
                </a>
            </h3>
            <div class="list-group-item-controls">
                <div class="btn-group btn-group-top">
                    <a href="{$WWWROOT}artefact/plans/edit/index.php?id={$plan->id}" title="{str(tag=editspecific arg1=$plan->title)|escape:html|safe}" class="btn btn-secondary btn-sm">
                        <span class="icon icon-lg icon-pencil" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag=edit}</span>
                    </a>
                    <a href="{$WWWROOT}artefact/plans/plan.php?id={$plan->id}" title="{str tag=managetasks section=artefact.plans}" class="btn btn-secondary btn-sm">
                        <span class="icon icon-lg icon-cog" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag=managetasks section=artefact.plans}</span>
                    </a>
                    <a href="{$WWWROOT}artefact/plans/delete/index.php?id={$plan->id}" title="{str(tag=deletespecific arg1=$plan->title)|escape:html|safe}" class="btn btn-secondary btn-sm">
                        <span class="icon icon-trash text-danger icon-lg" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag=delete}</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="postdescription">
            {$plan->description|clean_html|safe}
        </div>

        {if $plan->tags}
        <div class="tags">
            <strong>{str tag=tags}</strong>:
            {list_tags tags=$plan->tags owner=$plan->owner}
        </div>
        {/if}
    </div>
{/foreach}
</div>
