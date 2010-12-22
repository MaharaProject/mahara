{auto_escape on}
{foreach from=$tasks.data item=task}
    {if $task->completed == -1}
        <tr class="incomplete">
            <td class="c1">{$task->completiondate}</td>
            <td class="c2">{$task->title}</td>
            <td class="c3">{$task->description}</td>
            <td class="c4">&nbsp;</td>
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

    {/if}
            <td class="c5 buttonscell"><a href="{$WWWROOT}artefact/plans/edit/task.php?id={$task->task}" title="{str tag=edit}"><img src="{theme_url filename='images/edit.gif'}" alt="{str tag=edit}"></a>
            <a href="{$WWWROOT}artefact/plans/delete/task.php?id={$task->task}" title="{str tag=delete}"><img src="{theme_url filename='images/icon_close.gif'}" alt="{str tag=delete}"></a></td>
        </tr>
{/foreach}
{/auto_escape}
