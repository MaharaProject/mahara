{auto_escape on}
{foreach from=$tasks.data item=task}
    {if $task->completed == -1}
        <tr class="incomplete">
            <td class="c1">{$task->completiondate}</td>
            <td class="c2">{$task->title}</td>
            <td class="c3">{$task->description}</td>
            <td class="c4">&nbsp;</td>
            <td class="c5"><a href="{$WWWROOT}artefact/plans/edit/task.php?id={$task->task}">{str tag=edit}</a></td>
            <td class="c6"><a href="{$WWWROOT}artefact/plans/delete/task.php?id={$task->task}">{str tag=delete}</a></td>
        </tr>
    {else}
        <tr class="{cycle values='r0,r1'}">
            <td class="c1">{$task->completiondate}</td>
            <td class="c2">{$task->title}</td>
            <td class="c3">{$task->description}</td>
            {if $task->completed == 1}
                <td class="c4"><div class="completed"><img src="{$WWWROOT}theme/raw/static/images/success.gif" alt="" /></div></td>
            {else}
                <td class="c4">&nbsp;</td>
            {/if}
            <td class="c5"><a href="{$WWWROOT}artefact/plans/edit/task.php?id={$task->task}">Edit</a></td>
            <td class="c6"><a href="{$WWWROOT}artefact/plans/delete/task.php?id={$task->task}">Delete</a></td>
        </tr>
    {/if}
{/foreach}
{/auto_escape}
