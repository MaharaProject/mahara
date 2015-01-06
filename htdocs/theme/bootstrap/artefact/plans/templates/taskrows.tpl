        {foreach from=$tasks.data item=task}
        {if $task->completed == -1}
            <tr class="plan_incomplete">
                <td class="c1 completiondate">{$task->completiondate}</td>
{if $task->description}
                <td class="plantasktitledescript"><a class="task-title" href="">{$task->title}</a>
                <div class="task-desc hidden">{$task->description|clean_html|safe}</div></td>
{else}
                <td class="plantasktitle">{$task->title}</td>
{/if}
                <td class="c3 incomplete"><img src="{$WWWROOT}theme/raw/static/images/failure_small.png" alt="{str tag=overdue section=artefact.plans}" /></td>
            </tr>
        {else}
            <tr class="{cycle values='r0,r1'}">
                <td class="c1 completiondate">{$task->completiondate}</td>
{if $task->description}
                <td class="plantasktitledescript"><a class="task-title" href="">{$task->title}</a>
                <div class="task-desc hidden" id="task-desc-{$task->id}">{$task->description|clean_html|safe}</div></td>
{else}
                <td class="plantasktitle">{$task->title}</td>
{/if}
                {if $task->completed == 1}
                    <td class="c3 completed"><img src="{$WWWROOT}theme/raw/static/images/success_small.png" alt="{str tag=completed section=artefact.plans}" /></td>
                {else}
                    <td><span class="accessible-hidden">{str tag=incomplete section=artefact.plans}</span></td>
                {/if}
            </tr>
        {/if}
