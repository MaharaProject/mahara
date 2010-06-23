        {foreach from=$plans.data item=plan}
        {if $plan->completed == -1}
            <tr class="plan_incomplete">
                <td>{$plan->completiondate|escape}</td>
                <td>{$plan->title|escape}<div>{$plan->description|escape}</div></td>
                <td>&nbsp;</td>
            </tr>
        {else}
            <tr class="{cycle values='r0,r1'}">
                <td>{$plan->completiondate|escape}</td>
                <td>{$plan->title|escape}<div>{$plan->description|escape}</div></td>
                {if $plan->completed == 1}
                    <td><div class="plan_completed"><img src="{$WWWROOT}theme/raw/static/images/success.gif" alt="" /></div></td>
                {else}
                    <td>&nbsp;</td>
                {/if}
            </tr>
        {/if}
