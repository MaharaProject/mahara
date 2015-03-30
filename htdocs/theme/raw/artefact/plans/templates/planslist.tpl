{foreach from=$plans.data item=plan}
    <div class="{cycle values='r0,r1'} listrow">
            <h3 class="title"><a href="{$WWWROOT}artefact/plans/plan.php?id={$plan->id}">{$plan->title}</a></h3>

            <div class="fr planstatus">
                <a href="{$WWWROOT}artefact/plans/edit/index.php?id={$plan->id}" title="{str tag=edit}" >
                    <img src="{theme_image_url filename='btn_edit'}" alt="{str(tag=editspecific arg1=$plan->title)|escape:html|safe}"></a>
                <a href="{$WWWROOT}artefact/plans/plan.php?id={$plan->id}" title="{str tag=managetasks section=artefact.plans}">
                    <img src="{theme_image_url filename='btn_configure'}" alt="{str(tag=managetasksspecific section=artefact.plans arg1=$plan->title)|escape:html|safe}"></a>
                <a href="{$WWWROOT}artefact/plans/delete/index.php?id={$plan->id}" title="{str tag=delete}">
                    <img src="{theme_image_url filename='btn_deleteremove'}" alt="{str(tag=deletespecific arg1=$plan->title)|escape:html|safe}"></a>
            </div>

            <div class="detail">{$plan->description|clean_html|safe}</div>
            {if $plan->tags}
            <div>{str tag=tags}: {list_tags tags=$plan->tags owner=$plan->owner}</div>
            {/if}
            <div class="cb"></div>
    </div>
{/foreach}
