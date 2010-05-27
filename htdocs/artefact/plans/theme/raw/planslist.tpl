{auto_escape on}
{foreach from=$plans->data item=plan}
    {if $plan->completed == -1}
        <tr class="incomplete">
            <td>{$plan->completiondate}</td>
            <td>{$plan->title}</td>
            <td>{$plan->description}</td>
            <td>&nbsp;</td>
            <td><a href="/artefact/plans/edit.php?plan={$plan->plan}">Edit</a></td>
            <td><a href="/artefact/plans/delete.php?plan={$plan->plan}">Delete</a></td>
        </tr>
    {else}
        <tr class="{cycle values='r0,r1'}">
            <td>{$plan->completiondate}</td>
            <td>{$plan->title}</td>
            <td>{$plan->description}</td>
            {if $plan->completed == 1}
                <td><div class="completed"><img src="{$WWWROOT}theme/raw/static/images/success.gif" alt="" /></div></td>
            {else}
                <td>&nbsp;</td>
            {/if}
            <td><a href="/artefact/plans/edit.php?plan={$plan->plan}">Edit</a></td>
            <td><a href="/artefact/plans/delete.php?plan={$plan->plan}">Delete</a></td>
        </tr>
    {/if}
{/foreach}
{/auto_escape}
