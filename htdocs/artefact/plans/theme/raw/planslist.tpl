{foreach from=$plans.data item=plan}
    <tr class="{cycle values='r0,r1'}">
        <td>
            <div class="fr planstatus">
                 <a class="icon btn-manage" href="{$WWWROOT}artefact/plans/plan.php?id={$plan->id}">{str tag="managetasks" section="artefact.plans"}</a>
                 <a href="{$WWWROOT}artefact/plans/edit/index.php?id={$plan->id}" class="icon btn-big-edit" title="{str tag="edit"}" ></a>
                 <a href="{$WWWROOT}artefact/plans/delete/index.php?id={$plan->id}" class="icon btn-big-del" title="{str tag="delete"}"></a>
            </div>

            <h3><a href="{$WWWROOT}artefact/plans/plan.php?id={$plan->id}">{$plan->title}</a></h3>

        <div class="codesc">{$plan->description}</div>
        <div class="fl">
            <ul class="planslist">
                <li></li>
            </ul>
        </div>
        </td>
    </tr>
{/foreach}
