        {foreach from=$tasks.data item=task}
        {if $task->completed == -1}
            <tr class="plan_incomplete">
                <td>{$task->completiondate|escape}</td>
                <td>{$task->title|escape}<div>{$task->description|escape}</div></td>
                <td>&nbsp;</td>
            </tr>
        {else}
            <tr class="{cycle values='r0,r1'}">
                <td>{$task->completiondate|escape}</td>
                <td>{$task->title|escape}<div>{$task->description|escape}</div></td>
                {if $task->completed == 1}
                    <td><div class="plan_completed"><img src="{$WWWROOT}theme/raw/static/images/success.gif" alt="" /></div></td>
                {else}
                    <td>&nbsp;</td>
                {/if}
            </tr>
        {/if}
