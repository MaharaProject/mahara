<table>
{foreach from=$items item=i}
<tr>
    <td>
        <img src="{theme_url filename=cat('images/' $i.type '.gif')}" />
    </td>
    <td>
    {if $i.url}<a href="{$i.url|escape}">{/if}
    {$i.subject|escape}
    {if $i.url}</a>{/if}
    </td>
</tr>
{/foreach}
</table>
{if $desiredtypes}
<a href="{$WWWROOT}account/activity?type={$desiredtypes|escape}">{str tag=More section=blocktype.inbox} &raquo;</a>
{/if}