{if $USERGROUPS}
<table id="usergroupstable" class="fullwidth">
{foreach from=$USERGROUPS item=item}
    <tr>
        <td class="{cycle values='r0,r1'}">
            <h4><a href="{group_homepage_url($item)}">{$item->name}</a><span class="roledisplay"> - {$item->roledisplay}</span></h4>
            {if $item->description}
                <div class="details">{$item->description|str_shorten_html:100:true|safe}</div>
            {/if}
        </td>
    </tr>
{/foreach}
</table>
{else}
    {str tag='notinanygroups' section='group'}
{/if}
