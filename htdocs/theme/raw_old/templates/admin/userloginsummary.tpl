{if $data}
    {foreach from=$data item=institution}
    <tr class="{cycle values='r0,r1'}">
        {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
        {if $columns.institution}<td><a href="{$WWWROOT}institution/index.php?institution={$institution->name}">{$institution->displayname}</a></td>{/if}
        {if $columns.logins}<td>{$institution->count_logins}</td>{/if}
        {if $columns.activeusers}<td>{$institution->count_active}</td>{/if}
    </tr>
    {/foreach}
{/if}
