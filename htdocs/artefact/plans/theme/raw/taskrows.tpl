        {foreach from=$tasks.data item=task}
        {if $task->completed == -1}
            <tr class="plan_incomplete">
                <td class="completiondate">{$task->completiondate}</td>
{if $task->description}
                <td class="plantasktitledescript"><a class="task-title" href="">{$task->title}</a>
                <div class="task-desc hidden">{$task->description}</div></td>
{else}
                <td class="plantasktitle">{$task->title}</td>
{/if}
                <td>&nbsp;</td>
            </tr>
        {else}
            <tr class="{cycle values='r0,r1'}">
                <td class="completiondate">{$task->completiondate}</td>
{if $task->description}
                <td class="plantasktitledescript"><a class="task-title" href="">{$task->title}</a>
                <div class="task-desc hidden" id="task-desc-{$task->id}">{$task->description}</div></td>
{else}
                <td class="plantasktitle">{$task->title}</td>
{/if}
                {if $task->completed == 1}
                    <td class="completed"><div class="plan_completed"><img src="{$WWWROOT}theme/raw/static/images/success.gif" alt="" /></div></td>
                {else}
                    <td>&nbsp;</td>
                {/if}
            </tr>
        {/if}
