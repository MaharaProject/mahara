<table>
{foreach from=$items item=i}
<tr>
    <td>
        <img src="{theme_url filename=cat('images/' $i.type '.gif')}" />
    </td>
    <td>
    {if $i.url}<a href="{$i.url}">{/if}
    {$i.subject}
    {if $i.url}</a>{/if}
    </td>
</tr>
{/foreach}
</table>
<a href="{$WWWROOT}account/activity" target="_blank">{str tag=gotoinbox section=mahara} &raquo;</a>
