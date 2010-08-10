{foreach from=$plans.data item=plan}
    <tr class="{cycle values='r0,r1'}">
        <td>
            <div class="fr">
                <ul class="groupuserstatus">
                    <li><a href="{$WWWROOT}artefact/plans/edit/index.php?id={$plan->id}" class="btn-edit">{str tag="edit"}</a></li>
                    <li><a href="{$WWWROOT}artefact/plans/delete/index.php?id={$plan->id}" class="btn-del">{str tag="delete"}</a></li>
                </ul>
            </div>

            <h3><a href="{$WWWROOT}artefact/plans/plan.php?id={$plan->id}">{$plan->title}</a></h3>

        <div class="codesc">{$plan->description}</div>
        <div class="fl">
            <ul class="planslist">
                <li><a href="{$WWWROOT}artefact/plans/plan.php?id={$plan->id}">{str tag="managetasks" section="artefact.plans"}</a></li>
            </ul>
        </td>
        </div>
    </tr>
{/foreach}
