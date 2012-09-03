{foreach from=$tasks.data item=task}
    {if $task->completed == -1}
        <tr class="incomplete">
            <td class="completiondate">{$task->completiondate}</td>
            <td class="plantasktitle">{$task->title}</td>
            <td class="plantaskdescription">{$task->description}</td>
            <td>&nbsp;</td>
    {else}
        <tr class="{cycle values='r0,r1'}">
            <td class="completiondate">{$task->completiondate}</td>
            <td class="plantasktitle">{$task->title}</td>
            <td class="plantaskdescription">{$task->description}</td>
            {if $task->completed == 1}
                <td class="completed"><img src="{$WWWROOT}theme/raw/static/images/success.gif" alt="" /></td>
            {else}
                <td>&nbsp;</td>
            {/if}

    {/if}
            <td class="buttonscell btns2 planscontrols"><a href="{$WWWROOT}artefact/plans/edit/task.php?id={$task->task}" title="{str tag=edit}"><img src="{theme_url filename='images/edit.gif'}" alt="{str tag=edit}"></a>
            <a href="{$WWWROOT}artefact/plans/delete/task.php?id={$task->task}" title="{str tag=delete}"><img src="{theme_url filename='images/icon_close.gif'}" alt="{str tag=delete}"></a></td>
        </tr>
{/foreach}
