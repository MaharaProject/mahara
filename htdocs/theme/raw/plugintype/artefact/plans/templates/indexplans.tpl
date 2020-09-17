<div class="plan-list-group list-group list-group-lite list-group-top-border">
    {if $plans.group}
        {$groupurlquery = "group=$plans.group&"}
    {/if}

    {foreach from=$plans.data item=plan}
{*        ToDo: Do we need to mark plans with time critical tasks?*}
{*        {if $plan->template}*}
{*            {$templateBackgroundClass = " list-group-item-secondary"}*}
{*        {elseif $plan->hastimecriticaltasks}*}
{*            {$templateBackgroundClass = " list-group-item-warning"}*}
{*        {else}*}
{*            {$templateBackgroundClass = ""}*}
{*        {/if}*}
        <div class="list-group-item{$templateBackgroundClass}">
            <h2 class="list-group-item-heading text-inline">
                <a href="{$WWWROOT}artefact/plans/plan/view.php?{$groupurlquery}id={$plan->id}">{$plan->title}</a>
                {if $plan->template}
                    <div class="text-tiny">{$templatetext}</div>
                {/if}
            </h2>
            <div class="btn-top-right btn-group btn-group-top">
                {if $canedit}
                    <a href="{$WWWROOT}artefact/plans/plan/edit.php?{$groupurlquery}id={$plan->id}" title="{str(tag=editspecific arg1=$plan->title)|escape:html|safe}" class="btn btn-secondary btn-sm">
                        <span class="icon icon-pencil-alt" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag=edit}</span>
                    </a>
                    <a href="{$WWWROOT}artefact/plans/plan/view.php?{$groupurlquery}id={$plan->id}" title="{str tag=managetasks section=artefact.plans}" class="btn btn-secondary btn-sm">
                        <span class="icon icon-cog" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag=managetasks section=artefact.plans}</span>
                    </a>
                    <a href="{$WWWROOT}artefact/plans/plan/delete.php?{$groupurlquery}id={$plan->id}" title="{str(tag=deletespecific arg1=$plan->title)|escape:html|safe}" class="btn btn-secondary btn-sm">
                        <span class="icon icon-trash-alt text-danger" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag=delete}</span>
                    </a>
                {/if}
            </div>
            {if $plan->description}
            <div class="postdescription">
                {$plan->description|clean_html|safe}
            </div>
            {/if}
            {if $plan->tags}
            <div class="tags text-small">
                <strong>{str tag=tags}</strong>:
                {list_tags tags=$plan->tags owner=$plan->owner}
            </div>
            {/if}
        </div>
    {/foreach}
</div>
