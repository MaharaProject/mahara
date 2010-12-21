{foreach from=$plans.data item=plan}
    <tr class="{cycle values='r0,r1'}">
        <td>
            <div class="fr planstatus">
                 <a href="{$WWWROOT}artefact/plans/edit/index.php?id={$plan->id}" title="{str tag="edit"}" ><img src="{theme_url filename='images/edit.gif'}" alt="{str tag=edit}"></a>
                 <a href="{$WWWROOT}artefact/plans/plan.php?id={$plan->id}" title="{str tag=managetasks section=artefact.plans}"><img src="{theme_url filename='images/manage.gif'}" alt="{str tag=managetasks}"></a>
                 <a href="{$WWWROOT}artefact/plans/delete/index.php?id={$plan->id}" title="{str tag="delete"}"><img src="{theme_url filename='images/icon_close.gif'}" alt="{str tag=delete}"></a>
            </div>

            <h3><a href="{$WWWROOT}artefact/plans/plan.php?id={$plan->id}">{$plan->title}</a></h3>

        <div class="codesc">{$plan->description}</div>
        </td>
    </tr>
{/foreach}
