{auto_escape on}
{foreach from=$tasks.data item=task}
    {if $task->completed == -1}
        <tr class="incomplete">
            <td>{$task->completiondate}</td>
            <td>{$task->title}</td>
            <td>{$task->description}</td>
            <td>&nbsp;</td>
            <td><a href="{$WWWROOT}artefact/plans/edit/task.php?id={$task->task}">Edit</a></td>
            <td><a href="{$WWWROOT}artefact/plans/delete/task.php?id={$task->task}">Delete</a></td>
        </tr>
    {else}
        <tr class="{cycle values='r0,r1'}">
            <td>{$task->completiondate}</td>
            <td>{$task->title}</td>
            <td>{$task->description}</td>
            {if $task->completed == 1}
                <td><div class="completed"><img src="{$WWWROOT}theme/raw/static/images/success.gif" alt="" /></div></td>
            {else}
                <td>&nbsp;</td>
            {/if}
            <td><a href="{$WWWROOT}artefact/plans/edit/task.php?id={$task->task}">Edit</a></td>
            <td><a href="{$WWWROOT}artefact/plans/delete/task.php?id={$task->task}">Delete</a></td>
        </tr>
    {/if}
{/foreach}
{/auto_escape}
