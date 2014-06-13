{if $activities}
    <div class="activitystream">
        {foreach from=$activities item=activity}
            {include file="blocktype:activitystream:activity.tpl" activity=$activity}
        {/foreach}
    </div>
{else}
    <table class="fullwidth"><tr>
        <td align="center">{$noactivities|safe}</td>
    </tr></table>
{/if}
