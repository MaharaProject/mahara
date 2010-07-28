{auto_escape on}
        {foreach from=$tasks.data item=task}
        {if $task->completed == -1}
            <tr class="plan_incomplete">
                <td class="c1">{$task->completiondate|safe}</td>
                <td class="c2">{$task->title|safe}</td>
                <td class="c3">&nbsp;</td>
            </tr>
        {else}
            <tr class="{cycle values='r0,r1'}">
                <td class="c1">{$task->completiondate|safe}</td>
                <td class="c2">{$task->title|safe}</td>
                {if $task->completed == 1}
                    <td class="c3"><div class="plan_completed"><img src="{$WWWROOT}theme/raw/static/images/success.gif" alt="" /></div></td>
                {else}
                    <td class="c3">&nbsp;</td>
                {/if}
            </tr>
        {/if}
{auto_escape off}
