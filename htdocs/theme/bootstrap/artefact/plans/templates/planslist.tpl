{foreach from=$plans.data item=plan}
    <div class="post">
        <div class="post-heading">
            <h3 class="title pull-left"><a href="{$WWWROOT}artefact/plans/plan.php?id={$plan->id}">{$plan->title}</a></h3>

            <div class="pull-right planstatus">
                <a href="{$WWWROOT}artefact/plans/edit/index.php?id={$plan->id}" title="{str tag=edit}" class="btn btn-default btn-xs">
                    <span class="fa fa-pencil"></span>
                    <span class="sr-only">{str(tag=editspecific arg1=$plan->title)|escape:html|safe}</span>
                </a>
                <a href="{$WWWROOT}artefact/plans/plan.php?id={$plan->id}" title="{str tag=managetasks section=artefact.plans}" class="btn btn-default btn-xs">
                    <span class="fa fa-cog"></span>
                    <span class="sr-only">{str(tag=managetasksspecific section=artefact.plans arg1=$plan->title)|escape:html|safe}</span>
                </a>
                <a href="{$WWWROOT}artefact/plans/delete/index.php?id={$plan->id}" title="{str tag=delete}" class="btn btn-danger btn-xs">
                    <span class="fa fa-trash"></span>
                    <span class="sr-only">{str(tag=deletespecific arg1=$plan->title)|escape:html|safe}</span>
                </a>
            </div>
        </div>

            <div class="content postdetails">
                {$plan->description|clean_html|safe}
            </div>
            {if $plan->tags}
            <div><strong>{str tag=tags}</strong>: {list_tags tags=$plan->tags owner=$plan->owner}</div>
            {/if}
    </div>
{/foreach}
