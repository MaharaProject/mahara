<div class="plan-list-group list-group list-group-lite list-group-top-border">
    {if $plans.group}
        {assign var="groupurlquery" value='group=$plans.group&'}
    {/if}

    {foreach from=$plans.data item=plan}
{*        ToDo: Do we need to mark plans with time critical tasks?*}
{*        {if $plan->template}*}
{*            {assign var="templateBackgroundClass" value=" list-group-item-secondary"}*}
{*        {elseif $plan->hastimecriticaltasks}*}
{*            {assign var="templateBackgroundClass" value=" list-group-item-warning"}*}
{*        {else}*}
{*            {assign var="templateBackgroundClass" value=""}*}
{*        {/if}*}
        <div class="list-group-item{$templateBackgroundClass}">
            <div class="flex-row">
                <div class="flex-title">
                    <h2 class="list-group-item-heading text-inline">
                        <a href="{$WWWROOT}artefact/plans/plan/view.php?{$groupurlquery}id={$plan->id}">{$plan->title}</a>
                        {if $plan->template}
                            <div class="text-tiny">{$templatetext}</div>
                        {/if}
                    </h2>
                </div>
                <div class="flex-controls">
                    <div class="btn-top-right btn-group btn-group-top">
                        {if $canedit}
                            <button data-url="{$WWWROOT}artefact/plans/plan/edit.php?{$groupurlquery}id={$plan->id}" title="{str(tag=editspecific arg1=$plan->title)|escape:html|safe}" class="btn btn-secondary btn-sm">
                                <span class="icon icon-pencil-alt" role="presentation" aria-hidden="true"></span>
                                <span class="visually-hidden">{str tag=edit}</span>
                            </button>
                            <button data-url="{$WWWROOT}artefact/plans/plan/view.php?{$groupurlquery}id={$plan->id}" title="{str tag=managetasks section=artefact.plans}" class="btn btn-secondary btn-sm">
                                <span class="icon icon-cog" role="presentation" aria-hidden="true"></span>
                                <span class="visually-hidden">{str tag=managetasks section=artefact.plans}</span>
                            </button>
                            <button data-url="{$WWWROOT}artefact/plans/plan/delete.php?{$groupurlquery}id={$plan->id}" title="{str(tag=deletespecific arg1=$plan->title)|escape:html|safe}" class="btn btn-secondary btn-sm">
                                <span class="icon icon-trash-alt text-danger" role="presentation" aria-hidden="true"></span>
                                <span class="visually-hidden">{str tag=delete}</span>
                            </button>
                        {/if}
                    </div>
                </div>
            </div>
            {if $plan->description}
            <div class="postdescription">
                {$plan->description|clean_html|safe}
            </div>
            {/if}
            {if $plan->tags}
            <div class="tags text-small">
                {str tag=tags}: {list_tags tags=$plan->tags owner=$plan->owner}
            </div>
            {/if}
        </div>
    {/foreach}
</div>
