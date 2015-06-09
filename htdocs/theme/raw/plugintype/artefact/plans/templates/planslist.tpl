<div class="list-group list-group-lite">
{foreach from=$plans.data item=plan}
    <div class="list-group-item">
        <div class="clearfix">
            <h3 class="pull-left list-group-item-heading mt0">
                <a href="{$WWWROOT}artefact/plans/plan.php?id={$plan->id}">{$plan->title}
                </a>
            </h3>

            <div class="pull-right btn-group btn-group-top">
                <a href="{$WWWROOT}artefact/plans/edit/index.php?id={$plan->id}" title="{str(tag=editspecific arg1=$plan->title)|escape:html|safe}" class="btn btn-default btn-sm">
                    <span class="icon icon-pencil"></span>
                    {str tag=edit}
                </a>
                <a href="{$WWWROOT}artefact/plans/plan.php?id={$plan->id}" title="{str tag=managetasks section=artefact.plans}" class="btn btn-default btn-sm">
                    <span class="icon icon-cog"></span>
                    {str tag=managetasks section=artefact.plans}
                </a>
                <a href="{$WWWROOT}artefact/plans/delete/index.php?id={$plan->id}" title="{str(tag=deletespecific arg1=$plan->title)|escape:html|safe}" class="btn btn-default btn-sm">
                    <span class="icon icon-trash text-danger icon-lg"></span>
                    {str tag=delete}
                </a>
            </div>
        </div>

        <div class="postdescription">
            {$plan->description|clean_html|safe}
        </div>
        
        {if $plan->tags}
        <div class="mtm">
            <strong>{str tag=tags}</strong>: 
            {list_tags tags=$plan->tags owner=$plan->owner}
        </div>
        {/if}
    </div>
{/foreach}
</div>
