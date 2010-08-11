        {foreach from=$tasks.data item=task}
        {if $task->completed == -1}
            <tr class="plan_incomplete">
                <td class="c1">{$task->completiondate}</td>
{if $task->description}
                <td class="c2"><a class="task-title" href="">{$task->title}</a>
                <div class="task-desc hidden">{$task->description}</div></td>
{else}
                <td class="c2">{$task->title}</td>
{/if}
                <td class="c3">&nbsp;</td>
            </tr>
        {else}
            <tr class="{cycle values='r0,r1'}">
                <td class="c1">{$task->completiondate}</td>
{if $task->description}
                <td class="c2"><a class="task-title" href="">{$task->title}</a>
                <div class="task-desc hidden" id="task-desc-{$task->id}">{$task->description}</div></td>
{else}
                <td class="c2">{$task->title}</td>
{/if}
                {if $task->completed == 1}
                    <td class="c3"><div class="plan_completed"><img src="{$WWWROOT}theme/raw/static/images/success.gif" alt="" /></div></td>
                {else}
                    <td class="c3">&nbsp;</td>
                {/if}
            </tr>
        {/if}
