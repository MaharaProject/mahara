{if $USERGROUPS}
<table id="usergroupstable" class="fullwidth">
{foreach from=$USERGROUPS item=item}
    <tr>
        <td class="{cycle values='r0,r1'}">
            <h4><a href="{$WWWROOT}group/view.php?id={$item->id}">{$item->name}</a> - {$item->roledisplay}</h4>
            {if $item->description}
                {$item->description|str_shorten_html:100:true|safe}
            {/if}
        </td>
    </tr>
{/foreach}
</table>
{else}
    {str tag='notinanygroups' section='group'}
{/if}
