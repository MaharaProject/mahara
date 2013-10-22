{foreach from=$plans.data item=plan}
    <div class="{cycle values='r0,r1'} listrow">
            <div class="fr planstatus">
                 <a href="{$WWWROOT}artefact/plans/edit/index.php?id={$plan->id}" title="{str tag="edit"}" ><img src="{theme_url filename='images/btn_edit.png'}" alt="{str tag=edit}"></a>
                 <a href="{$WWWROOT}artefact/plans/plan.php?id={$plan->id}" title="{str tag=managetasks section=artefact.plans}"><img src="{theme_url filename='images/btn_configure.png'}" alt="{str tag=managetasks}"></a>
                 <a href="{$WWWROOT}artefact/plans/delete/index.php?id={$plan->id}" title="{str tag="delete"}"><img src="{theme_url filename='images/btn_deleteremove.png'}" alt="{str tag=delete}"></a>
            </div>

            <h3 class="title"><a href="{$WWWROOT}artefact/plans/plan.php?id={$plan->id}">{$plan->title}</a></h3>
            {if $plan->tags}
            <div>{str tag=tags}: {list_tags tags=$plan->tags owner=$plan->owner}</div>
            {/if}
            <div class="detail">{$plan->description|clean_html|safe}</div>
            <div class="cb"></div>
    </div>
{/foreach}
